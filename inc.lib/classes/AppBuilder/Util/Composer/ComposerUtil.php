<?php

namespace AppBuilder\Util\Composer;

use DOMDocument;
use DOMNodeList;
use DOMXPath;

class ComposerUtil
{
    const PACKAGE_URL = "https://packagist.org/packages/planetbiru/magic-app";

    /**
     * Check if DOMNodeList is valid
     *
     * @param DOMNodeList $path
     * @return boolean
     */
    public static function isValidDomPath($path)
    {
        return $path != null && $path->item(0) != null;
    }

    /**
     * Get MagicApp Version List
     *
     * @return string[]
     */
    public static function getMagicAppVersionList()
    {
        $html = file_get_contents(self::PACKAGE_URL);
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $versionPath = new DOMXPath($doc);
        $versionList = $versionPath->query("//*[contains(@class, 'versions')]/ul");
        $versions = array();
        if(self::isValidDomPath($versionList))
        {
            foreach($versionList->item(0)->childNodes as $child)
            {
                if(isset($child))
                {
                    $version = trim($child->textContent);
                    if(!empty($version) && stripos($version, 'dev-') === false)
                    {
                        $versions[] = $version;
                    }
                }
            }
        }
        return $versions;
    }
}
