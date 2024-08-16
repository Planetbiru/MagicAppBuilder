<?php

namespace AppBuilder\Util;

class ErrorCacheUtil
{

    public static function getCacheDir()
    {
        return dirname(__DIR__)."/tmp/";
    }
    public static function saveCacheError($path, $ft, $err)
    {
        self::deleteCacheError($path);
        file_put_contents($path."-".$ft, $err);
    }

    public static function getCacheError($path, $ft)
    {
        return file_get_contents($path."-".$ft);
    }

    public static function deleteCacheError($path)
    {
        $files = glob($path . '-*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}