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

    $div = $doc->createElement('div');
    $doc->appendChild($div);

    if (isset($baseModuleDirectory) && is_array($baseModuleDirectory)) {
        foreach ($baseModuleDirectory as $elem) {
            // Create and append the h4 element for module name
            $h4 = $doc->createElement('h4', $elem->getName());
            $div->appendChild($h4);

            $target = trim($elem->getPath(), "/\\");
            if (!empty($target)) {
                $target = "/" . $target;
            }

            $baseDirectory = $appConfig->getApplication()->getBaseApplicationDirectory();
            $dir = $baseDirectory . "$target";
            $pattern = $baseDirectory . "$target/*.php";
            $list = glob($pattern);
            $ul = $doc->createElement('ul', '');
            $ul->setAttribute('class', 'module-ul');
            $div->appendChild($ul);

            foreach ($list as $file) {
                $module = basename($file, '.php');
                $filetime = date('Y-m-d H:i:s', filemtime($file));
                $path = str_replace("\\", "//", trim($target . '/' . $module, "//"));

                // Create a list item for each file
                $li = $doc->createElement('li', '');
                $li->setAttribute('class', 'file-li');

                // Create the anchor tag for the file
                $a = $doc->createElement('a', $module . '.php');
                $a->setAttribute('href', '#');
                $a->setAttribute('data-file-name', $path);
                $a->setAttribute('data-toggle', 'tooltip');
                $a->setAttribute('data-placement', 'top');
                $a->setAttribute('data-title', $filetime);
                $li->appendChild($a);

                // Append the list item to the unordered list
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
