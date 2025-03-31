<?php
/**
 * Undocumented function
 *
 * @param string $dir
 * @param int $level
 * @return string
 */
function getDirTree($dir, $level = 0) {
    $files = scandir($dir);
    $html = '';

    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $html .= str_repeat('&nbsp;', $level * 4) . '<li><span class="dir" data-dir="' . $path . '" style="cursor:pointer;">' . $file . '</span></li>';
            }
            else if (is_file($path)) {
                $html .= str_repeat('&nbsp;', $level * 4) . '<li><span class="file" data-file="' . $path . '" style="cursor:pointer;">' . $file . '</span></li>';
                $html .= getDirTree($path, $level + 1); 
            }
        }
    }

    return $html;
}
