<?php

use AppBuilder\Util\Error\ErrorChecker;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
header("Content-type: text/plain");

/**
 * Get file path
 *
 * @param SecretObject $appConfig Application config
 * @param InputPost $inputPost Input post
 * @return string
 */
function getPath($appConfig, $inputPost)
{
    $baseDirectory = $appConfig->getApplication()->getBaseApplicationDirectory()."/inc.lib/classes";
    $baseValidator = dirname(dirname($appConfig->getApplication()->getBaseEntityDataNamespace()))."\\Validator";
    $baseValidator = str_replace("\\\\", "\\", $baseValidator);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseValidator, "\\/"));
    $inputValidator = $inputPost->getValidator();
    $inputValidator = trim($inputValidator);
    return $baseDir."/".$inputValidator.".php";
}

if($inputPost->getUserAction() == 'set')
{
    $applicationId = $appConfig->getApplication()->getId();
    $validator = $inputPost->getValidator();
    if (isset($validator) && !empty($validator)) {
        $content = $inputPost->getContent();
        $path = getPath($appConfig, $inputPost);
        file_put_contents($path, $content);

        $phpError = ErrorChecker::errorCheck($databaseBuilder, $path, $applicationId);
        $returnVar = intval($phpError->errorCode);
        $errorMessage = implode("\r\n", $phpError->errors);
        $lineNumber = $phpError->lineNumber;

        if ($returnVar !== 0) {
            ResponseUtil::sendJSON(array(
                "success" => false,
                "title" => "Parse Error",
                "message" => nl2br("<p>" . $errorMessage . "</p>"),
                "error_line" => $lineNumber
            ));
        } else {
            ResponseUtil::sendJSON(array(
                "success" => true,
                "message" => "Success",
                "error_line" => 0
            ));
        }
    }
}
else
{
    $path = getPath($appConfig, $inputPost);
    if(file_exists($path))
    {         
        echo file_get_contents($path);
    }
}