<?php

/**
 * Class InMemoryCache
 *
 * `InMemoryCache` is a simple in-memory caching mechanism implemented using
 * static PHP properties. It stores data temporarily within the current PHP process.
 *
 * This cache is **non-persistent**, meaning all data will be lost once the script
 * execution ends (e.g., after an HTTP request is completed).
 *
 */
class InMemoryCache
{
    private static $cache = array();
    private static $enabled = false; // default: cache is disabled

    /**
     * Sets the cache status (true=enabled, false=disabled).
     */
    public static function setEnabled($status)
    {
        self::$enabled = $status;
    }

    /**
     * Checks if the cache is currently enabled.
     */
    public static function isEnabled()
    {
        return self::$enabled;
    }

    /**
     * Stores data in the cache.
     */
    public static function set($key, $value, $ttl = 300)
    {
        if (!self::$enabled) return;

        self::$cache[$key] = array(
            'value'  => $value,
            'expire' => time() + $ttl,
        );
    }

    /**
     * Retrieves data from the cache (returns null if not found or expired).
     */
    public static function get($key)
    {
        if (!self::$enabled) return null;

        if (!isset(self::$cache[$key]))
        {
            return null;
        }

        $item = self::$cache[$key];

        // check TTL
        if ($item['expire'] < time()) {
            unset(self::$cache[$key]);
            return null;
        }

        return $item['value'];
    }

    /**
     * Checks if data exists in the cache (and has not expired).
     */
    public static function has($key)
    {
        if (!self::$enabled) return false;

        if (!isset(self::$cache[$key])) return false;

        if (self::$cache[$key]['expire'] < time()) {
            unset(self::$cache[$key]);
            return false;
        }

        return true;
    }

    /**
     * Deletes a specific cache entry.
     */
    public static function clear($key)
    {
        unset(self::$cache[$key]);
    }

    /**
     * Clears all cache entries.
     */
    public static function clearAll()
    {
        self::$cache = array();
    }
}
