<?php

namespace AppBuilder;

use MagicObject\Generator\PicoColumnGenerator;
use MagicObject\Generator\PicoEntityGenerator;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

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
        $picoTableName = $this->tableName;
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
            $className = ucfirst(PicoStringUtil::camelize($picoTableName));
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
     * @param string $propertyName The name of the property in the related entity.
     * @param string $referenceColumnName The name of the reference column in the related entity.
     * @return string Returns the generated property reference string.
     */
    public function createPropertyReference($joinColumn, $entityName, $objectName, $propertyName, $referenceColumnName)
    {
        $description = $this->getPropertyName(PicoStringUtil::snakeize($objectName));
        $objectName = PicoStringUtil::camelize($objectName);

        $docs = array();
        $docStart = "\t/**";
        $docEnd = "\t */";

        $attrs = array();
        $attrs[] = "name=\"$joinColumn\"";
        $attrs[] = "referenceColumnName=\"$referenceColumnName\"";

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
     * @param string $picoTableName The name of the table to base the class name on.
     * @return string Returns the determined class name.
     */
    private function getClassName($realEntityName, $picoTableName)
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
            $className = ucfirst(PicoStringUtil::camelize($picoTableName));
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
            $picoTableName = $realTableName;
        }
        else
        {
            $picoTableName = $this->tableName;
        }
        return $picoTableName;
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
        $picoTableName = $this->tableName;
        
        $className = $this->getClassName($realEntityName, $picoTableName);
        
        $fileName = $this->baseNamespace."/".$className;
        $path = $this->baseDir."/".$fileName.".php";
        $path = str_replace("\\", "/", $path); 
        $dir = dirname($path);
        
        $this->prepareDir($dir);

        $rows = PicoColumnGenerator::getColumnList($this->database, $picoTableName);
        
        if($predecessorField != null || $successorField != null)
        {
            $rows = $this->updateField($rows, $predecessorField, $successorField, $removePk);
        }

        $picoTableName = $this->getTableName($realTableName);
        $reservedColumns = $this->getReservedColumns();     

        $attrs = array();
        if(is_array($rows))
        {
            foreach($rows as $row)
            {
                $columnName = $row['Field'];
                $columnType = $row['Type'];
                $columnKey = $row['Key'];
                $columnNull = $row['Null'];
                $columnDefault = $row['Default'];
                $columnExtra = $row['Extra'];

                $nonupdatable = in_array($columnName, $nonupdatables);

                $prop = $this->createProperty($typeMap, $row, $nonupdatables);

                if(!in_array($columnName, $reservedColumns))
                {
                    $attrs[] = $prop;
                }

                if(isset($referenceData) && is_array($referenceData) && isset($referenceData[$columnName]) && $referenceData[$columnName]->getType() == 'entity')
                {
                    $referenceEntity = $referenceData[$columnName]->getEntity();
                    $entityName = $referenceEntity->getEntityName(); // var type
                    $propertyName = $referenceEntity->getPropertyName(); // object value
                    $objectName = $referenceEntity->getObjectName(); // var name
                    $referenceColumnName = $referenceEntity->getPrimaryKey(); // reference column key
                    $joinColumn = $columnName;
                    if(!empty( $objectName) && !empty($propertyName))
                    {
                        $propRef = $this->createPropertyReference($joinColumn, $entityName, $objectName, $propertyName, $referenceColumnName);
                        $attrs[] = $propRef;
                    }
                }

            }
        }    
        $prettify = $this->prettify ? 'true' : 'false';
        

        $uses = array();
        $uses[] = "";
        $classStr = '<?php

namespace '.$this->baseNamespace.';

use MagicObject\MagicObject;'.implode("\r\n", $uses).'

/**
 * '.$className.' is entity of table '.$picoTableName.'. You can join this entity to other entity using annotation JoinColumn. 
 * Don\'t forget to add "use" statement if the entity is outside the namespace.
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#entity
 * 
 * @package '.$this->baseNamespace.'
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify='.$prettify.')
 * @Table(name="'.$picoTableName.'")
 */
class '.$className.' extends MagicObject
{
'.implode("\r\n", $attrs).'
}';
        if($this->updateEntity || !file_exists($path))
        {
            return file_put_contents($path, $classStr);
        }
        return 0;
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