<?php

namespace MagicObject\File;

use MagicObject\Exceptions\FileNotFoundException;
use MagicObject\Exceptions\InvalidParameterException;

/**
 * Class representing an uploaded file item.
 *
 * This class manages the information of an uploaded file and provides methods
 * to interact with the file, such as copying or moving it to a destination path.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoUploadFileItem
{
    /**
     * Array to store uploaded file information.
     *
     * @var array
     */
    private $value = array();
    
    /**
     * Constructor.
     *
     * Initializes the PicoUploadFileItem with file data.
     *
     * @param array $file An associative array containing file upload information.
     * @throws InvalidParameterException if the provided file data is invalid.
     */
    public function __construct($file)
    {
        if (!isset($file) || !is_array($file) || empty($file)) {
            throw new InvalidParameterException("Invalid constructor: file data must be a non-empty array.");
        }
        $this->value = $file;
    }
    
    /**
     * Copies the uploaded file to a specified destination path.
     *
     * @param string $path The target path where the file will be copied.
     * @return bool True on success; otherwise, false.
     * @throws FileNotFoundException if the temporary file is not found.
     */
    public function copyTo($path)
    {
        if (isset($this->value['tmp_name'])) {
            return copy($this->value['tmp_name'], $path);
        } else {
            throw new FileNotFoundException("Temporary file not found.");
        }
    }
    
    /**
     * Moves the uploaded file to a specified destination path.
     *
     * @param string $path The target path where the file will be moved.
     * @return bool True on success; otherwise, false.
     * @throws FileNotFoundException if the temporary file is not found.
     */
    public function moveTo($path)
    {
        if (isset($this->value['tmp_name'])) {
            return move_uploaded_file($this->value['tmp_name'], $path);
        } else {
            throw new FileNotFoundException("Temporary file not found.");
        }
    }
    
    /**
     * Gets the temporary file name.
     *
     * @return string|null The temporary file name or null if not set.
     */
    public function getTmpName()
    {
        return isset($this->value['tmp_name']) ? $this->value['tmp_name'] : null;
    }
    
    /**
     * Gets the original file name.
     *
     * @return string|null The original file name or null if not set.
     */
    public function getName()
    {
        return isset($this->value['name']) ? $this->value['name'] : null;
    }
    
    /**
     * Gets the error associated with the file upload.
     *
     * @return mixed The error code or null if not set.
     */
    public function getError()
    {
        return isset($this->value['error']) ? $this->value['error'] : null;
    }
    
    /**
     * Gets the size of the uploaded file.
     *
     * @return int The file size in bytes; returns 0 if not set.
     */
    public function getSize()
    {
        return isset($this->value['size']) ? $this->value['size'] : 0;
    }
    
    /**
     * Gets the MIME type of the uploaded file.
     *
     * @return string|null The MIME type or null if not set.
     */
    public function getType()
    {
        return isset($this->value['type']) ? $this->value['type'] : null;
    }
}
