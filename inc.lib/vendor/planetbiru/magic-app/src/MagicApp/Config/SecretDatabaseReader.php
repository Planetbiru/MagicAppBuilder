<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

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