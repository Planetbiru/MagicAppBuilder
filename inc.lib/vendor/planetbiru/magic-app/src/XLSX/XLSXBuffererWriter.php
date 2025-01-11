<?php

namespace MagicApp\XLSX;

/**
 * Class XLSXBuffererWriter
 *
 * This class is responsible for writing data to an XLSX file in a buffered manner. 
 * It manages a write buffer to optimize file operations, flushing data to the 
 * underlying file resource when the buffer reaches a certain size. 
 * The class also includes functionality to validate UTF-8 encoding for the data being written. 
 */
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
     * Flag to check UTF-8 encoding
     *
     * @var boolean
     */
    protected $checkUtf8 = false;

    /**
     * Constructor
     *
     * @param string $filename File name
     * @param string $fd_fopen_flags Open file flag
     * @param boolean $checkUtf8 Flag to check UTF-8 encoding
     */
    public function __construct($filename, $fd_fopen_flags = 'w', $checkUtf8 = false)
    {
        $this->checkUtf8 = $checkUtf8;
        $this->fd = fopen($filename, $fd_fopen_flags);
        if ($this->fd === false) {
            XLSXWriter::log("Unable to open $filename for writing.");
        }
    }

    /**
     * Write to buffer
     *
     * @param string $string Data to write
     * @return self The current instance, allowing method chaining
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
     * Purge the buffer, writing its contents to the file
     *
     * @return self The current instance, allowing method chaining
     */
    protected function purge()
    {
        if ($this->fd) {
            if ($this->checkUtf8 && !self::isValidUTF8($this->buffer)) {
                XLSXWriter::log("Error, invalid UTF-8 encoding detected.");
                $this->checkUtf8 = false;
            }
            fwrite($this->fd, $this->buffer);
            $this->buffer = '';
        }
        return $this;
    }

    /**
     * Close the file and flush any remaining buffer
     *
     * @return self The current instance, allowing method chaining
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
     *
     * Automatically closes the file when the object is destroyed
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return integer Current file position or -1 on error
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
     * @param integer $pos Position to seek to
     * @return integer 0 on success, -1 on failure
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
     * Validate UTF-8 encoding of a string
     *
     * @param string $string String to validate
     * @return boolean True if valid UTF-8, false otherwise
     */
    protected static function isValidUTF8($string)
    {
        if (function_exists('mb_check_encoding')) {
            return mb_check_encoding($string, 'UTF-8') ? true : false;
        }
        return preg_match("//u", $string) ? true : false;
    }
}
