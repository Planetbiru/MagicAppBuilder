<?php

use AppBuilder\Util\Error\ErrorChecker;
use AppBuilder\Util\ResponseUtil;
use AppBuilder\Util\ValidatorUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

if($inputPost->getUserAction() == 'create')
{
    $applicationId = $appConfig->getApplication()->getId();
    $validator = $inputPost->getValidator();
    if (isset($validator) && !empty($validator)) {
        $definition = $inputPost->getDefinition(); // JSON
        $path = ValidatorUtil::getPath($appConfig, $inputPost);
    }
}
