<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Tests\Isometric;

use MattiaBasone\MinecraftSkin\Component\Component;
use MattiaBasone\MinecraftSkin\Component\Side;
use MattiaBasone\MinecraftSkin\Flat\Avatar;
use MattiaBasone\MinecraftSkin\Flat\ImageManipulation;
use MattiaBasone\MinecraftSkin\Flat\ImageSection;
use MattiaBasone\MinecraftSkin\Flat\LayerValidator;
use MattiaBasone\MinecraftSkin\Isometric\Head;
use MattiaBasone\MinecraftSkin\Point;
use MattiaBasone\MinecraftSkin\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Head::class)]
#[CoversClass(Avatar::class)]
#[CoversClass(Component::class)]
#[CoversClass(ImageManipulation::class)]
#[CoversClass(ImageSection::class)]
#[CoversClass(LayerValidator::class)]
#[CoversClass(Point::class)]
#[CoversClass(Side::class)]
class HeadTest extends BaseTestCase
{
    #[DataProvider('generateIsometricHeadDataProvider')]
    public function testGenerateIsometricHead(string $username, int $size): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), "head_test");
        $head = (new Head(self::getRawSkinPath($username)))->render($size);
        $head->writeImage($tempFile);

        self::assertSame(
            file_get_contents(self::getHeadSkinPath($username, $size)),
            file_get_contents($tempFile)
        );

        unlink($tempFile);
    }

    public static function generateIsometricHeadDataProvider(): array
    {
        return [
            ['_Cyb3r', 32],
            ['MHF_Steve', 48],
            ['SteveWithHelmet', 64],
        ];
    }
}