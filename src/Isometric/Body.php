<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Isometric;

use MattiaBasone\MinecraftSkin\Component\Component;
use MattiaBasone\MinecraftSkin\Component\Side;
use MattiaBasone\MinecraftSkin\Exception\ImageCreateFromPngFailedException;
use MattiaBasone\MinecraftSkin\Flat\Avatar as FlatAvatar;

/**
 * Isometric view of the entire skin (head with hat layer, torso, arms and legs).
 *
 * The returned image height is the requested size, the width follows the isometric aspect ratio.
 *
 * Axes (in skin pixels): X goes from the character's right to its left, Y goes down,
 * Z goes from back to front. Visible faces are the right side, the front and the top.
 */
class Body implements IsometricImage
{
    private const float COSINE_PI_6 = \M_SQRT3 / 2.0;

    private const float OVERLAY_INFLATE = 0.25;

    private const int MIN_SCALE = 4;
    private const int MAX_SCALE = 16;

    /**
     * Approximate height of the rendered body in skin pixels, used to pick the texture scale.
     */
    private const int APPROX_HEIGHT = 44;

    public function __construct(private readonly string $rawSkinImagePath)
    {
    }

    /**
     * @throws ImageCreateFromPngFailedException
     * @throws \ImagickException
     */
    #[\Override]
    public function render(int $size): \Imagick
    {
        if ($size < 1) {
            throw new \InvalidArgumentException('size must be greater than 0');
        }

        $scale = max(self::MIN_SCALE, min(self::MAX_SCALE, (int) ceil($size / self::APPROX_HEIGHT)));

        $skin = new \Imagick($this->rawSkinImagePath);
        $skin->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);
        $modern = $skin->getImageWidth() === 64 && $skin->getImageHeight() === 64;

        // Parts as [component, x, y, z, width, height, depth, mirrored], drawn from the farthest to the nearest
        $slim = $modern && $this->isSlim($skin);
        $aw = $slim ? 3 : 4;
        $leftArm = $modern ? Component::getLeftArm() : Component::getRightArm();
        $leftLeg = $modern ? Component::getLeftLeg() : Component::getRightLeg();
        $parts = [
            [$leftArm, 12, 8, 2, $aw, 12, 4, !$modern, $modern ? Component::getLeftArmLayer() : null],
            [$leftLeg, 8, 20, 2, 4, 12, 4, !$modern, $modern ? Component::getLeftLegLayer() : null],
            [Component::getRightLeg(), 4, 20, 2, 4, 12, 4, false, $modern ? Component::getRightLegLayer() : null],
            [Component::getTorso(), 4, 8, 2, 8, 12, 4, false, $modern ? Component::getTorsoLayer() : null],
            [Component::getRightArm(), $slim ? 1 : 0, 8, 2, $aw, 12, 4, false, $modern ? Component::getRightArmLayer() : null],
        ];

        $layers = [];
        foreach ($parts as $index => [$component, $x, $y, $z, $w, $h, $d, $mirrored, $overlay]) {
            $isArm = $index === 0 || $index === 4;
            $cw = $isArm && $slim ? 3 : null;
            // The inner face of the far limbs is hidden by the torso / near leg
            $hiddenRight = $index < 2;
            if (!$hiddenRight) {
                $layers[] = $this->distortFace($skin, $component, Side::RIGHT, $mirrored, $scale, $x, $y, $z, [0, 0, 1], [0, 1, 0], $d, $h);
            }
            $layers[] = $this->distortFace($skin, $component, Side::FRONT, $mirrored, $scale, $x, $y, $z + $d, [1, 0, 0], [0, 1, 0], $w, $h, false, $cw);
            $layers[] = $this->distortFace($skin, $component, Side::TOP, $mirrored, $scale, $x, $y, $z, [1, 0, 0], [0, 0, 1], $w, $d, true, $cw);

            if ($overlay !== null) {
                // Second layer: slightly larger than the base part so it does not clip into it
                $e = self::OVERLAY_INFLATE;
                if (!$hiddenRight) {
                    $layers[] = $this->distortFace($skin, $overlay, Side::RIGHT, false, $scale, $x - $e, $y - $e, $z - $e, [0, 0, 1], [0, 1, 0], $d + 2 * $e, $h + 2 * $e);
                }
                $layers[] = $this->distortFace($skin, $overlay, Side::FRONT, false, $scale, $x - $e, $y - $e, $z + $d + $e, [1, 0, 0], [0, 1, 0], $w + 2 * $e, $h + 2 * $e, false, $cw);
                $layers[] = $this->distortFace($skin, $overlay, Side::TOP, false, $scale, $x - $e, $y - $e, $z - $e, [1, 0, 0], [0, 0, 1], $w + 2 * $e, $d + 2 * $e, true, $cw);
            }
        }

        // Head (with hat layer) uses the flat avatar renderer
        $avatar = new FlatAvatar($this->rawSkinImagePath);
        foreach ([[Side::RIGHT, [0, 0, 1], [0, 1, 0], 4, 0, 0], [Side::FRONT, [1, 0, 0], [0, 1, 0], 4, 0, 8], [Side::TOP, [1, 0, 0], [0, 0, 1], 4, 0, 0]] as [$side, $u, $v, $x, $y, $z]) {
            $avatar->render(8 * $scale, $side);
            $face = new \Imagick();
            $face->readImageBlob($avatar->toPng(0));
            $layers[] = $this->distort($face, $scale, $x, $y, $z, $u, $v, 8, 8, $side === Side::TOP);
        }

        return $this->compose($layers, $size);
    }

    /**
     * Slim (3px) arms leave the 4th column of the arm front fully transparent.
     */
    private function isSlim(\Imagick $skin): bool
    {
        foreach ([[55, 20], [47, 52]] as [$x, $startY]) {
            for ($y = $startY; $y < $startY + 12; ++$y) {
                if ($skin->getImagePixelColor($x, $y)->getColorValue(\Imagick::COLOR_ALPHA) > 0.0) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param list<int> $u
     * @param list<int> $v
     * @return array{0: \Imagick, 1: int, 2: int}
     * @throws \ImagickException
     */
    private function distortFace(
        \Imagick $skin,
        Component $component,
        string $side,
        bool $mirrored,
        int $scale,
        float|int $x,
        float|int $y,
        float|int $z,
        array $u,
        array $v,
        float|int $width,
        float|int $height,
        bool $top = false,
        ?int $cropWidth = null,
    ): array {
        // A mirrored limb (old skins) swaps its left and right sides
        $sourceSide = $mirrored && $side === Side::RIGHT ? Side::LEFT : $side;
        $area = $component->getSideByIdentifier($sourceSide);

        $face = clone $skin;
        $texWidth = $cropWidth ?? $area->getWidth();
        $face->cropImage(
            $texWidth,
            $area->getHeight(),
            $area->getTopLeft()->getX(),
            $area->getTopLeft()->getY()
        );
        $face->setImagePage(0, 0, 0, 0);
        if ($mirrored) {
            $face->flopImage();
        }
        $face->scaleImage($texWidth * $scale, $area->getHeight() * $scale);

        return $this->distort($face, $scale, $x, $y, $z, $u, $v, $width, $height, $top);
    }

    /**
     * @param list<int> $u unit vector (X, Y, Z) of the texture horizontal axis
     * @param list<int> $v unit vector (X, Y, Z) of the texture vertical axis
     * @return array{0: \Imagick, 1: int, 2: int}
     * @throws \ImagickException
     */
    private function distort(
        \Imagick $face,
        int $scale,
        float|int $x,
        float|int $y,
        float|int $z,
        array $u,
        array $v,
        float|int $width,
        float|int $height,
        bool $top = false,
    ): array {
        $face->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
        $face->setBackgroundColor(new \ImagickPixel('transparent'));
        $face->brightnessContrastImage($top ? 6.0 : ($u[2] === 1 ? 0.0 : 8.0), $top ? 6.0 : ($u[2] === 1 ? 0.0 : 8.0));

        $w = $face->getImageWidth();
        $h = $face->getImageHeight();

        $origin = $this->project($x, $y, $z, $scale);
        $right = $this->project($x + $u[0] * $width, $y + $u[1] * $width, $z + $u[2] * $width, $scale);
        $down = $this->project($x + $v[0] * $height, $y + $v[1] * $height, $z + $v[2] * $height, $scale);

        $face->distortImage(
            \Imagick::DISTORTION_AFFINE,
            [
                0, 0, $origin[0], $origin[1],
                $w, 0, $right[0], $right[1],
                0, $h, $down[0], $down[1],
            ],
            true
        );

        $page = $face->getImagePage();

        return [$face, $page['x'], $page['y']];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function project(float|int $x, float|int $y, float|int $z, int $scale): array
    {
        return [
            ($x + $z) * self::COSINE_PI_6 * $scale,
            ($y + ($z - $x) / 2) * $scale,
        ];
    }

    /**
     * @param list<array{0: \Imagick, 1: int, 2: int}> $layers
     * @throws \ImagickException
     */
    private function compose(array $layers, int $size): \Imagick
    {
        $minX = \PHP_INT_MAX;
        $minY = \PHP_INT_MAX;
        $maxX = \PHP_INT_MIN;
        $maxY = \PHP_INT_MIN;
        foreach ($layers as [$image, $x, $y]) {
            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x + $image->getImageWidth());
            $maxY = max($maxY, $y + $image->getImageHeight());
        }

        $canvas = new \Imagick();
        $canvas->newImage($maxX - $minX, $maxY - $minY, new \ImagickPixel('transparent'));
        foreach ($layers as [$image, $x, $y]) {
            $image->setImagePage(0, 0, 0, 0);
            $canvas->compositeImage($image, \Imagick::COMPOSITE_OVER, $x - $minX, $y - $minY);
        }

        $canvas->setImageFormat('png');
        $width = max(1, (int) round($canvas->getImageWidth() * $size / $canvas->getImageHeight()));
        $canvas->resizeImage($width, $size, self::DEFAULT_RESIZE_FILTER, self::DEFAULT_BLUR_VALUE);

        return $canvas;
    }
}
