<?php

namespace AppBuilder\Util\Entity;

use AppBuilder\Util\Error\ErrorChecker;
use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\Geometry\Area;
use MagicObject\Geometry\Point;
use MagicObject\Geometry\Polygon;
use MagicObject\Geometry\Rectangle;
use ReflectionClass;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\SVGNode;
use SVG\Nodes\Texts\SVGText;
use SVG\SVG;

class EntityRelationshipDiagram //NOSONAR
{
    const NAMESPACE_SEPARATOR = "\\";
    const STROKE_DIAGRAM = '#8496B1';
    const STROKE_LINE = '#2E4C95';
    const STROKE_ICON = '#2A56BD';
    
    const TRANSPARENT = 'transparent';
    const HEADER_BACKGROUND_COLOR = '#E0EDFF';
    const ENTITY_BACKGROUND_COLOR = '#FFFFFF';
    const TEXT_COLOR_COLUMN = '#3a4255';
    const TEXT_COLOR_TABLE = '#1d3c86';
    const TEXT_OFFSET_X = 20;
    const TEXT_OFFSET_Y = 14;
    const FONT_FAMILY = 'Arial';
    const FONT_SIZE = '8pt';
    const ICON_COLUMN_PRIMARY_KEY_COLOR = '#CC0088';
    const ICON_COLUMN_COLOR = '#2A56BD';
    
    const OFFSET_X = 3;
    
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
    
    private $zoom = 1;
    
    /**
     * Cache directory
     *
     * @var string
     */
    private $cacheDir;

    /**
     * Database connection
     *
     * @var PicoDatabase
     */
    private $databaseBuilder;

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

    /**
     * Set database connection
     *
     * @param PicoDatabase $databaseBuilder
     * @return self Returns the current instance for method chaining.
     */
    public function setDatabaseBuilder($databaseBuilder)
    {
        $this->databaseBuilder = $databaseBuilder;
        return $this;
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
     * @return self Returns the current instance for method chaining.
     */
    public function addEntity($entity, $level = 1)
    {
        $this->entities[] = $entity;
        $info = $entity->tableInfo();
        $this->entityTable[basename(get_class($entity))] = $info->getTableName();
        $info = $entity->tableInfo();
        $reflectionClass = new ReflectionClass($entity);
        $entityName = basename(get_class($entity));
        $namespace = basename(dirname(get_class($entity)));
        $this->updateDiagram($reflectionClass, $entityName, $namespace, $info, $level);
        return $this;
    }
    
    /**
     * Table info
     *
     * @param ReflectionClass $reflectionClass
     * @param string $entityName
     * @param string $namespace
     * @param PicoTableInfo $info
     * @param integer $level
     * @return self Returns the current instance for method chaining.
     */
    private function updateDiagram($reflectionClass, $entityName, $namespace, $info, $level)
    {
        $tableName = $info->getTableName();
        if(!isset($this->entitieDiagramItem[$tableName]))
        {
            $x = $this->marginX + (count($this->entitieDiagramItem) * $this->entityWidth) + (count($this->entitieDiagramItem) * $this->entityMarginX);
            $y = $this->marginY;
            $entityId = $tableName;
            $this->entitieDiagramItem[$tableName] = new EntityDiagramItem($entityName, $namespace, $tableName, $entityId, $x, $y, $this->entityWidth);
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
            
            if(((isset($this->maximumLevel) && $level < $this->maximumLevel) || $this->maximumLevel < 1) && $this->includeFile($realClassName))
            {
                $obj = new $realClassName();
                $info2 = $obj->tableInfo();
                $referenceTableName = $info2->getTableName();
                $this->entitieDiagramItem[$tableName]->setJoinColumn($columnName, $propertyType, $referenceTableName, $referenceColumnName);
                $this->addEntity($obj, $level + 1);
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
     * @return boolean
     */
    private function includeFile($realClassName)
    {
        $application = $this->appConfig->getApplication();
        $classToInclude = $realClassName;
        $baseDirectory = $application->getBaseEntityDirectory();
        $path = $baseDirectory."/".str_replace("\\", "/", $classToInclude).".php";
        
        if(file_exists($path))
        {
            $return_var = ErrorChecker::errorCheck($this->databaseBuilder, $path);
            if($return_var == 0)
            {
                require_once $path;
                return true;
            }
        }
        return false;
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
     * Prepare entity relationship depend on the entities
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
    
    public function getImageMap()
    {
        $offsetX = self::OFFSET_X;
        $this->arrangeDiagram();
        $this->prepareEntityRelationship();
        
        $yOffset = intval($this->columnHeight / 2);
        
        $area = array();
        $distance = 3;
        
        foreach($this->entitieDiagramItem as $diagram)
        {
            $rect = new Rectangle(new Point($diagram->getX(), $diagram->getY()), new Point($diagram->getX()+$diagram->getWidth(), $diagram->getY()+$diagram->getHeight()));
            $attributes = array(
                'data-type'=>'area-entity',
                'data-namespace'=>$diagram->getNamespace(),
                'data-entity'=>$diagram->getEntityName(),
                'data-table-name'=>$diagram->getTableName(),
                'data-title'=>$diagram->getEntityName()
            );
            $area[] = new Area($rect, $this->zoom, '#'.$diagram->getTableName(), $attributes);
        }
        foreach($this->entityRelationships as $entityRelationship)
        {
            $p1 = $entityRelationship->getStart();
            $p2 = $entityRelationship->getEnd();
            
            $x1 = $p1->getAbsolutePosition()->x + $offsetX;
            $y1 = $p1->getAbsolutePosition()->y + $yOffset;
            $x2 = $p2->getAbsolutePosition()->x + $entityRelationship->getDiagram()->getWidth() - $offsetX;
            $y2 = $p2->getAbsolutePosition()->y + $yOffset;
            
            $dx = $x2 - $x1;
            $dy = $y2 - $y1;
            $nx = -$dy;
            $ny = $dx;

            $length = sqrt($nx * $nx + $ny * $ny);
            $nx /= $length;
            $ny /= $length;
     
            $divX = $distance * $nx;
            $divY = $distance * $ny;
            
            $point11 = new Point(intval(($x1+$divX)), intval(($y1+$divY)));
            $point12 = new Point(intval(($x1-$divX)), intval(($y1-$divY)));
            $point21 = new Point(intval(($x2-$divX)), intval(($y2-$divY)));
            $point22 = new Point(intval(($x2+$divX)), intval(($y2+$divY)));
            
            $poly = new Polygon(array($point11, $point12, $point21, $point22));
            $href = '#'.$entityRelationship->getDiagram()->getTableName()
            .'-'.$entityRelationship->getColumn()->getColumnName()
            .'-'.$entityRelationship->getReferenceDiagram()->getTableName()
            .'-'.$entityRelationship->getReferenceColumn()->getColumnName();
            
            $attributes = array(
                'data-type'=>'area-relation',
                'data-namespace'=>$entityRelationship->getDiagram()->getNamespace(),
                'data-entity'=>$entityRelationship->getDiagram()->getEntityName(),
                'data-table-name'=>$entityRelationship->getDiagram()->getTableName(),
                'data-column-name'=>$entityRelationship->getColumn()->getColumnName(),
                'data-reference-namespace'=>$entityRelationship->getReferenceDiagram()->getNamespace(),
                'data-reference-entity'=>$entityRelationship->getReferenceDiagram()->getEntityName(),
                'data-reference-table-name'=>$entityRelationship->getReferenceDiagram()->getTableName(),
                'data-reference-column-name'=>$entityRelationship->getReferenceColumn()->getColumnName(),
                'data-title'=>$entityRelationship->getDiagram()->getEntityName().' &#10132; '.$entityRelationship->getReferenceDiagram()->getEntityName()
            );
            $area[] = new Area($poly, $this->zoom, $href, $attributes);
        }
        
        
        $result = [];
        foreach($area as $item)
        {
            $result[] = $item."";
        }
        return implode("\r\n", $result);
    }
    
    /**
     * Draw ERD
     *
     * @return string
     */
    public function drawERD()
    {
        $offsetX = self::OFFSET_X;
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
            
            $x1 = $p1->getAbsolutePosition()->x + $offsetX;
            $y1 = $p1->getAbsolutePosition()->y + $yOffset;
            $x2 = $p2->getAbsolutePosition()->x + $entityRelationship->getDiagram()->getWidth() - $offsetX;
            $y2 = $p2->getAbsolutePosition()->y + $yOffset;
                        
            $line = new SVGLine($x1, $y1, $x2, $y2);
            $line->setStyle('stroke', self::STROKE_LINE);
            $relationGroupDoc->addChild($line);
            
            $dotColor1 = $p1->getType() == EntityRelationship::MANY ? self::ICON_COLUMN_PRIMARY_KEY_COLOR : self::ICON_COLUMN_COLOR;
            $dotColor2 = $p2->getType() == EntityRelationship::MANY ? self::ICON_COLUMN_PRIMARY_KEY_COLOR : self::ICON_COLUMN_COLOR;
            
            $dot1 = new SVGCircle($x1, $y1, 2);
            $dot2 = new SVGCircle($x2, $y2, 2);
            $dot1->setStyle('fill', $dotColor1);
            $dot2->setStyle('fill', $dotColor2);
            $relationGroupDoc->addChild($dot1);
            $relationGroupDoc->addChild($dot2);     
        }

        foreach($this->entitieDiagramItem as $diagram)
        {
            $diagramSVG = $this->createEntityItem($diagram);
            $doc->addChild($diagramSVG);       
        }
        $doc->addChild($relationGroupDoc); 
        
        // set zoom
        $width = $this->width;
        $height = $this->height;
        
        $width2 = $this->width * $this->zoom;
        $height2 = $this->height * $this->zoom;
        $doc->setAttribute('width', $width2);
        $doc->setAttribute('height', $height2);
        $doc->setAttribute('viewBox', "0 0 $width $height");
        
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
     * Arange diagram into a atrix
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
            $this->height = $height + $this->marginY;
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
        $offsetX = 3;
        $offsetY = 0;
        $index = 0;
        $svgGroup = new SVG($diagram->getWidth(), $diagram->getHeight());
        
        $entity = $svgGroup->getDocument();
        
        $entity->setAttribute('x', $diagram->getX());
        $entity->setAttribute('y', $diagram->getY());
        $entity->setAttribute('name', $diagram->getEntityName());
        $entity->setAttribute('data-table-name', $diagram->getTableName());
        $entity->setStyle('position', 'absolute');
        $entity->setStyle('z-index', 1);

        $mainFrame = new SVGRect(0, 0, $diagram->getWidth(), $diagram->getHeight());
        $mainFrame->setStyle('fill', self::TRANSPARENT);
        $mainFrame->setStyle('stroke', self::STROKE_DIAGRAM);
        
        $bodyBG = new SVGRect(0, 0, $diagram->getWidth(), $diagram->getHeight());
        $bodyBG->setStyle('fill', self::ENTITY_BACKGROUND_COLOR);
        
        $headerBG = new SVGRect(0, 0, $diagram->getWidth(), $diagram->getHeaderHeight() - 1);
        $headerBG->setStyle('fill', self::HEADER_BACKGROUND_COLOR);
        
        $entity->addChild($bodyBG); 
        $entity->addChild($headerBG);
        $entity->addChild($mainFrame);
        
        $x = 0;
        $y = self::TEXT_OFFSET_Y + ($index * $diagram->getColumnHeight());
        $entityHeader = new SVGText($diagram->getTableName(), self::TEXT_OFFSET_X, $y);
        $entityHeader->setStyle('font-family', self::FONT_FAMILY);
        $entityHeader->setStyle('font-size', self::FONT_SIZE);
        $entityHeader->setStyle('fill', self::TEXT_COLOR_TABLE);
        $entity->addChild($entityHeader);
        
        $iconTable = $this->createIconTable($offsetX, $offsetY);
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
        $separator->setStyle('stroke', self::STROKE_DIAGRAM);
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
        $offsetX = 3;
        $offsetY = 0;
        $svgColumn = new SVG($diagram->getWidth(), $diagram->getColumnHeight());
        $columnElem = $svgColumn->getDocument();
        $columnElem->setAttribute('x', $x);
        $columnElem->setAttribute('y', $y-1);
        
        $rect = new SVGRect(1, 1, $diagram->getWidth() - 2, $diagram->getColumnHeight());
        $rect->setStyle('fill', self::TRANSPARENT);
        
        $columnElem->addChild($rect);
        
        $text = new SVGText($column->getColumnName(), self::TEXT_OFFSET_X, self::TEXT_OFFSET_Y);
        $text->setStyle('font-family', self::FONT_FAMILY);
        $text->setStyle('font-size', self::FONT_SIZE);
        $text->setStyle('fill', self::TEXT_COLOR_COLUMN);
        
        $columnElem->setAttribute('class', $diagram->getEntityId().'_'.$column->getColumnName());
        if($column->getReferenceTableName() != null && $column->getReferenceColumnName() != null)
        {
            $columnElem->setAttribute('reference-table-name', $column->getReferenceTableName());
            $columnElem->setAttribute('reference-column-name', $column->getReferenceColumnName());
            $columnElem->setAttribute('reference-name', $column->getReferenceTableName() . '_' . $column->getReferenceColumnName());
        }
        
        $columnElem->addChild($this->createSeparatorLine($diagram));
        $icon = $this->createIconColumn($column, $offsetX, $offsetY);
        $columnElem->addChild($text);
        $columnElem->addChild($icon);
        
        return $columnElem;
    }
    
    /**
     * Create icon table
     *
     * @return SVGPath
     */
    private function createIconTable($offsetX = 0, $offsetY = 0)
    {
        $icon = new SVGGroup();
        $points = array();
        $points[] = new Point(4, 9);
        $points[] = new Point(4, 7);
        $points[] = new Point(12, 7);
        $points[] = new Point(12, 13);
        $points[] = new Point(4, 13);
        $points[] = new Point(4, 9);
        $points[] = new Point(12, 9);
        $points[] = new Point(12, 11);
        $points[] = new Point(4, 11);
        $description = $this->createPathDescription($points, true, $offsetX, $offsetY);
        $path1 = new SVGPath($description);

        $fillStyle = self::STROKE_ICON;
        $path1->setStyle('fill', 'white');   
        $path1->setStyle('stroke', $fillStyle); 
        $path1->setAttribute('stroke-width', 0.5); 
        
        $points = array();
        $points[] = new Point(6, 13);
        $points[] = new Point(6, 7);
        $points[] = new Point(8, 7);
        $points[] = new Point(8, 13);
        $points[] = new Point(10, 13);
        $points[] = new Point(10, 7);

        $description = $this->createPathDescription($points, false, $offsetX, $offsetY);
        $path2 = new SVGPath($description);
        $path2->setStyle('fill', self::TRANSPARENT);   
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
    private function createIconColumn($column, $offsetX = 0, $offsetY = 0)
    {
        $icon = new SVGGroup();
        $points = array();
        $points[] = new Point(8, 6);
        $points[] = new Point(12, 10);
        $points[] = new Point(8, 14);
        $points[] = new Point(4, 10);

        $pkColor = self::ICON_COLUMN_PRIMARY_KEY_COLOR;
        $columnColor = self::ICON_COLUMN_COLOR;
        
        if($column->getPrimaryKey())
        {
            $description = $this->createPathDescription($points, true, $offsetX, $offsetY);
            $path = new SVGPath($description);
            $path->setStyle('fill', $pkColor);
            $path->setStyle('stroke', $pkColor);
        }
        else if($column->hasReference())
        {
            $description = $this->createPathDescription($points, true, $offsetX, $offsetY);
            $path = new SVGPath($description);
            $path->setStyle('fill', $columnColor);
            $path->setStyle('stroke', $columnColor);   
        }
        else
        {
            $description = $this->createPathDescription($points, true, $offsetX, $offsetY);
            $path = new SVGPath($description);
            $path->setStyle('fill', self::TRANSPARENT);   
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
    private function createPathDescription($points, $closed = true, $offsetX = 0, $offsetY = 0)
    {
        $coords = array();
        foreach($points as $point)
        {
            $coords[] = ($point->x + $offsetX) . ' ' . ($point->y + $offsetY);
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

    /**
     * Get the value of zoom
     */ 
    public function getZoom()
    {
        return $this->zoom;
    }

    /**
     * Set the value of zoom
     *
     * @return  self
     */ 
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;

        return $this;
    }

    /**
     * Get cache directory
     *
     * @return  string
     */ 
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Set cache directory
     *
     * @param  string  $cacheDir  Cache directory
     *
     * @return  self
     */ 
    public function setCacheDir(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;

        return $this;
    }
}