<?php

namespace App\Support\ESignature;

use Imagick;
use ImagickException;

/**
 * PDF <-> image round trip for the e-signature flow, backed entirely by the `imagick`
 * PHP extension and system Ghostscript (both already installed — no PDF Composer package
 * needed). Coordinates for signature placement are fractions (0-1) of the target page's
 * dimensions, so they're independent of whatever DPI a given render happens to use.
 */
class PdfSigning
{
    protected const RESOLUTION = 150;

    /**
     * @throws ImagickException
     */
    public function pageCount(string $pdfPath): int
    {
        $image = new Imagick;
        $image->pingImage($pdfPath);
        $count = $image->getNumberImages();
        $image->clear();
        $image->destroy();

        return $count;
    }

    /**
     * Rasterize a single page (0-indexed) to a PNG blob, for on-screen display during signing.
     */
    public function rasterizePage(string $pdfPath, int $pageNumber): string
    {
        $image = new Imagick;
        $image->setResolution(self::RESOLUTION, self::RESOLUTION);
        $image->readImage("{$pdfPath}[{$pageNumber}]");
        $image->setImageFormat('png');
        $blob = $image->getImageBlob();
        $image->clear();
        $image->destroy();

        return $blob;
    }

    /**
     * Rasterize every page of the source PDF, composite the (already transparent)
     * signature image onto the target page at the given fractional coordinates, then
     * reassemble every page — signed and untouched — into one new PDF at $destinationPath.
     */
    public function signAndReassemble(
        string $sourcePdfPath,
        string $signatureImagePath,
        int $signPageNumber,
        float $x,
        float $y,
        float $width,
        float $height,
        string $destinationPath,
    ): void {
        $pageCount = $this->pageCount($sourcePdfPath);
        $output = new Imagick;

        for ($page = 0; $page < $pageCount; $page++) {
            $pageImage = new Imagick;
            $pageImage->setResolution(self::RESOLUTION, self::RESOLUTION);
            $pageImage->readImage("{$sourcePdfPath}[{$page}]");
            $pageImage->setImageFormat('png');

            if ($page === $signPageNumber) {
                $pageWidth = $pageImage->getImageWidth();
                $pageHeight = $pageImage->getImageHeight();

                $signature = new Imagick($signatureImagePath);
                $signature->setImageFormat('png');
                $signature->resizeImage(
                    max(1, (int) round($width * $pageWidth)),
                    max(1, (int) round($height * $pageHeight)),
                    Imagick::FILTER_LANCZOS,
                    1,
                );

                $pageImage->compositeImage(
                    $signature,
                    Imagick::COMPOSITE_OVER,
                    (int) round($x * $pageWidth),
                    (int) round($y * $pageHeight),
                );

                $signature->clear();
                $signature->destroy();
            }

            $output->addImage($pageImage);
            $pageImage->clear();
            $pageImage->destroy();
        }

        $output->setImageFormat('pdf');
        $output->writeImages($destinationPath, true);
        $output->clear();
        $output->destroy();
    }
}
