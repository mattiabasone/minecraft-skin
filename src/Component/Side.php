<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Component;

use MattiaBasone\MinecraftSkin\Point;

/**
 * @phpstan-import-type SectionCoordinates from Coordinates
 */
class Side
{
    public const string TOP = 'TOP';
    public const string BOTTOM = 'BOTTOM';
    public const string FRONT = 'FRONT';
    public const string BACK = 'BACK';
    public const string RIGHT = 'RIGHT';
    public const string LEFT = 'LEFT';

    public function __construct(protected Point $topLeft, protected Point $bottomRight)
    {
    }

    public function getTopLeft(): Point
    {
        return $this->topLeft;
    }

    public function getBottomRight(): Point
    {
        return $this->bottomRight;
    }

    /**
     * @return int<1, max>
     */
    public function getWidth(): int
    {
        $width = $this->bottomRight->getX() - $this->topLeft->getX();
        if ($width < 1) {
            throw new \InvalidArgumentException('Side width must be greater than 0');
        }

        return $width;
    }

    /**
     * @return int<1, max>
     */
    public function getHeight(): int
    {
        $height = $this->bottomRight->getY() - $this->topLeft->getY();
        if ($height < 1) {
            throw new \InvalidArgumentException('Side height must be greater than 0');
        }

        return $height;
    }

    /**
     * @param SectionCoordinates $rawPoints
     */
    public static function fromRawPoints(array $rawPoints): self
    {
        return new self(
            new Point($rawPoints[0][0], $rawPoints[0][1]),
            new Point($rawPoints[1][0], $rawPoints[1][1])
        );
    }
}
