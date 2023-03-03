# Minecraft Skin

This package provide some utilities for manipulating Minecraft skins.

For instance, if you want to render an avatar:
```php

use MattiaBasone\MinecraftSkin\Flat\Avatar;
use MattiaBasone\MinecraftSkin\Component\Side;

$avatar = new Avatar("/path/to/minecraft-skin.pnh");

$avatar->render(256, Side::FRONT);

// Get the \GdResource
$resource = $avatar->getResource();
// or as string
$rawImage = (string) $avatar;
file_put_contents("/my/output/file.png", $rawImage);

```