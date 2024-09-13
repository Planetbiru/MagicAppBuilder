<?php

namespace MagicObject\File;

use MagicObject\Exceptions\FileNotFoundException;
use MagicObject\Exceptions\InvalidParameterException;

/**
 * Upload file item
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoUploadFileItem
{
    /**
     * Variable to store uploade file information
     *
     * @var array
     */
    private $value = array();
    
    /**
     * Constructor
     *
     * @param array $file
     */
    public function __construct($file)
    {
        if(!isset($file) || !is_array($file) || empty($file))
        {
            throw new InvalidParameterException("Invalid constructor");
        }
        $this->value = $file;
    }
    
    /**
     * Copy file to destination path
     *
     * @param string $path
     * @return boolean
     * @throws FileNotFoundException
     */
    public function copyTo($path)
    {
        if(isset($this->value['tmp_name']))
        {
            return copy($this->value['tmp_name'], $path);
        }
        else
        {
            throw new FileNotFoundException("Temporary file not found");
        }
    }
    
    /**
     * Move uploaded file to destination path
     *
     * @param string $path
     * @return boolean
     * @throws FileNotFoundException
     */
    public function moveTo($path)
    {
        if(isset($this->value['tmp_name']))
        {
            return move_uploaded_file($this->value['tmp_name'], $path);
        }
        else
        {
            throw new FileNotFoundException("Temporary file not found");
        }
    }
    
    /**
     * Get temporary name
     *
     * @return string
     */
    public function getTmpName()
    {
        return !isset($this->value['tmp_name']) ? null : $this->value['tmp_name'];
    }
    
    /**
     * Get file name
     *
     * @return string
     */
    public function getName()
    {
        return !isset($this->value['name']) ? null : $this->value['name'];
    }
    
    /**
     * Get error
     *
     * @return mixed
     */
    public function getError()
    {
        return !isset($this->value['error']) ? null : $this->value['error'];
    }
    
    /**
     * Get file size
     *
     * @return integer
     */
    public function getSize()
    {
        return !isset($this->value['size']) ? 0 : $this->value['size'];
    }
    
    /**
     * Get file type
     *
     * @return string
     */
    public function getType()
    {
        return !isset($this->value['type']) ? null : $this->value['type'];
    }
}