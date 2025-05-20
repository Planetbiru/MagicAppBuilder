<?php

namespace MagicAppTemplate;

use MagicObject\Request\InputServer;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

/**
 * Class AppIpForwarder
 *
 * Responsible for setting the user's real IP address into $_SERVER['REMOTE_ADDR']
 * based on HTTP headers, typically used when the application is behind a proxy
 * or load balancer such as Cloudflare or NGINX.
 *
 * This class reads a list of trusted IP forwarding headers (e.g., HTTP_CF_CONNECTING_IP,
 * HTTP_X_FORWARDED_FOR) from a configuration object, and ensures the IP is valid.
 *
 * @package MagicAppTemplate
 */
class AppIpForwarder
{
    const REMOTE_ADDR = 'REMOTE_ADDR';

    /**
     * Sets $_SERVER['REMOTE_ADDR'] to the client’s real IP address
     * based on forwarded headers defined in the configuration object.
     *
     * @param SecretObject $forwargingConfig Configuration object containing:
     *        - getEnabled(): boolean flag to determine whether forwarding is enabled.
     *        - getHeaders(): array of header keys to check for IPs.
     *
     * @return void
     */
    public static function apply($forwargingConfig) // NOSONAR
    {       
        if(!isset($forwargingConfig)) {
            // If no configuration is provided, we cannot proceed
            return;
        }
        
        $headers = $forwargingConfig->getHeaders();
        $enabled = $forwargingConfig->getEnabled();

        if ($enabled && !empty($headers)) {

            // Loop through each configured header
            $inputServer = new InputServer();
            foreach ($headers as $header) {
                $camelHeader = PicoStringUtil::camelize(str_replace('-', '_', strtolower($header)));
                $value = $inputServer->get($camelHeader);
                if (!empty($value)) {
                    // Check if the value is a valid IP address
                    // If the header contains multiple IPs, we will take the first valid one
                    // Note: This is a simplified approach; in production, you may want to
                    // Some headers (e.g. X-Forwarded-For) may contain multiple IPs separated by commas
                    if (strpos($value, ',') !== false) {
                        $parts = explode(',', $value);
                        foreach ($parts as $ip) {
                            $ip = trim($ip);
                            if (self::isValidIP($ip)) {
                                $_SERVER[self::REMOTE_ADDR] = $ip;
                                return;
                            }
                        }
                    } else {
                        $ip = trim($value);
                        if (self::isValidIP($ip)) {
                            $_SERVER[self::REMOTE_ADDR] = $ip;
                            return;
                        }
                    }
                }
            }

            // If no valid IP found, fall back to a safe default
            if (!isset($_SERVER[self::REMOTE_ADDR]) || !self::isValidIP($_SERVER[self::REMOTE_ADDR])) {
                $_SERVER[self::REMOTE_ADDR] = '0.0.0.0';
            }
        }
    }

    /**
     * Validates whether the provided string is a valid IP address (IPv4 or IPv6).
     *
     * @param string $ip The IP address to validate
     * @return bool True if valid, false otherwise
     */
    private static function isValidIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
}
