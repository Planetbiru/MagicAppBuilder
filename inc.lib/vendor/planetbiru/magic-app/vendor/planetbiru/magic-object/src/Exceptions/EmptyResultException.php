<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class EmptyResultException
 *
 * Custom exception class for handling scenarios where a result is expected
 * but none is returned. This can be useful for database queries or API calls
 * where a missing result should be treated as an exceptional case.
 */
class EmptyResultException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for EmptyResultException.
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
