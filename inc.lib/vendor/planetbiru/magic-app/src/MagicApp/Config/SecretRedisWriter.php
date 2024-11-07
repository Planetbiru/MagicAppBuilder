<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

/**
 * Class SecretRedisWriter
 *
 * This class is responsible for securely handling and encrypting Redis 
 * configuration details such as the server connection parameters. It extends 
 * the `SecretObject` class, which ensures that sensitive data is encrypted 
 * when being stored or written to persistent storage (e.g., a database or file system).
 *
 * The properties in this class are encrypted using the `@EncryptIn` annotation 
 * to ensure that the sensitive data is encrypted before being stored.
 *
 * This class provides secure access to Redis connection settings such as 
 * host, port, and password, and is designed to be used in applications 
 * that require the encryption of Redis configuration parameters before they 
 * are stored.
 *
 * @package MagicApp\Config
 * @link https://github.com/Planetbiru/MagicApp
 * @author Kamshory
 */
class SecretRedisWriter extends SecretObject
{
	/**
	 * Redis server host
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $host;

	/**
	 * Redis port
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $port;

	/**
	 * Redis password
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $password;
}