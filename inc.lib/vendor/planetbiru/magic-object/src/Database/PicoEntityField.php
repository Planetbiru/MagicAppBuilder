<?php

namespace MagicObject\Database;

/**
 * Entity field
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoEntityField
{
    /**
     * Entity
     *
     * @var string
     */
    private $entity = null;

    /**
     * Object name
     *
     * @var string
     */
    private $objectName = null;

    /**
     * Field
     *
     * @var string
     */
    private $field = null;

    /**
     * Parent field
     *
     * @var string
     */
    private $parentField = null;

    /**
     * Function format
     *
     * @var string
     */
    private $functionFormat = "%s";

    /**
     * Get entity
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get parent field
     *
     * @return string
     */
    public function getParentField()
    {
        return $this->parentField;
    }

    /**
     * Get function format
     *
     * @return string
     */
    public function getFunctionFormat()
    {
        return $this->functionFormat;
    }

    /**
     * Constructor
     *
     * @param string $fieldRaw Raw field
     * @param PicoTableInfo|null $info Table info
     */
    public function __construct($fieldRaw, $info = null)
    {
        $field = $this->extractField($fieldRaw);
        if(strpos($field, ".") !== false)
        {
            $arr = explode(".", $field, 2);
            $this->field = $arr[1];
            $this->objectName = $arr[0];
            $this->parentField = $arr[0];

            if($info != null && $info->getJoinColumns() != null)
            {
                $columns = $info->getJoinColumns();
                if(isset($columns[$this->objectName]) && isset($columns[$this->objectName]['propertyType']))
                {
                    $this->entity = $columns[$this->objectName]['propertyType'];
                }
            }
        }
        else
        {
            $this->field = $field;
        }
    }

    /**
     * Extract field from any function
     *
     * @param string $fieldRaw Raw field
     * @return string
     */
    public function extractField($fieldRaw)
    {
        if(strpos($fieldRaw, "(") === false)
        {
            $this->functionFormat = "%s";
            return $fieldRaw;
        }
        else
        {
            $pattern = '/(\((?:\(??[^\(]*?\)))/m'; //NOSONAR
            preg_match_all($pattern , $fieldRaw, $out);
            $field = trim($out[0][0], "()");
            $this->functionFormat = str_replace($field, "%s", $fieldRaw);
            return $field;
        }
    }
}