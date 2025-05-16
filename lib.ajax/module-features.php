<?php

use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$path = sprintf(
    "%s/applications/%s/data/%s/features.json",
    $activeWorkspace->getDirectory(),
    $activeApplication->getApplicationId(),
    $entityAdmin->getAdminId()
);

if ($inputPost->getData() != null) {

    if (!file_exists(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $inputPost->getData());
}
else
{
    if (file_exists($path)) {
        header('Content-Type: application/json');
        echo file_get_contents($path);
    } else {
        header('Content-Type: application/json');
        echo "{}";
    }
}