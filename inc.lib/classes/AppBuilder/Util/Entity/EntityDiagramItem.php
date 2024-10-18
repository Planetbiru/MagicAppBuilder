<?php

namespace AppBuilder\Util\Entity;

/**
 * Class EntityDiagramItem
 *
 * Represents an entity item in an entity diagram, encapsulating its properties,
 * including name, namespace, table name, dimensions, and associated columns.
 */
class EntityDiagramItem //NOSONAR
{
    /**
     * The name of the entity.
     *
     * @var string
     */
    private $entityName;

    /**
     * The namespace of the entity.
     *
     * @var string
     */
    private $namespace;
    
    /**
     * The name of the database table associated with the entity.
     *
     * @var string
     */
    private $tableName;
    
    /**
     * The unique identifier for the entity.
     *
     * @var string
     */
    private $entityId;
    
    /**
     * The width of the entity item in the diagram.
     *
     * @var integer
     */
    private $width = 1;
    
    /**
     * The height of the entity item in the diagram.
     *
     * @var integer
     */
    private $height = 20;
    
    /**
     * The height of the header in the entity item.
     *
     * @var integer
     */
    private $headerHeight = 20;
    
    /**
     * The height of each column in the entity item.
     *
     * @var integer
     */
    private $columnHeight = 20;
    
    /**
     * The X coordinate of the entity item in the diagram.
     *
     * @var integer
     */
    private $x = 0;
    
    /**
     * The Y coordinate of the entity item in the diagram.
     *
     * @var integer
     */
    private $y = 0;

    /**
     * Array of columns associated with the entity.
     *
     * @var EntityDiagramColumn[]
     */
    private $columns = [];

    /**
     * Constructor for the EntityDiagramItem class.
     *
     * Initializes the entity item with the provided attributes.
     *
     * @param string $entityName The name of the entity.
     * @param string $namespace The namespace of the entity.
     * @param string $tableName The associated database table name.
     * @param string $entityId The unique identifier for the entity.
     * @param integer $x The X coordinate for the entity item.
     * @param integer $y The Y coordinate for the entity item.
     * @param integer $width The width of the entity item.
     */
    public function __construct($entityName, $namespace, $tableName, $entityId, $x = 0, $y = 0, $width = 160)
    {
        $this->entityName = $entityName;
        $this->namespace = $namespace;
        $this->tableName = $tableName;
        $this->entityId = $entityId;
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
    }

    /**
     * Check if a column exists by its name.
     *
     * @param string $columnName The name of the column to check.
     * @return boolean True if the column exists, otherwise false.
     */
    public function hasColumn($columnName)
    {
        return isset($this->columns[$columnName]);
    }

    /**
     * Add a column to the entity item.
     *
     * @param array $column An associative array containing column attributes.
     * @return self Returns the current instance for method chaining.
     */
    public function addColumn($column)
    {
        $columnName = $column['name'];
        $this->columns[$columnName] = new EntityDiagramColumn($column);
        
        $this->height = $this->headerHeight + ($this->columnHeight * count($this->columns));
        
        return $this;
    }
    
    /**
     * Set the primary key column for the entity item.
     *
     * @param string $columnName The name of the column to set as primary key.
     * @return self Returns the current instance for method chaining.
     */
    public function setPrimaryKeyColumn($columnName)
    {
        $this->columns[$columnName]->setPrimaryKey(true);
        return $this;
    }
    
    /**
     * Set a column as a reference column.
     *
     * @param string $columnName The name of the column to set as reference.
     * @return self Returns the current instance for method chaining.
     */
    public function setReferenceColumn($columnName)
    {
        $this->columns[$columnName]->setReferenceColumn(true);
        return $this;
    }
    
    /**
     * Set a join column with reference information.
     *
     * @param string $columnName The name of the join column.
     * @param string $propertyType The property type of the join.
     * @param string $referenceTableName The name of the referenced table.
     * @param string $referenceColumnName The name of the referenced column.
     * @return self Returns the current instance for method chaining.
     */
    public function setJoinColumn($columnName, $propertyType, $referenceTableName, $referenceColumnName)
    {
        $this->columns[$columnName]->setJoinColumn($propertyType, $referenceTableName, $referenceColumnName);
        return $this;
    }

    /**
     * Get the name of the entity.
     * 
     * @return string The entity name.
     */ 
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set the name of the entity.
     *
     * @param string $entityName The entity name.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Get the name of the associated table.
     * 
     * @return string The table name.
     */ 
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set the name of the associated table.
     *
     * @param string $tableName The table name.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get the width of the entity item.
     * 
     * @return integer The width.
     */ 
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set the width of the entity item.
     *
     * @param integer $width The width.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get the maximum X coordinate of the entity item.
     *
     * @return integer The maximum X coordinate.
     */
    public function getMaxX()
    {
        return $this->x + $this->width;
    }
    
    /**
     * Get the maximum Y coordinate of the entity item.
     *
     * @return integer The maximum Y coordinate.
     */
    public function getMaxY()
    {
        return $this->y + $this->height;
    }

    /**
     * Get the X coordinate of the entity item.
     * 
     * @return integer The X coordinate.
     */ 
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set the X coordinate of the entity item.
     *
     * @param integer $x The X coordinate.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get the Y coordinate of the entity item.
     * 
     * @return integer The Y coordinate.
     */ 
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set the Y coordinate of the entity item.
     *
     * @param integer $y The Y coordinate.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Get the height of the entity item.
     * 
     * @return integer The height.
     */ 
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the height of the entity item.
     *
     * @param integer $height The height.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get the columns associated with the entity item.
     * 
     * @return EntityDiagramColumn[] The columns.
     */ 
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get the height of each column in the entity item.
     * 
     * @return integer The column height.
     */ 
    public function getColumnHeight()
    {
        return $this->columnHeight;
    }

    /**
     * Set the height of each column in the entity item.
     *
     * @param integer $columnHeight The column height.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setColumnHeight($columnHeight)
    {
        $this->columnHeight = $columnHeight;

        return $this;
    }

    /**
     * Get the height of the header in the entity item.
     * 
     * @return integer The header height.
     */ 
    public function getHeaderHeight()
    {
        return $this->headerHeight;
    }

    /**
     * Set the height of the header in the entity item.
     *
     * @param integer $headerHeight The header height.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setHeaderHeight($headerHeight)
    {
        $this->headerHeight = $headerHeight;

        return $this;
    }

    /**
     * Get the entity ID.
     * 
     * @return string The entity ID.
     */ 
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set the entity ID.
     *
     * @param string $entityId The entity ID.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get the namespace of the entity.
     * 
     * @return string The namespace.
     */ 
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the namespace of the entity.
     *
     * @param string $namespace The namespace.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }
}
