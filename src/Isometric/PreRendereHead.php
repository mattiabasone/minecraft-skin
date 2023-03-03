<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Isometric;

readonly class PreRendereHead implements IsometricImage
{
    public function __construct(private string $preRenderedImagePath)
    {
    }

    /**
     * @throws \ImagickException
     */
    public function render(int $size): \Imagick
    {
        $head = new \Imagick($this->preRenderedImagePath);
        $head->setImageFormat('png');
        $head->resizeImage($size, $size, self::DEFAULT_RESIZE_FILTER, self::DEFAULT_BLUR_VALUE);

        return $head;
    }
}