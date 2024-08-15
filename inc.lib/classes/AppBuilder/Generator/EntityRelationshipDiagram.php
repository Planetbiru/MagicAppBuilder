<?php

namespace AppBuilder\Generator;

use Exception;
use MagicObject\Database\PicoTableInfo;
use MagicObject\Geometry\Point;
use MagicObject\MagicObject;
use MagicObject\SecretObject;
use ReflectionClass;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Shapes\SVGPath;
use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\SVGNode;
use SVG\Nodes\Texts\SVGText;

class EntityRelationshipDiagram //NOSONAR
{
    const NAMESPACE_SEPARATOR = "\\";
    const STROKE_DIAGRAM = '#8496B1';
    const STROKE_LINE = '#606C80';
    const HEADER_BACKGROUND_COLOR = '#E0EDFF';
    const ENTITY_BACKGROUND_COLOR = '#FFFFFF';
    const TEXT_COLOR_COLUMN = '#444444';
    const TEXT_COLOR_TABLE = '#1d3c86';
    const TEXT_OFFSET_X = 16;
    const TEXT_OFFSET_Y = 14;
    const FONT_FAMILY = 'Arial';
    const FONT_SIZE = '8pt';
    
    const ICON_COLUMN_PRIMARY_KEY_COLOR = '#CC0088';
    const ICON_COLUMN_COLOR = '#2A56BD';
    
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
    
    /**
     * Entity width
     *
     * @var integer
     */
    private $entityWidth = 1;

    /**
     * Margin between 2 entities in X axis
     *
     * @var integer
     */
    private $entityMarginX = 40;
    
    /**
     * Margin between 2 entities in Y axis
     *
     * @var integer
     */
    private $entityMarginY = 20;
    
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
    private $height = 1;
    
    /**
     * Margin X
     *
     * @var integer
     */
    private $marginX = 20;
    
    /**
     * Margin Y
     *
     * @var integer
     */
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
     * Maximum level
     *
     * @var integer
     */
    private $maximumLevel;
    
    /**
     * Maximum columns
     *
     * @var integer
     */
    private $maximumColumn = 0;
    
    /**
     * Constructor
     *
     * @param MagicObject[] $entities
     */
    public function __construct($appConfig, $entityWidth, $entityMarginX = null, $entityMarginY = null, $entities = null)
    {
        $this->appConfig = $appConfig;
        $this->entityWidth = $entityWidth;
        if(isset($entityMarginX))
        {
            $this->entityMarginX = $entityMarginX;
        }
        if(isset($entityMarginY))
        {
            $this->entityMarginY = $entityMarginY;
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
     * @param integer $level
     * @return self
     */
    public function addEntity($entity, $level = 1)
    {
        $this->entities[] = $entity;
        $info = $entity->tableInfo();
        $this->entityTable[basename(get_class($entity))] = $info->getTableName();
        $info = $entity->tableInfo();
        $reflectionClass = new ReflectionClass($entity);
        $entityName = basename(get_class($entity));
        $this->updateDiagram($reflectionClass, $entityName, $info, $level);
        return $this;
    }
    
    /**
     * Table info
     *
     * @param ReflectionClass $reflectionClass
     * @param string $entityName
     * @param PicoTableInfo $info
     * @param integer $level
     * @return self
     */
    private function updateDiagram($reflectionClass, $entityName, $info, $level)
    {
        $tableName = $info->getTableName();
        if(!isset($this->entitieDiagramItem[$tableName]))
        {
            $x = $this->marginX + (count($this->entitieDiagramItem) * $this->entityWidth) + (count($this->entitieDiagramItem) * $this->entityMarginX);
            $y = $this->marginY;
            $entityId = $tableName;
            $this->entitieDiagramItem[$tableName] = new EntityDiagramItem($entityName, $tableName, $entityId, $x, $y, $this->entityWidth);
            $this->entitieDiagramItem[$tableName]->setHeaderHeight($this->headerHeight);
            $this->entitieDiagramItem[$tableName]->setColumnHeight($this->columnHeight);
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
                $referenceColumnName = $column['referenceColumnName'];
                $this->processReference($reflectionClass, $tableName, $columnName, $referenceColumnName, $propertyType, $level);
            }
        }
    }
    
    /**
     * Process reference
     *
     * @param ReflectionClass $reflectionClass
     * @param string $entityName
     * @param PicoTableInfo $info
     * @param integer $level
     * @return void
     */
    private function processReference($reflectionClass, $tableName, $columnName, $referenceColumnName, $propertyType, $level)
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

            if((isset($this->maximumLevel) && $level < $this->maximumLevel) || $this->maximumLevel < 1)
            {
                $this->includeFile($realClassName);
                
                $reflect = new ReflectionClass($realClassName);
                $file = $reflect->getFileName();
                if(file_exists($file))
                {
                    include_once $file;
                    $obj = new $realClassName();
                    $info2 = $obj->tableInfo();
                    $referenceTableName = $info2->getTableName();
                    $this->entitieDiagramItem[$tableName]->setJoinColumn($columnName, $propertyType, $referenceTableName, $referenceColumnName);
                    $this->addEntity($obj, $level + 1);
                }
            }
            
        }
        catch(Exception $e)
        {
            // do nothing
            error_log($e->getMessage());
        }
    }
    
    /**
     * Include file
     *
     * @param string $realClassName
     * @return void
     */
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
    
    /**
     * Prepare entity relationship
     *
     * @return void
     */
    private function prepareEntityRelationship()
    {
        $this->entityRelationships = array();
        $this->prepareColumnPosition();
        $this->prepareRelationships();
    }
    
    /**
     * Prepare relationships
     *
     * @return void
     */
    private function prepareRelationships()
    {
        foreach($this->entitieDiagramItem as $diagram)
        {
            $index = 0;
            foreach($diagram->getColumns() as $column)
            {
                if($column->hasReferenceEntity())
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
    
    /**
     * Prepare column position
     *
     * @return void
     */
    private function prepareColumnPosition()
    {
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
    }
    
    /**
     * Get diagram by name
     *
     * @param string $tableName
     * @return void
     */
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
    
    /**
     * Draw ERD
     *
     * @return string
     */
    public function drawERD()
    {
        $this->arrangeDiagram();
        $this->prepareEntityRelationship();
        
        
        
        $image = new SVG($this->width, $this->height);
        $doc = $image->getDocument();
        
        $relationGroupDoc = new SVGGroup();

        $yOffset = intval($this->columnHeight / 2);
        foreach($this->entityRelationships as $entityRelationship)
        {
            $p1 = $entityRelationship->getStart();
            $p2 = $entityRelationship->getEnd();
            
            $x1 = $p1->getAbsolutePosition()->x;
            $y1 = $p1->getAbsolutePosition()->y + $yOffset;
            $x2 = $p2->getAbsolutePosition()->x + $entityRelationship->getDiagram()->getWidth();
            $y2 = $p2->getAbsolutePosition()->y + $yOffset;
                        
            $line = new SVGLine($x1, $y1, $x2, $y2);
            $line->setStyle('stroke', self::STROKE_LINE);
            $relationGroupDoc->addChild($line);
            
            $dot1 = new SVGCircle($x1, $y1, 2);
            $dot2 = new SVGCircle($x2, $y2, 2);
            $dot1->setStyle('fill', self::STROKE_LINE);
            $dot2->setStyle('fill', self::STROKE_LINE);
            $relationGroupDoc->addChild($dot1);
            $relationGroupDoc->addChild($dot2);
            
        }

        foreach($this->entitieDiagramItem as $diagram)
        {
            $diagramSVG = $this->createEntityItem($diagram);
            $doc->addChild($diagramSVG);       
        }
        $doc->addChild($relationGroupDoc); 
        return $image->__toString();
    }
    
    /**
     * Check if need to wrap diagram
     *
     * @param integer $countDiagram
     * @return boolean
     */
    private function needWrapDiagram($countDiagram)
    {
        return $this->maximumColumn > 0 && $countDiagram > $this->maximumColumn;
    }
    
    /**
     * Arange diagram
     *
     * @return void
     */
    private function arrangeDiagram()
    {
        $countDiagram = count($this->entitieDiagramItem);
        $max = $this->maximumColumn;
        $originalHeight = $this->calculateHeight();
        $maxHeightLastLine = 0;
        
        if($this->needWrapDiagram($countDiagram))
        {
            $rows = ceil($countDiagram / $max);
            
            $height = ($rows * $originalHeight) + (($rows - 1) * $this->entityMarginY);
        
            $maxX = 0;
            $index = 1;
            foreach($this->entitieDiagramItem as $diagram)
            {
                $maxDiagramX = $diagram->getMaxX();
                
                $maxX = max($maxX, $maxDiagramX);
                $index++;
                if($index > $max)
                {
                    break;
                }
            }
            $maxX += $this->marginX;
            $width = $maxX;   
            
            $currentRowHeight = 0;
            $currentRowTop = $this->marginY;
            
            $index = 0;
            foreach($this->entitieDiagramItem as $diagram)
            {
                $mod = $index % $max;
                $row = floor($index / $max);
                $x = ($mod * $diagram->getWidth()) + $this->marginX + ($mod * $this->entityMarginX);
                $y = $currentRowTop;
                $diagram->setX($x);
                $diagram->setY($y);         
                if($row == ($rows - 1))
                {
                    $maxHeightLastLine = max($maxHeightLastLine, $diagram->getHeight());
                }
                $currentRowHeight = max($currentRowHeight, $diagram->getHeight());
                $height = $currentRowTop + $currentRowHeight;
                
                if($mod == ($max - 1))
                {
                    $currentRowTop += ($currentRowHeight + $this->entityMarginY);
                    $currentRowHeight = 0;
                }
                $index++;
            }
            
            $this->width = $width;
            $this->height = $height;
        }
        else
        {
            $this->height = $originalHeight;
            $this->width = $this->calculateWidth();
        }
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
        
        $entity = $svgGroup->getDocument();
        
        $entity->setAttribute('x', $diagram->getX());
        $entity->setAttribute('y', $diagram->getY());
        $entity->setAttribute('name', $diagram->getEntityName());
        $entity->setAttribute('data-table-name', $diagram->getTableName());
        $entity->setStyle('position', 'absolute');
        $entity->setStyle('z-index', 1);

        $square = new SVGRect(0, 0, $diagram->getWidth(), $diagram->getHeight());
        $square->setStyle('fill', self::ENTITY_BACKGROUND_COLOR);
        $square->setStyle('stroke', self::STROKE_DIAGRAM);
        
        $headerBg = new SVGRect(1, 1, $diagram->getWidth() - 2, $diagram->getHeaderHeight() - 1);
        $headerBg->setStyle('fill', self::HEADER_BACKGROUND_COLOR);
        $entity->addChild($square);
        $entity->addChild($headerBg);     
        
        $x = 0;
        $y = self::TEXT_OFFSET_Y + ($index * $diagram->getColumnHeight());
        $entityHeader = new SVGText($diagram->getTableName(), self::TEXT_OFFSET_X, $y);
        $entityHeader->setStyle('font-family', self::FONT_FAMILY);
        $entityHeader->setStyle('font-size', self::FONT_SIZE);
        $entityHeader->setStyle('fill', self::TEXT_COLOR_TABLE);
        $entity->addChild($entityHeader);
        
        $iconTable = $this->createIconTable();
        $entity->addChild($iconTable);

        foreach($diagram->getColumns() as $column)
        {
            $y = $diagram->getHeaderHeight() + ($index * $diagram->getColumnHeight());                
            $entity->addChild($this->createColumn($diagram, $column, $x, $y));
            $index++;
        }
        return $entity;
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
     * Create column
     *
     * @param EntityDiagramItem $diagram
     * @param EntityDiagramColumn $column
     * @param integer $x X coordinate
     * @param integer $y Y coordinate
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
     * Create icon table
     *
     * @return SVGPath
     */
    private function createIconTable()
    {
        $icon = new SVGGroup();
        
        $points = array();
        $points[] = new Point(4, 10);
        $points[] = new Point(4, 8);
        $points[] = new Point(12, 8);
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
        $points[] = new Point(6, 8);
        $points[] = new Point(8, 8);
        $points[] = new Point(8, 14);
        $points[] = new Point(10, 14);
        $points[] = new Point(10, 8);

        $description = $this->createPathDescription($points, false);
        $path2 = new SVGPath($description);
        $path2->setStyle('fill', 'transparent');   
        $path2->setStyle('stroke', $fillStyle); 
        $path2->setAttribute('stroke-width', 0.5); 
        
        $icon->addChild($path1);
        $icon->addChild($path2);
        
        $icon->setAttribute('class', 'entity-icon icon-table');
        return $icon;
    }
    
    /**
     * Create icon column
     *
     * @param EntityDiagramColumn $column
     * @return SVGPath
     */
    private function createIconColumn($column)
    {
        $icon = new SVGGroup();
        $points = array();
        $points[] = new Point(8, 6);
        $points[] = new Point(12, 10);
        $points[] = new Point(8, 14);
        $points[] = new Point(4, 10);
        
        $description = $this->createPathDescription($points);
        
        $path = new SVGPath($description);
        
        $pkColor = self::ICON_COLUMN_PRIMARY_KEY_COLOR;
        $columnColor = self::ICON_COLUMN_COLOR;
        
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
        $icon->setAttribute('class', 'entity-icon icon-column');
        $icon->addChild($path);
        return $icon;
    }
    
    /**
     * Create path description from points
     *
     * @param Point $points
     * @param boolean $closed
     * @return string
     */
    private function createPathDescription($points, $closed = true)
    {
        $coords = array();
        foreach($points as $point)
        {
            $coords[] = $point->x . ' ' . $point->y;
        }
        return "M".implode(" L", $coords).($closed?" Z":"");
    }
    
    /**
     * Calculate diagram width
     *
     * @return integer
     */
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
    
    /**
     * Calculate diagram height
     *
     * @return integer
     */
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
     * Get maximum level
     *
     * @return  integer
     */ 
    public function getMaximumLevel()
    {
        return $this->maximumLevel;
    }

    /**
     * Set maximum level
     *
     * @param  integer  $maximumLevel  Maximum level
     *
     * @return  self
     */ 
    public function setMaximumLevel($maximumLevel)
    {
        $this->maximumLevel = $maximumLevel;

        return $this;
    }

    

    /**
     * Get maximum columns
     *
     * @return  integer
     */ 
    public function getMaximumColumn()
    {
        return $this->maximumColumn;
    }

    /**
     * Set maximum columns
     *
     * @param  integer  $maximumColumn  Maximum columns
     *
     * @return  self
     */ 
    public function setMaximumColumn($maximumColumn)
    {
        $this->maximumColumn = $maximumColumn;

        return $this;
    }
}