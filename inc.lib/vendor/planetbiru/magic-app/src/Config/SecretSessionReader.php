<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

/**
 * Class SecretSessionReader
 *
 * This class is responsible for securely handling and decrypting session 
 * configuration details such as session save handler and save path. It extends 
 * the `SecretObject` class, which ensures that sensitive data is decrypted 
 * when being read from persistent storage (e.g., a database or configuration file).
 *
 * The properties in this class are decrypted using the `@DecryptOut` annotation 
 * to ensure that the sensitive data is decrypted when accessed.
 *
 * This class provides secure access to session configuration settings such as 
 * the session save handler and path, and is designed to be used in applications 
 * that require the decryption of session-related configuration parameters.
 *
 * @package MagicApp\Config
 * @link https://github.com/Planetbiru/MagicApp
 * @author Kamshory
 */
class SecretSessionReader extends SecretObject
{
    /**
	 * Session save handler
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $saveHandler;
	
	/**
	 * Session save path
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $savePath;
    
}