<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Flat;

use MattiaBasone\MinecraftSkin\Component\Component;
use MattiaBasone\MinecraftSkin\Component\Side;
use MattiaBasone\MinecraftSkin\Point;

class SkinFront extends BaseSkinSection
{
    protected string $side = Side::FRONT;

    /**
     * @return array<string, Point>
     */
    protected function startingPoints(): array
    {
        return [
            Component::HEAD => new Point(4, 0),
            Component::TORSO => new Point(4, 8),
            Component::RIGHT_ARM => new Point(0, 8),
            Component::LEFT_ARM => new Point(12, 8),
            Component::RIGHT_LEG => new Point(4, 20),
            Component::LEFT_LEG => new Point(8, 20),
        ];
    }
}
