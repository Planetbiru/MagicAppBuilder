<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class MatrixCalculationException
 *
 * A custom exception class used specifically for errors related to matrix operations.
 * This can be thrown during invalid operations such as dimension mismatches, 
 * division by zero, or unsupported matrix formats.
 *
 * Extends the base Exception class and provides access to a previous exception for detailed debugging.
 *
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class MatrixCalculationException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for MatrixCalculationException.
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
