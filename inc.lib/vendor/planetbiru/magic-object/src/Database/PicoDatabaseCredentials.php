<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

/**
 * PicoDatabaseCredentials class
 * The SecretObject will encrypt all attributes to prevent unauthorized user read the database configuration
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseCredentials extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var integer
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName = "";

	/**
	 * Database schema
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $databseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";
}