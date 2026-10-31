<?php

declare(strict_types=1);

namespace MattiaBasone\MinecraftSkin\Tests\Isometric;

use MattiaBasone\MinecraftSkin\Component\Component;
use MattiaBasone\MinecraftSkin\Component\Side;
use MattiaBasone\MinecraftSkin\Flat\Avatar;
use MattiaBasone\MinecraftSkin\Flat\ImageManipulation;
use MattiaBasone\MinecraftSkin\Flat\ImageSection;
use MattiaBasone\MinecraftSkin\Flat\LayerValidator;
use MattiaBasone\MinecraftSkin\Isometric\Body;
use MattiaBasone\MinecraftSkin\Point;
use MattiaBasone\MinecraftSkin\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Component::class)]
#[CoversClass(Side::class)]
#[CoversClass(Avatar::class)]
#[CoversTrait(ImageManipulation::class)]
#[CoversClass(ImageSection::class)]
#[CoversClass(LayerValidator::class)]
#[CoversClass(Point::class)]
#[CoversClass(Body::class)]
class BodyTest extends BaseTestCase
{
    #[DataProvider('skinsDataProvider')]
    public function testGenerateIsometricBody(string $username, int $size): void
    {
        $body = new Body(self::getRawSkinPath($username))->render($size);

        self::assertSame($size, $body->getImageHeight());
        self::assertLessThan($body->getImageHeight(), $body->getImageWidth());
        self::assertSame('PNG', $body->getImageFormat());
    }

    /**
     * @return list<array{0: string, 1: int}>
     */
    public static function skinsDataProvider(): array
    {
        return [
            ['_Cyb3r', 1024],
            ['MHF_Steve', 400],
            ['SteveWithHelmet', 256],
            ['Kai_Slim_Arms', 512],
        ];
    }
}
