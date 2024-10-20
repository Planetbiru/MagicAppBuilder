<?php

namespace AppBuilder\Util\Entity;

class EntityDiagramItem //NOSONAR
{
    /**
     * Entity name
     *
     * @var string
     */
    private $entityName;

    /**
     * Namespace
     *
     * @var string
     */
    private $namespace;
    
    /**
     * Table name
     *
     * @var string
     */
    private $tableName;
    
    /**
     * Entity ID
     *
     * @var string
     */
    private $entityId;
    
    /**
     * Width
     *
     * @var integer
     */
    private $width = 1;
    
    /**
     * Height
     *
     * @var integer
     */
    private $height = 20;
    
    /**
     * Header height
     *
     * @var integer
     */
    private $headerHeight = 20;
    
    /**
     * Column height
     *
     * @var integer
     */
    private $columnHeight = 20;
    
    /**
     * Coordinate X
     *
     * @var integer
     */
    private $x = 0;
    
    /**
     * Coordinate Y
     *
     * @var integer
     */
    private $y = 0;
    
    /**
     * Constructor
     *
     * @param string $entityName
     * @param string $namespace
     * @param string $tableName
     * @param string $entityId
     * @param integer $x
     * @param integer $y
     * @param integer $width
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
     * Column
     *
     * @var EntityDiagramColumn[]
     */
    private $columns = array();
    
    public function hasColumn($columnName)
    {
        return isset($this->columns[$columnName]);
    }
    
    public function addColumn($column)
    {
        $columnName = $column['name'];
        $this->columns[$columnName] = new EntityDiagramColumn($column);
        
        $this->height = $this->headerHeight + ($this->columnHeight * count($this->columns));
        
        return $this;
    }
    
    /**
     * Set primary key column
     *
     * @param string $columnName
     * @return self
     */
    public function setPrimaryKeyColumn($columnName)
    {
        $this->columns[$columnName]->setPrimaryKey(true);
        return $this;
    }
    
    /**
     * Set reference column
     *
     * @param string $columnName
     * @return self
     */
    public function setReferenceColumn($columnName)
    {
        $this->columns[$columnName]->setReferenceColumn(true);
        return $this;
    }
    
    /**
     * Set join column
     *
     * @param string $columnName
     * @param string $propertyType
     * @param string $referenceTableName
     * @param string $referenceColumnName
     * @return self
     */
    public function setJoinColumn($columnName, $propertyType, $referenceTableName, $referenceColumnName)
    {
        $this->columns[$columnName]->setJoinColumn($propertyType, $referenceTableName, $referenceColumnName);
        return $this;
    }

    /**
     * Get the value of entityName
     */ 
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set the value of entityName
     *
     * @return  self
     */ 
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Get the value of tableName
     */ 
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set the value of tableName
     *
     * @return  self
     */ 
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get the value of width
     */ 
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set the value of width
     *
     * @return  self
     */ 
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }
    
    public function getMaxX()
    {
        return $this->x + $this->width;
    }
    
    public function getMaxY()
    {
        return $this->y + $this->height;
    }

    

    /**
     * Get the value of x
     */ 
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set the value of x
     *
     * @return  self
     */ 
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get the value of y
     */ 
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set the value of y
     *
     * @return  self
     */ 
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Get the value of height
     */ 
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the value of height
     *
     * @return  self
     */ 
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get column
     *
     * @return  EntityDiagramColumn[]
     */ 
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get the value of columnHeight
     */ 
    public function getColumnHeight()
    {
        return $this->columnHeight;
    }

    /**
     * Set the value of columnHeight
     *
     * @return  self
     */ 
    public function setColumnHeight($columnHeight)
    {
        $this->columnHeight = $columnHeight;

        return $this;
    }

    /**
     * Get the value of headerHeight
     */ 
    public function getHeaderHeight()
    {
        return $this->headerHeight;
    }

    /**
     * Set the value of headerHeight
     *
     * @return  self
     */ 
    public function setHeaderHeight($headerHeight)
    {
        $this->headerHeight = $headerHeight;

        return $this;
    }

    /**
     * Get the value of entityId
     */ 
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set the value of entityId
     *
     * @return  self
     */ 
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get namespace
     *
     * @return  string
     */ 
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set namespace
     *
     * @param  string  $namespace  Namespace
     *
     * @return  self
     */ 
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }
}