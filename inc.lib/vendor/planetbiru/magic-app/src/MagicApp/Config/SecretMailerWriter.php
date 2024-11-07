<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

/**
 * Class SecretMailerWriter
 *
 * This class is responsible for encrypting and storing sensitive email 
 * configuration details such as SMTP server settings, sender's credentials, 
 * and other related configurations. The class extends the `SecretObject` 
 * class, which handles the encryption of sensitive data before storing them.
 *
 * The properties in this class store the necessary configuration values for 
 * sending emails via an SMTP server. These properties are marked with the 
 * `@EncryptIn` annotation, which indicates that the values should be encrypted 
 * before being stored. This class is intended to securely manage and encrypt 
 * mailer credentials and configuration settings.
 *
 * @package MagicApp\Config
 * @link https://github.com/Planetbiru/MagicApp
 * @author Kamshory
 */
class SecretMailerWriter extends SecretObject
{
    /**
	 * SMTP host
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $host;
    
    /**
	 * SMTP port
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $port;
    
    /**
	 * Sender username
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $username;
    
    /**
	 * Sender password
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $password;
    
    /**
	 * Sender mail address
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $senderAddress;
    
    /**
	 * Sender name
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $senderName;
    
}