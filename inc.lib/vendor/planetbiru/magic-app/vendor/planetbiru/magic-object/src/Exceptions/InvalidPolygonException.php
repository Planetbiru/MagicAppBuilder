<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class InvalidPolygonException
 *
 * Custom exception class for handling errors related to invalid polygon definitions.
 * This exception can be thrown when a polygon fails validation criteria, such as 
 * having an insufficient number of vertices, improper angle measurements, or 
 * invalid geometric configurations. It is useful in applications involving 
 * geometric computations, shape processing, or graphical representations.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class InvalidPolygonException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for InvalidPolygonException.
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
