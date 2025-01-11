<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

/**
 * PicoDatabaseCredentials class
 * 
 * This class encapsulates database credentials and utilizes the SecretObject to encrypt all attributes,
 * ensuring the security of database configuration details from unauthorized access.
 * 
 * It provides getter methods to retrieve database connection parameters such as driver, host, port,
 * username, password, database name, schema, and application time zone.
 * 
 * Example usage:
 * ```php
 * <?php
 * $credentials = new PicoDatabaseCredentials();
 * $credentials->setHost('localhost');
 * $credentials->setUsername('user');
 * $credentials->setPassword('password');
 * ```
 * 
 * The attributes are automatically encrypted when set, providing a secure way to handle sensitive
 * information within your application.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseCredentials extends SecretObject
{
    /**
     * Database driver (e.g., 'mysql', 'pgsql', 'mariadb', 'sqlite').
     *
     * @var string
     */
    protected $driver;

    /**
     * Database file path for SQLite.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $databaseFilePath;

    /**
     * Database server host.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $host;
    
    /**
     * Database server port.
     *
     * @var int
     */
    protected $port;

    /**
     * Database username.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $username;

    /**
     * Database user password.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $password;

    /**
     * Database name.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $databaseName;

    /**
     * Database schema (default: 'public').
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $databaseSchema; 

    /**
     * Application time zone.
     *
     * @var string
     */
    protected $timeZone;

    /**
     * Charset
     *
     * @var string
     */
    protected $charset;

}
