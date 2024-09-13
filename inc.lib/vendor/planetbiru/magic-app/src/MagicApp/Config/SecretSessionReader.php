<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

class SecretSessionReader extends SecretObject
{
    /**
	 * Session save handler
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $saveHandler;
    
}