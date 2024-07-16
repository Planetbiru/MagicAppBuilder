<?php

namespace MagicApp;

use MagicObject\MagicObject;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

class AppInclude
{
    /**
     * Main header
     *
     * @param string $dir
     * @param MagicObject|SecretObject $config
     * @return string
     */
    public function mainAppHeader($dir, $config)
    {
        $path = $config->getBaseIncludeDirectory()."/".$config->getInludeHeaderFile();
        if($config != null && PicoStringUtil::endsWith($path, ".php") && file_exists($path))
        {
            return $path;
        }
        else
        {
            return $dir . "/inc.app/header.php";
        }
    }
    
    /**
     * Main footer
     *
     * @param string $dir
     * @param MagicObject|SecretObject $config
     * @return string
     */
    public function mainAppFooter($dir, $config)
    {
        $path = $config->getBaseIncludeDirectory()."/".$config->getInludeFooterFile();
        if($config != null && PicoStringUtil::endsWith($path, ".php") && file_exists($path))
        {
            return $path;
        }
        else
        {
            return $dir . "/inc.app/footer.php";
        }
    }

    /**
     * Forbidden
     *
     * @param string $dir
     * @param MagicObject|SecretObject $config
     * @return string
     */
    public function appForbiddenPage($dir, $config)
    {
        $path = $config->getBaseIncludeDirectory()."/".$config->getInludeForbiddenFile();
        if($config != null && PicoStringUtil::endsWith($path, ".php") && file_exists($path))
        {
            return $path;
        }
        else
        {
            return $dir . "/inc.app/403.php";
        }
    }

    /**
     * Page not found
     *
     * @param string $dir
     * @param MagicObject|SecretObject $config
     * @return string
     */
    public function appNotFoundPage($dir, $config)
    {
        $path = $config->getBaseIncludeDirectory()."/".$config->getInludeNotFoundFile();
        if($config != null && PicoStringUtil::endsWith($path, ".php") && file_exists($path))
        {
            return $path;
        }
        else
        {
            return $dir . "/inc.app/404.php";
        }
    }
}