<?php

namespace AppBuilder\Generator;

use Exception;
use MagicObject\Database\PicoTableInfo;
use MagicObject\Geometry\Point;
use MagicObject\MagicObject;
use MagicObject\SecretObject;
use ReflectionClass;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Shapes\SVGPath;
use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Texts\SVGText;

class EntityRelationshipDiagram
{
    const NAMESPACE_SEPARATOR = "\\";
    const STROKE_LINE = '#999999';
    const HEADER_BACKGROUND_COLOR = '#EEEEEE';
    const TEXT_OFFSET_X = 15;
    const TEXT_OFFSET_Y = 14;
    const FONT_FAMILY = 'Arial';
    const FONT_SIZE = '8pt';
    
    /**
     * Entity DiagramItem
     *
     * @var EntityDiagramItem[]
     */
    private $entitieDiagramItem = array();
    
    private $entityWidth = 1;
    private $entityMaxHeight;
    private $entityMargin = 40;

    
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
     * Application config
     *
     * @var SecretObject
     */
    private $appConfig;
    
    /**
     * Constructor
     *
     * @param MagicObject[] $entities
     */
    public function __construct($appConfig, $entityWidth, $entityMaxHeight = null, $entityMargin = null, $entities = null)
    {
        $this->appConfig = $appConfig;
        $this->entityWidth = $entityWidth;
        if(isset($entityMaxHeight))
        {
            $this->entityMaxHeight = $entityMaxHeight;
        }
        if(isset($entityMargin))
        {
            $this->entityMargin = $entityMargin;
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
        $reflectionClass = new ReflectionClass($entity);
        $entityName = basename(get_class($entity));
        $this->updateDiagram($reflectionClass, $entityName, $info);

        return $this;
    }
    
    /**
     * Table info
     *
     * @param ReflectionClass $reflectionClass
     * @param string $entityName
     * @param PicoTableInfo $info
     * @return self
     */
    private function updateDiagram($reflectionClass, $entityName, $info)
    {
        $tableName = $info->getTableName();
        if(!isset($this->entitieDiagramItem[$tableName]))
        {
            $x = 20 + (count($this->entitieDiagramItem) * $this->entityWidth) + (count($this->entitieDiagramItem) * $this->entityMargin);
            $y = 20;
            $entityId = $tableName;
            $this->entitieDiagramItem[$tableName] = new EntityDiagramItem($entityName, $tableName, $entityId, $x, $y, $this->entityWidth, $this->entityMaxHeight);
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
        foreach($info->getPrimaryKeys() as $column)
        {
            $columnName = $column['name'];
            if($this->entitieDiagramItem[$tableName]->hasColumn($columnName))
            {
                $this->entitieDiagramItem[$tableName]->setPrimaryKeyColumn($columnName);
            }
        }
        
        // update join column
        foreach($info->getJoinColumns() as $column)
        {
            $columnName = $column['name'];
            
            if($this->entitieDiagramItem[$tableName]->hasColumn($columnName))
            {
                $propertyType = $column['propertyType'];
                $this->processReference($reflectionClass, $tableName, $columnName, $propertyType);
            }
        }
    }
    
    private function processReference($reflectionClass, $tableName, $columnName, $propertyType)
    {
        try
        {
            $this->entitieDiagramItem[$tableName]->setReferenceColumn($columnName);
            
            if(strpos($propertyType, self::NAMESPACE_SEPARATOR) === false)
            {
                $realClassName = $reflectionClass->getNamespaceName().self::NAMESPACE_SEPARATOR.$propertyType;
            }
            else
            {
                $realClassName = $propertyType;
            }

            
            $this->includeFile($realClassName);
            
            $reflect = new ReflectionClass($realClassName);
            $file = $reflect->getFileName();
            if(file_exists($file))
            {
                include_once $file;
                $obj = new $realClassName();
                $info2 = $obj->tableInfo();
                $referenceTableName = $info2->getTableName();
                $referenceColumnName = $columnName;
                $this->entitieDiagramItem[$tableName]->setJoinColumn($columnName, $propertyType, $referenceTableName, $referenceColumnName);
                $this->addEntity($obj);
            }
            
            
        }
        catch(Exception $e)
        {
            // do nothing
            error_log($e->getMessage());
        }
    }
    
    private function includeFile($realClassName)
    {
        $application = $this->appConfig->getApplication();
        $classToInclude = $realClassName;
        $baseDirectory = $application->getBaseEntityDirectory();
        $path = $baseDirectory."/".str_replace("\\", "/", $classToInclude).".php";
        
        if(file_exists($path))
        {
            require_once $path;
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
            
            
            
            
            $headerBg = new SVGRect(0, 0, $diagram->getWidth(), $diagram->getHeaderHeight());
            $headerBg->setStyle('fill', self::HEADER_BACKGROUND_COLOR);
            $group->addChild($headerBg);
            $group->addChild($square);
            
            $x = 0;
            $y = self::TEXT_OFFSET_Y + ($index * $diagram->getColumnHeight());
            $entityHeader = new SVGText($diagram->getTableName(), self::TEXT_OFFSET_X, $y);
            $entityHeader->setStyle('font-family', self::FONT_FAMILY);
            $entityHeader->setStyle('font-size', self::FONT_SIZE);
            $group->addChild($entityHeader);

            foreach($diagram->getColumns() as $column)
            {
                $y = $diagram->getHeaderHeight() + ($index * $diagram->getColumnHeight());                
                $group->addChild($this->createColumn($diagram, $column, $x, $y));
                $index++;
            }
            $doc->addChild($group);       
        }
        return $image->__toString();
    }
    
    /**
     * Create separator
     *
     * @param EntityDiagramItem $diagram
     * @return SVGLine
     */
    private function createSeparatorLine($diagram)
    {
        $separator = new SVGLine(0, 0, $diagram->getWidth(), 0);
        $separator->setStyle('stroke', self::STROKE_LINE);
        return $separator;
    }
    
    /**
     * Create separator
     *
     * @param EntityDiagramItem $diagram
     * @param EntityDiagramColumn $column
     * @param integer $x
     * @param integer $y
     * @return SVGLine
     */
    private function createColumn($diagram, $column, $x, $y)
    {
        $svgColumn = new SVG($diagram->getWidth(), $diagram->getColumnHeight());
        $columnElem = $svgColumn->getDocument();
        
        
        $columnElem->setAttribute('x', $x);
        $columnElem->setAttribute('y', $y);
        
        $columnSvg = new SVGText($column->getColumnName(), self::TEXT_OFFSET_X, self::TEXT_OFFSET_Y);
        $columnSvg->setStyle('font-family', self::FONT_FAMILY);
        $columnSvg->setStyle('font-size', self::FONT_SIZE);
        
        $columnElem->setAttribute('class', $diagram->getEntityId().'_'.$column->getColumnName());
        if($column->getReferenceTableName() != null && $column->getReferenceColumnName() != null)
        {
            $columnElem->setAttribute('reference', $column->getReferenceTableName() . '_' . $column->getReferenceColumnName());
        }
        
        $columnElem->addChild($columnSvg);
        $columnElem->addChild($this->createSeparatorLine($diagram));
        $icon = $this->createIconColumn($column);
        $columnElem->addChild($icon);
        
        return $columnElem;
        
    }
    
    /**
     * Create icon column
     *
     * @param EntityDiagramColumn $column
     * @return SVGPath
     */
    private function createIconColumn($column)
    {
        $points = array();
        $points[] = new Point(8, 6);
        $points[] = new Point(12, 10);
        $points[] = new Point(8, 14);
        $points[] = new Point(4, 10);
        
        $description = $this->createPathDescription($points);
        
        $path = new SVGPath($description);
        
        if($column->getPrimaryKey())
        {
            $fillStyle = '#CC0088';
            $path->setStyle('fill', $fillStyle);
        }
        else if($column->hasReference())
        {
            $fillStyle = '#5522EE';
            $path->setStyle('fill', $fillStyle);
        }
        else
        {
            $fillStyle = '#5522EE';
            $path->setStyle('fill', 'transparent');   
            $path->setStyle('stroke', $fillStyle); 
        }
        
        return $path;
    }
    
    private function createPathDescription($points)
    {
        $coords = array();
        foreach($points as $point)
        {
            $coords[] = $point->x . ' ' . $point->y;
        }
        return "M".implode("L", $coords)." Z";
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