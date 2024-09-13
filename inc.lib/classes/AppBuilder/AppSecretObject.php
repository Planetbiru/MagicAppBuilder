<?php

namespace AppBuilder;

use MagicObject\SecretObject;

/**
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Yaml(property-naming-strategy=SNAKE_CASE)
 */
class AppSecretObject extends SecretObject
{

}