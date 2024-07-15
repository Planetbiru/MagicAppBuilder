<?php

namespace AppBuilder;

use MagicObject\Generator\PicoColumnGenerator;
use MagicObject\Generator\PicoEntityGenerator;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

class AppEntityGenerator extends PicoEntityGenerator
{
    /**
     * Prepare directory
     *
     * @param string $dir
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
     * Check if entity file is exists
     *
     * @return boolean
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
     * Get class name
     *
     * @param string $realEntityName
     * @param string $picoTableName
     * @return string
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
     * Get table name
     *
     * @param string $realTableName
     * @return string
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
     * Generate custom entity
     *
     * @param string $realEntityName
     * @param string $realTableName
     * @param array $predecessorField
     * @param array $successorField
     * @param boolean $removePk
     * @param MagicObject[] $referenceData
     * @return integer
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
                $attrs[] = $prop;

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
 * Visit https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#entity
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify='.$prettify.')
 * @Table(name="'.$picoTableName.'")
 */
class '.$className.' extends MagicObject
{
'.implode("\r\n", $attrs).'
}';
        return file_put_contents($path, $classStr);
    }
    
    /**
     * Add predecessor and successor field and remove key and extra
     *
     * @param array $rows
     * @param array $predecessor
     * @param array $successor
     * @return array
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
     * Remove duplication
     *
     * @param array $additional
     * @param array $rows
     * @return array
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
}