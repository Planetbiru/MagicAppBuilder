<?php

namespace MagicApp\Utility;

/**
 * Class CloudflareUtil
 *
 * A utility class for handling Cloudflare-related operations, specifically
 * for managing IP addresses. This class provides methods to retrieve the
 * client's real IP address when using Cloudflare and to validate IP addresses
 * against known Cloudflare IP ranges.
 */
class CloudflareUtil
{
    /**
     * @var array IP ranges belonging to Cloudflare.
     */
    public static $cloudflareIpRanges = array(
        '204.93.240.0/24',
        '204.93.177.0/24',
        '199.27.128.0/21',
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15'
    );

    /**
     * Get the client's real IP address.
     *
     * This method retrieves the real IP address of the client using the
     * CF-Connecting-IP header provided by Cloudflare. If the header is not
     * present or the request is not validated, it falls back to using the
     * REMOTE_ADDR server variable.
     *
     * @param bool $validateRequest Optional. If true, validates that the
     *                              request is coming from a Cloudflare IP.
     * @return string The client's IP address.
     */
    public static function getClientIp($validateRequest = false)
    {
        // NA by default.
        $ipAddress = 'NA';

        // Check to see if the CF-Connecting-IP header exists.
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            // Assume that the request is invalid unless proven otherwise.
            $validCFRequest = false;
            if ($validateRequest) {
                // Make sure that the request came via Cloudflare.
                foreach (self::$cloudflareIpRanges as $range) {
                    // Use the ip_in_range function from Joomla.
                    if (self::ipInRange($_SERVER['REMOTE_ADDR'], $range)) {
                        // IP is valid. Belongs to Cloudflare.
                        $validCFRequest = true;
                        break;
                    }
                }
            } else {
                $validCFRequest = true;
            }

            // If it's a valid Cloudflare request
            if ($validCFRequest) {
                // Use the CF-Connecting-IP header.
                $ipAddress = $_SERVER["HTTP_CF_CONNECTING_IP"];
            } else {
                // If it isn't valid, then use REMOTE_ADDR.
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            }
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            // Otherwise, use REMOTE_ADDR.
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        return $ipAddress;
    }

    /**
     * Check if an IP address is within a specified range.
     *
     * This method determines whether a given IP address falls within a
     * specified IP range, which can be in CIDR format or a custom range.
     *
     * @param string $ip The IP address to check.
     * @param string $range The IP range in CIDR or custom format.
     * @return bool True if the IP is in the range, false otherwise.
     */
    public static function ipInRange($ip, $range)
    {
        if (strpos($range, '/') !== false) {
            return self::withRage1($ip, $range);
        } else {
            return self::withRange2($ip, $range);
        }
    }

    /**
     * Determine if an IP address is within a CIDR range.
     *
     * @param string $ip The IP address to check.
     * @param string $range The CIDR range (e.g., '192.168.1.0/24').
     * @return bool True if the IP is in the CIDR range, false otherwise.
     */
    public static function withRage1($ip, $range)
    {
        // $range is in IP/NETMASK format
        list($range, $netmask) = explode('/', $range, 2);
        if (strpos($netmask, '.') !== false) {
            // $netmask is a 255.255.0.0 format
            $netmask = str_replace('*', '0', $netmask);
            $netmask_dec = ip2long($netmask);
            return (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec);
        } else {
            // $netmask is a CIDR size block
            // fix the range argument
            $x = explode('.', $range);
            while (count($x) < 4) {
                $x[] = '0';
            }
            list($a, $b, $c, $d) = $x;
            $range = sprintf("%u.%u.%u.%u", empty($a) ? '0' : $a, empty($b) ? '0' : $b, empty($c) ? '0' : $c, empty($d) ? '0' : $d);
            $range_dec = ip2long($range);
            $ip_dec = ip2long($ip);

            # Strategy 2 - Use math to create it
            $wildcard_dec = pow(2, (32 - $netmask)) - 1;
            $netmask_dec = ~$wildcard_dec;

            return ($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec);
        }
    }

    /**
     * Determine if an IP address is within a non-CIDR range.
     *
     * @param string $ip The IP address to check.
     * @param string $range The range in custom format (e.g., '192.168.1.*' or '192.168.1.0-192.168.1.255').
     * @return bool True if the IP is in the range, false otherwise.
     */
    public static function withRange2($ip, $range)
    {
        // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
        if (strpos($range, '*') !== false) { // a.b.*.* format
            // Just convert to A-B format by setting * to 0 for A and 255 for B
            $lower = str_replace('*', '0', $range);
            $upper = str_replace('*', '255', $range);
            $range = "$lower-$upper";
        }

        if (strpos($range, '-') !== false) { // A-B format
            list($lower, $upper) = explode('-', $range, 2);
            $lower_dec = (float)sprintf("%u", ip2long($lower));
            $upper_dec = (float)sprintf("%u", ip2long($upper));
            $ip_dec = (float)sprintf("%u", ip2long($ip));
            return ($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec);
        }

        return false;
    }
}
