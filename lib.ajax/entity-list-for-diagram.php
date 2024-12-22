<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$separatorNLT = "\r\n\t";

if ($appConfig->getApplication() == null) {
    exit();
}

try {
    $inputGet = new InputGet();
    $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $chk = $inputGet->getAutoload() == 'true' ? ' checked' : '';

    // Create a new DOMDocument to generate the HTML structure
    $dom = new DOMDocument();
    $dom->formatOutput = true;

    // Create the outer div element
    $div = $dom->createElement('div');
    $dom->appendChild($div);

    // Add Data Section
    $h4Data = $dom->createElement('h4', 'Data');
    $div->appendChild($h4Data);

    $baseEntity = $appConfig->getApplication()->getBaseEntityDataNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntity, "\\/"));

    // Get list of PHP files
    $list = glob($baseDir . "/*.php");
    $liElements = [];

    // Process each file
    foreach ($list as $idx => $file) {
        $entity = basename($file, '.php');
        $dir = basename(dirname($file));
        $return_var = ErrorChecker::errorCheck($cacheDir, $file);
        
        // Create <li> elements for valid files
        $li = $dom->createElement('li');
        $li->setAttribute('class', 'entity-li');

        if ($return_var === 0) {
            $filetime = date('Y-m-d H:i:s', filemtime($file));
            $tableInfo = EntityUtil::getTableName($file);
            $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;

            // Create the checkbox input
            $input = $dom->createElement('input');
            $input->setAttribute('type', 'checkbox');
            $input->setAttribute('class', 'entity-checkbox');
            $input->setAttribute('name', 'entity[' . $idx . ']');
            $input->setAttribute('value', $dir . '\\' . $entity);
            if ($inputGet->getAutoload() == 'true') {
                $input->setAttribute('checked', 'checked');
            }

            // Add a whitespace (text node) after the checkbox
            $whitespace = $dom->createTextNode(' ');

            // Create the link (a) element
            $a = $dom->createElement('a', $entity);
            $a->setAttribute('href', '#');
            $a->setAttribute('data-entity-name', $dir . '\\' . $entity);
            $a->setAttribute('data-toggle', 'tooltip');
            $a->setAttribute('data-placement', 'top');
            $a->setAttribute('data-title', $filetime);

            // Append the input, whitespace, and a elements to the li element
            $li->appendChild($input);
            $li->appendChild($whitespace);
            $li->appendChild($a);
            $liElements[$tableName][] = $li;
        } else {
            // Create <li> elements for invalid files (syntax errors)
            $li->setAttribute('class', 'entity-li file-syntax-error');

            // Create the checkbox input (disabled)
            $input = $dom->createElement('input');
            $input->setAttribute('type', 'checkbox');
            $input->setAttribute('class', 'entity-checkbox');
            $input->setAttribute('name', 'entity[' . $idx . ']');
            $input->setAttribute('value', $dir . '\\' . $entity);
            $input->setAttribute('disabled', 'disabled');
            $input->setAttribute('data-toggle', 'tooltip');
            $input->setAttribute('data-placement', 'top');
            $input->setAttribute('data-title', $filetime);

            // Add a whitespace (text node) after the checkbox
            $whitespace = $dom->createTextNode(' ');

            // Create the span element for entity name
            $span = $dom->createElement('span', $entity);

            // Append the input, whitespace, and span elements to the li element
            $li->appendChild($input);
            $li->appendChild($whitespace);
            $li->appendChild($span);
            $liElements[$idx][] = $li;
        }
    }

    // Create the <ul> element and append all <li> elements
    $ulData = $dom->createElement('ul');
    $div->appendChild($ulData);

    foreach ($liElements as $elements) {
        foreach ($elements as $li) {
            $ulData->appendChild($li);
        }
    }

    // Add App Section
    $h4App = $dom->createElement('h4', 'App');
    $div->appendChild($h4App);

    // Process the App files
    $baseEntity = $appConfig->getApplication()->getBaseEntityAppNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseEntity, "\\/"));

    // Get list of PHP files
    $list = glob($baseDir . "/*.php");
    $liElements = [];

    foreach ($list as $idx => $file) {
        $entity = basename($file, '.php');
        $dir = basename(dirname($file));
        $return_var = ErrorChecker::errorCheck($cacheDir, $file);

        // Create <li> elements for valid app files
        $li = $dom->createElement('li');
        $li->setAttribute('class', 'entity-li');

        if ($return_var === 0) {
            $filetime = date('Y-m-d H:i:s', filemtime($file));
            $tableInfo = EntityUtil::getTableName($file);
            $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;

            // Create the checkbox input
            $input = $dom->createElement('input');
            $input->setAttribute('type', 'checkbox');
            $input->setAttribute('class', 'entity-checkbox');
            $input->setAttribute('name', 'entity[' . $idx . ']');
            $input->setAttribute('value', $dir . '\\' . $entity);
            if ($inputGet->getAutoload() == 'true') {
                $input->setAttribute('checked', 'checked');
            }

            // Add a whitespace (text node) after the checkbox
            $whitespace = $dom->createTextNode(' ');

            // Create the link (a) element
            $a = $dom->createElement('a', $entity);
            $a->setAttribute('href', '#');
            $a->setAttribute('data-entity-name', $dir . '\\' . $entity);
            $a->setAttribute('data-toggle', 'tooltip');
            $a->setAttribute('data-placement', 'top');
            $a->setAttribute('data-title', $filetime);

            // Append the input, whitespace, and a elements to the li element
            $li->appendChild($input);
            $li->appendChild($whitespace);
            $li->appendChild($a);
            $liElements[$tableName][] = $li;
        } else {
            // Create <li> elements for invalid app files (syntax errors)
            $li->setAttribute('class', 'entity-li file-syntax-error');

            // Create the checkbox input (disabled)
            $input = $dom->createElement('input');
            $input->setAttribute('type', 'checkbox');
            $input->setAttribute('class', 'entity-checkbox');
            $input->setAttribute('name', 'entity[' . $idx . ']');
            $input->setAttribute('value', $dir . '\\' . $entity);
            $input->setAttribute('disabled', 'disabled');
            $input->setAttribute('data-toggle', 'tooltip');
            $input->setAttribute('data-placement', 'top');
            $input->setAttribute('data-title', $filetime);

            // Add a whitespace (text node) after the checkbox
            $whitespace = $dom->createTextNode(' ');

            // Create the span element for entity name
            $span = $dom->createElement('span', $entity);

            // Append the input, whitespace, and span elements to the li element
            $li->appendChild($input);
            $li->appendChild($whitespace);
            $li->appendChild($span);
            $liElements[$idx][] = $li;
        }
    }

    // Create the <ul> element and append all <li> elements
    $ulApp = $dom->createElement('ul');
    $div->appendChild($ulApp);

    foreach ($liElements as $elements) {
        foreach ($elements as $li) {
            $ulApp->appendChild($li);
        }
    }

    // Output the HTML content
    echo $dom->saveHTML();
} catch (Exception $e) {
    error_log($e->getMessage());
    // Handle exception
}
