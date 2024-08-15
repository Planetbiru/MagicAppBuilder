<?php

namespace AppBuilder\Generator;

use MagicObject\Geometry\Point;

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
     * Reference olumn
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
     * Start
     *
     * @var EntityRelationshipEnd
     */
    private $start;
    
    /**
     * End
     *
     * @var EntityRelationshipEnd
     */
    private $end;
    
    /**
     * Constructor
     *
     * @param EntityDiagramItem $diagram
     * @param EntityRelationshipEnd $column
     * @param EntityDiagramItem $referenceDiagram
     * @param EntityRelationshipEnd $referenceColumn
     */
    public function __construct($diagram, $column, $referenceDiagram, $referenceColumn)
    {
        $this->diagram = $diagram;
        $this->column = $column;
        $this->referenceDiagram = $referenceDiagram;
        $this->referenceColumn = $referenceColumn;
        
        $pr1 = new Point($this->column->getX(), $this->column->getY());
        $pr2 = new Point($this->referenceColumn->getX(), $this->referenceColumn->getY());
        
        $pa1 = new Point($diagram->getX() + $this->column->getX(), $diagram->getY() + $this->column->getY());
        $pa2 = new Point($referenceDiagram->getX() + $this->referenceColumn->getX(), $referenceDiagram->getY() + $this->referenceColumn->getY());
        
        
        if($diagram->getTableName() == $this->referenceDiagram->getTableName())
        {
            $this->type = self::ONE_TO_MANY;
            $this->start = new EntityRelationshipEnd(self::MANY, $pr2, $pa2);
            $this->end = new EntityRelationshipEnd(self::ONE, $pr1, $pa1);
        }
        else
        {
            if($diagram->getX() < $referenceDiagram->getX())
            {
                $this->type = self::ONE_TO_MANY;
                $this->start = new EntityRelationshipEnd(self::MANY, $pr2, $pa2);
                $this->end = new EntityRelationshipEnd(self::ONE, $pr1, $pa1);
            }
            else
            {
                $this->type = self::ONE_TO_MANY;
                $this->start = new EntityRelationshipEnd(self::ONE, $pr1, $pa1);
                $this->end = new EntityRelationshipEnd(self::MANY, $pr2, $pa2);
            }
        }
    }
    


    /**
     * Get relation type
     *
     * @return  string
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set relation type
     *
     * @param  string  $type  Relation type
     *
     * @return  self
     */ 
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get start
     *
     * @return  EntityRelationshipEnd
     */ 
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set start
     *
     * @param  EntityRelationshipEnd  $start  Start
     *
     * @return  self
     */ 
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get end
     *
     * @return  EntityRelationshipEnd
     */ 
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set end
     *
     * @param  EntityRelationshipEnd  $end  End
     *
     * @return  self
     */ 
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get reference diagram
     *
     * @return  EntityDiagramItem
     */ 
    public function getReferenceDiagram()
    {
        return $this->referenceDiagram;
    }

    /**
     * Set reference diagram
     *
     * @param  EntityDiagramItem  $referenceDiagram  Reference diagram
     *
     * @return  self
     */ 
    public function setReferenceDiagram($referenceDiagram)
    {
        $this->referenceDiagram = $referenceDiagram;

        return $this;
    }

    /**
     * Get diagram
     *
     * @return  EntityDiagramItem
     */ 
    public function getDiagram()
    {
        return $this->diagram;
    }

    /**
     * Set diagram
     *
     * @param  EntityDiagramItem  $diagram  Diagram
     *
     * @return  self
     */ 
    public function setDiagram($diagram)
    {
        $this->diagram = $diagram;

        return $this;
    }
}