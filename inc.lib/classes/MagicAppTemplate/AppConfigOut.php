<?php

namespace MagicAppTemplate;

use MagicObject\SecretObject;

/**
 * Class AppConfigOut
 *
 * Represents the output configuration for the application,
 * specifically for values that will be encrypted before being stored or transmitted.
 * This class extends SecretObject and uses annotations to automatically encrypt
 * sensitive configuration data such as database and session settings.
 *
 * @package MagicAppTemplate
 */
class AppConfigOut extends SecretObject
{
    /**
     * Database configuration (to be encrypted on output)
     *
     * Holds the database connection settings that should be encrypted
     * before being sent or persisted. The @EncryptOut annotation ensures
     * automatic encryption is applied.
     *
     * @EncryptOut
     * @var string
     */
    protected $database;

    /**
     * Sessions configuration (to be encrypted on output)
     *
     * Contains session configuration data which is sensitive and should
     * be encrypted when output. This is handled automatically by the framework
     * via the @EncryptOut annotation.
     *
     * @EncryptOut
     * @var string
     */
    protected $sessions;
}
