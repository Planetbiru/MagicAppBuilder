<?php

use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoInputFieldFilter;
use MagicApp\AppDto\MocroServices\PicoInputFieldOption;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormFilterList;
use MagicApp\AppUserPermission;
use MagicApp\PicoModule;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";


$data = new PicoUserFormFilterList();

$data->addFilter(
    new PicoInputFieldFilter(
        new PicoInputField("adminId", "Admin"), 
        "select", 
        "string", 
        "map", 
        [
            new PicoInputFieldOption("admin", "Administrator")
        ], 
        null, 
        new PicoInputField("admin", "Administrator")
    )
);

$data->addFilter(
    new PicoInputFieldFilter(
        new PicoInputField("adminId", "Admin"), 
        "select", 
        "string", 
        "url", 
        "workspace.php?user_action=show-option&field=adminId", 
        null, 
        new PicoInputField("admin", "Administrator")
    )
);

$data->addFilter(
    new PicoInputFieldFilter(
        new PicoInputField("name", "Name"), 
        "text", 
        "string", 
        null, 
        new PicoInputField("Administrator", "Administrator")
    )
);

$data->addFilter(
    new PicoInputFieldFilter(
        new PicoInputField("active", "Aktif"), 
        "checkbox", 
        "boolean", 
        null, 
        new PicoInputField(true, "Ya")
    )
);

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "umk", "Umk");
$userPermission = new AppUserPermission(null, null, null, null, null);

$section = "filter-list";
echo PicoResponseBody::getInstance()
    ->setModule($currentModule, $section)
    ->setPermission($userPermission)
    ->setData($data)
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;