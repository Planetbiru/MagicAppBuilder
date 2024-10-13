<?php

namespace MagicObject\Database;

/**
 * Class representing an entity field in a database.
 *
 * This class encapsulates information about an entity field, including
 * its associated entity, field name, and any parent field relationships.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoEntityField
{
    /**
     * The associated entity.
     *
     * @var string|null
     */
    private $entity;

    /**
     * The object name associated with the field.
     *
     * @var string|null
     */
    private $objectName;

    /**
     * The field name.
     *
     * @var string|null
     */
    private $field;

    /**
     * The parent field name.
     *
     * @var string|null
     */
    private $parentField;

    /**
     * The function format for the field.
     *
     * @var string
     */
    private $functionFormat = "%s";

    /**
     * Constructor for PicoEntityField.
     *
     * @param string $fieldRaw The raw field input.
     * @param PicoTableInfo|null $info Table information (optional).
     */
    public function __construct($fieldRaw, $info = null)
    {
        $field = $this->extractField($fieldRaw);
        
        if (strpos($field, ".") !== false) {
            $arr = explode(".", $field, 2);
            $this->field = $arr[1];
            $this->objectName = $arr[0];
            $this->parentField = $arr[0];

            if ($info !== null && $info->getJoinColumns() !== null) {
                $columns = $info->getJoinColumns();
                if (isset($columns[$this->objectName]) && isset($columns[$this->objectName]['propertyType'])) {
                    $this->entity = $columns[$this->objectName]['propertyType'];
                }
            }
        } else {
            $this->field = $field;
        }
    }

    /**
     * Get the associated entity.
     *
     * @return string|null The entity name.
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get the field name.
     *
     * @return string|null The field name.
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get the parent field name.
     *
     * @return string|null The parent field name.
     */
    public function getParentField()
    {
        return $this->parentField;
    }

    /**
     * Get the function format for the field.
     *
     * @return string The function format.
     */
    public function getFunctionFormat()
    {
        return $this->functionFormat;
    }

    /**
     * Extract the field name from a raw field input.
     *
     * If the input contains a function, it extracts the field name and updates the function format.
     *
     * @param string $fieldRaw The raw field input.
     * @return string The extracted field name.
     */
    public function extractField($fieldRaw)
    {
        if (strpos($fieldRaw, "(") === false) {
            $this->functionFormat = "%s";
            return $fieldRaw;
        } else {
            $pattern = '/(\((?:\(??[^\(]*?\)))/m'; // NOSONAR
            preg_match_all($pattern, $fieldRaw, $out);
            $field = trim($out[0][0], "()");
            $this->functionFormat = str_replace($field, "%s", $fieldRaw);
            return $field;
        }
    }
}
