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
        int $destinationX,
        int $destinationY,
        int $sourceX,
        int $sourceY,
        int $sourceWidth,
        int $sourceHeight,
        int $mergePercentage
    ): void {
        // creating a cut resource
        $cut = imagecreatetruecolor($sourceWidth, $sourceHeight);

        if ($cut instanceof \GdImage === false) {
            throw new ImageTrueColorCreationFailedException();
        }

        // copying relevant section from background to the cut resource
        imagecopy(
            dst_image: $cut,
            src_image: $destinationImage,
            dst_x: 0,
            dst_y: 0,
            src_x: $destinationX,
            src_y: $destinationY,
            src_width: $sourceWidth,
            src_height: $sourceHeight
        );

        // copying relevant section from watermark to the cut resource
        imagecopy(
            dst_image: $cut,
            src_image: $sourceImage,
            dst_x: 0,
            dst_y: 0,
            src_x: $sourceX,
            src_y: $sourceY,
            src_width: $sourceWidth,
            src_height: $sourceHeight
        );

        // insert cut resource to destination image
        imagecopymerge(
            dst_image: $destinationImage,
            src_image: $cut,
            dst_x: $destinationX,
            dst_y: $destinationY,
            src_x: 0,
            src_y: 0,
            src_width: $sourceWidth,
            src_height: $sourceHeight,
            pct: $mergePercentage
        );
    }
}
