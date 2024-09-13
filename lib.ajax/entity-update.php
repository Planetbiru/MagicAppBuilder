<?php

use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputPost = new InputPost();

try {
    header("Content-type: application/json");
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

        exec("php -l $path 2>&1", $output, $return_var);

        $errors = array();
        if (isset($output) && is_array($output)) {
            foreach ($output as $line) {
                $errors[] = $line . "\n";
            }
        }

        if ($return_var !== 0) {
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

            echo json_encode(array(
                "success" => false,
                "error_title" => "Parse Error",
                "error_message" => nl2br("<p>" . $errorMessage . "</p>"),
                "error_line" => $lineNumber
            ));
        } else {
            echo json_encode(array(
                "success" => true,
                "error_message" => "Success",
                "error_line" => 0
            ));
        }
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
}
