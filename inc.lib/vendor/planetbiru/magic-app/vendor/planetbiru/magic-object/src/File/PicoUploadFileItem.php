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
 * @author Kamshory
 * @package MagicObject\File
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
     * Checks if the uploaded file is a multiple file upload.
     *
     * @return bool true if the file is a multiple upload; otherwise, false.
     */
    public function isExists()
    {
        return isset($this->value['tmp_name']) && is_file($this->value['tmp_name']);
    }
    
    /**
     * Copies the uploaded file to a specified destination path.
     *
     * @param string $path The target path where the file will be copied.
     * @return bool true on success; otherwise, false.
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
     * This method attempts to create the target directory if it does not exist.
     *
     * @param string $path The target path where the file will be moved.
     * @return bool true on success; otherwise, false.
     * @throws FileNotFoundException if the temporary file is not found.
     */
    public function moveTo($path)
    {
        if(!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
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
     * Generates a random file name with the specified length.
     *
     * @param int $length The desired length of the random file name.
     * @return string The generated random file name with the same extension as the original file.
     */
    public function getRandomName($length)
    {
        $randomName = substr(bin2hex(random_bytes($length)), 0, $length);
        $name = $this->getName();
        if ($name) {
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            return $randomName . '.' . $extension;
        }
        return $randomName;
    }
    
    /**
     * Gets the file extension of the uploaded file.
     *
     * @return string|null The file extension or null if not set.
     */
    public function getExtension()
    {
        $name = $this->getName();
        if ($name) {
            return pathinfo($name, PATHINFO_EXTENSION);
        }
        return null;
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
