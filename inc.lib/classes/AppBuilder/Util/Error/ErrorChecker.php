<?php

namespace AppBuilder\Util\Error;

use AppBuilder\Util\Cache\ErrorCacheUtil;

class ErrorChecker
{

    public static function errorCheck($cacheDir, $path)
    {

        // begin check
        $ft = filemtime($path);
        
        $cachePath = $cacheDir.preg_replace("/[^a-zA-Z0-9]/", "", $path);
        $return_var = 1;
        if(file_exists($cachePath."-".$ft))
        {
            $err = ErrorCacheUtil::getCacheError($cachePath, $ft);
            if($err != 'true')
            {
                $return_var = 0;
            }
        }
        else
        {
            exec("php -l $path 2>&1", $output, $return_var);
            ErrorCacheUtil::saveCacheError($cachePath, $ft, $return_var === 0 ? 'false': 'true');
        }
        // end check
        return $return_var;
    }
}