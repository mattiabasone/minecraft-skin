<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Tests\Flat;

use MattiaBasone\MinecraftSkin\Component\Component;
use MattiaBasone\MinecraftSkin\Component\Side;
use MattiaBasone\MinecraftSkin\Flat\Avatar;
use MattiaBasone\MinecraftSkin\Flat\ImageSection;
use MattiaBasone\MinecraftSkin\Flat\LayerValidator;
use MattiaBasone\MinecraftSkin\Flat\SkinBack;
use MattiaBasone\MinecraftSkin\Flat\SkinFront;
use MattiaBasone\MinecraftSkin\Point;
use MattiaBasone\MinecraftSkin\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(SkinFront::class)]
#[CoversClass(SkinBack::class)]
#[CoversClass(Component::class)]
#[CoversClass(Side::class)]
#[CoversClass(ImageSection::class)]
#[CoversClass(LayerValidator::class)]
#[CoversClass(Point::class)]
class SkinTest extends BaseTestCase
{
    #[DataProvider('renderDataProvider')]
    public function testRenderFront(string $username, int $size): void
    {
        $image = new SkinFront(self::getRawSkinPath($username));
        $image->render($size);

        $expectedImage = file_get_contents(self::getSkinPath($username, $size, Side::FRONT));

        self::assertSame($expectedImage, (string) $image);
    }

    #[DataProvider('renderDataProvider')]
    public function testRenderBack(string $username, int $size): void
    {
        $image = new SkinBack(self::getRawSkinPath($username));
        $image->render($size);

        $expectedImage = file_get_contents(self::getSkinPath($username, $size, Side::BACK));

        self::assertSame($expectedImage, (string) $image);
    }

    public static function renderDataProvider(): array
    {
        return [
            ['_Cyb3r', 128],
            ['MHF_Steve', 64],
        ];
    }
}
