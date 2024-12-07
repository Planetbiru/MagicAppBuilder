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

    /**
     * Get the database driver.
     *
     * @return string Returns the database driver.
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Get the database host.
     *
     * @return string Returns the database host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the database port.
     *
     * @return int Returns the database port.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get the database username.
     *
     * @return string Returns the database username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the database password.
     *
     * @return string Returns the database password.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get the database name.
     *
     * @return string Returns the database name.
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * Get the database schema.
     *
     * @return string Returns the database schema.
     */
    public function getDatabaseSchema()
    {
        return $this->databaseSchema;
    }

    /**
     * Get the application time zone.
     *
     * @return string Returns the application time zone.
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * Get the charset.
     *
     * @return string The charset currently set.
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set the charset.
     *
     * @param string $charset The charset to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

}
