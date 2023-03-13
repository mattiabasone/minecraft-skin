<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Isometric;

use MattiaBasone\MinecraftSkin\Component\Side;
use MattiaBasone\MinecraftSkin\Exception\ImageCreateFromPngFailedException;
use MattiaBasone\MinecraftSkin\Exception\ImageResourceCreationFailedException;
use MattiaBasone\MinecraftSkin\Exception\ImageTrueColorCreationFailedException;
use MattiaBasone\MinecraftSkin\Flat\Avatar as FlatAvatar;

class Head implements IsometricImage
{
    /**
     * Cosine PI/6.
     */
    private const COSINE_PI_6 = \M_SQRT3 / 2;

    private const HEAD_BASE_SIZE = 512;

    public function __construct(private readonly string $rawSkinImagePath)
    {
    }

    /**
     * @throws ImageCreateFromPngFailedException
     * @throws ImageResourceCreationFailedException
     * @throws ImageTrueColorCreationFailedException
     * @throws \ImagickException
     */
    public function render(int $size): \Imagick
    {
        $head = $this->renderFullSize();
        // Set format to PNG
        $head->setImageFormat('png');
        $head->resizeImage($size, $size, self::DEFAULT_RESIZE_FILTER, self::DEFAULT_BLUR_VALUE);

        return $head;
    }

    /**
     * Get ImagickPixel transparent object.
     */
    final protected function getImagickPixelTransparent(): \ImagickPixel
    {
        return new \ImagickPixel('transparent');
    }

    /**
     * Render Isometric from avatar sections.
     *
     * @throws ImageCreateFromPngFailedException
     * @throws ImageResourceCreationFailedException
     * @throws ImageTrueColorCreationFailedException
     * @throws \ImagickException
     */
    protected function renderFullSize(): \Imagick
    {
        $avatar = new FlatAvatar($this->rawSkinImagePath);

        // Face
        $avatar->render(self::HEAD_BASE_SIZE, Side::FRONT);

        $face = new \Imagick();
        $face->readImageBlob((string) $avatar);
        $face->brightnessContrastImage(8.0, 8.0);
        $face->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
        $face->setBackgroundColor(
            $this->getImagickPixelTransparent()
        );

        $face->distortImage(
            \Imagick::DISTORTION_AFFINE,
            $this->getFrontPoints(),
            true
        );

        // Top
        $avatar->render(self::HEAD_BASE_SIZE, Side::TOP);

        $top = new \Imagick();
        $top->readImageBlob((string) $avatar);
        $top->brightnessContrastImage(6.0, 6.0);
        $top->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
        $top->setBackgroundColor(
            $this->getImagickPixelTransparent()
        );

        $top->distortImage(
            \Imagick::DISTORTION_AFFINE,
            $this->getTopPoints(),
            true
        );

        // Right
        $avatar->render(self::HEAD_BASE_SIZE, Side::RIGHT);

        $right = new \Imagick();
        $right->readImageBlob((string) $avatar);
        $right->brightnessContrastImage(4.0, 4.0);

        $right->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
        $right->setBackgroundColor(
            $this->getImagickPixelTransparent()
        );

        $right->distortImage(
            \Imagick::DISTORTION_AFFINE,
            $this->getRightPoints(),
            true
        );

        // Head image
        $doubleAvatarSize = self::HEAD_BASE_SIZE * 2;
        $finalImageSize = $doubleAvatarSize + 2;

        $head = new \Imagick();
        $head->newImage($finalImageSize, $finalImageSize, $this->getImagickPixelTransparent());

        // This is weird, but it works
        $faceX = ((int) round($doubleAvatarSize / 2)) - 5;
        $faceY = $rightY = ((int) round($doubleAvatarSize / 4));
        $topX = $rightX = ((int) round($doubleAvatarSize / 16));
        $topY = 0;

        // Add Face Section
        $head->compositeimage($face->getimage(), \Imagick::COMPOSITE_PLUS, $faceX, $faceY);

        // Add Top Section
        $head->compositeimage($top->getimage(), \Imagick::COMPOSITE_PLUS, $topX, $topY);

        // Add Right Section
        $head->compositeimage($right->getimage(), \Imagick::COMPOSITE_PLUS, $rightX, $rightY);

        return $head;
    }

    /**
     * Point for face section.
     */
    private function getFrontPoints(): array
    {
        $size = self::HEAD_BASE_SIZE;
        $cosine_result = round(self::COSINE_PI_6 * $size);
        $halfSize = round($size / 2);

        return [
            0, 0, 0, 0,
            0, $size, 0, $size,
            $size, 0, -$cosine_result, $halfSize,
        ];
    }

    /**
     * Points for top section.
     */
    private function getTopPoints(): array
    {
        $size = self::HEAD_BASE_SIZE;
        $cosineResult = round(self::COSINE_PI_6 * $size);
        $halfSize = round($size / 2);

        return [
            0, $size, 0, 0,
            0, 0, -$cosineResult, -$halfSize,
            $size, $size, $cosineResult, -$halfSize,
        ];
    }

    /**
     * Points for right section.
     */
    private function getRightPoints(): array
    {
        $size = self::HEAD_BASE_SIZE;
        $cosineResult = round(self::COSINE_PI_6 * $size);
        $halfSize = round($size / 2);

        return [
            $size, 0, 0, 0,
            0, 0, -$cosineResult, -$halfSize,
            $size, $size, 0, $size,
        ];
    }
}
