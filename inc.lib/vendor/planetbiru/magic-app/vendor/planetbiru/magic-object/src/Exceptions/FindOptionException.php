<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class FindOptionException
 *
 * Custom exception class for handling errors that occur during the process
 * of finding or retrieving options. This exception can be thrown when
 * an expected option is not found, whether in configuration settings,
 * database queries, or other operational contexts.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class FindOptionException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for FindOptionException.
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
