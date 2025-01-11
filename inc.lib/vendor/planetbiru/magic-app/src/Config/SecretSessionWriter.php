<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

/**
 * Class SecretSessionWriter
 *
 * This class is responsible for securely handling and encrypting session 
 * configuration details such as the session save handler and save path. It extends 
 * the `SecretObject` class, which ensures that sensitive data is encrypted before 
 * being written to persistent storage (e.g., a database or configuration file).
 *
 * The properties in this class are encrypted using the `@EncryptIn` annotation 
 * to ensure that sensitive data is securely stored when being written.
 *
 * This class provides secure access to session configuration settings, and is designed 
 * to be used in applications that require encryption of session-related configuration 
 * parameters before they are saved.
 *
 * @package MagicApp\Config
 * @link https://github.com/Planetbiru/MagicApp
 * @author Kamshory
 */
class SecretSessionWriter extends SecretObject
{
    /**
	 * Session save handler
	 *
	 * @EncryptIn
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