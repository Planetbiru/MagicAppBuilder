<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

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