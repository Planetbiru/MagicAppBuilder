<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class ClassNotFoundException
 *
 * Custom exception class for handling scenarios where a class could not be found.
 * This exception is typically thrown when attempting to use or instantiate a class
 * that does not exist or is not autoloaded properly. 
 * It can be used in situations such as when a class name is misspelled, 
 * or the class file is not found in the expected location.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class ClassNotFoundException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for ClassNotFoundException.
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
