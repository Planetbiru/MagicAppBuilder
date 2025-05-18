<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class DataRetrievalException
 *
 * Custom exception class for handling scenarios where a result is expected
 * but none is returned. This can be useful for database queries or API calls
 * where a missing result should be treated as an exceptional case.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class DataRetrievalException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for DataRetrievalException.
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
