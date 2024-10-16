<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use stdClass;

/**
 * Class to manage entity labels and their annotations.
 * 
 * Provides methods to retrieve and filter entity metadata, including labels, columns, and other attributes.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoEntityLabel
{
    const ANNOTATION_TABLE            = "Table";
    const ANNOTATION_LABEL            = "label";
    const ANNOTATION_COLUMN           = "Column";
    const ANNOTATION_JOIN_COLUMN      = "JoinColumn";
    const ANNOTATION_VAR              = "var";
    const ANNOTATION_ID               = "Id";
    const ANNOTATION_GENERATED_VALUE  = "GeneratedValue";
    const ANNOTATION_NOT_NULL         = "NotNull";
    const ANNOTATION_DEFAULT_COLUMN   = "DefaultColumn";

    const KEY_NAME                    = "name";
    const KEY_NULL                    = "null";
    const KEY_NOT_NULL                = "notnull";
    const KEY_NULLABLE                = "nullable";
    const KEY_INSERTABLE              = "insertable";
    const KEY_UPDATABLE               = "updatable";
    const KEY_STRATEGY                = "strategy";
    const KEY_GENERATOR               = "generator";
    const KEY_PROPERTY_TYPE           = "propertyType";
    const KEY_VALUE                   = "value";
    const KEY_ENTITY_OBJECT           = "entityObject";

    const VALUE_TRUE                  = "true";
    const VALUE_FALSE                 = "false";

    /**
     * The class name of the entity.
     *
     * @var string
     */
    private $className;

    /**
     * Supported languages for labels.
     *
     * @var string[]
     */
    private $langs;

    /**
     * Constructor for the PicoEntityLabel class.
     *
     * @param MagicObject $object The entity object.
     * @param string[] $langs Supported languages.
     */
    public function __construct($object, array $langs)
    {
        $this->className = get_class($object);
        $this->langs = $langs;
    }

    /**
     * Get the mapping of labels based on the specified language.
     *
     * @param string|null $lang The language to filter the labels by.
     * @return array|null The filtered labels, or null if the language is not supported.
     */
    public function getMap($lang = null)
    {
        $info = $this->getObjectInfo();
        $labels = $info->labels;
        $map = array();

        // Get labels from join columns
        if (isset($info->joinColumns) && is_array($info->joinColumns)) {
            foreach ($info->joinColumns as $propertyName => $column) {
                if (isset($labels[$propertyName])) {
                    $map[$column[self::KEY_NAME]] = $labels[$propertyName];
                }
            }
        }

        // Get labels from columns
        if (isset($info->columns) && is_array($info->columns)) {
            foreach ($info->columns as $propertyName => $column) {
                if (isset($labels[$propertyName])) {
                    $map[$column[self::KEY_NAME]] = $labels[$propertyName];
                }
            }
        }

        // Merge labels with the map
        $merged = array_merge($map, $labels);
        return $this->filter($merged, $lang);
    }

    /**
     * Filter the merged labels based on the specified language.
     *
     * @param array $merged Merged array of labels.
     * @param string|null $lang The language to filter by.
     * @return array|null The filtered labels, or null if the language is not supported.
     */
    private function filter(array $merged, $lang)
    {
        if ($lang === null) {
            return $merged;
        }
        if (!in_array($lang, $this->langs)) {
            return null;
        }

        $filtered = array();
        foreach ($merged as $prop => $val) {
            $filtered[$prop] = $val[$lang] ?? null;
        }
        return $filtered;
    }

    /**
     * Parse a key-value string from the annotation parser.
     *
     * @param PicoAnnotationParser $reflexClass Reflection class for the entity.
     * @param string $queryString The query string to parse.
     * @param string $parameter The parameter name.
     * @return array The parsed key-value pairs.
     * @throws InvalidAnnotationException If the query input is invalid.
     */
    private function parseKeyValue(PicoAnnotationParser $reflexClass, $queryString, $parameter)
    {
        try {
            return $reflexClass->parseKeyValue($queryString);
        } catch (InvalidQueryInputException $e) {
            throw new InvalidAnnotationException("Invalid annotation @" . $parameter);
        }
    }

    /**
     * Get object information, including metadata about labels, columns, and more.
     *
     * @return stdClass An object containing entity metadata.
     */
    public function getObjectInfo()
    {
        $reflexClass = new PicoAnnotationParser($this->className);
        $table = $reflexClass->getParameter(self::ANNOTATION_TABLE);
        $values = $this->parseKeyValue($reflexClass, $table, self::ANNOTATION_TABLE);
        $picoTableName = $values[self::KEY_NAME];

        $columns = array();
        $joinColumns = array();
        $primaryKeys = array();
        $autoIncrementKeys = array();
        $notNullColumns = array();
        $defaultValue = array();
        $labels = array();
        $props = $reflexClass->getProperties();

        // Iterate through properties of the class
        foreach ($props as $prop) {
            $reflexProp = new PicoAnnotationParser($this->className, $prop->name, PicoAnnotationParser::PROPERTY);
            $parameters = $reflexProp->getParameters();

            // Process column label and parameters
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_LABEL) === 0) {
                    $values = $this->parseKeyValue($reflexProp, $val, $param);
                    if (!empty($values)) {
                        foreach ($values as $k1 => $v1) {
                            if (!in_array($k1, $this->langs)) {
                                unset($values[$k1]);
                            }
                        }
                        $labels[$prop->name] = $values;
                    }
                }

                if (strcasecmp($param, self::ANNOTATION_COLUMN) === 0) {
                    $values = $this->parseKeyValue($reflexProp, $val, $param);
                    if (!empty($values)) {
                        $columns[$prop->name] = $values;
                    }
                }
            }

            // Set column type
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_VAR) === 0 && isset($columns[$prop->name])) {
                    $type = explode(' ', trim($val))[0];
                    $columns[$prop->name][self::KEY_PROPERTY_TYPE] = $type;
                }
            }

            // Process join columns
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_JOIN_COLUMN) === 0) {
                    $values = $this->parseKeyValue($reflexProp, $val, $param);
                    if (!empty($values)) {
                        $joinColumns[$prop->name] = $values;
                    }
                }
            }

            // Set join column type
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_VAR) === 0 && isset($joinColumns[$prop->name])) {
                    $type = explode(' ', trim($val))[0];
                    $joinColumns[$prop->name][self::KEY_PROPERTY_TYPE] = $type;
                    $joinColumns[$prop->name][self::KEY_ENTITY_OBJECT] = true;
                }
            }

            // List primary keys
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_ID) === 0 && isset($columns[$prop->name])) {
                    $primaryKeys[$prop->name] = [self::KEY_NAME => $columns[$prop->name][self::KEY_NAME]];
                }
            }

            // List auto-generated columns
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_GENERATED_VALUE) === 0 && isset($columns[$prop->name])) {
                    $vals = $this->parseKeyValue($reflexProp, $val, $param);
                    $autoIncrementKeys[$prop->name] = [
                        self::KEY_NAME => $columns[$prop->name][self::KEY_NAME] ?? null,
                        self::KEY_STRATEGY => $vals[self::KEY_STRATEGY] ?? null,
                        self::KEY_GENERATOR => $vals[self::KEY_GENERATOR] ?? null,
                    ];
                }
            }

            // Define default column values
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_DEFAULT_COLUMN) === 0) {
                    $vals = $this->parseKeyValue($reflexProp, $val, $param);
                    if (isset($vals[self::KEY_VALUE])) {
                        $defaultValue[$prop->name] = [
                            self::KEY_NAME => $columns[$prop->name][self::KEY_NAME] ?? null,
                            self::KEY_VALUE => $vals[self::KEY_VALUE],
                            self::KEY_PROPERTY_TYPE => $columns[$prop->name][self::KEY_PROPERTY_TYPE],
                        ];
                    }
                }
            }

            // List not null columns
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_NOT_NULL) === 0 && isset($columns[$prop->name])) {
                    $notNullColumns[$prop->name] = [self::KEY_NAME => $columns[$prop->name][self::KEY_NAME]];
                }
            }
        }

        // Consolidate object information
        $info = new stdClass;
        $info->tableName = $picoTableName;
        $info->columns = $columns;
        $info->joinColumns = $joinColumns;
        $info->primaryKeys = $primaryKeys;
        $info->autoIncrementKeys = $autoIncrementKeys;
        $info->defaultValue = $defaultValue;
        $info->notNullColumns = $notNullColumns;
        $info->labels = $labels;

        return $info;
    }
}
