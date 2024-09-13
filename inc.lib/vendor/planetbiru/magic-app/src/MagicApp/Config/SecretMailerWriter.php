<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

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