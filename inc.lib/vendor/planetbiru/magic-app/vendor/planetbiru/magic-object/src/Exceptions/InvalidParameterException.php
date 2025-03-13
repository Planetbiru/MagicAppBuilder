<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class InvalidParameterException
 *
 * Custom exception class for handling errors related to invalid parameters.
 * This exception can be thrown when a function or method receives parameters 
 * that do not meet the expected criteria, such as invalid types, missing 
 * required values, or parameters that fall outside of acceptable ranges.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class InvalidParameterException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for InvalidParameterException.
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
