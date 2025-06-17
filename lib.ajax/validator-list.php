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
        $returnVar = ErrorChecker::errorCheck($databaseBuilder, $file, $applicationId);
        if ($returnVar === 0) {          
            

            $className = "\\".$baseValidator."\\".$validatorName;
            $path = $baseDir."/".$validatorName.".php";

            $liData[$tableName][] = array(
                'name'        => $validatorName, 
                'html'        => sprintf($format1, $dir, $validatorName, $validatorName),
                'filetime'    => $filetime,
                'validatorName'  => $validatorName,
                'className'   => $className,
                'path'        => $path
            );
        } else {
            if (!isset($liData[$idx])) {
                $liData[$idx] = array();
            }
            $liData[$idx][] = array(
                'name'        => $validatorName, 
                'html'        => sprintf($format2, $dir, $validatorName, $validatorName),
                'filetime'    => $filetime
            );
        }
    }

    ksort($liData);
    foreach ($liData as $tableName=>$items) {
        foreach ($items as $item) {
            $li = $doc->createElement('li');
            $li->setAttribute('class', 'validator-li');
            $a = $doc->createElement('a', strip_tags($item['html']));
            $a->setAttribute('href', '#');
            $a->setAttribute('data-validator-name', $item['name']);
            $a->setAttribute('data-table-name', $tableName);
            $a->setAttribute('data-toggle', 'tooltip');
            $a->setAttribute('data-placement', 'top');
            if(isset($item['validatorName']) && isset($item['className']) && isset($item['path']))
            {
                $validatorName = $item['validatorName'];
                $className = $item['className'];
                $path = $item['path'];
                $a->setAttribute('data-title', $className);
                $a->setAttribute('data-html', 'true');
            }
            else
            {
                $a->setAttribute('data-title', 'Last Update '.$item['filetime']);
            }
            $li->appendChild($a);
            $ulData->appendChild($li);
        }
    }

    // Output the result
    echo $doc->saveHTML();

} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
}
