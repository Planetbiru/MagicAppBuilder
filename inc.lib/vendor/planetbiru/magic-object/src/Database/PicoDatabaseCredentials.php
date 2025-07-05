<?php

namespace MagicObject\Database;

use InvalidArgumentException;
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
 * $credentials->setDriver('mysql');
 * $credentials->setHost('localhost');
 * $credentials->setPort(3306);
 * $credentials->setUsername('user');
 * $credentials->setPassword('password');
 * $credentials->setDatabaseName('app');
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
     * Import database credentials from a URL datasource string.
     *
     * Supported format:
     * scheme://username:password@host:port/database?schema=public&charset=utf8&timezone=Asia/Jakarta
     *
     * @param string $url The datasource URL
     * @param string|null $username Optional username to override the one from URL
     * @param string|null $password Optional password to override the one from URL
     * @return self Returns the current instance for method chaining.
     */
    public function importFromUrl($url, $username = null, $password = null) // NOSONAR
    {
        $parts = parse_url($url);
        
        if (!$parts) {
            throw new InvalidArgumentException("Invalid database URL");
        }

        // Basic connection parts
        if (isset($parts['scheme'])) {
            $this->setDriver($parts['scheme']);
        }

        if ($this->getDriver() === 'sqlite' && isset($parts['path'])) {
            $this->setDatabaseFilePath($parts['path']);
            return;
        }

        if (isset($parts['host'])) {
            $this->setHost($parts['host']);
        }

        if (isset($parts['port'])) {
            $port = (int) $parts['port'];
            if ($port < 0) {
                throw new InvalidArgumentException("Invalid port: must be a non-negative integer");
            }
            $this->setPort($port);
        }

        // Username and password
        if ($username !== null) {
            $this->setUsername($username);
        } elseif (isset($parts['user'])) {
            $this->setUsername($parts['user']);
        }

        if ($password !== null) {
            $this->setPassword($password);
        } elseif (isset($parts['pass'])) {
            $this->setPassword($parts['pass']);
        }

        if (isset($parts['path'])) {
            $dbName = ltrim($parts['path'], '/');
            $this->setDatabaseName($dbName);
        }

        // Optional query parameters
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);

            if (isset($query['schema'])) {
                $this->setDatabaseSchema($query['schema']);
            }

            if (isset($query['timezone'])) {
                $this->setTimeZone($query['timezone']);
            }

            if (isset($query['charset'])) {
                $this->setCharset($query['charset']);
            }
        }
        return $this;
    }


}
