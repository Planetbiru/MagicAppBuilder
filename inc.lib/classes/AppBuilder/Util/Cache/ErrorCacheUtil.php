<?php

namespace AppBuilder\Util\Cache;

class ErrorCacheUtil
{

    public static function getCacheDir()
    {
        return dirname(__DIR__)."/tmp/";
    }
    public static function saveCacheError($path, $ft, $err)
    {
        $dir = dirname($path);
        if(!file_exists($dir))
        {
            mkdir($dir, 0755, true);
        }
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