<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Flat;

use MattiaBasone\MinecraftSkin\Component\Component;
use MattiaBasone\MinecraftSkin\Exception\ImageCreateFromPngFailedException;
use MattiaBasone\MinecraftSkin\Exception\ImageResourceCreationFailedException;
use MattiaBasone\MinecraftSkin\Exception\ImageTrueColorCreationFailedException;

class Avatar extends ImageSection
{
    use ImageManipulation;

    /**
     * Render avatar image.
     *
     * @param int $size Avatar size
     * @param string $type Section rendered
     *
     * @throws ImageCreateFromPngFailedException
     * @throws ImageResourceCreationFailedException
     * @throws ImageTrueColorCreationFailedException
     */
    public function render(int $size, string $type): void
    {
        // generate png from url/path
        $baseSkinImage = $this->createImageFromPng($this->skinPath);
        imagealphablending($baseSkinImage, false);
        imagesavealpha($baseSkinImage, true);

        // Sections Coordinates
        $headSide = Component::getHead()->getSideByIdentifier($type);
        $helmSide = Component::getHeadLayer()->getSideByIdentifier($type);

        $tmpImageResource = $this->emptyBaseImage($headSide->getWidth(), $headSide->getHeight());
        imagecopy($tmpImageResource, $baseSkinImage, 0, 0, $headSide->getTopLeft()->getX(), $headSide->getTopLeft()->getY(), $headSide->getWidth(), $headSide->getHeight());

        // if all pixel have transparency or the colors are not the same
        if ((new LayerValidator())->check($baseSkinImage, $helmSide)) {
            $this->imageCopyMergeAlpha(
                $tmpImageResource,
                $baseSkinImage,
                0,
                0,
                $helmSide->getTopLeft()->getX(),
                $helmSide->getTopLeft()->getY(),
                $headSide->getWidth(),
                $headSide->getHeight(),
                100
            );
        }

        $this->imgResource = $this->emptyBaseImage($size, $size);
        imagecopyresized($this->imgResource, $tmpImageResource, 0, 0, 0, 0, $size, $size, $headSide->getWidth(), $headSide->getHeight());
    }
}
