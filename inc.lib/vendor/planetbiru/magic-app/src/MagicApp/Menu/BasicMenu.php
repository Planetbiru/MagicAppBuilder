<?php

namespace MagicApp\Menu;

use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;

class BasicMenu
{
    /**
     * Entity
     *
     * @var MagicObject
     */
    protected $entity;
    
    /**
     * Specification
     *
     * @var PicoSpecification
     */
    protected $specification;
    
    /**
     * Sortable
     *
     * @var PicoSortable
     */
    protected $sortable;
    /**
     * Constructor
     *
     * @param MagicObject $entity
     * @param PicoSpecification $specification
     * @param PicoSortable $sortable
     */
    public function __construct($entity, $specification = null, $sortable = null)
    {
       $this->entity = $entity;
       $this->specification = $specification;
       $this->sortable = $sortable;    
    }
    
    /**
     * Load data
     *
     * @return PicoPageData
     */
    public function load()
    {
        return $this->entity->findAll($this->entity, $this->specification, null, $this->sortable);     
    }
    
    /**
     * Render menu
     *
     * @param PicoPageData $data
     * @param callable $callbackFunction
     * @return string
     */
    public function render($data, $callbackFunction)
    {
        if(is_callable($callbackFunction))
        {
            return call_user_func($callbackFunction, $data);
        }
        return null;
    }
    
    /**
     * Load and render menu
     *
     * @param callable $callbackFunction
     * @return string
     */
    public function loadAndRender($callbackFunction)
    {
        $data = $this->load();
        return $this->render($data, $callbackFunction);
    }
}