<?php

namespace MagicObject\File;

/**
 * Upload file container
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoUploadFileContainer
{
    /**
     * Array to store uploaded file information
     *
     * @var array
     */
    private $values = array();
    
    /**
     * Constructor
     *
     * @param array $file
     */
    public function __construct($file = null)
    {
        if($file != null)
        {
            $this->values = $file;
        }
    }
    
    /**
     * Check if file is multiple upload or not
     *
     * @return boolean
     */
    public function isMultiple()
    {
        return isset($this->values['tmp_name']) && is_array($this->values['tmp_name']);
    }
    
    /**
     * Check if file is multiple upload or not
     *
     * @param integer $index Uploaded file index
     * @return boolean
     */
    public function isExists($index)
    {
        return $this->isMultiple() && isset($this->values['tmp_name'][$index]);
    }
    
    /**
     * Get total file with similar key
     *
     * @return integer
     */
    public function getFileCount()
    {
        if(empty($this->values))
        {
            return 0;
        }
        return $this->isMultiple() ? count($this->values['tmp_name']) : 1;
    }
    
    /**
     * Get all file
     *
     * @return PicoUploadFileItem[]
     */
    public function getAll()
    {
        $result = array();
        if(!empty($this->values))
        {
            if($this->isMultiple())
            {
                // multiple file
                $count = $this->getFileCount();
                for($i = 0; $i < $count; $i++)
                {
                    $result[] = new PicoUploadFileItem($this->getItem($i));
                }
            }
            else
            {
                // single file
                $result[] = new PicoUploadFileItem($this->values);
            }
        }
        return $result;
    }
    
    /**
     * Get one file
     *
     * @param integer $index Uploade file index
     * @return array
     */
    public function getItem($index)
    {
        $file = array(
            'tmp_name' => $this->values['tmp_name'][$index],
            'name' => $this->values['name'][$index]
        );
        
        if(isset($this->values['error'][$index]))
        {
            $file['error'] = $this->values['error'][$index];
        }
        if(isset($this->values['type'][$index]))
        {
            $file['type'] = $this->values['type'][$index];
        }
        if(isset($this->values['size'][$index]))
        {
            $file['size'] = $this->values['size'][$index];
        }

        // PHP 8
        if(isset($this->values['full_path'][$index]))
        {
            $file['full_path'] = $this->values['full_path'][$index];
        }
        
        return $file;
    }
    
    /**
     * Magic object to convert object to string
     *
     * @return string
     */
    public function __toString()
    {
        return empty($this->values) ? "{}" : json_encode($this->values);
    }
}