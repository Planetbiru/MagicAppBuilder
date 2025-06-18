<?php

use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

try {
    $applicationId = $appConfig->getApplication()->getId();
    $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntity, "\\/"));
    $entity = $inputPost->getEntity();
    if (isset($entity) && !empty($entity)) {
        $content = $inputPost->getContent();
        $entityName = trim($entity);
        $path = $baseDir . "/" . $entityName . ".php";
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
} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
    ResponseUtil::sendJSON(new stdClass);
}
