<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class FileNotFoundException
 *
 * Custom exception class for handling file not found errors.
 * This can be used in scenarios where a required file is missing,
 * such as when attempting to read or access a file that does not exist.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class FileNotFoundException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for FileNotFoundException.
     *
     * @param string $message  Exception message
     * @param int $code        Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
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
