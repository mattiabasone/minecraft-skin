<?php

namespace MattiaBasone\MinecraftSkin\Tests\Flat;

use MattiaBasone\MinecraftSkin\Component\Side;
use MattiaBasone\MinecraftSkin\Flat\Avatar;
use MattiaBasone\MinecraftSkin\Tests\BaseTestCase;

class AvatarTest extends BaseTestCase
{
    /**
     * @dataProvider renderAvatarDataProvider
     */
    public function testRenderAvatar(string $username, int $size, string $side): void
    {
        $avatar = new Avatar(self::getRawSkinPath($username));
        $avatar->render($size, $side);

        $expectedImage = file_get_contents(self::getAvatarSkinPath($username, $size, $side));

        self::assertSame($expectedImage, (string) $avatar);
    }

    public function renderAvatarDataProvider(): array
    {
        return [
            ['_Cyb3r', 128, Side::FRONT],
            ['_Cyb3r', 48, Side::TOP],
            ['MHF_Steve', 64, Side::FRONT],
            ['MHF_Steve', 16, Side::RIGHT],
            ['MHF_Steve', 16, Side::LEFT],
            ['MHF_Steve', 32, Side::BOTTOM],
        ];
    }
}