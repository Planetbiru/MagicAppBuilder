<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$separatorNLT = "\r\n\t";

if ($appConfig->getApplication() == null) {
    exit();
}

try {
    $inputGet = new InputGet();
    $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $chk = $inputGet->getAutoload() == 'true' ? ' checked' : '';

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $rootDiv = $dom->createElement('div');
    $dom->appendChild($rootDiv);

    $createCheckbox = function($class, $labelText, $checked = false) use ($dom) {
        $div = $dom->createElement('div');
        $div->setAttribute('style', 'white-space:nowrap');
        
        $input = $dom->createElement('input');
        $input->setAttribute('type', 'checkbox');
        $input->setAttribute('class', $class);
        if ($checked) {
            $input->setAttribute('checked', 'checked');
        }
    
        // Adding space after checkbox
        $space = $dom->createTextNode(' '); // Adding a space after the checkbox
        
        $label = $dom->createElement('label', $labelText);
        
        $div->appendChild($input);
        $div->appendChild($space); // Add the space
        $div->appendChild($label);
        
        return $div;
    };
    

    $rootDiv->appendChild($createCheckbox('entity-check-controll', 'Select all', $chk));
    $rootDiv->appendChild($createCheckbox('entity-merge', 'Merge queries by table', true));

    $rootDiv->appendChild($dom->createElement('h4', 'Data'));

    $baseEntity = $appConfig->getApplication()->getBaseEntityDataNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntity, "\\/"));
    
    $list = glob($baseDir . "/*.php");
    $li = array();
    $format1 = function($idx, $dir, $entityName, $chk, $filetime) use ($dom) {
        $li = $dom->createElement('li');
        $li->setAttribute('class', 'entity-li');
        
        $input = $dom->createElement('input');
        $input->setAttribute('type', 'checkbox');
        $input->setAttribute('class', 'entity-checkbox entity-checkbox-query');
        $input->setAttribute('name', "entity[$idx]");
        $input->setAttribute('value', "$dir\\$entityName");
        if ($chk) {
            $input->setAttribute('checked', 'checked');
        }
        
        
        // Add space after checkbox (using span with &nbsp;)
        $span = $dom->createElement('span');
        $span->appendChild($dom->createTextNode(' ')); // Adding space after checkbox
        
        $a = $dom->createElement('a', $entityName);
        $a->setAttribute('href', '#');
        $a->setAttribute('data-entity-name', "$dir\\$entityName");
        $a->setAttribute('data-toggle', 'tooltip');
        $a->setAttribute('data-placement', 'top');
        $a->setAttribute('data-title', $filetime);
        $a->setAttribute('data-html', 'true');
        
        $li->appendChild($input);
        $li->appendChild($span); // Add space
        $li->appendChild($a);
        
        return $li;
    };
    
    $format2 = function($idx, $dir, $entityName, $filetime) use ($dom) {
        $li = $dom->createElement('li');
        $li->setAttribute('class', 'entity-li file-syntax-error');
        
        $input = $dom->createElement('input');
        $input->setAttribute('type', 'checkbox');
        $input->setAttribute('class', 'entity-checkbox entity-checkbox-query');
        $input->setAttribute('name', "entity[$idx]");
        $input->setAttribute('value', "$dir\\$entityName");
        $input->setAttribute('disabled', 'disabled');
        $input->setAttribute('data-toggle', 'tooltip');
        $input->setAttribute('data-placement', 'top');
        $input->setAttribute('data-title', $filetime);
        
        // Add space after checkbox (using span with &nbsp;)
        $span = $dom->createElement('span');
        $span->appendChild($dom->createTextNode(' ')); // Adding space after checkbox
        
        $li->appendChild($input);
        $li->appendChild($span); // Add space
        $li->appendChild($dom->createTextNode($entityName));
        
        return $li;
    };
    

    foreach ($list as $idx => $file) {
        $entityName = basename($file, '.php');
        $dir = basename(dirname($file));   
        $return_var = ErrorChecker::errorCheck($databaseBuilder, $file);
        
        if ($return_var === 0) {
            $filetime = date('Y-m-d H:i:s', filemtime($file));
            $entityInfo = EntityUtil::getTableName($file);
            $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;
            if (!isset($li[$tableName])) {
                $li[$tableName] = array();
            }

            $className = "\\".$baseEntity."\\".$entityName;
            $path = $baseDir."/".$entityName.".php";
            $title = EntityUtil::getEntityTooltip($databaseBuilder, $path, $className, $filetime);
            $li[$tableName][] = $format1($idx, $dir, $entityName, $chk, $title);
        } else {
            if (!isset($li[$idx])) {
                $li[$idx] = array();
            }
            $li[$idx][] = $format2($idx, $dir, $entityName, $filetime);
        }
    }

    ksort($li);

    $lim = array();
    foreach ($li as $elem) {
        $lim = array_merge($lim, $elem);
    }

    $ul = $dom->createElement('ul');
    $ul->setAttribute('class', 'entity-ul');
    foreach ($lim as $elem) {
        $ul->appendChild($elem);
    }
    $rootDiv->appendChild($ul);

    $rootDiv->appendChild($dom->createElement('h4', 'App'));

    $baseEntity = $appConfig->getApplication()->getBaseEntityAppNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntity, "\\/"));
    $list = glob($baseDir . "/*.php");
    $li = array();

    foreach ($list as $idx => $file) {
        $entityName = basename($file, '.php');
        $dir = basename(dirname($file));   
        $return_var = ErrorChecker::errorCheck($databaseBuilder, $file);
        
        if ($return_var === 0) {
            $filetime = date('Y-m-d H:i:s', filemtime($file));
            $entityInfo = EntityUtil::getTableName($file);
            $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;
            if (!isset($li[$tableName])) {
                $li[$tableName] = array();
            }

            $className = "\\".$baseEntity."\\".$entityName;
            $path = $baseDir."/".$entityName.".php";
            $title = EntityUtil::getEntityTooltip($databaseBuilder, $path, $className, $filetime);
            $li[$tableName][] = $format1($idx, $dir, $entityName, $chk, $title);
        } else {
            if (!isset($li[$idx])) {
                $li[$idx] = array();
            }
            $li[$idx][] = $format2($idx, $dir, $entityName, $filetime);
        }
    }
    
    ksort($li);
 
    $lim = array();
    foreach ($li as $elem) {
        $lim = array_merge($lim, $elem);
    }

    $ul = $dom->createElement('ul');
    $ul->setAttribute('class', 'entity-ul');
    foreach ($lim as $elem) {
        $ul->appendChild($elem);
    }
    $rootDiv->appendChild($ul);

    // Output result to buffer
    echo $dom->saveHTML();
} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
}
