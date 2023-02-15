<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Flat;

use MattiaBasone\MinecraftSkin\Exception\ImageCreateFromPngFailedException;

class Raw extends ImageSection
{
    /**
     * @throws ImageCreateFromPngFailedException
     */
    public function render(): void
    {
        $this->imgResource = $this->createImageFromPng($this->skinPath);
        imagealphablending($this->imgResource, true);
        imagesavealpha($this->imgResource, true);
    }
}
