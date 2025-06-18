<?php

use AppBuilder\Util\Error\ErrorChecker;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$separatorNLT = "\r\n\t";

if ($appConfig->getApplication() == null) {
    exit();
}

try {
    $applicationId = $appConfig->getApplication()->getId();
    $baseDirectory = $appConfig->getApplication()->getBaseApplicationDirectory()."/inc.lib/classes";
    
    // Create the DOMDocument instance
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true; // Make output readable (nice formatting)

    // Create the <div> element
    $div = $doc->createElement('div');
    $doc->appendChild($div);

    // Process Data entities
    $baseValidator = dirname(dirname($appConfig->getApplication()->getBaseEntityDataNamespace()))."\\Validator";
    $baseValidator = str_replace("\\\\", "\\", $baseValidator);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseValidator, "\\/"));
    $list = glob($baseDir . "/*.php");
    
    $ulData = $doc->createElement('ul');
    $ulData->setAttribute('class', 'validator-ul');
    $div->appendChild($ulData);

    $format3 = "%s";
    $format1 = '<li class="validator-li"><a href="#" data-validator-name='.$format3.' data-toggle="tooltip" data-placement="top" data-title="%s">%s</a></li>';
    $format2 = '<li class="validator-li file-syntax-error"><a href="#" data-validator-name='.$format3.' data-toggle="tooltip" data-placement="top" data-title="%s">%s</a></li>';

    $liData = array();

    foreach ($list as $idx => $file) {
        $validatorName = basename($file, '.php');
        $filetime = date('Y-m-d H:i:s', filemtime($file));
        $dir = basename(dirname($file));
        $phpError = ErrorChecker::errorCheck($databaseBuilder, $file, $applicationId);
        $returnVar = intval($phpError->errorCode);
        
        $li = $doc->createElement('li');
        $li->setAttribute('class', 'validator-li');
        $a = $doc->createElement('a', $validatorName);
        $a->setAttribute('href', '#');
        $a->setAttribute('data-validator-name', $validatorName);
        $a->setAttribute('data-toggle', 'tooltip');
        $a->setAttribute('data-placement', 'top');
        
        $title = $validatorName;

        if($phpError->lineNumber != -1)
        {
            $title .= "<br>Error at line ".$phpError->lineNumber;
            $a->setAttribute('data-error', 'true');
            $a->setAttribute('data-error-line-number', $phpError->lineNumber);
        }
        else
        {
            $a->setAttribute('data-error', 'false');
        }

        $a->setAttribute('data-title', $title);
        $a->setAttribute('data-html', 'true');
        
        $li->appendChild($a);
        $ulData->appendChild($li);
    }
    

    // Output the result
    echo $doc->saveHTML();

} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
}
