<?php

namespace AppBuilder\Generator;

use Exception;
use MagicObject\Database\PicoTableInfo;
use MagicObject\MagicObject;
use SVG\Nodes\Shapes\SVGLine;
use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Texts\SVGText;

class EntityRelationshipDiagram
{
    const STROKE_LINE = '#999999';
    /**
     * Entity DiagramItem
     *
     * @var EntityDiagramItem[]
     */
    private $entitieDiagramItem = array();
    private $relationshipDiagramItem = array();
    
    private $entityWidth = 1;
    private $entityMaxHeight;
    private $entityMargin = 50;

    
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
    private $entities = array();
    
    /**
     * Constructor
     *
     * @param MagicObject[] $entities
     */
    public function __construct($entityWidth, $entityMaxHeight = null, $entities = null)
    {
        $this->entityWidth = $entityWidth;
        if(isset($entityMaxHeight))
        {
            $this->entityMaxHeight = $entityMaxHeight;
        }
        if(isset($entities) && is_array($entities))
        {
            $this->addEntities($entities);
        }
        
    }
    
    public function addEntities($entities)
    {
        foreach($entities as $entity)
        {
            $this->addEntity($entity);
        }
        return $this;
    }
    
    /**
     * Add entity
     *
     * @param MagicObject $entity
     * @return self
     */
    public function addEntity($entity)
    {
        $this->entities[] = $entity;
        $info = $entity->tableInfo();
        $this->entityTable[basename(get_class($entity))] = $info->getTableName();

        $info = $entity->tableInfo();
        $entityName = basename(get_class($entity));
        $this->updateDiagram($entityName, $info);

        return $this;
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
            $x = 20 + (count($this->entitieDiagramItem) * $this->entityWidth) + (count($this->entitieDiagramItem) * $this->entityMargin);
            $y = 20;
            $this->entitieDiagramItem[$tableName] = new EntityDiagramItem($entityName, $tableName, $x, $y, $this->entityWidth, $this->entityMaxHeight);
        }
        
        // update column
        foreach($info->getColumns() as $column)
        {
            $columnName = $column['name'];
            if(!$this->entitieDiagramItem[$tableName]->hasColumn($columnName))
            {
                $this->entitieDiagramItem[$tableName]->addColumn($column);
            }
        }
        
        // update join column
        foreach($info->getJoinColumns() as $column)
        {
            $columnName = $column['name'];
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
        
        
        $image = new SVG($this->width, $this->height);
        $doc = $image->getDocument();

        foreach($this->entitieDiagramItem as $diagram)
        {
            $index = 0;
            $svgGroup = new SVG($diagram->getWidth(), $diagram->getHeight());
            
            $group = $svgGroup->getDocument();
            
            $group->setAttribute('x', $diagram->getX());
            $group->setAttribute('y', $diagram->getY());
            $group->setAttribute('name', $diagram->getEntityName());
            $group->setAttribute('x', $diagram->getX());
            $group->setAttribute('y', $diagram->getY());
            
            $square = new SVGRect(0, 0, $diagram->getWidth(), $diagram->getHeight());
            $square->setStyle('fill', 'none');
            $square->setStyle('stroke', self::STROKE_LINE);
            
            
            $group->addChild($square);
            
            $headerBg = new SVGRect(1, 1, $diagram->getWidth()-2, $diagram->getHeaderHeight()-2);
            $headerBg->setStyle('fill', '#EEEEEE');
            $group->addChild($headerBg);
            
            
            $x = 10;
            $y = 13 + ($index * $diagram->getColumnHeight());
            $entityHeader = new SVGText($diagram->getTableName(), $x, $y);
            $entityHeader->setStyle('font-family', 'Arial');
            $entityHeader->setStyle('font-size', '8pt');
            $entityHeader->setStyle('font-weight', 'normal');            
            $group->addChild($entityHeader);

            $separator = new SVGLine(0, $diagram->getColumnHeight(), $diagram->getWidth(), $diagram->getColumnHeight());
            $separator->setStyle('stroke', self::STROKE_LINE);
            $group->addChild($separator);         

            foreach($diagram->getColumns() as $column)
            {
                $index++;
                $y = -7 + $diagram->getHeaderHeight() + ($index * $diagram->getColumnHeight());
                
                if($index > 1)
                {
                    $separator = new SVGLine(0, $y - 14, $diagram->getWidth(), $y - 14);
                    $separator->setStyle('stroke', self::STROKE_LINE);
                    $group->addChild($separator);
                }
                
                $group->addChild($this->createColumn($diagram, $column, $x, $y));

            }
            $doc->addChild($group);       
        }
        return $image->__toString();
    }
    
    private function createColumn($diagram, $column, $x, $y)
    {
        $svgColumn = new SVG($diagram->getWidth(), 40);
        $columnElem = $svgColumn->getDocument();
        
        $columnElem->setAttribute('x', $x);
        $columnElem->setAttribute('y', $y-10);
        
        $columnSvg = new SVGText($column->getColumnName(), 0, 10);
        $columnSvg->setStyle('font-family', 'Arial');
        $columnSvg->setStyle('font-size', '8pt');
        $columnSvg->setStyle('font-weight', 'normal');
        
        
        $columnElem->addChild($columnSvg);
        
        return $columnElem;
        
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
    
    public function __toString()
    {
        return $this->drawERD();
    }
}