<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

/**
 * Class SecretDatabaseWriter
 * 
 * This class is responsible for encrypting and writing the configuration details 
 * for a database connection. The class extends the `SecretObject` class and 
 * manages sensitive database configuration values (such as database driver, 
 * file, host, port, username, password, etc.) before they are stored or transmitted 
 * in an encrypted format.
 * 
 * The properties of this class represent various database connection settings, 
 * and are marked with the `@EncryptIn` annotation to signify that these values 
 * should be encrypted before being saved or used. This class is intended for 
 * securely managing database connection details, ensuring that sensitive data 
 * is not stored in plaintext.
 *
 * @package MagicApp\Config
 * @link https://github.com/Planetbiru/MagicApp
 * @author Kamshory
 */
class SecretDatabaseWriter extends SecretObject
{
    /**
	 * Database server driver
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $driver;
	
	/**
	 * Database file path (for SQLite)
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $databaseFilePath;

	/**
	 * Database server host
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $host;

	/**
	 * Database port
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $port;

	/**
	 * Database username
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $username;

	/**
	 * Database user password
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $password;

	/**
	 * Database name
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $databaseName;
	
	/**
	 * Database schema
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $databseSchema;
}