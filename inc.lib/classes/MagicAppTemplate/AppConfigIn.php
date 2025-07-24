<?php

namespace MagicAppTemplate;

use MagicObject\SecretObject;

/**
 * Class AppConfigIn
 *
 * Represents the input configuration for the application,
 * specifically handling sensitive data such as database and session settings.
 * This class extends SecretObject and supports automatic decryption of protected fields.
 *
 * @package MagicAppTemplate
 */
class AppConfigIn extends SecretObject
{
    /**
     * Database configuration (encrypted input)
     *
     * This property holds the database connection details, which are expected
     * to be encrypted. The @DecryptIn annotation indicates that the value will
     * be decrypted automatically when accessed.
     *
     * @DecryptIn
     * @var string
     */
    protected $database;

    /**
     * Sessions configuration (encrypted input)
     *
     * This property contains session-related configuration data,
     * which is also expected to be encrypted and decrypted via the framework.
     *
     * @DecryptIn
     * @var string
     */
    protected $sessions;

    /**
     * Account security configuration (encrypted input)
     *
     * This property holds security-related settings used for account protection.
     * The value is encrypted and will be decrypted automatically when accessed.
     *
     * @DecryptIn
     * @var string
     */
    protected $accountSecurity;
}
