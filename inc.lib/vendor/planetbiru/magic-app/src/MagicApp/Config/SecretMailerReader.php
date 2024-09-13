<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

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