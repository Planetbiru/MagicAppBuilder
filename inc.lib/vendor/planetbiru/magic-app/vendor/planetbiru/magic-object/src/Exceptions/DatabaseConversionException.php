<?php

namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when a database type conversion fails or encounters an unsupported dialect.
 * 
 * This exception is typically used during database schema translation processes
 * where a field or column type cannot be properly mapped from one SQL dialect
 * to another (e.g., MySQL to PostgreSQL, SQLite to MySQL, etc).
 * 
 * Useful for debugging or safely aborting migrations when incompatibility is detected.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class DatabaseConversionException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for DatabaseConversionException.
     *
     * @param string $message  Exception message
     * @param int $code        Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->previous = $previous;
    }

    /**
     * Get the previous exception.
     *
     * @return Throwable|null The previous exception
     */
    public function getPreviousException()
    {
        return $this->previous;
    }
}
