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
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\SVGNode;
use SVG\Nodes\Texts\SVGText;

class EntityRelationshipDiagram
{
    const NAMESPACE_SEPARATOR = "\\";
    const STROKE_LINE = '#999999';
    const HEADER_BACKGROUND_COLOR = '#EEEEEE';
    const TEXT_COLOR_COLUMN = '#555555';
    const TEXT_COLOR_TABLE = '#214497';
    const TEXT_OFFSET_X = 16;
    const TEXT_OFFSET_Y = 14;
    const FONT_FAMILY = 'Arial';
    const FONT_SIZE = '8pt';
    
    /**
     * Entity DiagramItem
     *
     * @var EntityDiagramItem[]
     */
    private $entitieDiagramItem = array();
    
    /**
     * Entity reference
     *
     * @var EntityRelationship[]
     */
    private $entityRelationships = array();
    
    private $entityWidth = 1;
    private $entityMaxHeight;
    private $entityMargin = 40;

    
    private $width = 1;
    private $height = 1;
    private $marginX = 20;
    private $marginY = 20;
    
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
            $x = $this->marginX + (count($this->entitieDiagramItem) * $this->entityWidth) + (count($this->entitieDiagramItem) * $this->entityMargin);
            $y = $this->marginY;
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
    
    private function prepareEntityRelationship()
    {
        $this->entityRelationships = array();
        
        foreach($this->entitieDiagramItem as $diagram)
        {
            $index = 0;
            foreach($diagram->getColumns() as $column)
            {
                $x = 0;
                $y = $diagram->getHeaderHeight() + ($index * $diagram->getColumnHeight());
                $column->setX($x);
                $column->setY($y);
                $index++;
            }
        }
        
        foreach($this->entitieDiagramItem as $diagram)
        {
            $index = 0;
            foreach($diagram->getColumns() as $column)
            {
                $x = 0;
                if($column->getReferenceTableName() != null && $column->getReferenceColumnName() != null)
                {
                    $tableName = $column->getReferenceTableName();
                    $referenceColumnName = $column->getReferenceColumnName();
                    $referenceDiagram = $this->getDiagramByTableName($tableName);
                    if(isset($referenceDiagram))
                    {
                        $referenceColumn = $this->getDiagramColumnByTableColumnName($referenceDiagram, $referenceColumnName);
                        if(isset($referenceColumn))
                        {
                            $this->entityRelationships[] = new EntityRelationship($diagram, $column, $referenceDiagram, $referenceColumn);
                        }
                    }
                }
                $index++;
            }
        }
    }
    
    private function getDiagramByTableName($tableName)
    {
        return isset($this->entitieDiagramItem[$tableName]) ? $this->entitieDiagramItem[$tableName] : null;
    }
    
    /**
     * Get column by name
     *
     * @param EntityDiagramItem $diagram
     * @return EntityDiagramColumn
     */
    private function getDiagramColumnByTableColumnName($diagram, $columnName)
    {
        if(!isset($diagram))
        {
            return null;
        }
        $columns = $diagram->getColumns();
        return isset($columns[$columnName]) ? $columns[$columnName] : null;
    }
    
    public function drawERD()
    {
        $this->prepareEntityRelationship();
        $this->width = $this->calculateWidth();
        $this->height = $this->calculateHeight();
        
        
        
        $image = new SVG($this->width, $this->height);
        $doc = $image->getDocument();
        
        $relationGroup = new SVG($this->width, $this->height);
        $relationGroupDoc = $relationGroup->getDocument();
        $relationGroupDoc->setAttribute('y', 10);
        $relationGroupDoc->setStyle('position', 'absolute');
        $relationGroupDoc->setStyle('z-index', 0);
        foreach($this->entityRelationships as $entityRelationship)
        {
            $p1 = $entityRelationship->getStart();
            $p2 = $entityRelationship->getEnd();
            error_log(print_r(array($p1->getAbsolutePosition()->x, $p1->getAbsolutePosition()->y, $p2->getAbsolutePosition()->x + $entityRelationship->getDiagram()->getWidth(), $p2->getAbsolutePosition()->y), true));
            $line = new SVGLine($p1->getAbsolutePosition()->x, $p1->getAbsolutePosition()->y, $p2->getAbsolutePosition()->x + $entityRelationship->getDiagram()->getWidth(), $p2->getAbsolutePosition()->y);
            $line->setStyle('stroke', 'black');
            $relationGroupDoc->addChild($line);
             
        }
        $doc->addChild($relationGroupDoc); 

        foreach($this->entitieDiagramItem as $diagram)
        {
            $diagramSVG = $this->createEntityItem($diagram);
            $doc->addChild($diagramSVG);       
        }
        return $image->__toString();
    }
    
    /**
     * Create entity item
     *
     * @param EntityDiagramItem $diagram
     * @return SVGNode
     */
    private function createEntityItem($diagram)
    {
        $index = 0;
        $svgGroup = new SVG($diagram->getWidth(), $diagram->getHeight());
        
        $group = $svgGroup->getDocument();
        
        $group->setAttribute('x', $diagram->getX());
        $group->setAttribute('y', $diagram->getY());
        $group->setAttribute('name', $diagram->getEntityName());
        $group->setStyle('position', 'absolute');
        $group->setStyle('z-index', 1);

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
        $entityHeader->setStyle('fill', self::TEXT_COLOR_TABLE);
        $group->addChild($entityHeader);
        
        $iconTable = $this->createIconTable();
        $group->addChild($iconTable);

        foreach($diagram->getColumns() as $column)
        {
            $y = $diagram->getHeaderHeight() + ($index * $diagram->getColumnHeight());                
            $group->addChild($this->createColumn($diagram, $column, $x, $y));
            $index++;
        }
        return $group;
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
        $columnSvg->setStyle('fill', self::TEXT_COLOR_COLUMN);
        
        $columnElem->setAttribute('class', $diagram->getEntityId().'_'.$column->getColumnName());
        if($column->getReferenceTableName() != null && $column->getReferenceColumnName() != null)
        {
            $columnElem->setAttribute('reference-table-name', $column->getReferenceTableName());
            $columnElem->setAttribute('reference-column-name', $column->getReferenceColumnName());
            $columnElem->setAttribute('reference-name', $column->getReferenceTableName() . '_' . $column->getReferenceColumnName());
        }
        
        $columnElem->addChild($columnSvg);
        $columnElem->addChild($this->createSeparatorLine($diagram));
        $iconColumn = $this->createIconColumn($column);
        $columnElem->addChild($iconColumn);
        
        return $columnElem;
        
    }
    
    /**
     * Create icon column
     *
     * @return SVGPath
     */
    private function createIconTable()
    {
        $group = new SVGGroup();
        
        $points = array();
        $points[] = new Point(4, 10);
        $points[] = new Point(4, 7);
        $points[] = new Point(12, 7);
        $points[] = new Point(12, 14);
        $points[] = new Point(4, 14);
        $points[] = new Point(4, 10);
        $points[] = new Point(12, 10);
        $points[] = new Point(12, 12);
        $points[] = new Point(4, 12);
        $description = $this->createPathDescription($points, true);
        $path1 = new SVGPath($description);

        $fillStyle = '#2A56BD';
        $path1->setStyle('fill', 'white');   
        $path1->setStyle('stroke', $fillStyle); 
        $path1->setAttribute('stroke-width', 0.5); 
        
        $points = array();
        $points[] = new Point(6, 14);
        $points[] = new Point(6, 7);
        $points[] = new Point(8, 7);
        $points[] = new Point(8, 14);
        $points[] = new Point(10, 14);
        $points[] = new Point(10, 7);

        $description = $this->createPathDescription($points, false);
        $path2 = new SVGPath($description);
        $path2->setStyle('fill', 'transparent');   
        $path2->setStyle('stroke', $fillStyle); 
        $path2->setAttribute('stroke-width', 0.5); 
        
        $group->addChild($path1);
        $group->addChild($path2);
        return $group;
    }
    
    /**
     * Create icon column
     *
     * @param EntityDiagramColumn $column
     * @return SVGPath
     */
    private function createIconColumn($column)
    {
        $group = new SVGGroup();
        $points = array();
        $points[] = new Point(8, 6);
        $points[] = new Point(12, 10);
        $points[] = new Point(8, 14);
        $points[] = new Point(4, 10);
        
        $description = $this->createPathDescription($points);
        
        $path = new SVGPath($description);
        
        $pkColor = '#CC0088';
        $columnColor = '#2A56BD';
        
        if($column->getPrimaryKey())
        {
            $path->setStyle('fill', $pkColor);
            $path->setStyle('stroke', $pkColor);
        }
        else if($column->hasReference())
        {
            $path->setStyle('fill', $columnColor);
            $path->setStyle('stroke', $columnColor);
            
        }
        else
        {
            $path->setStyle('fill', 'transparent');   
            $path->setStyle('stroke', $columnColor); 
        }
        
        $group->addChild($path);
        return $group;
    }
    
    private function createPathDescription($points, $closed = true)
    {
        $coords = array();
        foreach($points as $point)
        {
            $coords[] = $point->x . ' ' . $point->y;
        }
        return "M".implode(" L", $coords).($closed?" Z":"");
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
        $maxX += $this->marginX;
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
        $maxY += $this->marginY;
        return $maxY;
    }
    
    public function __toString()
    {
        return $this->drawERD();
    }



    /**
     * Get the value of marginX
     */ 
    public function getMarginX()
    {
        return $this->marginX;
    }

    /**
     * Set the value of marginX
     *
     * @return  self
     */ 
    public function setMarginX($marginX)
    {
        $this->marginX = $marginX;

        return $this;
    }

    /**
     * Get the value of marginY
     */ 
    public function getMarginY()
    {
        return $this->marginY;
    }

    /**
     * Set the value of marginY
     *
     * @return  self
     */ 
    public function setMarginY($marginY)
    {
        $this->marginY = $marginY;

        return $this;
    }
}