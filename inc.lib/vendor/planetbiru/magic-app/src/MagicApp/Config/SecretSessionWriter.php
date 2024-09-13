<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

class SecretSessionWriter extends SecretObject
{
    /**
	 * Session save handler
	 *
	 * @EncryptIn
	 * @var string
	 */
	protected $saveHandler;
    
}