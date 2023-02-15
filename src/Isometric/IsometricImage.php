<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Isometric;

interface IsometricImage
{
    const DEFAULT_BLUR_VALUE = 0.9;
    const DEFAULT_RESIZE_FILTER = \Imagick::FILTER_LANCZOS2;

    public function render(int $size): \Imagick;
}