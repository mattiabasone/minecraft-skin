<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Flat;

use MattiaBasone\MinecraftSkin\Exception\ImageCreateFromPngFailedException;
use MattiaBasone\MinecraftSkin\Exception\ImageResourceCreationFailedException;

abstract class ImageSection
{
    protected \GdImage $skinResource;

    protected int $skinWidth;

    protected int $skinHeight;
    /**
     * Resource with the image.
     */
    protected ?\GdImage $imgResource = null;

    /**
     * @throws ImageCreateFromPngFailedException
     */
    public function __construct(protected string $skinPath)
    {
        $this->skinResource = $this->createImageFromPng($this->skinPath);
        $this->skinWidth = imagesx($this->skinResource);
        $this->skinHeight = imagesy($this->skinResource);
    }

    public function __toString(): string
    {
        return $this->toPng();
    }

    /**
     * PNG-encode the generated image.
     *
     * @param int<-1, 9> $compressionLevel Zlib compression level. Defaults to PHP's own default (-1).
     *                                     Pass 0 for fast, uncompressed encoding when the blob is only
     *                                     used as an in-memory handoff (e.g. to Imagick) and never stored.
     */
    public function toPng(int $compressionLevel = -1): string
    {
        if (\is_null($this->imgResource)) {
            return "";
        }

        ob_start();
        imagepng($this->imgResource, null, $compressionLevel);

        return (string) ob_get_clean();
    }

    public function is64x64(): bool
    {
        return $this->skinWidth === 64 && $this->skinHeight === 64;
    }

    /**
     * Get generated resource image.
     */
    public function getResource(): ?\GdImage
    {
        return $this->imgResource;
    }

    /**
     * @throws ImageCreateFromPngFailedException
     */
    protected function createImageFromPng(string $path): \GdImage
    {
        $resource = imagecreatefrompng($path);
        if ($resource === false) {
            throw new ImageCreateFromPngFailedException("Cannot create png image from file {$path}");
        }

        return $resource;
    }

    /**
     * @param int<1, max> $width
     * @param int<1, max> $height
     * @throws ImageResourceCreationFailedException
     */
    protected function emptyBaseImage(int $width, int $height): \GdImage
    {
        $tmpImageResource = imagecreatetruecolor($width, $height);
        if ($tmpImageResource === false) {
            throw new ImageResourceCreationFailedException('imagecreatetruecolor() failed');
        }
        imagealphablending($tmpImageResource, false);
        imagesavealpha($tmpImageResource, true);
        $transparent = (int) imagecolorallocatealpha($tmpImageResource, 255, 255, 255, 127);
        imagefilledrectangle($tmpImageResource, 0, 0, $width, $height, $transparent);

        return $tmpImageResource;
    }
}
