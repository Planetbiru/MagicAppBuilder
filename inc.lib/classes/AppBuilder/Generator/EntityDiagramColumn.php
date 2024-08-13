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
        $this->referenceColumnName = $referenceTableName;
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
}