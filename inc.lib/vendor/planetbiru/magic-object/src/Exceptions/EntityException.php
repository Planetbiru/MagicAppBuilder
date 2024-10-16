<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class EntityException
 *
 * Custom exception class for handling errors related to entity operations.
 * This can include issues such as validation failures, database errors, or
 * other exceptions that occur during entity processing.
 */
class EntityException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for EntityException.
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
