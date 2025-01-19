<?php

namespace AppBuilder\Util\Error;

/**
 * Class PhpError
 *
 * Represents an error that occurs in PHP code, providing details such as the error code, line number, and associated error messages.
 *
 * @package AppBuilder\Util\Error
 */
class PhpError
{
    /**
     * The error code associated with the PHP error.
     *
     * @var string
     */
    public $errorCode;

    /**
     * The line number where the PHP error occurred in the source file.
     *
     * @var string
     */
    public $lineNumber;

    /**
     * The raw line number (can be in a different format or more detailed).
     *
     * @var string
     */
    public $lineNumberRaw;

    /**
     * A list of error messages or details related to the PHP error.
     *
     * @var string[]
     */
    public $errors;
}
