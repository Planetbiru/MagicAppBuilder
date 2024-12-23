<?php

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$separatorNLT = "\r\n\t";

if ($appConfig->getApplication() == null) {
    exit();
}

try {
    $baseModuleDirectory = $appConfig->getApplication()->getBaseModuleDirectory();

    // Create a new DOMDocument object to build the HTML structure
    $doc = new DOMDocument();
    $doc->formatOutput = true;

    // Create the outer div
    $div = $doc->createElement('div');
    $doc->appendChild($div);

    if (isset($baseModuleDirectory) && is_array($baseModuleDirectory)) {
        foreach ($baseModuleDirectory as $elem) {
            // Create module group div
            $moduleGroupDiv = $doc->createElement('div');
            $moduleGroupDiv->setAttribute('class', 'module-group');
            $div->appendChild($moduleGroupDiv);

            // Create h4 for module name with checkbox
            $h4 = $doc->createElement('h4');
            $label = $doc->createElement('label');
            $input = $doc->createElement('input');
            $input->setAttribute('type', 'checkbox');
            $input->setAttribute('class', 'select-module');
            $label->appendChild($input);
            $labelText = $doc->createTextNode(" " . $elem->getName());
            $label->appendChild($labelText);
            $h4->appendChild($label);
            $moduleGroupDiv->appendChild($h4);

            $target = trim($elem->getPath(), "/\\");
            if (!empty($target)) {
                $target = "/" . $target;
            }

            $baseDirectory = $appConfig->getApplication()->getBaseApplicationDirectory();
            $dir = $baseDirectory . "$target";
            $pattern = $baseDirectory . "$target/*.php";
            $list = glob($pattern);
            
            // Create the unordered list for the module files
            $ul = $doc->createElement('ul');
            $ul->setAttribute('class', 'module-ul');
            $moduleGroupDiv->appendChild($ul);

            foreach ($list as $file) {
                $module = basename($file, '.php');
                $filetime = date('Y-m-d H:i:s', filemtime($file));
                $path = str_replace("\\", "//", trim($target . '/' . $module, "//")) . ".php";

                // Create list item with checkbox for each file
                $li = $doc->createElement('li');
                $li->setAttribute('class', 'file-li');
                
                $fileLabel = $doc->createElement('label');
                $fileInput = $doc->createElement('input');
                $fileInput->setAttribute('class', 'module-for-translate');
                $fileInput->setAttribute('type', 'checkbox');
                $fileInput->setAttribute('value', $path);
                $fileLabel->appendChild($fileInput);
                $fileLabelText = $doc->createTextNode(" " . $module . '.php');
                $fileLabel->appendChild($fileLabelText);

                $li->appendChild($fileLabel);
                $ul->appendChild($li);
            }
        }
    }

    // Output the generated HTML
    echo $doc->saveHTML();

} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
}