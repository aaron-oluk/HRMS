<?php

use App\Support\ESignature\PdfSigning;
use App\Support\Signature\BackgroundRemover;

function makeSyntheticSignature(string $path): void
{
    $image = new Imagick;
    $image->newImage(200, 100, new ImagickPixel('white'));
    $image->setImageFormat('png');
    $draw = new ImagickDraw;
    $draw->setStrokeColor(new ImagickPixel('black'));
    $draw->setStrokeWidth(6);
    $draw->line(20, 80, 180, 20);
    $image->drawImage($draw);
    $image->writeImage($path);
    $image->clear();
    $image->destroy();
}

function makeSyntheticPdf(string $path, int $pages = 2): void
{
    $document = new Imagick;
    for ($i = 0; $i < $pages; $i++) {
        $page = new Imagick;
        $page->newImage(400, 300, new ImagickPixel('white'));
        $page->setImageFormat('png');
        $document->addImage($page);
        $page->clear();
        $page->destroy();
    }
    $document->setImageFormat('pdf');
    $document->writeImages($path, true);
    $document->clear();
    $document->destroy();
}

test('background remover makes the background transparent and keeps the ink opaque', function () {
    $rawPath = tempnam(sys_get_temp_dir(), 'sig').'.png';
    $transparentPath = tempnam(sys_get_temp_dir(), 'sig').'.png';
    makeSyntheticSignature($rawPath);

    (new BackgroundRemover)->process($rawPath, $transparentPath);

    $result = new Imagick($transparentPath);
    $whiteCornerAlpha = $result->getImagePixelColor(2, 2)->getColorValue(Imagick::COLOR_ALPHA);
    $strokeAlpha = $result->getImagePixelColor(100, 50)->getColorValue(Imagick::COLOR_ALPHA);

    expect($whiteCornerAlpha)->toBeLessThan(0.1);
    expect($strokeAlpha)->toBeGreaterThan(0.9);

    unlink($rawPath);
    unlink($transparentPath);
});

test('signing a pdf composites the signature onto the target page and preserves every page', function () {
    $pdfPath = tempnam(sys_get_temp_dir(), 'doc').'.pdf';
    $rawSignaturePath = tempnam(sys_get_temp_dir(), 'sig').'.png';
    $transparentSignaturePath = tempnam(sys_get_temp_dir(), 'sig').'.png';
    $signedPath = tempnam(sys_get_temp_dir(), 'signed').'.pdf';

    makeSyntheticPdf($pdfPath, pages: 2);
    makeSyntheticSignature($rawSignaturePath);
    (new BackgroundRemover)->process($rawSignaturePath, $transparentSignaturePath);

    $pdfSigning = new PdfSigning;
    expect($pdfSigning->pageCount($pdfPath))->toBe(2);

    $pdfSigning->signAndReassemble(
        sourcePdfPath: $pdfPath,
        signatureImagePath: $transparentSignaturePath,
        signPageNumber: 0,
        x: 0.1,
        y: 0.1,
        width: 0.3,
        height: 0.15,
        destinationPath: $signedPath,
    );

    expect(file_exists($signedPath))->toBeTrue();
    expect($pdfSigning->pageCount($signedPath))->toBe(2);

    $signedFirstPage = new Imagick;
    $signedFirstPage->setResolution(150, 150);
    $signedFirstPage->readImage("{$signedPath}[0]");
    $width = $signedFirstPage->getImageWidth();
    $height = $signedFirstPage->getImageHeight();
    $signedFirstPage->cropImage((int) (0.3 * $width), (int) (0.15 * $height), (int) (0.1 * $width), (int) (0.1 * $height));

    $stats = $signedFirstPage->getImageChannelStatistics()[Imagick::CHANNEL_RED];
    // A pristine white crop would have minima == maxima == full brightness; the composited
    // ink stroke pulls the minimum down and introduces real variation.
    expect($stats['minima'])->toBeLessThan($stats['maxima']);

    unlink($pdfPath);
    unlink($rawSignaturePath);
    unlink($transparentSignaturePath);
    unlink($signedPath);
});
