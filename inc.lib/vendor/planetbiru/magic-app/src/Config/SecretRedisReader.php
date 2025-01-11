<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

/**
 * Class SecretRedisReader
 *
 * This class is responsible for reading and decrypting sensitive Redis 
 * configuration details, such as server connection parameters. The class 
 * extends the `SecretObject` class, which ensures that sensitive data 
 * is decrypted securely when accessed.
 *
 * The properties in this class are encrypted, and the `@DecryptOut` annotation 
 * is used to indicate that the values should be decrypted when read from storage 
 * (e.g., a database or configuration file).
 *
 * This class provides secure access to Redis connection settings such as 
 * host, port, and password. It is designed to be used in applications that 
 * require secure management of Redis configuration parameters.
 *
 * @package MagicApp\Config
 * @link https://github.com/Planetbiru/MagicApp
 * @author Kamshory
 */
class SecretRedisReader extends SecretObject
{
	/**
	 * Redis server host
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $host;

	/**
	 * Redis port
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $port;

	/**
	 * Redis password
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $password;
}