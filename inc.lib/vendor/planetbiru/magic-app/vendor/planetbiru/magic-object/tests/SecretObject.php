<?php

use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @JSON(property-naming-strategy=SNAKE_CASE)
 */
class ConfigSecret1 extends SecretObject
{
    /**
     * Database
     * 
     * @EncryptOut
     * @var mixed
     */
    protected $database;

    /**
     * Session
     * 
     * @EncryptOut
     * @var mixed
     */
    protected $session;
}

/**
 * @JSON(property-naming-strategy=SNAKE_CASE)
 */
class ConfigSecret2 extends SecretObject
{
    /**
     * Database
     * 
     * @DecryptOut
     * @var mixed
     */
    protected $database;

    /**
     * Session
     * 
     * @DecryptOut
     * @var mixed
     */
    protected $session;
}

$secret1 = new ConfigSecret1(null, function(){
    return bin2hex("You secret here");
});

$yaml1 = '
result_per_page: 20
song_base_url: http//domain.tld/songs
song_base_path: /var/www/songs
song_draft_base_url: http//domain.tld/songs-draft
song_draft_base_path: /var/www/songs-draft
proxy_provider: cloudflare
app_name: Music Production Manager
user_image:
  width: 512
  height: 512
album_image:
  width: 512
  height: 512
song_image:
  width: 512
  height: 512
database:
  time_zone_system: Asia/Jakarta
  default_charset: utf8
  driver: mysql
  host: localhost
  port: 3306
  username: user
  password: pass
  database_name: music
  database_schema: public
  time_zone: Asia/Jakarta
  salt: Asia/Jakarta
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: files
  save_path: /tmp/session
vocal_guide_instrument: piano
prop1:
  prop2:
    prop3: $(database.time_zone}
';
/*
$secret1->loadYamlString(
$yaml1,
false, true, true
);

echo $secret1->dumpYaml(null, 4);
*/

$yaml2 = '
database:
    time_zone_system: UEMGavyLkN7rFAmoXBsdDKwuGC+zFttpPTAaqeMH0XUEZaAMKbyvykNtfqT+F8FcAbQCUHV66qjYfjArzgrHlA==
    default_charset: dawURojqYqXdvt2YdZ+kWsq47bcA7FKWnEfGHPMxJr3KUyKxAC0VrH8Khfqcm5iIzQFHE/1wQbdgxJNiffkayw==
    driver: Ur4FCOYvXGPpoMpHm3fwhdK5D3SaP0+MGe4IuPAvpzhAvRcjOW7EZe5VvDAf+0CLeus9tCqqE1sTXj/dxfmkaA==
    host: zyfIMxYi/qQAbazR+nOaLnXFUN9qDYiapxlvocKYxkL8uuN6zRrP3Jsj0mlj6UnLOlvhfDgF3Pq0PrP2ZORGNg==
    port: Cs3NTbxIXJ0lf1umQuuDXbPqdBPlVg+jeXi6UqqUUvVUQWgAhbfenfP8g81cONoY2dXof+P1V5Gr/q+iDLNv5Q==
    username: 5NImv2VEL1WSbt3cqx7gi/8f158SYtssj74zTN2fRrIDsGOxsnEa8+50H3Y1MCaJV7SnZo851dnjEhm38Tzjsg==
    password: /1SK1m9qVjbWnGa6/xai2H82OWvzXeErRvtQ0RYceGsr3fvAfGfqcDSY6pq8KoXg6MmtJS0FLyjXUq8dftM7yQ==
    database_name: ZQ0PTeDl7AOtjDBV1PHzlwMbPBVZmhXuO65O1pDYYCGQSMNf3GpQJi9SkvGApQ8kBKUgPAmchCTYb8ChL2szrQ==
    database_schema: 628aqGw8hLV8Malwg6jninVfNRgDUgANtXdPfIuz8IohoMQ4NROIuj7Y28/SeLD74NPoiBYFZpaON9+jV8QQug==
    time_zone: zfpl4IxctWh2Y/UnrANjuCKlnevw8MmoSboMtzV3oBzKKN0gg+TT/Zz/QieHkVd+pn7OHx2OXvLadkzzWQTMUQ==
    salt: hdsBgcmvq/rzfNUuhgQiBop4Hp4wqd5w03Il9zaOshK0LgGtaEK1WTcPx5OdL+9VvhmZvs7g/jDGYjYoHwXjIg==
session:
    name: 4u5xAE2K74pUxZyIVsphoYUka3vpnzUx7op6CnELtdWNEepz/jQLaKynItFt5dx6bv7wjBUFL1AaZaA4ypw/CP6xtR5WFQy+RV6V8VqYM8o=
    max_life_time: cspEenRp8+kwUY1RNvmEdWcLqmsRZ+UJZVjY4JwRsuIIfkr+J0w1SYCuRMzMtHG4/hZ5tlXhtGdRZyM7quCbZg==
    save_handler: LIlCCaWHVqg9R4G6ghxDZnuenMLgSI6HjiW+tVGNDa7UyIA7FkFtOgOJtvT/EolUc+kkJSXiMo+76QhvFFq8Dg==
    save_path: CZijzyucTzrj3tZ1M9PbQ6Hky1+4Gz3RnXwZNSe9/SL+9QZdpK4PoW2TSLsuQ+cKBgKgkncd7JXWgA3CFg0f1A==
result_per_page: 20
song_base_url: http//domain.tld/songs
song_base_path: /var/www/songs
song_draft_base_url: http//domain.tld/songs-draft
song_draft_base_path: /var/www/songs-draft
proxy_provider: cloudflare
app_name: "Music Production Manager"
user_image:
    width: 512
    height: 512
album_image:
    width: 512
    height: 512
song_image:
    width: 512
    height: 512
vocal_guide_instrument: piano
prop1:
  prop2:
    prop3: ${user_image.width}
';

/*
$secret2 = new ConfigSecret2(null, function(){
    return bin2hex("You secret here");
});

$secret2->loadYamlString($yaml2, false, true, true);

echo $secret2->dumpYaml(null, 4);

*/
$yaml4 ='
application:
    id: your-application
    name: Your Application Name
    application_base_namespace: YourApplication
    application_base_directory: /var/www/html/your-application
    entity_base_namespace: YourApplication\Data\Entity
    entity_base_directory: /var/www/html/your-application/inc.lib/classes
    entity_base_namespace: YourApplication\\Data\Entity
    base_include_directory: inc.app
    base_asset_directory: lib.assets
    composer:
        base_directory: inc.lib
        psr0: true
        psr4: false
        psr0_base_directory:
            -
                namespace: YourApplication
                directory: classes
        psr4_base_directory: null
    magic_object:
        version: 1.6
entity_info:
    active: active
    draft: draft
    waiting_for: waiting_for
    admin_create: admin_create
    admin_edit: admin_edit
    admin_ask_edit: admin_ask_edit
    time_create: time_create
    time_edit: time_edit
    time_ask_edit: time_ask_edit
    ip_create: ip_create
    ip_edit: ip_edit
    ip_ask_edit: ip_ask_edit
    sort_order: sort_order
    approval_id: approval_id
    approval_note: approval_note
    approval_status: approval_status
sessions:
    name: APPBUILDER
    lifetime: 14400
    save_handler: files
    save_path: ""
current_action:
    user_function: currentAction->getUserId()
    time_function: currentAction->getTime()
    ip_function: currentAction->getIp()
global_variable_database: database
';

$secret2 = new ConfigSecret2(null, function(){
  return bin2hex("You secret here");
});

$secret2->loadYamlString($yaml4, false, true, true);
echo $secret2->getApplication()->getComposer()->getPsr0BaseDirectory()[0];
//var_dump($secret2->getApplication()->getComposer());