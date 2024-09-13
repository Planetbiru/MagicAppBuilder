<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

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