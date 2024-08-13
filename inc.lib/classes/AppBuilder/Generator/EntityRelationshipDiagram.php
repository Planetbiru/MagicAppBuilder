<?php

namespace AppBuilder\Generator;

use Exception;
use MagicObject\Database\PicoTableInfo;
use MagicObject\MagicObject;
use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;

class EntityRelationshipDiagram
{
    /**
     * Entity DiagramItem
     *
     * @var EntityDiagramItem[]
     */
    private $entitieDiagramItem = array();
    private $relationshipDiagramItem = array();
    
    private $entityWidth = 1;
    private $entityMaxHeight = 1;

    
    private $width = 1;
    private $height = 1;
    private $x = 0;
    private $y = 0;
    
    /**
     * Entity table
     *
     * @var string[]
     */
    private $entityTable = array();
    
    /**
     * Original entities
     *
     * @var MagicObject[]
     */
    private $entities;
    
    /**
     * Constructor
     *
     * @param MagicObject[] $entities
     */
    public function __construct($entities, $entityWidth, $entityMaxHeight)
    {
        $this->entityWidth = $entityWidth;
        $this->entityMaxHeight = $entityMaxHeight;
        $this->entities = $entities;
        foreach($this->entities as $entity)
        {
            $info = $entity->tableInfo();
            $this->entityTable[basename(get_class($entity))] = $info->getTableName();
        }
        foreach($this->entities as $entity)
        {
            $info = $entity->tableInfo();
            $entityName = basename(get_class($entity));
            $this->updateDiagram($entityName, $info);
        }
    }
    
    /**
     * Table info
     *
     * @param string $entityName
     * @param PicoTableInfo $info
     * @return self
     */
    private function updateDiagram($entityName, $info)
    {
        $tableName = $info->getTableName();
        if(!isset($this->entitieDiagramItem[$tableName]))
        {
            $this->entitieDiagramItem[$tableName] = new EntityDiagramItem($entityName, $tableName, $this->entityWidth, $this->entityMaxHeight);
        }
        
        // update column
        foreach($info->getColumns() as $column)
        {
            $columnName = $column['NAME'];
            if(!$this->entitieDiagramItem[$tableName]->hasColumn($columnName))
            {
                $this->entitieDiagramItem[$tableName]->addColumn($column);
            }
        }
        
        // update join column
        foreach($info->getJoinColumns() as $column)
        {
            $columnName = $column['NAME'];
            if($this->entitieDiagramItem[$tableName]->hasColumn($columnName))
            {
                $propertyType = $column['propertyType'];
                try
                {
                    $obj = new $propertyType();
                    $info2 = $obj->tableInfo();
                    $referenceTableName = $info2->getTableName();
                    $referenceColumnName = $columnName;
                    $this->entitieDiagramItem[$tableName]->setJoinColumn($columnName, $propertyType, $referenceTableName, $referenceColumnName);
                }
                catch(Exception $e)
                {
                    // do nothing
                }
            }
        }
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
    
    public function drawERD()
    {
        $this->width = $this->calculateWidth();
        $this->height = $this->calculateHeight();
    }
    
    public function calculateWidth()
    {
        $maxX = 0;
        foreach($this->entitieDiagramItem as $diagram)
        {
            $maxDiagramX = $diagram->getMaxX();
            if($maxX < $maxDiagramX)
            {
                $maxX = $maxDiagramX;
            }
        }
        return $maxX;
    }
    
    public function calculateHeight()
    {
        $maxY = 0;
        foreach($this->entitieDiagramItem as $diagram)
        {
            $maxDiagramY = $diagram->getMaxY();
            if($maxY < $maxDiagramY)
            {
                $maxY = $maxDiagramY;
            }
        }
        return $maxY;
    }
}