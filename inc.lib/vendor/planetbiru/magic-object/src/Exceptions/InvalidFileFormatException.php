<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class InvalidFileFormatException
 *
 * Custom exception class for handling errors related to invalid file formats.
 * This exception can be thrown when a file fails validation based on expected
 * format criteria, such as incorrect file extensions, corrupted files, or 
 * improper file structures. It is useful in file upload processes or data 
 * import functionalities.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class InvalidFileFormatException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for InvalidFileFormatException.
     *
     * @param string $message  Exception message
     * @param int $code        Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->previous = $previous;
    }

    /**
     * Get the previous exception.
     *
     * @return Throwable|null
     */
    public function getPreviousException()
    {
        return $this->previous;
    }
}
