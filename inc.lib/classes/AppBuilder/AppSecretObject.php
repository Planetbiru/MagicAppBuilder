<?php

namespace AppBuilder;

use MagicObject\SecretObject;

/** 
 * Class AppSecretObject
 *
 * Merupakan versi khusus dari `SecretObject` yang disesuaikan untuk aplikasi AppBuilder.
 * Kelas ini dikonfigurasi dengan strategi penamaan properti `SNAKE_CASE` untuk serialisasi JSON dan YAML,
 * memastikan konsistensi untuk objek konfigurasi dan data di seluruh aplikasi.
 *
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Yaml(property-naming-strategy=SNAKE_CASE)
 */
class AppSecretObject extends SecretObject
{

}