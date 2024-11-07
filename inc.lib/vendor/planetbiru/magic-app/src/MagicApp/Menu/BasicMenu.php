<?php

namespace MagicApp\Menu;

use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;

/**
 * Class BasicMenu
 *
 * Represents a basic menu structure that loads and renders data for the menu using an entity, 
 * a specification (for filtering), and sortable options (for ordering). It provides functionality 
 * to load data from a database or other entity, apply specifications and sorting, and render the data 
 * using a callback function. The `BasicMenu` class can be extended or customized to cater to various 
 * menu structures in an application.
 *
 * @package MagicApp\Menu
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
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
     * Initializes the BasicMenu with the specified entity, specification,
     * and sortable options.
     *
     * @param MagicObject $entity The entity to be used for data loading.
     * @param PicoSpecification|null $specification Optional. The specification
     *        for filtering data.
     * @param PicoSortable|null $sortable Optional. The sortable options for
     *        ordering the data.
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
     * Loads data from the entity based on the provided specification and
     * sorting options.
     *
     * @return PicoPageData The loaded data.
     */
    public function load()
    {
        return $this->entity->findAll($this->entity, $this->specification, null, $this->sortable);     
    }
    
    /**
     * Render menu
     *
     * Renders the menu using the provided callback function on the given data.
     *
     * @param PicoPageData $data The data to be rendered.
     * @param callable $callbackFunction The function to be called for rendering.
     * @return string|null The rendered output, or null if the callback is not callable.
     */
    public function render($data, $callbackFunction)
    {
        if (is_callable($callbackFunction)) {
            return call_user_func($callbackFunction, $data);
        }
        return null;
    }
    
    /**
     * Load and render menu
     *
     * Combines loading and rendering the menu in a single method call.
     *
     * @param callable $callbackFunction The function to be called for rendering.
     * @return string|null The rendered output from the callback function.
     */
    public function loadAndRender($callbackFunction)
    {
        $data = $this->load();
        return $this->render($data, $callbackFunction);
    }
}
