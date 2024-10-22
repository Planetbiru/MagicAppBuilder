<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class NoUpdatableColumnException
 *
 * Custom exception class for handling scenarios where an attempt 
 * is made to update a database record, but no columns are available 
 * for updating. This exception is typically thrown during operations 
 * where it is essential to have at least one updatable column defined, 
 * such as in ORM frameworks or data access layers. It helps to ensure 
 * that update operations are valid and that the integrity of the 
 * database is maintained.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class NoUpdatableColumnException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for NoUpdatableColumnException.
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
