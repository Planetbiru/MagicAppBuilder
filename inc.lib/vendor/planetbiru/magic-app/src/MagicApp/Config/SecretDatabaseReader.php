<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

/**
 * Class SecretDatabaseReader
 * 
 * This class is responsible for reading and decrypting the configuration details
 * for a database connection. The class extends the `SecretObject` class and 
 * retrieves sensitive database configuration values (such as database driver, 
 * file, host, port, username, password, etc.) from an encrypted source.
 * 
 * The properties of this class represent various database connection settings, 
 * and are marked with the `@DecryptOut` annotation to signify that these values 
 * should be decrypted when accessed. This class is designed to be used for 
 * managing sensitive database connection details securely.
 *
 * @package MagicApp\Config
 * @link https://github.com/Planetbiru/MagicApp
 * @author Kamshory
 */
class SecretDatabaseReader extends SecretObject
{
    /**
	 * Database server driver
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $driver;
	
	/**
	 * Database file path (for SQLite)
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseFilePath;

	/**
	 * Database server host
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $host;

	/**
	 * Database port
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $port;

	/**
	 * Database username
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $username;

	/**
	 * Database user password
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $password;

	/**
	 * Database name
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName;
	
	/**
	 * Database schema
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databseSchema;
}