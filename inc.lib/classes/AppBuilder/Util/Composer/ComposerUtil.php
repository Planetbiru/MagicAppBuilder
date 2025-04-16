<?php

namespace AppBuilder\Util\Composer;

use AppBuilder\Exception\ConnectionException;
use DOMDocument;
use DOMNamedNodeMap;
use DOMNodeList;
use DOMXPath;
use Exception;

/**
 * Class ComposerUtil
 *
 * Utility class for interacting with Composer packages. 
 * This class provides methods to retrieve version information for the MagicApp package from Packagist.
 */
class ComposerUtil
{
    /**
     * The URL of the MagicApp package on Packagist.
     *
     * @var string
     */
    const PACKAGE_URL = "https://packagist.org/packages/planetbiru/magic-app";

    /**
     * Check if a DOMNodeList is valid.
     *
     * @param DOMNodeList $path The DOMNodeList to check.
     * @return boolean True if the DOMNodeList is valid, false otherwise.
     */
    public static function isValidDomPath($path)
    {
        return $path !== null && $path->item(0) !== null;
    }

    /**
     * Get the list of available versions for the MagicApp package.
     *
     * @return array An array of version information, each containing 'key', 'value', and 'latest' status.
     * @throws ConnectionException If there is a problem retrieving the package information.
     */
    public static function getMagicAppVersionList()
    {
        $url = self::PACKAGE_URL;
        $html = "";

        try 
        {
            $html = @file_get_contents($url);

            if ($html === false) 
            {
                throw new ConnectionException("Failed to retrieve content from URL: $url");
            }
            
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $versionPath = new DOMXPath($doc);
            $versionList = $versionPath->query("//*[contains(@class, 'versions')]/ul");
            $versions = array();

            if (self::isValidDomPath($versionList)) 
            {
                foreach ($versionList->item(0)->childNodes as $child) 
                {
                    if (isset($child)) 
                    {
                        $attributes = $child->attributes;
                        $version = trim($child->textContent);
                        if (!empty($version) && stripos($version, 'dev-') === false) 
                        {
                            $versions[] = array(
                                "key" => $version, 
                                "value" => $version,
                                "latest" => self::hasClass($attributes, "open")
                            );
                        }
                    }
                }
            }
            return $versions;   
        } 
        catch (Exception $e) 
        {
            throw new ConnectionException("Failed to retrieve content from URL: $url");
        }
    }
    
    /**
     * Check if a specific class is present in the DOMNode's attributes.
     *
     * @param Countable<DOMNamedNodeMap> $attributes The attributes of the DOMNode.
     * @param string $className The class name to check for.
     * @return boolean True if the class is present, false otherwise.
     */
    public static function hasClass($attributes, $className)
    {
        for ($i = 0; $i < $attributes->length; $i++) 
        {
            if ($attributes->item($i)->name === "class") 
            {
                $classes = $attributes->item($i)->value;
                $arr = explode(" ", $classes);
                if (in_array($className, $arr)) 
                {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Check if the server has internet access, specifically for Packagist.org.
     * Tries using cURL first, then falls back to stream context if cURL is unavailable.
     *
     * @param string $url URL to check connectivity.
     * @param int $timeout Timeout in seconds.
     * @return bool True if the server can access the internet; false otherwise.
     */
    public static function checkInternetConnection($url = 'https://packagist.org', $timeout = 5) // NOSONAR
    {
        // 1. Use cURL if available
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_NOBODY => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200;
        }

        // 2. Fallback: Use stream context

        $options = [
            'http' => [
                'method' => 'HEAD',
                'timeout' => $timeout,
                'ignore_errors' => true, // Allow to read response headers even on HTTP errors
            ]
        ];
        $context = stream_context_create($options);

        $headers = @get_headers($url, 1, $context);
        if ($headers === false) {
            return false;
        }

        // Extract HTTP status code
        if (is_array($headers) && isset($headers[0])) {
            preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $headers[0], $matches);
            $httpCode = isset($matches[1]) ? (int)$matches[1] : 0;

            return $httpCode === 200;
        }

        return false;
    }


}
