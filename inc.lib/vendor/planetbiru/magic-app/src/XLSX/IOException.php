<?php

namespace MagicApp\XLSX;

use Exception;
use Throwable;

/**
 * Class IOException
 *
 * Custom exception class for handling input/output errors.
 */
class IOException extends Exception
{
    /**
     * Previous exception that led to this exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * IOException constructor.
     *
     * @param string $message Exception message
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->previous = $previous;
    }

    /**
     * Get the previous exception
     *
     * @return Throwable|null Previous exception
     */
    public function getPreviousException()
    {
        return $this->previous;
    }
}
