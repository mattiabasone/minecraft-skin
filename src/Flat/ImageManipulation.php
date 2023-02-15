<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Flat;

use MattiaBasone\MinecraftSkin\Exception\ImageTrueColorCreationFailedException;

trait ImageManipulation
{
    /**
     * @see https://www.php.net/manual/en/function.imagecopymerge.php#92787
     * @throws ImageTrueColorCreationFailedException
     */
    protected function imageCopyMergeAlpha(
        \GdImage $destinationImage,
        \GdImage $sourceImage,
        int      $destinationX,
        int      $destinationY,
        int      $sourceX,
        int      $sourceY,
        int      $sourceWidth,
        int      $sourceHeight,
        int      $mergePercentage
    ): void {
        // creating a cut resource
        $cut = imagecreatetruecolor($sourceWidth, $sourceHeight);

        if ($cut instanceof \GdImage === false) {
            throw new ImageTrueColorCreationFailedException();
        }

        // copying relevant section from background to the cut resource
        imagecopy($cut, $destinationImage, 0, 0, $destinationX, $destinationY, $sourceWidth, $sourceHeight);

        // copying relevant section from watermark to the cut resource
        imagecopy($cut, $sourceImage, 0, 0, $sourceX, $sourceY, $sourceWidth, $sourceHeight);

        // insert cut resource to destination image
        imagecopymerge($destinationImage, $cut, $destinationX, $destinationY, 0, 0, $sourceWidth, $sourceHeight, $mergePercentage);
    }
}
