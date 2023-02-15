<?php

namespace MattiaBasone\MinecraftSkin\Tests;

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected static function getRawSkinPath(string $name): string
    {
        return sprintf('%s/skins/raw/%s.png', __DIR__, $name);
    }

    protected static function getAvatarSkinPath(string $name, int $size, string $side): string
    {
        return sprintf('%s/skins/avatar/%s_%d_%s.png', __DIR__, $name, $size, $side);
    }
}