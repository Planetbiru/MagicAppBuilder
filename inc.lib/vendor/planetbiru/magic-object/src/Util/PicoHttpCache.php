<?php

namespace MagicObject\Util;

use InvalidArgumentException;

/**
 * Class PicoHttpCache
 *
 * A utility class for managing HTTP caching headers.
 * 
 * This class provides methods to set cache lifetime for HTTP responses.
 *
 * @package MagicObject\Util
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoHttpCache
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Send headers to the browser to cache the current URL.
     *
     * This method sets the appropriate headers for caching,
     * including the expiration date and cache control directives.
     *
     * @param int $lifetime Cache lifetime in seconds.
     * @return void
     * @throws InvalidArgumentException if $lifetime is negative.
     */
    public static function cacheLifetime($lifetime)
    {
        if ($lifetime < 0) {
            throw new InvalidArgumentException('Cache lifetime must be a non-negative integer.');
        }
        $ts = gmdate("D, d M Y H:i:s", time() + $lifetime) . " GMT";
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: max-age=$lifetime");
    }
}