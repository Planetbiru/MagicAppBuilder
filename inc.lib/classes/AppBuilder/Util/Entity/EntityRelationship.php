<?php

namespace AppBuilder\Util\Entity;

use MagicObject\Geometry\Point;

/**
 * Class EntityRelationship
 *
 * Represents a relationship between entities in an entity-relationship diagram.
 * This class manages the relationship type, start and end points, and associated diagrams and columns.
 *
 * @package AppBuilder\Util\Entity
 */
class EntityRelationship
{
    const ONE = 'ONE';
    const MANY = 'MANY';
    const ONE_TO_MANY = 'ONE_TO_MANY';
    const MANY_TO_ONE = 'MANY_TO_ONE';

    /**
     * Diagram
     *
     * @var EntityDiagramItem
     */
    private $diagram;

    /**
     * Reference diagram
     *
     * @var EntityDiagramItem
     */
    private $referenceDiagram;

    /**
     * Column
     *
     * @var EntityDiagramColumn
     */
    private $column;

    /**
     * Reference column
     *
     * @var EntityDiagramColumn
     */
    private $referenceColumn;

    /**
     * Relation type
     *
     * @var string
     */
    private $type;

    /**
     * Start of the relationship
     *
     * @var EntityRelationshipEnd
     */
    private $start;

    /**
     * End of the relationship
     *
     * @var EntityRelationshipEnd
     */
    private $end;

    /**
     * Constructor
     *
     * Initializes the relationship between two entities based on provided diagrams and columns.
     *
     * @param EntityDiagramItem $diagram          The diagram of the current entity.
     * @param EntityRelationshipEnd $column        The column of the current entity.
     * @param EntityDiagramItem $referenceDiagram  The diagram of the related entity.
     * @param EntityRelationshipEnd $referenceColumn The column of the related entity.
     */
    public function __construct($diagram, $column, $referenceDiagram, $referenceColumn)
    {
        $this->diagram = $diagram;
        $this->column = $column;
        $this->referenceDiagram = $referenceDiagram;
        $this->referenceColumn = $referenceColumn;

        // Relative position
        $pr1 = new Point($this->column->getX(), $this->column->getY());
        $pr2 = new Point($this->referenceColumn->getX(), $this->referenceColumn->getY());

        // Absolute position
        $pa1 = new Point($diagram->getX() + $this->column->getX(), $diagram->getY() + $this->column->getY());
        $pa2 = new Point($referenceDiagram->getX() + $this->referenceColumn->getX(), $referenceDiagram->getY() + $this->referenceColumn->getY());

        // Determine relationship type and ends
        if ($diagram->getTableName() === $this->referenceDiagram->getTableName()) {
            $this->type = self::ONE_TO_MANY;
            $this->start = new EntityRelationshipEnd(self::MANY, $pr2, $pa2, $this->referenceColumn);
            $this->end = new EntityRelationshipEnd(self::ONE, $pr1, $pa1, $this->column);
        } else {
            if ($diagram->getX() <= $referenceDiagram->getX()) {
                $this->type = self::ONE_TO_MANY;
                $this->start = new EntityRelationshipEnd(self::MANY, $pr2, $pa2, $this->referenceColumn);
                $this->end = new EntityRelationshipEnd(self::ONE, $pr1, $pa1, $this->column);
            } else {
                $this->type = self::ONE_TO_MANY;
                $this->start = new EntityRelationshipEnd(self::ONE, $pr1, $pa1, $this->column);
                $this->end = new EntityRelationshipEnd(self::MANY, $pr2, $pa2, $this->referenceColumn);
            }
        }
    }

    /**
     * Get relation type
     *
     * @return string The type of the relationship (e.g., ONE_TO_MANY).
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set relation type
     *
     * @param string $type Relation type (e.g., ONE_TO_MANY).
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get start of the relationship
     *
     * @return EntityRelationshipEnd The starting point of the relationship.
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set start of the relationship
     *
     * @param EntityRelationshipEnd $start Start point of the relationship.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get end of the relationship
     *
     * @return EntityRelationshipEnd The ending point of the relationship.
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set end of the relationship
     *
     * @param EntityRelationshipEnd $end End point of the relationship.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get reference diagram
     *
     * @return EntityDiagramItem The diagram of the related entity.
     */
    public function getReferenceDiagram()
    {
        return $this->referenceDiagram;
    }

    /**
     * Set reference diagram
     *
     * @param EntityDiagramItem $referenceDiagram Reference diagram.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setReferenceDiagram($referenceDiagram)
    {
        $this->referenceDiagram = $referenceDiagram;

        return $this;
    }

    /**
     * Get diagram
     *
     * @return EntityDiagramItem The diagram of the current entity.
     */
    public function getDiagram()
    {
        return $this->diagram;
    }

    /**
     * Set diagram
     *
     * @param EntityDiagramItem $diagram Diagram of the current entity.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setDiagram($diagram)
    {
        $this->diagram = $diagram;

        return $this;
    }

    /**
     * Get column
     *
     * @return EntityDiagramColumn The column of the current entity.
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Set column
     *
     * @param EntityDiagramColumn $column Column of the current entity.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Get reference column
     *
     * @return EntityDiagramColumn The column of the related entity.
     */
    public function getReferenceColumn()
    {
        return $this->referenceColumn;
    }

    /**
     * Set reference column
     *
     * @param EntityDiagramColumn $referenceColumn Reference column of the related entity.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setReferenceColumn($referenceColumn)
    {
        $this->referenceColumn = $referenceColumn;

        return $this;
    }
}
