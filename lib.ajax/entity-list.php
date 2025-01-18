<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$separatorNLT = "\r\n\t";

if ($appConfig->getApplication() == null) {
    exit();
}

try {
    $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    
    // Create the DOMDocument instance
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true; // Make output readable (nice formatting)

    // Create the <div> element
    $div = $doc->createElement('div');
    $doc->appendChild($div);

    // Add "Data" section
    $h4Data = $doc->createElement('h4', 'Data');
    $div->appendChild($h4Data);

    // Process Data entities
    $baseEntity = $appConfig->getApplication()->getBaseEntityDataNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntity, "\\/"));
    $list = glob($baseDir . "/*.php");

    $ulData = $doc->createElement('ul');
    $ulData->setAttribute('class', 'entity-ul');
    $div->appendChild($ulData);

    $format1 = '<li class="entity-li"><a href="#" data-entity-name=$format3 data-toggle="tooltip" data-placement="top" data-title="%s">%s</a></li>';
    $format2 = '<li class="entity-li file-syntax-error"><a href="#" data-entity-name=$format3 data-toggle="tooltip" data-placement="top" data-title="%s">%s</a></li>';
    $format3 = "%s\\%s";

    $liData = [];

    foreach ($list as $idx => $file) {
        $entityName = basename($file, '.php');
        $filetime = 'Last Update '.date('Y-m-d H:i:s', filemtime($file));
        $dir = basename(dirname($file));
        $return_var = ErrorChecker::errorCheck($databaseBuilder, $file);
        if ($return_var === 0) {          
            $entityInfo = EntityUtil::getTableName($file);
            $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;
            if (!isset($liData[$tableName])) {
                $liData[$tableName] = [];
            }
            $liData[$tableName][] = ['name'=>sprintf($format3, $dir, $entityName), 'html'=>sprintf($format1, $dir, $entityName, $filetime, $entityName)];
        } else {
            if (!isset($liData[$idx])) {
                $liData[$idx] = [];
            }
            $liData[$idx][] = ['name'=>sprintf($format3, $dir, $entityName), 'html'=>sprintf($format2, $dir, $entityName, $filetime, $entityName)];
        }
    }

    ksort($liData);
    foreach ($liData as $items) {
        foreach ($items as $item) {
            $li = $doc->createElement('li');
            $li->setAttribute('class', 'entity-li');
            $a = $doc->createElement('a', strip_tags($item['html']));
            $a->setAttribute('href', '#');
            $a->setAttribute('data-entity-name', $item['name']);
            $a->setAttribute('data-toggle', 'tooltip');
            $a->setAttribute('data-placement', 'top');
            $a->setAttribute('data-title', $filetime);
            $li->appendChild($a);
            $ulData->appendChild($li);
        }
    }

    // Add "App" section
    $h4App = $doc->createElement('h4', 'App');
    $div->appendChild($h4App);

    // Process App entities
    $baseEntity = $appConfig->getApplication()->getBaseEntityAppNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntity, "\\/"));
    $list = glob($baseDir . "/*.php");

    $ulApp = $doc->createElement('ul');
    $ulApp->setAttribute('class', 'entity-ul');
    $div->appendChild($ulApp);

    $liApp = [];

    foreach ($list as $idx => $file) {
        $entityName = basename($file, '.php');
        $filetime = 'Last Update '.date('Y-m-d H:i:s', filemtime($file));
        $dir = basename(dirname($file));
        $return_var = ErrorChecker::errorCheck($databaseBuilder, $file);
        if ($return_var === 0) {
            $entityInfo = EntityUtil::getTableName($file);
            $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;
            if (!isset($liApp[$tableName])) {
                $liApp[$tableName] = [];
            }
            $liApp[$tableName][] = ['name'=>sprintf($format3, $dir, $entityName), 'html'=>sprintf($format1, $dir, $entityName, $filetime, $entityName)];
        } else {
            if (!isset($liApp[$idx])) {
                $liApp[$idx] = [];
            }
            $liApp[$idx][] = ['name'=>sprintf($format3, $dir, $entityName), 'html'=>sprintf($format2, $dir, $entityName, $filetime, $entityName)];
        }
    }

    ksort($liApp);
    foreach ($liApp as $items) {
        foreach ($items as $item) {
            $li = $doc->createElement('li');
            $li->setAttribute('class', 'entity-li');
            $a = $doc->createElement('a', strip_tags($item['html']));
            $a->setAttribute('href', '#');
            $a->setAttribute('data-entity-name', $item['name']);
            $a->setAttribute('data-toggle', 'tooltip');
            $a->setAttribute('data-placement', 'top');
            $a->setAttribute('data-title', $filetime);
            $li->appendChild($a);
            $ulApp->appendChild($li);
        }
    }

    // Output the result
    echo $doc->saveHTML();

} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
}
