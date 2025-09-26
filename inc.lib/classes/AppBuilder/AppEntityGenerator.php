<?php

namespace AppBuilder;

use MagicObject\Generator\PicoColumnGenerator;
use MagicObject\Generator\PicoEntityGenerator;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

/**
 * Class AppEntityGenerator
 *
 * A specialized entity generator that extends `PicoEntityGenerator` to create custom entity classes for the application.
 * It handles the generation of main entities, as well as related entities like approval and trash tables. This class
 * is responsible for merging database schema information with application-specific configurations, such as adding
 * predecessor and successor fields, handling entity relationships through reference data, and controlling whether
 * existing entity files should be updated.
 *
 * @package AppBuilder
 */
class AppEntityGenerator extends PicoEntityGenerator
{
    /**
     * Indicates if the entity should be updated.
     *
     * @var bool
     */
    private $updateEntity;

    /**
     * Entity information.
     *
     * @var EntityInfo
     */
    private $entityInfo;

    /**
     * Indicates if this is the main entity.
     *
     * @var bool
     */
    private $mainEntity;

    /**
     * Constructor.
     *
     * @param PicoDatabase $database The database connection.
     * @param string $baseDir The base directory for entity files.
     * @param string $tableName The name of the table associated with the entity.
     * @param string $baseNamespace The base namespace for the entity class.
     * @param string|null $entityName Optional name for the entity class.
     * @param EntityInfo|null $entityInfo Optional entity information object.
     * @param bool $updateEntity Indicates whether to update the entity if it exists.
     */
    public function __construct($database, $baseDir, $tableName, $baseNamespace, $entityName = null, $entityInfo = null, $updateEntity = true)
    {
        parent::__construct($database, $baseDir, $tableName, $baseNamespace, $entityName);
        $this->entityInfo = $entityInfo;
        $this->updateEntity = $updateEntity;
    }

    /**
     * Set whether this is the main entity.
     *
     * @param bool $mainEntity Indicates if this is the main entity.
     * @return self Returns the current instance for method chaining.
     */
    public function setMainEntity($mainEntity)
    {
        $this->mainEntity = $mainEntity;
        return $this;
    }

    /**
     * Prepare the specified directory.
     *
     * @param string $dir The directory to prepare.
     * @return void
     */
    private function prepareDir($dir)
    {
        if(!file_exists($dir))
        {
            mkdir($dir, 0755, true);
        }
    }
    
    /**
     * Check if the entity file exists.
     *
     * @param string|null $realEntityName Optional real entity name to check.
     * @param string|null $realTableName Optional real table name to check.
     * @return bool Returns true if the entity file exists, false otherwise.
     */
    public function isEntityExists($realEntityName = null, $realTableName = null)
    {
        $tableName = $this->tableName;
        if($realEntityName != null)
        {
            $className = $realEntityName;
        }
        else if($this->entityName != null)
        {
            $className = $this->entityName;
        }
        else
        {
            $className = ucfirst(PicoStringUtil::camelize($tableName));
        }
        $fileName = $this->baseNamespace."/".$className;
        $path = $this->baseDir."/".$fileName.".php";
        $path = str_replace("\\", "/", $path); 
        return file_exists($path);
    }

    /**
     * Create a property reference for a related entity.
     *
     * @param string $joinColumn The name of the join column.
     * @param string $entityName The name of the related entity.
     * @param string $objectName The name of the object variable.
     * @param string $referenceColumnName The name of the reference column in the related entity.
     * @param string $tableName The name of the reference table in the related entity.
     * @return string Returns the generated property reference string.
     */
    public function createPropertyReference($joinColumn, $entityName, $objectName, $referenceColumnName, $tableName)
    {
        $description = $this->getPropertyName(PicoStringUtil::snakeize($objectName), true);
        $objectName = PicoStringUtil::camelize($objectName);

        $docs = array();
        $docStart = "\t/**";
        $docEnd = "\t */";

        $attrs = array();
        $attrs[] = "name=\"$joinColumn\"";
        $attrs[] = "referenceColumnName=\"$referenceColumnName\"";
        $attrs[] = "referenceTableName=\"$tableName\"";

        $docs[] = $docStart;
        $docs[] = "\t * ".$description;
        $docs[] = "\t * ";
        $docs[] = "\t * @JoinColumn(".implode(", ", $attrs).")";
        $docs[] = "\t * @Label(content=\"$description\")";
        $docs[] = "\t * @var $entityName";
        $docs[] = $docEnd;
        $prop = "\tprotected \$$objectName;";
        return implode("\r\n", $docs)."\r\n".$prop."\r\n";
    }

    /**
     * Get the class name based on provided parameters.
     *
     * @param string|null $realEntityName Optional real entity name to use.
     * @param string $tableName The name of the table to base the class name on.
     * @return string Returns the determined class name.
     */
    private function getClassName($realEntityName, $tableName)
    {
        if($realEntityName != null)
        {
            $className = $realEntityName;
        }
        else if($this->entityName != null)
        {
            $className = $this->entityName;
        }
        else
        {
            $className = ucfirst(PicoStringUtil::camelize($tableName));
        }
        return $className;
    }
    

    /**
     * Get the table name based on the provided real table name.
     *
     * @param string|null $realTableName The actual table name; if null, uses the default table name.
     * @return string Returns the determined table name.
     */
    private function getTableName($realTableName)
    {
        if($realTableName != null)
        {
            $tableName = $realTableName;
        }
        else
        {
            $tableName = $this->tableName;
        }
        return $tableName;
    }

    /**
     * Get the reserved columns for the entity.
     *
     * @return array Returns an array of reserved column names.
     */
    public function getReservedColumns()
    {
        $reserved = array();
        if(!$this->mainEntity)
        {
            $reserved[] = $this->entityInfo->getDraft();
            $reserved[] = $this->entityInfo->getWaitingFor();
            $reserved[] = $this->entityInfo->getApprovalId();
            $reserved[] = $this->entityInfo->getAdminAskEdit();
            $reserved[] = $this->entityInfo->getTimeAskEdit();
            $reserved[] = $this->entityInfo->getIpAskEdit();
        }
        return $reserved;
    }

    /**
     * Checks if a column in the reference data corresponds to an entity reference.
     *
     * This method verifies whether the specified column exists in the reference data
     * and whether it represents an entity reference type.
     *
     * @param array $referenceData An associative array of reference data.
     * @param string $columnName The column name to check within the reference data.
     * @return boolean Returns true if the column is a valid entity reference, false otherwise.
     */
    private function isReference($referenceData, $columnName)
    {
        return isset($referenceData) && is_array($referenceData) && isset($referenceData[$columnName]) && $referenceData[$columnName]->getType() == 'entity';
    }

    /**
     * Checks if at least one of the given fields is not null.
     *
     * This method determines whether the provided predecessor or successor fields 
     * are not null, indicating their presence.
     *
     * @param array|null $predecessorField An array of predecessor fields (optional).
     * @param array|null $successorField An array of successor fields (optional).
     * @return bool True if at least one of the fields is not null, false otherwise.
     */
    private function atLeastOneNotNull($predecessorField, $successorField)
    {
        return $predecessorField != null || $successorField != null;
    }

    /**
     * Checks if both parameters are not empty.
     *
     * This method verifies that both the provided object name and property name 
     * are not empty, ensuring their values are valid.
     *
     * @param string $objectName The name of the object to check.
     * @param string $propertyName The name of the property to check.
     * @return bool True if both parameters are not empty, false otherwise.
     */
    private function notEmpty($objectName, $propertyName)
    {
        return !empty($objectName) && !empty($propertyName);
    }

    /**
     * Converts a boolean value to its string representation.
     *
     * This method takes a boolean value and returns its string equivalent, 
     * either "true" or "false".
     *
     * @param bool $value The boolean value to convert.
     * @return string The string representation of the boolean value ("true" or "false").
     */
    private function boolToString($value)
    {
        return $value ? 'true' : 'false';
    }

    /**
     * Generate a custom entity class file.
     *
     * @param string|null $realEntityName Optional real entity name for the class.
     * @param string|null $realTableName Optional real table name for the entity.
     * @param array|null $predecessorField Optional predecessor fields to include.
     * @param array|null $successorField Optional successor fields to include.
     * @param bool $removePk Indicates whether to remove primary key attributes.
     * @param MagicObject[]|null $referenceData Optional reference data for related entities.
     * @param array|null $nonupdatables Optional fields that cannot be updated.
     * @return int Returns the number of bytes written to the file or 0 if not updated.
     */
    public function generateCustomEntity($realEntityName = null, $realTableName = null, $predecessorField = null, $successorField = null, $removePk = false, $referenceData = null, $nonupdatables = null)
    {
        $typeMap = $this->getTypeMap();
        $columnMap = $this->getColumnMap();
        $tableName = $this->tableName;
        
        $className = $this->getClassName($realEntityName, $tableName);
        
        $fileName = $this->baseNamespace."/".$className;
        $path = $this->baseDir."/".$fileName.".php";
        $path = str_replace("\\", "/", $path); 
        
        $dir = dirname($path);
        
        $this->prepareDir($dir);

        $rows = PicoColumnGenerator::getColumnList($this->database, $tableName);
        
        if($this->atLeastOneNotNull($predecessorField, $successorField))
        {
            $rows = $this->updateField($rows, $predecessorField, $successorField, $removePk);
        }

        $tableName = $this->getTableName($realTableName);
        $reservedColumns = $this->getReservedColumns();     

        $attrs = array();
        if(is_array($rows))
        {
            foreach($rows as $row)
            {
                $columnName = $row['Field'];

                $prop = $this->createProperty($typeMap, $columnMap, $row, $nonupdatables);

                if(!in_array($columnName, $reservedColumns))
                {
                    $attrs[] = $prop;
                }

                if($this->isReference($referenceData, $columnName))
                {
                    $referenceEntity = $referenceData[$columnName]->getEntity();
                    $entityName = $referenceEntity->getEntityName(); // var type
                    $propertyName = $referenceEntity->getPropertyName(); // object value
                    $objectName = $referenceEntity->getObjectName(); // var name
                    $referenceColumnName = $referenceEntity->getPrimaryKey(); // reference column key
                    $joinColumn = $columnName;
                    if($this->notEmpty($objectName, $propertyName))
                    {
                        $propRef = $this->createPropertyReference($joinColumn, $entityName, $objectName, $referenceColumnName, $referenceEntity->getTableName());
                        $attrs[] = $propRef;
                    }
                }

            }
        }    
        $prettify = $this->boolToString($this->prettify);
        

        $uses = array();
        $uses[] = "";
        $classStr = '<?php

namespace '.$this->baseNamespace.';

use MagicObject\MagicObject;'.implode("\r\n", $uses).'

/**
 * The '.$className.' class represents an entity in the "'.$tableName.'" table.
 *
 * This entity maps to the "'.$tableName.'" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package '.$this->baseNamespace.'
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify='.$prettify.')
 * @Table(name="'.$tableName.'")
 */
class '.$className.' extends MagicObject
{
'.implode("\r\n", $attrs).'
}';
        if($this->isNeedUpdateEntity($path))
        {
            return file_put_contents($path, $classStr);
        }
        return 0;
    }

    /**
     * Determines if the entity file needs to be updated.
     *
     * This method checks whether the entity update flag is set or if the specified file does not exist.
     *
     * @param string $path The file path to check.
     * @return bool True if the entity needs to be updated or the file does not exist, false otherwise.
     */
    private function isNeedUpdateEntity($path)
    {
        return $this->updateEntity || !file_exists($path);
    }
    
    /**
     * Update fields by adding predecessor and successor fields and optionally removing primary key attributes.
     *
     * @param array $rows The original rows from the database schema.
     * @param array|null $predecessor Optional predecessor fields to add.
     * @param array|null $successor Optional successor fields to add.
     * @param bool $removePk Indicates whether to remove primary key attributes.
     * @return array Returns the updated array of rows.
     */
    private function updateField($rows, $predecessor = null, $successor = null, $removePk = false)
    {
        $tmp = array();
        if($predecessor && is_array($predecessor))
        {
            $predecessor = $this->removeDuplicated($predecessor, $rows);
            foreach($predecessor as $row)
            {
                $tmp[] = $row;
            }
        }
        if(isset($rows) && is_array($rows))
        {
            foreach($rows as $row)
            {
                if($removePk)
                {
                    $row['Key'] = "";
                    $row['Extra'] = "";
                }
                $tmp[] = $row;
            }
        }
        if($successor && is_array($successor))
        {
            $successor = $this->removeDuplicated($successor, $rows);
            foreach($successor as $row)
            {
                $tmp[] = $row;
            }
        }
        return $tmp;
    }
    
    /**
     * Remove duplicated fields from the additional fields array.
     *
     * @param array $additional The additional fields to check.
     * @param array $rows The existing rows to compare against.
     * @return array Returns the array of additional fields without duplicates.
     */
    public function removeDuplicated($additional, $rows)
    {
        $existing = array();
        if(isset($rows) && is_array($rows))
        {
            foreach($rows as $row)
            {
                $existing[] = $row['Field'];
            }
        }
        $result = array();
        foreach($additional as $row)
        {
            if(!in_array($row['Field'], $existing))
            {
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * Get whether the entity should be updated.
     *
     * @return bool Returns true if the entity should be updated; otherwise, false.
     */
    public function getUpdateEntity()
    {
        return $this->updateEntity;
    }

    /**
     * Set whether the entity should be updated.
     *
     * @param bool $updateEntity Indicates whether to update the entity.
     * @return self Returns the current instance for method chaining.
     */
    public function setUpdateEntity($updateEntity)
    {
        $this->updateEntity = $updateEntity;

        return $this;
    }
}