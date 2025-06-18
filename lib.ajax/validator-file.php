<?php

use AppBuilder\Util\Error\ErrorChecker;
use AppBuilder\Util\ResponseUtil;
use AppBuilder\Util\ValidatorUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
header("Content-type: text/plain");

if($inputPost->getUserAction() == 'set')
{
    $applicationId = $appConfig->getApplication()->getId();
    $validator = $inputPost->getValidator();
    if (isset($validator) && !empty($validator)) {
        $content = $inputPost->getContent();
        $path = ValidatorUtil::getPath($appConfig, $inputPost);
        if($inputPost->getNewValidator() !== null && $inputPost->getNewValidator() != "")
        {
            $newValidator = $inputPost->getNewValidator();
            $path = str_replace($validator, $newValidator, $path);
            $content = str_replace($validator, $newValidator, $content);       
            file_put_contents($path, $content);
        }
        else
        {
            file_put_contents($path, $content);
        }

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
                "error_line" => 0,
                "new_content" => $content
            ));
        }
    }
}
else
{
    $path = ValidatorUtil::getPath($appConfig, $inputPost);
    if(file_exists($path))
    {         
        echo file_get_contents($path);
    }
}