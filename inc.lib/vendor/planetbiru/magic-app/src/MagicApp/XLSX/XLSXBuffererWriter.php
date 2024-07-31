<?php

namespace MagicApp\XLSX;

class XLSXBuffererWriter
{
	/**
	 * Resource to file
	 *
	 * @var resource
	 */
	protected $fd = null;
	
	/**
	 * Buffer
	 *
	 * @var string
	 */
	protected $buffer = '';
	
	/**
	 * Flag to check UF8
	 *
	 * @var boolean
	 */
	protected $checkUf8 = false;

	/**
	 * Constructor
	 *
	 * @param string $filename File name
	 * @param string $fd_fopen_flags Open file flag
	 * @param boolean $checkUf8 Flag to check UF8
	 */
	public function __construct($filename, $fd_fopen_flags = 'w', $checkUf8 = false)
	{
		$this->checkUf8 = $checkUf8;
		$this->fd = fopen($filename, $fd_fopen_flags);
		if ($this->fd === false) {
			XLSXWriter::log("Unable to open $filename for writing.");
		}
	}

	/**
	 * Write to buffer
	 *
	 * @param string $string
	 * @return self
	 */
	public function write($string)
	{
		$this->buffer .= $string;
		if (isset($this->buffer[8191])) {
			$this->purge();
		}
		return $this;
	}

	/**
	 * Purge
	 *
	 * @return self
	 */
	protected function purge()
	{
		if ($this->fd) {
			if ($this->checkUf8 && !self::isValidUTF8($this->buffer)) {
				XLSXWriter::log("Error, invalid UTF8 encoding detected.");
				$this->checkUf8 = false;
			}
			fwrite($this->fd, $this->buffer);
			$this->buffer = '';
		}
		return $this;
	}

	/**
	 * Close file
	 *
	 * @return self
	 */
	public function close()
	{
		$this->purge();
		if ($this->fd) {
			fclose($this->fd);
			$this->fd = null;
		}
		return $this;
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Returns the current position of the file read/write pointer
	 *
	 * @return integer
	 */
	public function ftell()
	{
		if ($this->fd) {
			$this->purge();
			return ftell($this->fd);
		}
		return -1;
	}

	/**
	 * Seeks on a file pointer
	 *
	 * @param integer $pos
	 * @return integer
	 */
	public function fseek($pos)
	{
		if ($this->fd) {
			$this->purge();
			return fseek($this->fd, $pos);
		}
		return -1;
	}

	/**
	 * Validate UTF8
	 *
	 * @param string $string
	 * @return boolean
	 */
	protected static function isValidUTF8($string)
	{
		if (function_exists('mb_check_encoding')) {
			return mb_check_encoding($string, 'UTF-8') ? true : false;
		}
		return preg_match("//u", $string) ? true : false;
	}
}
