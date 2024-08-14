<?php

namespace AppBuilder\Generator;

class EntityDiagramColumn
{
    private $columnName;
    private $dataType;
    private $dataLength;
    private $referenceEntityName;
    private $referenceTableName;
    private $referenceColumnName;
    
    /**
     * Position X
     *
     * @var integer
     */
    private $x;
    
    /**
     * Position Y
     *
     * @var integer
     */
    private $y;
    
    /**
     * Reference column
     *
     * @var boolean
     */
    private $referenceColumn = false;
    
    /**
     * Primary key
     *
     * @var boolean
     */
    private $primaryKey = false;
    /**
     * Construuctor
     *
     * @param array $column
     */
    public function __construct($column)
    {
        $this->setColumn($column);
    }
    
    /**
     * Set column
     *
     * @param array $column
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
     * Set join column
     *
     * @param array $column
     * @return void
     */
    public function setJoinColumn($referenceEntityName, $referenceTableName, $referenceColumnName)
    {
        $this->referenceEntityName = $referenceEntityName;
        $this->referenceTableName = $referenceTableName;
        $this->referenceColumnName = $referenceColumnName;
    }

    /**
     * Get the value of columnName
     */ 
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Set the value of columnName
     *
     * @return  self
     */ 
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * Get the value of dataType
     */ 
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set the value of dataType
     *
     * @return  self
     */ 
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get the value of dataLength
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
     * Get the value of referenceEntityName
     */ 
    public function getReferenceEntityName()
    {
        return $this->referenceEntityName;
    }

    /**
     * Set the value of referenceEntityName
     *
     * @return  self
     */ 
    public function setReferenceEntityName($referenceEntityName)
    {
        $this->referenceEntityName = $referenceEntityName;

        return $this;
    }

    /**
     * Get the value of referenceTableName
     */ 
    public function getReferenceTableName()
    {
        return $this->referenceTableName;
    }

    /**
     * Set the value of referenceTableName
     *
     * @return  self
     */ 
    public function setReferenceTableName($referenceTableName)
    {
        $this->referenceTableName = $referenceTableName;

        return $this;
    }

    /**
     * Get the value of referenceColumnName
     */ 
    public function getReferenceColumnName()
    {
        return $this->referenceColumnName;
    }

    /**
     * Set the value of referenceColumnName
     *
     * @return  self
     */ 
    public function setReferenceColumnName($referenceColumnName)
    {
        $this->referenceColumnName = $referenceColumnName;

        return $this;
    }

    /**
     * Get primary key
     *
     * @return  boolean
     */ 
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set primary key
     *
     * @param  boolean  $primaryKey  Primary key
     *
     * @return  self
     */ 
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }
    
    /**
     * Check if column has reference
     *
     * @return boolean
     */
    public function hasReference()
    {
        return $this->referenceColumn || isset($this->referenceColumnName);
    }

    /**
     * Get reference column
     *
     * @return  boolean
     */ 
    public function getReferenceColumn()
    {
        return $this->referenceColumn;
    }

    /**
     * Set reference column
     *
     * @param  boolean  $referenceColumn  Reference column
     *
     * @return  self
     */ 
    public function setReferenceColumn($referenceColumn)
    {
        $this->referenceColumn = $referenceColumn;

        return $this;
    }

    /**
     * Get position X
     *
     * @return  integer
     */ 
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set position X
     *
     * @param  integer  $x  Position X
     *
     * @return  self
     */ 
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get position Y
     *
     * @return  integer
     */ 
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set position Y
     *
     * @param  integer  $y  Position Y
     *
     * @return  self
     */ 
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }
}