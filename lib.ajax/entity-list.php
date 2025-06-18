<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$separatorNLT = "\r\n\t";

if ($appConfig->getApplication() == null) {
    exit();
}

try {
    $applicationId = $appConfig->getApplication()->getId();
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

    $format3 = "%s\\%s";
    $format1 = '<li class="entity-li"><a href="#" data-entity-name='.$format3.' data-toggle="tooltip" data-placement="top" data-title="%s">%s</a></li>';
    $format2 = '<li class="entity-li file-syntax-error"><a href="#" data-entity-name='.$format3.' data-toggle="tooltip" data-placement="top" data-title="%s">%s</a></li>';

    $liData = array();

    foreach ($list as $idx => $file) {
        $entityName = basename($file, '.php');
        $filetime = date('Y-m-d H:i:s', filemtime($file));
        $dir = basename(dirname($file));
        $phpError = ErrorChecker::errorCheck($databaseBuilder, $file, $applicationId);
        $returnVar = intval($phpError->errorCode);
        if ($returnVar === 0) {          
            $entityInfo = EntityUtil::getTableName($file);
            $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;
            if (!isset($liData[$tableName])) {
                $liData[$tableName] = array();
            }

            $className = "\\".$baseEntity."\\".$entityName;
            $path = $baseDir."/".$entityName.".php";

            $liData[$tableName][] = array(
                'name'        => sprintf($format3, $dir, $entityName), 
                'html'        => sprintf($format1, $dir, $entityName, $filetime, $entityName),
                'filetime'    => $filetime,
                'entityName'  => $entityName,
                'className'   => $className,
                'path'        => $path,
                'lineNumber'  => $phpError->lineNumber
            );
        } else {
            if (!isset($liData[$idx])) {
                $liData[$idx] = array();
            }
            $liData[$idx][] = array(
                'name'        => sprintf($format3, $dir, $entityName), 
                'html'        => sprintf($format2, $dir, $entityName, $filetime, $entityName),
                'filetime'    => $filetime,
                'lineNumber'  => $phpError->lineNumber,
                'entityName'  => $entityName,
            );
        }
    }

    ksort($liData);
    foreach ($liData as $tableName=>$items) {
        foreach ($items as $item) {
            $li = $doc->createElement('li');
            $li->setAttribute('class', 'entity-li');
            $a = $doc->createElement('a', strip_tags($item['html']));
            $a->setAttribute('href', '#');
            $a->setAttribute('data-entity-name', $item['name']);
            $a->setAttribute('data-table-name', $tableName);
            $a->setAttribute('data-toggle', 'tooltip');
            $a->setAttribute('data-placement', 'top');
            if(isset($item['entityName']) && isset($item['className']) && isset($item['path']))
            {
                $entityName = $item['entityName'];
                $className = $item['className'];
                $path = $item['path'];
                $title = EntityUtil::getEntityTooltip($databaseBuilder, $path, $className, $filetime);
                $a->setAttribute('data-title', $title);
                $a->setAttribute('data-html', 'true');
                $a->setAttribute('data-error', 'false');
            }
            else
            {
                $title = $item['name'];
                $title .= "<br>Error at line ".$item['lineNumber'];
                $a->setAttribute('data-title', $title);
                $a->setAttribute('data-html', 'true');
                $a->setAttribute('data-error', 'true');
                $a->setAttribute('data-error-line-number', $item['lineNumber']);
            }
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

    $liApp = array();

    foreach ($list as $idx => $file) {
        $entityName = basename($file, '.php');
        $filetime = date('Y-m-d H:i:s', filemtime($file));
        $dir = basename(dirname($file));
        $phpError = ErrorChecker::errorCheck($databaseBuilder, $file, $applicationId);
        $returnVar = intval($phpError->errorCode);
        if ($returnVar === 0) {
            $entityInfo = EntityUtil::getTableName($file);
            $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;
            if (!isset($liApp[$tableName])) {
                $liApp[$tableName] = array();
            }

            $className = "\\".$baseEntity."\\".$entityName;
            $path = $baseDir."/".$entityName.".php";

            $liApp[$tableName][] = array(
                'name'        => sprintf($format3, $dir, $entityName), 
                'html'        => sprintf($format1, $dir, $entityName, $filetime, $entityName),
                'filetime'    => $filetime,
                'entityName'  => $entityName,
                'className'   => $className,
                'path'        => $path
            );
        } else {
            if (!isset($liApp[$idx])) {
                $liApp[$idx] = array();
            }
            $liApp[$idx][] = array(
                'name'        => sprintf($format3, $dir, $entityName), 
                'html'        => sprintf($format2, $dir, $entityName, $filetime, $entityName),
                'filetime'    => $filetime,
                'entityName'  => $entityName,
                'className'   => $className,
            );
        }
    }

    ksort($liApp);
    foreach ($liApp as $tableName=>$items) {
        foreach ($items as $item) {
            $li = $doc->createElement('li');
            $li->setAttribute('class', 'entity-li');
            $a = $doc->createElement('a', strip_tags($item['html']));
            $a->setAttribute('href', '#');
            $a->setAttribute('data-entity-name', $item['name']);
            $a->setAttribute('data-table-name', $tableName);
            $a->setAttribute('data-toggle', 'tooltip');
            $a->setAttribute('data-placement', 'top');
            
            if(isset($item['entityName']) && isset($item['className']) && isset($item['path']))
            {
                $entityName = $item['entityName'];
                $className = $item['className'];
                $path = $item['path'];
                $title = EntityUtil::getEntityTooltip($databaseBuilder, $path, $className, $filetime);
                $a->setAttribute('data-title', $title);
                $a->setAttribute('data-html', 'true');
                $a->setAttribute('data-error', 'false');
            }
            else
            {
                $entityName = $item['entityName'];
                $className = $item['className'];
                $title = "$entityName<br>Error at line ".$phpError->lineNumber;
                $a->setAttribute('data-title', $title);
                $a->setAttribute('data-html', 'true');
                $a->setAttribute('data-error', 'true');
            }


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
