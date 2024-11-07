<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

/**
 * Class SecretMailerReader
 *
 * This class is responsible for reading and decrypting sensitive email 
 * configuration details such as SMTP server settings, sender's credentials, 
 * and other related configurations. The class extends the `SecretObject` 
 * class, which handles the decryption of sensitive data.
 *
 * The properties in this class are used to store the necessary configuration 
 * values for sending emails via an SMTP server. These properties are marked 
 * with the `@DecryptOut` annotation to indicate that the values should be 
 * decrypted when read. This class is intended to securely manage mailer 
 * credentials and configuration settings.
 *
 * @package MagicApp\Config
 * @link https://github.com/Planetbiru/MagicApp
 * @author Kamshory
 */
class SecretMailerReader extends SecretObject
{
    /**
	 * SMTP host
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $host;
    
    /**
	 * SMTP port
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $port;
    
    /**
	 * Sender username
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $username;
    
    /**
	 * Sender password
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $password;
    
    /**
	 * Sender mail address
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $senderAddress;
    
    /**
	 * Sender name
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $senderName;
    
}