<?php

namespace AppBuilder\Util\Entity;

/**
 * Class EntityDiagramColumn
 *
 * Represents a column in an entity diagram, encapsulating its properties 
 * such as name, data type, references, and positional attributes.
 */
class EntityDiagramColumn //NOSONAR
{
    /**
     * The name of the column.
     *
     * @var string
     */
    private $columnName;

    /**
     * The data type of the column.
     *
     * @var string
     */
    private $dataType;

    /**
     * The length of the data in the column.
     *
     * @var int|null
     */
    private $dataLength;

    /**
     * The name of the referenced entity, if applicable.
     *
     * @var string|null
     */
    private $referenceEntityName;

    /**
     * The name of the referenced table, if applicable.
     *
     * @var string|null
     */
    private $referenceTableName;

    /**
     * The name of the referenced column, if applicable.
     *
     * @var string|null
     */
    private $referenceColumnName;
    
    /**
     * The X coordinate of the column's position in the diagram.
     *
     * @var integer|null
     */
    private $x;

    /**
     * The Y coordinate of the column's position in the diagram.
     *
     * @var integer|null
     */
    private $y;

    /**
     * Indicates whether this column is a reference column.
     *
     * @var boolean
     */
    private $referenceColumn = false;

    /**
     * Indicates whether this column is a primary key.
     *
     * @var boolean
     */
    private $primaryKey = false;

    /**
     * Constructor for the EntityDiagramColumn class.
     *
     * Initializes the column with the provided attributes.
     *
     * @param array $column An associative array containing column attributes
     *                      (e.g., 'name', 'type', 'length').
     */
    public function __construct($column)
    {
        $this->setColumn($column);
    }
    
    /**
     * Set the properties of the column from an associative array.
     *
     * @param array $column An associative array containing column attributes.
     * @return void
     */
    public function setColumn($column)
    {
        $this->columnName = $column['name'];
        $this->dataType = $column['type'];
        
        if(isset($column['length']))
        {
            $dataLength = intval($column['length']);
            if($dataLength > 0)
            {
                $this->dataLength = intval($column['length']);
            }
        }
    }
    
    /**
     * Set the join column properties for references to other entities.
     *
     * @param string $referenceEntityName The name of the referenced entity.
     * @param string $referenceTableName The name of the referenced table.
     * @param string $referenceColumnName The name of the referenced column.
     * @return void
     */
    public function setJoinColumn($referenceEntityName, $referenceTableName, $referenceColumnName)
    {
        $this->referenceEntityName = $referenceEntityName;
        $this->referenceTableName = $referenceTableName;
        $this->referenceColumnName = $referenceColumnName;
    }

    /**
     * Get the name of the column.
     * 
     * @return string The column name.
     */ 
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Set the name of the column.
     *
     * @param string $columnName The column name.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * Get the data type of the column.
     * 
     * @return string The data type.
     */  
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set the data type of the column.
     *
     * @param string $dataType The data type.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get the length of the data in the column.
     * 
     * @return int|null The data length or null if not set.
     */  
    public function getDataLength()
    {
        return $this->dataLength;
    }

    /**
     * Set the value of dataLength
     *
     * @return  self
     */ 
    public function setDataLength($dataLength)
    {
        $this->dataLength = $dataLength;

        return $this;
    }

    /**
     * Get the name of the referenced entity.
     * 
     * @return string|null The reference entity name or null if not set.
     */ 
    public function getReferenceEntityName()
    {
        return $this->referenceEntityName;
    }

    /**
     * Set the name of the referenced entity.
     *
     * @param string $referenceEntityName The reference entity name.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setReferenceEntityName($referenceEntityName)
    {
        $this->referenceEntityName = $referenceEntityName;

        return $this;
    }

    /**
     * Get the name of the referenced table.
     * 
     * @return string|null The reference table name or null if not set.
     */ 
    public function getReferenceTableName()
    {
        return $this->referenceTableName;
    }

    /**
     * Set the name of the referenced table.
     *
     * @param string $referenceTableName The reference table name.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setReferenceTableName($referenceTableName)
    {
        $this->referenceTableName = $referenceTableName;

        return $this;
    }

    /**
     * Get the name of the referenced column.
     * 
     * @return string|null The reference column name or null if not set.
     */ 
    public function getReferenceColumnName()
    {
        return $this->referenceColumnName;
    }

    /**
     * Set the name of the referenced column.
     *
     * @param string $referenceColumnName The reference column name.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setReferenceColumnName($referenceColumnName)
    {
        $this->referenceColumnName = $referenceColumnName;

        return $this;
    }

    /**
     * Get whether this column is a primary key.
     *
     * @return boolean True if primary key, otherwise false.
     */ 
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set whether this column is a primary key.
     *
     * @param boolean $primaryKey True if primary key, otherwise false.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }
    
    /**
     * Check if the column has a reference to another entity.
     *
     * @return boolean True if a reference exists, otherwise false.
     */
    public function hasReference()
    {
        return $this->referenceColumn || isset($this->referenceColumnName);
    }
    
    /**
     * Check if the column has a reference entity defined.
     *
     * @return boolean True if both reference table and column names are set, otherwise false.
     */
    public function hasReferenceEntity()
    {
        return $this->referenceTableName != null && $this->referenceColumnName != null;
    }

    /**
     * Get whether this column is a reference column.
     *
     * @return boolean True if this is a reference column, otherwise false.
     */ 
    public function getReferenceColumn()
    {
        return $this->referenceColumn;
    }

    /**
     * Set whether this column is a reference column.
     *
     * @param boolean $referenceColumn True if this is a reference column, otherwise false.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setReferenceColumn($referenceColumn)
    {
        $this->referenceColumn = $referenceColumn;

        return $this;
    }

    /**
     * Get the X coordinate of the column's position.
     *
     * @return integer|null The X position or null if not set.
     */ 
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set the X coordinate of the column's position.
     *
     * @param integer $x The X position.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get the Y coordinate of the column's position.
     *
     * @return integer|null The Y position or null if not set.
     */  
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set the Y coordinate of the column's position.
     *
     * @param integer $y The Y position.
     * @return self Returns the current instance for method chaining.
     */  
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }
}