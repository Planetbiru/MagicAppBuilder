<?php

namespace AppBuilder\Util\Entity;

use MagicObject\Geometry\Point;

class EntityRelationshipEnd
{
    /**
     * Type
     *
     * @var string
     */
    private $type;
    
    /**
     * Relative position
     *
     * @var Point
     */
    private $relativePosition;
    
    /**
     * Absolute position
     *
     * @var Point
     */
    private $absolutePosition;
    
    /**
     * Column
     *
     * @var EntityDiagramColumn
     */
    private $column;
    
    public function __construct($type, $relativePosition, $absolutePosition, $column)
    {
        $this->type = $type;
        $this->relativePosition = $relativePosition;
        $this->absolutePosition = $absolutePosition;
        $this->column = $column;
    }

    /**
     * Get type
     *
     * @return  string
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param  string  $type  Type
     *
     * @return  self
     */ 
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get relative position
     *
     * @return  Point
     */ 
    public function getRelativePosition()
    {
        return $this->relativePosition;
    }

    /**
     * Set relative position
     *
     * @param  Point  $relativePosition  Relative position
     *
     * @return  self
     */ 
    public function setRelativePosition(Point $relativePosition)
    {
        $this->relativePosition = $relativePosition;

        return $this;
    }

    /**
     * Get absolute position
     *
     * @return  Point
     */ 
    public function getAbsolutePosition()
    {
        return $this->absolutePosition;
    }

    /**
     * Set absolute position
     *
     * @param  Point  $absolutePosition  Absolute position
     *
     * @return  self
     */ 
    public function setAbsolutePosition(Point $absolutePosition)
    {
        $this->absolutePosition = $absolutePosition;

        return $this;
    }

    /**
     * Get column
     *
     * @return  EntityDiagramColumn
     */ 
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Set column
     *
     * @param  EntityDiagramColumn  $column  Column
     *
     * @return  self
     */ 
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }
}