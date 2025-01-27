<?php

use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

try {
    header("Content-type: application/json");
    $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntity, "\\/"));
    $entity = $inputPost->getEntity();
    $newEntity = $inputPost->getNewEntity();
    if (isset($newEntity) && !empty($newEntity)) {
        $content = $inputPost->getContent();
        $entityName = trim($entity);
        $newEntityName = trim($newEntity);

        $path = $baseDir . "/" . $newEntityName . ".php";
        if(file_exists($path))
        {
            ResponseUtil::sendJSON(array(
                "success" => false,
                "message" => "Entity already exists. Please choose another name.",
                "error_line" => 0
            ));
        }
        else
        {
            $baseEntityName = basename($entityName);
            $baseNewEntityName = basename($newEntityName);

            $content = str_replace("$baseEntityName class", "$baseNewEntityName class", $content);
            $content = str_replace("class $baseEntityName", "class $baseNewEntityName", $content);         

            file_put_contents($path, $content);

            exec("php -l $path 2>&1", $output, $returnVar);

            $errors = array();
            if (isset($output) && is_array($output)) {
                foreach ($output as $line) {
                    $errors[] = $line . "\n";
                }
            }

            if ($returnVar !== 0) {
                $errorMessage = implode("\r\n", $errors);
                $lineNumber = 0;
                $p1 = stripos($errorMessage, 'PHP Parse error');
                if ($p1 !== false) {
                    $p2 = stripos($errorMessage, ' on line ', $p1);
                    if ($p2 !== false) {
                        $p2 += 9;
                        $lineNumberRaw = substr($errorMessage, $p2);
                        $p3 = strpos($errorMessage, "\r\n", $p2);
                        $lineNumber = intval(trim(substr($errorMessage, $p2, $p3 - $p2)));
                    }
                }

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
} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
    ResponseUtil::sendJSON(new stdClass);
}
