<?php
class InMemoryCache
{
    private static $cache = array();
    private static $enabled = true; // default: cache aktif

    /**
     * Mengatur status cache (true=aktif, false=nonaktif)
     */
    public static function setEnabled($status)
    {
        self::$enabled = $status;
    }

    /**
     * Mengecek apakah cache sedang aktif
     */
    public static function isEnabled()
    {
        return self::$enabled;
    }

    /**
     * Menyimpan data ke cache
     */
    public static function set($key, $value, $ttl = 300)
    {
        if (!self::$enabled) return;

        self::$cache[$key] = array(
            'value' => $value,
            'expire' => time() + $ttl
        );
    }

    /**
     * Mengambil data dari cache (null jika tidak ada atau expired)
     */
    public static function get(string $key)
    {
        if (!self::$enabled) return null;

        if (!isset(self::$cache[$key])) {
            return null;
        }

        $item = self::$cache[$key];

        // cek TTL
        if ($item['expire'] < time()) {
            unset(self::$cache[$key]);
            return null;
        }

        return $item['value'];
    }

    /**
     * Mengecek apakah ada data di cache (dan belum kedaluwarsa)
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
     * Menghapus cache tertentu
     */
    public static function clear($key)
    {
        unset(self::$cache[$key]);
    }

    /**
     * Membersihkan semua cache
     */
    public static function clearAll()
    {
        self::$cache = array();
    }
}
