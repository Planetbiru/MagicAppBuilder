<?php

use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$inputPost = new InputPost();

try {
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

        $errors = [];
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
}
