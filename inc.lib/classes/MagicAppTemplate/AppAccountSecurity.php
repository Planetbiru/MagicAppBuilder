<?php

namespace MagicAppTemplate;

use MagicObject\SecretObject;

/**
 * Class AppAccountSecurity
 *
 * This class provides methods for securely hashing user passwords
 * using a configurable algorithm and salt from application configuration.
 */
class AppAccountSecurity
{
    /**
     * Generates a hashed string using the specified algorithm and salt from configuration.
     *
     * @param SecretObject $appConfig The application configuration object containing account security settings.
     * @param string $appSessionPassword The base password or session token to hash.
     * @param int $iterations The number of times the hashing algorithm should be applied.
     * @return string The resulting hashed string after the specified number of iterations.
     */
    public static function generateHash($appConfig, $appSessionPassword, $iterations = 1)
    {
        // Retrieve account security settings from configuration or use a default SecretObject.
        $appAccountSecurity = $appConfig->issetAccountSecurity() ? $appConfig->getAccountSecurity() : new SecretObject();
        // Get hashing algorithm (default to 'sha1' if not provided).
        $appSecurityAlgorithm = $appAccountSecurity->getAlgorithm();
        if (!isset($appSecurityAlgorithm) || empty($appSecurityAlgorithm)) {
            $appSecurityAlgorithm = 'sha1';
        }

        // Get salt value (default to empty string if not provided).
        $appSecuritySalt = $appAccountSecurity->getSalt();
        if (!isset($appSecuritySalt)) {
            $appSecuritySalt = '';
        }

        // Initialize result with the raw password/session token.
        $result = $appSessionPassword;

        // Apply hashing algorithm with salt repeatedly for the specified number of iterations.
        $i = 0;
        do {
            $result = hash($appSecurityAlgorithm, $result . $appSecuritySalt);
            $i++;
        } while ($i < $iterations);

        // Return the final hashed result.
        return $result;
    }
}
