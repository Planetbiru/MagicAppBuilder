<?php

namespace MagicObject\Util\Image;

use Exception;

/**
 * Class ImageUtilException
 *
 * Custom exception class for handling image-related errors in the MagicObject library.
 *
 * **Example:**
 * ```php
 * <?php
 * throw new ImageUtilException("Error processing image", 500);
 * ```
 *
 * @author Kamshory
 * @package MagicObject\Util\Image
 * @link https://github.com/Planetbiru/MagicObject
 */
class ImageUtilException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable
     */
    private $previous;

    /**
     * @param string $message Exception message
     * @param mixed $code Exception code
     * @param Throwable $previous Previous exception
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        if (!is_null($previous)) {
            $this->previous = $previous;
        }
    }
}