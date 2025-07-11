<?php

use AppBuilder\AppDatabase;
use AppBuilder\Util\ValidatorUtil;
use MagicObject\Generator\PicoEntityGenerator;
use MagicObject\Request\InputPost;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

// Handle POST input using MagicObject's input abstraction
$inputPost = new InputPost();

/**
 * Extended implementation of PicoEntityGenerator
 * for customizing type mapping logic.
 */
class PicoEntityGeneratorImpl extends PicoEntityGenerator{
    // Holds mapping between SQL types and PHP types
    private $map = array();

    /**
     * Populates the type map from the base class
     */
    public function getMap()
    {
        $this->map = $this->getTypeMap();
    }

    /**
     * Converts SQL data type to PHP type.
     * Falls back to 'string' if no match is found.
     *
     * @param string $type SQL column type
     * @return string PHP type
     */
    public function convertType($type)
    {
        foreach($this->map as $sqlType => $phpType)
        {
            error_log($sqlType);
            if(stripos(trim($type), trim($sqlType)) === 0)
            {
                return $phpType;
            }
        }
        return "string";
    }
}

// Handle 'create' action for validator generation
if($inputPost->getUserAction() == 'create')
{
    $applicationId = $appConfig->getApplication()->getId();
    $baseApplicationNamespace = $appConfig->getApplication()->getBaseApplicationNamespace();
    $validator = $inputPost->getValidator();
    $tableName = $inputPost->getTableName();

    if (isset($validator) && !empty($validator)) {
        // Get validator definition in JSON format
        $definition = $inputPost->getDefinition(); // JSON
        $path = ValidatorUtil::getPath($appConfig, $inputPost);
        
        // Decode validation rules
        $data = json_decode($definition, true);

        // Create entity generator instance
        $gen = new PicoEntityGeneratorImpl(null, null, null, null);
        $gen->getMap();
 
        // Fetch table column information
        $tableColumnInfo = AppDatabase::getColumnList($appConfig, $databaseConfig, $database, $tableName);
        $fields = $tableColumnInfo['fields'];
        $dataTypes = array();
        $valProps = array();
        
        foreach($fields as $field)
        {
            $fieldName = $field['column_name'];
            $maximumLength = $field['maximum_length'];
            $dataType = $gen->convertType($field['column_type']);
            $dataTypes[$fieldName] = $dataType;

            // Only generate properties for fields with validation definitions
            $number = 1;
            if(isset($data[$fieldName]))
            {
                $vals = array();
                $validations = $data[$fieldName];
                foreach($validations as $validation)
                {
                    $validationType = $validation['type'];
                    unset($validation['type']);
                    $attributes = $validation;

                    // Format validation attributes as annotation parameters
                    $attributeParts = [];
                    foreach ($attributes as $k => $v) {
                        if (isset($v) && !empty($v)) {
                            if (is_string($v) && $validationType != 'Enum') {
                                $attributeParts[] = "$k=\"" . addslashes($v) . "\"";
                            } else {
                                $attributeParts[] = "$k=$v";
                            }
                        }
                    }
                    $attrs = "(".implode(", ", $attributeParts).")";
                    if($attrs == "()")
                    {
                        $attrs = "";
                    }
                    // Add validation annotation line
                    $vals[] = "**$validationType**$attrs";
                }

                // Add property type annotation and declaration
                $valProps[] = " * $number. `$" . PicoStringUtil::camelize($fieldName) . "` ( ".implode(", ", $vals)." )";
                
                $number++;
            }
        }
        
        $properties = array();

        // Begin PHP class definition for the validator
$properties[] = '<?php

namespace '.$baseApplicationNamespace.'\Validator;

use MagicObject\MagicObject;

/**
 * Auto-generated validator class for the "'.$tableName.'" table.
 *
 * This class defines validation rules for updating data related to the "'.$tableName.'" table.
 * It is generated based on the structure of the table and the JSON definition provided.
 *
 * You may add additional validation rules or modify the generated annotations as needed.
 * 
 * Validated properties:
'.implode("\r\n", $valProps).'
 * 
 * @Validator
 * @Table(name="'.$tableName.'")
 * @package '.$baseApplicationNamespace.'\\'.$validator.'
 */
class '.$validator.' extends MagicObject
{';

        // Loop through each column in the table
        foreach($fields as $field)
        {
            $fieldName = $field['column_name'];
            $maximumLength = $field['maximum_length'];
            $dataType = $gen->convertType($field['column_type']);
            $dataTypes[$fieldName] = $dataType;

            // Only generate properties for fields with validation definitions
            if(isset($data[$fieldName]))
            {
                $properties[] = "\t/** ";

                $validations = $data[$fieldName];
                foreach($validations as $validation)
                {
                    $validationType = $validation['type'];
                    unset($validation['type']);
                    $attributes = $validation;

                    // Format validation attributes as annotation parameters
                    $attributeParts = [];
                    foreach ($attributes as $k => $v) {
                        if ($v === '' || $v === null) {
                            $attributeParts[] = "$k=\"\"";
                        } else {
                            if (is_string($v) && $validationType != 'Enum') {
                                $attributeParts[] = "$k=\"" . addslashes($v) . "\"";
                            } else {
                                $attributeParts[] = "$k=$v";
                            }
                        }
                    }

                    // Add validation annotation line
                    $properties[] = "\t * @$validationType(" . implode(", ", $attributeParts) . ")";
                }

                // Add property type annotation and declaration
                $properties[] = "\t * @var " . $dataType;
                $properties[] = "\t */";
                $properties[] = "\tprotected $" . PicoStringUtil::camelize($fieldName) . ";";
                $properties[] = "";
            }
        }

        // Close the class definition
        $properties[] = "}";

        // Prepare directory
        if(!file_exists(dirname($path)))
        {
            mkdir(dirname($path), 0577, true);
        }

        // Write the generated class to file
        file_put_contents($path, implode("\r\n", $properties));
    }
}

require_once __DIR__ ."/validator-list.php";