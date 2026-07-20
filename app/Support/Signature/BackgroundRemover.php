<?php

namespace App\Support\Signature;

use Imagick;
use ImagickPixel;

/**
 * Strips the background from a photographed/scanned signature so it can be composited
 * onto a document. This is a luminance-threshold alpha mask, not ML-based segmentation:
 * pixels lighter than $lightThreshold become fully transparent, pixels darker than
 * $darkThreshold stay fully opaque, and everything between gets a linear alpha ramp so
 * ink strokes keep smooth edges. Works well for a dark signature on light/white paper;
 * it will not cleanly separate a signature from colored paper or very faint ink.
 */
class BackgroundRemover
{
    public function __construct(
        protected float $darkThreshold = 0.39,
        protected float $lightThreshold = 0.92,
    ) {}

    public function process(string $sourcePath, string $destinationPath): void
    {
        $image = new Imagick($sourcePath);
        $image->setImageFormat('png');
        $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);

        $iterator = $image->getPixelIterator();

        foreach ($iterator as $row) {
            /** @var ImagickPixel $pixel */
            foreach ($row as $pixel) {
                // Normalized (0-1) color values, so this is correct regardless of the
                // build's quantum depth (Q8 vs Q16).
                $colors = $pixel->getColor(true);
                $luminance = (0.299 * $colors['r']) + (0.587 * $colors['g']) + (0.114 * $colors['b']);

                $alpha = match (true) {
                    $luminance <= $this->darkThreshold => 1.0,
                    $luminance >= $this->lightThreshold => 0.0,
                    default => 1.0 - (($luminance - $this->darkThreshold) / ($this->lightThreshold - $this->darkThreshold)),
                };

                $pixel->setColorValue(Imagick::COLOR_ALPHA, $alpha);
            }

            $iterator->syncIterator();
        }

        $image->setImageFormat('png');
        $image->writeImage($destinationPath);
        $image->clear();
        $image->destroy();
    }
}
