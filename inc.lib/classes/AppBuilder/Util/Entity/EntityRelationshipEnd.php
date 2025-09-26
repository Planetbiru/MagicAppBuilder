<?php

namespace AppBuilder\Util\Entity;

use MagicMath\Geometry\Point;

/**
 * Class representing an entity relationship endpoint.
 */
class EntityRelationshipEnd
{
    /**
     * The type of relationship endpoint.
     *
     * @var string
     */
    private $type;
    
    /**
     * The position relative to a reference point.
     *
     * @var Point
     */
    private $relativePosition;
    
    /**
     * The absolute position in the coordinate system.
     *
     * @var Point
     */
    private $absolutePosition;
    
    /**
     * The associated column in the entity diagram.
     *
     * @var EntityDiagramColumn
     */
    private $column;
    
    /**
     * Constructor.
     *
     * @param string               $type              The type of relationship endpoint.
     * @param Point                $relativePosition  The position relative to a reference point.
     * @param Point                $absolutePosition  The absolute position in the coordinate system.
     * @param EntityDiagramColumn  $column            The associated column in the entity diagram.
     */
    public function __construct($type, $relativePosition, $absolutePosition, $column)
    {
        $this->type = $type;
        $this->relativePosition = $relativePosition;
        $this->absolutePosition = $absolutePosition;
        $this->column = $column;
    }

    /**
     * Get the type of relationship endpoint.
     *
     * @return string
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type of relationship endpoint.
     *
     * @param string $type The type of relationship endpoint.
     *
     * @return self
     */ 
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the relative position.
     *
     * @return Point
     */ 
    public function getRelativePosition()
    {
        return $this->relativePosition;
    }

    /**
     * Set the relative position.
     *
     * @param Point $relativePosition The position relative to a reference point.
     *
     * @return self
     */ 
    public function setRelativePosition(Point $relativePosition)
    {
        $this->relativePosition = $relativePosition;
        return $this;
    }

    /**
     * Get the absolute position.
     *
     * @return Point
     */ 
    public function getAbsolutePosition()
    {
        return $this->absolutePosition;
    }

    /**
     * Set the absolute position.
     *
     * @param Point $absolutePosition The absolute position in the coordinate system.
     *
     * @return self
     */ 
    public function setAbsolutePosition(Point $absolutePosition)
    {
        $this->absolutePosition = $absolutePosition;
        return $this;
    }

    /**
     * Get the associated column.
     *
     * @return EntityDiagramColumn
     */ 
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Set the associated column.
     *
     * @param EntityDiagramColumn $column The associated column in the entity diagram.
     *
     * @return self
     */ 
    public function setColumn($column)
    {
        $this->column = $column;
        return $this;
    }
}
