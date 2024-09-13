<?php

namespace MagicApp\Config;

use MagicObject\SecretObject;

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