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
     * @var int
     */
    private $entityWidth = 1;

    /**
     * Margin between 2 entities in X axis
     *
     * @var int
     */
    private $entityMarginX = 40;
    
    /**
     * Margin between 2 entities in Y axis
     *
     * @var int
     */
    private $entityMarginY = 20;
    
    /**
     * Width
     *
     * @var int
     */
    private $width = 1;
    
    /**
     * Height
     *
     * @var int
     */
    private $height = 1;
    
    /**
     * Margin X
     *
     * @var int
     */
    private $marginX = 20;
    
    /**
     * Margin Y
     *
     * @var int
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
     * @var int
     */
    private $headerHeight = 20;
    
    /**
     * Column height
     *
     * @var int
     */
    private $columnHeight = 20;
    
    /**
     * Maximum level
     *
     * @var int
     */
    private $maximumLevel;
    
    /**
     * Maximum columns
     *
     * @var int
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
     * @param array        $appConfig     Application configuration settings.
     * @param int          $entityWidth   Width of an entity.
     * @param int|null     $entityMarginX Optional horizontal margin between entities.
     * @param int|null     $entityMarginY Optional vertical margin between entities.
     * @param MagicObject[]|null $entities Optional array of MagicObject instances.
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
     * Updates the entity diagram with table and column information.
     *
     * This method ensures that the entity diagram includes the specified table and its columns,
     * updates primary keys, and processes join columns to establish relationships.
     *
     * @param ReflectionClass $reflectionClass Reflection class of the entity.
     * @param string $entityName Name of the entity.
     * @param string $namespace Namespace of the entity.
     * @param PicoTableInfo $info Table information object.
     * @param integer $level Recursion depth level for processing references.
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
     * @param ReflectionClass $reflectionClass  Reflection class of the entity.
     * @param string         $tableName         Name of the table.
     * @param string         $columnName        Name of the column.
     * @param string         $referenceColumnName Name of the reference column.
     * @param string         $propertyType      Type of the property.
     * @param int            $level             Current recursion level.
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
     * Includes the specified class file if it exists and passes error checking.
     *
     * @param string $realClassName Fully qualified class name to be included.
     * @return bool Returns true if the file was included successfully, otherwise false.
     */
    private function includeFile($realClassName)
    {
        $application = $this->appConfig->getApplication();
        $classToInclude = $realClassName;
        $baseDirectory = $application->getBaseEntityDirectory();
        $path = $baseDirectory."/".str_replace("\\", "/", $classToInclude).".php";
        
        if(file_exists($path))
        {
            $returnVar = ErrorChecker::errorCheck($this->databaseBuilder, $path);
            if($returnVar == 0)
            {
                require_once $path;
                return true;
            }
        }
        return false;
    }

    /**
     * Get the width value.
     *
     * @return int The current width.
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set the width value.
     *
     * @param int $width The new width to set.
     * @return self Returns the instance for method chaining.
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
     * @return self Returns the instance for method chaining.
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
     * Get the diagram associated with a given table name.
     *
     * @param string $tableName The name of the table.
     * @return EntityDiagramItem|null The diagram item if found, otherwise null.
     */
    private function getDiagramByTableName($tableName)
    {
        return isset($this->entitieDiagramItem[$tableName]) ? $this->entitieDiagramItem[$tableName] : null;
    }
    
    /**
     * Get the column associated with a given table column name.
     *
     * @param EntityDiagramItem $diagram The diagram containing the column.
     * @param string $columnName The name of the column.
     * @return EntityDiagramColumn|null The diagram column if found, otherwise null.
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
     * Generate an image map representation of the entity diagram.
     *
     * This method arranges the diagram, prepares entity relationships, 
     * and constructs an HTML-compatible image map for visualization.
     *
     * @return string The generated image map in HTML format.
     */
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
     * Generate a markdown representation of the entity diagram.
     *
     * @return string The markdown-formatted entity diagram.
     */
    public function getMarkdown()
    {
        $this->arrangeDiagram();
        $this->prepareEntityRelationship();
        
        $result = [];
        $result[] = '# Diagram Explanation';
        $result[] = '';
        
        foreach($this->entitieDiagramItem as $diagram)
        {
            $result[] = '## Table `'.$diagram->getTableName().'`';
            $result[] = '';
            $result[] = '### CREATE TABLE Statement';
            $result[] = '';
            $result[] = '```sql';
            $result[] = $this->getSQL($diagram);
            $result[] = '```';
            $result[] = '';
            $result[] = '### Table Columns Description';
            $result[] = '';

            $result[] = '| '.sprintf('%-40s', 'Field').' | '.sprintf('%-15s', 'Type').' | '.sprintf('%-6s', 'Length').' | '.sprintf('%-8s', 'Nullable').' | '.sprintf('%-5s', 'PK').' | '.sprintf('%-14s', 'Extra').' |';
            $result[] = '| '.str_repeat('-', 40).' | '.str_repeat('-', 15).' | '.str_repeat('-', 6).' | '.str_repeat('-', 8).' | '.str_repeat('-', 5).' | '.str_repeat('-', 14).' |';

            $columns = $diagram->getColumns();
            foreach($columns as $field=>$column)
            {
                $result[] = '| '.sprintf('%-40s', $field).' | '.sprintf('%-15s', $column->getDataType()).' | '.sprintf('%-6s', $column->getDataLength()).' | '.sprintf('%-8s', $column->getNullable()).' | '.sprintf('%-5s', $column->getPrimaryKey() ? 'true' : 'false').' | '.sprintf('%-14s', $column->getExtra()).' |';
            }

            $result[] = '';
        }
        
        return implode("\r\n", $result);
    }

    /**
     * Generate SQL CREATE TABLE statements for each entity.
     * 
     * @param EntityDiagramItem $diagram Diagram
     *
     * @return string SQL representation of the entity diagram.
     */
    public function getSQL($diagram) // NOSONAR
    {
        
        $tableName = $diagram->getTableName();
        $columns = $diagram->getColumns();
        $columnDefs = [];
        $primaryKeys = [];

        foreach ($columns as $field => $column) {
            $definition = "`$field` " . strtoupper($column->getDataType());

            $length = $column->getDataLength();
            if ($length && strpos($column->getDataType(), '(') === false) {
                $definition .= "($length)";
            }

            $definition .= $column->getNullable() === 'NO' ? ' NOT NULL' : ' NULL';

            if ($column->getExtra()) {
                $definition .= ' ' . strtoupper($column->getExtra());
            }

            $columnDefs[] = $definition;

            if ($column->getPrimaryKey()) {
                $primaryKeys[] = "`$field`";
            }
        }

        if (!empty($primaryKeys)) {
            $columnDefs[] = 'PRIMARY KEY (' . implode(', ', $primaryKeys) . ')';
        }

        $sql = "CREATE TABLE `$tableName` (\n    " . implode(",\n    ", $columnDefs) . "\n);";
        $sqlStatements[] = $sql;

        return implode("\n\n", $sqlStatements);
    }

    
    /**
     * Generate an SVG representation of the Entity-Relationship Diagram (ERD).
     *
     * This method arranges the diagram, prepares entity relationships, and 
     * constructs an SVG image representing the ERD, including entities and 
     * their relationships.
     *
     * @return string The generated SVG as a string.
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
        
        return (string) $image;
    }
    
    /**
     * Determine if the diagram needs to wrap into multiple rows.
     *
     * This method checks if the number of diagrams exceeds the maximum allowed
     * in a single row, indicating that wrapping is required.
     *
     * @param int $countDiagram The total number of diagrams.
     * @return bool True if the diagram needs to wrap, false otherwise.
     */
    private function needWrapDiagram($countDiagram)
    {
        return $this->maximumColumn > 0 && $countDiagram > $this->maximumColumn;
    }
    
    /**
     * Arrange the diagram into a matrix layout.
     *
     * This method organizes entity diagrams into rows and columns based on 
     * the maximum allowed columns. It calculates the necessary width and height, 
     * positions each entity within the grid, and updates the overall diagram dimensions.
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
     * Create an SVG representation of an entity diagram item.
     *
     * This method generates an SVG element representing an entity, including:
     * - A main frame
     * - A background
     * - A header
     * - Column representations
     * - An icon table
     *
     * @param EntityDiagramItem $diagram The entity diagram item to be visualized.
     * @return SVGNode The generated SVG representation of the entity.
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
     * Create a horizontal separator line for an entity diagram.
     *
     * This method generates an SVG line that acts as a separator between different 
     * sections of an entity diagram.
     *
     * @param EntityDiagramItem $diagram The entity diagram item for which the separator is created.
     * @return SVGLine The generated SVG separator line.
     */
    private function createSeparatorLine($diagram)
    {
        $separator = new SVGLine(0, 0, $diagram->getWidth(), 0);
        $separator->setStyle('stroke', self::STROKE_DIAGRAM);
        return $separator;
    }
    
    /**
     * Create an SVG representation of a column within an entity diagram.
     *
     * This method generates an SVG element representing a column, including:
     * - A background rectangle
     * - Column text
     * - A separator line
     * - An optional reference attribute (if the column has a foreign key relationship)
     * - An icon representing the column type
     *
     * @param EntityDiagramItem $diagram The entity diagram item to which the column belongs.
     * @param EntityDiagramColumn $column The column to be visualized.
     * @param integer $x The X coordinate of the column within the entity.
     * @param integer $y The Y coordinate of the column within the entity.
     * @return SVGNode The generated SVG representation of the column.
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
     * Create an SVG icon representing a database table.
     *
     * This method generates a table icon using SVG paths to visually represent 
     * a tabular structure.
     *
     * @param integer $offsetX Optional X offset for positioning the icon.
     * @param integer $offsetY Optional Y offset for positioning the icon.
     * @return SVGGroup The generated SVG group representing the table icon.
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
     * Create an SVG icon representing a column in an entity diagram.
     *
     * This method generates a small visual representation of a column, which can:
     * - Indicate a **primary key** (colored using `ICON_COLUMN_PRIMARY_KEY_COLOR`)
     * - Indicate a **foreign key/reference** (colored using `ICON_COLUMN_COLOR`)
     * - Be a **regular column** (transparent fill with `ICON_COLUMN_COLOR` stroke)
     *
     * @param EntityDiagramColumn $column The column for which the icon is created.
     * @param integer $offsetX Optional horizontal offset for positioning.
     * @param integer $offsetY Optional vertical offset for positioning.
     * @return SVGGroup The generated SVG group containing the column icon.
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
     * Generate an SVG path description string from a set of points.
     *
     * This method constructs an SVG-compatible path description using `M` (move to)
     * and `L` (line to) commands. If `$closed` is `true`, the path is closed using `Z`.
     *
     * @param Point[] $points An array of Point objects defining the path.
     * @param boolean $closed Whether the path should be closed (default: true).
     * @param integer $offsetX Optional horizontal offset applied to all points.
     * @param integer $offsetY Optional vertical offset applied to all points.
     * @return string The SVG path description string.
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
     * Calculate the total width of the entity diagram.
     *
     * This method iterates over all entity diagram items and determines 
     * the maximum X-coordinate to compute the total width of the diagram.
     *
     * @return integer The calculated diagram width in pixels.
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
     * Calculate the total height of the entity diagram.
     *
     * This method iterates over all entity diagram items and determines 
     * the maximum Y-coordinate to compute the total height of the diagram.
     *
     * @return integer The calculated diagram height in pixels.
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
    
    /**
     * Convert the entity diagram to a string representation.
     *
     * This method returns the diagram as an SVG string using `drawERD()`.
     *
     * @return string The SVG representation of the entity diagram.
     */
    public function __toString()
    {
        return $this->drawERD();
    }

    /**
     * Get the horizontal margin.
     *
     * @return integer The X margin value.
     */ 
    public function getMarginX()
    {
        return $this->marginX;
    }

    /**
     * Set the horizontal margin.
     *
     * @param integer $marginX The X margin value.
     * @return self Returns the instance for method chaining.
     */ 
    public function setMarginX($marginX)
    {
        $this->marginX = $marginX;

        return $this;
    }

    /**
     * Get the vertical margin.
     *
     * @return integer The Y margin value.
     */ 
    public function getMarginY()
    {
        return $this->marginY;
    }

    /**
     * Set the vertical margin.
     *
     * @param integer $marginY The Y margin value.
     * @return self Returns the instance for method chaining.
     */ 
    public function setMarginY($marginY)
    {
        $this->marginY = $marginY;

        return $this;
    }

    /**
     * Get the height of the entity header.
     *
     * @return integer The height of the header section in pixels.
     */ 
    public function getHeaderHeight()
    {
        return $this->headerHeight;
    }

    /**
     * Set the height of the entity header.
     *
     * @param integer $headerHeight The header height in pixels.
     * @return self Returns the instance for method chaining.
     */ 
    public function setHeaderHeight($headerHeight)
    {
        $this->headerHeight = $headerHeight;

        return $this;
    }

    /**
     * Set the height of each column in the entity diagram.
     *
     * @param integer $columnHeight The height of the columns in pixels.
     * @return self Returns the instance for method chaining.
     */ 
    public function setColumnHeight($columnHeight)
    {
        $this->columnHeight = $columnHeight;

        return $this;
    }

    /**
     * Get the maximum depth level of the diagram.
     *
     * @return integer The maximum hierarchy level.
     */ 
    public function getMaximumLevel()
    {
        return $this->maximumLevel;
    }

    /**
     * Set the maximum depth level of the diagram.
     *
     * @param integer $maximumLevel The maximum hierarchy level.
     * @return self Returns the instance for method chaining.
     */ 
    public function setMaximumLevel($maximumLevel)
    {
        $this->maximumLevel = $maximumLevel;

        return $this;
    }

    /**
     * Get the maximum number of columns allowed in the diagram.
     *
     * @return integer The maximum column count.
     */ 
    public function getMaximumColumn()
    {
        return $this->maximumColumn;
    }

    /**
     * Set the maximum number of columns in the diagram.
     *
     * @param integer $maximumColumn The maximum column count.
     * @return self Returns the instance for method chaining.
     */ 
    public function setMaximumColumn($maximumColumn)
    {
        $this->maximumColumn = $maximumColumn;

        return $this;
    }

    /**
     * Get the zoom level of the entity diagram.
     *
     * @return float The zoom factor applied to the diagram.
     */ 
    public function getZoom()
    {
        return $this->zoom;
    }

    /**
     * Set the zoom level of the entity diagram.
     *
     * @param float $zoom The zoom factor to apply.
     * @return self Returns the instance for method chaining.
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;

        return $this;
    }

    /**
     * Get the cache directory path.
     *
     * @return string The directory path where cached diagrams are stored.
     */ 
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Set the cache directory path.
     *
     * @param string $cacheDir The directory path for storing cached diagrams.
     * @return self Returns the instance for method chaining.
     */ 
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;

        return $this;
    }
}