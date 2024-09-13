<?php

namespace MagicObject\Util\File;

class FileUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    public static function getFiles($directory, $level = 0, $origin = null)
    {
        if($level == 0)
        {
            $origin = $directory;
        }
        $files = array_diff(scandir($directory), array('.', '..'));
        $allFiles = array();

        foreach ($files as $file) {
            $fullPath = $directory. DIRECTORY_SEPARATOR .$file;
            $file2add = $file;
            if($level > 0)
            {
                $file2add = substr($fullPath, strlen($origin) + 1);
            }
            is_dir($fullPath) ? array_push($allFiles, ...self::getFiles($fullPath, $level + 1, $origin)) : array_push($allFiles, $file2add);
        }
        return $allFiles;
    }
}