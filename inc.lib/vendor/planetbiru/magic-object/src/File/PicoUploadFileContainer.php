<?php

namespace MagicObject\File;

/**
 * Class representing a container for uploaded files.
 *
 * This class manages the uploaded file information and provides methods
 * to handle single or multiple file uploads.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoUploadFileContainer
{
    /**
     * Array to store information about uploaded files.
     *
     * @var array
     */
    private $values = array();
    
    /**
     * Constructor.
     *
     * Initializes the container with uploaded file data.
     *
     * @param array|null $file An associative array containing file upload information.
     */
    public function __construct($file = null)
    {
        if ($file !== null) {
            $this->values = $file;
        }
    }
    
    /**
     * Checks if multiple files were uploaded.
     *
     * @return boolean True if multiple files were uploaded; otherwise, false.
     */
    public function isMultiple()
    {
        return isset($this->values['tmp_name']) && is_array($this->values['tmp_name']);
    }
    
    /**
     * Checks if a specific file exists in the upload.
     *
     * @param int $index The index of the uploaded file.
     * @return boolean True if the file exists; otherwise, false.
     */
    public function isExists($index)
    {
        return $this->isMultiple() && isset($this->values['tmp_name'][$index]);
    }
    
    /**
     * Gets the total number of uploaded files.
     *
     * @return int The number of files uploaded.
     */
    public function getFileCount()
    {
        if (empty($this->values)) {
            return 0;
        }
        return $this->isMultiple() ? count($this->values['tmp_name']) : 1;
    }
    
    /**
     * Retrieves all uploaded files.
     *
     * @return PicoUploadFileItem[] An array of PicoUploadFileItem objects representing uploaded files.
     */
    public function getAll()
    {
        $result = array();
        if (!empty($this->values)) {
            if ($this->isMultiple()) {
                // Handle multiple files
                $count = $this->getFileCount();
                for ($i = 0; $i < $count; $i++) {
                    $result[] = new PicoUploadFileItem($this->getItem($i));
                }
            } else {
                // Handle single file
                $result[] = new PicoUploadFileItem($this->values);
            }
        }
        return $result;
    }
    
    /**
     * Gets information about a specific uploaded file.
     *
     * @param int $index The index of the uploaded file.
     * @return array An associative array containing information about the uploaded file.
     */
    public function getItem($index)
    {
        $file = array(
            'tmp_name' => $this->values['tmp_name'][$index],
            'name' => $this->values['name'][$index]
        );
        
        if (isset($this->values['error'][$index])) {
            $file['error'] = $this->values['error'][$index];
        }
        if (isset($this->values['type'][$index])) {
            $file['type'] = $this->values['type'][$index];
        }
        if (isset($this->values['size'][$index])) {
            $file['size'] = $this->values['size'][$index];
        }

        // PHP 8
        if (isset($this->values['full_path'][$index])) {
            $file['full_path'] = $this->values['full_path'][$index];
        }
        
        return $file;
    }
    
    /**
     * Converts the object to a string representation.
     *
     * @return string A JSON-encoded string of the uploaded file information.
     */
    public function __toString()
    {
        return empty($this->values) ? "{}" : json_encode($this->values);
    }
}
