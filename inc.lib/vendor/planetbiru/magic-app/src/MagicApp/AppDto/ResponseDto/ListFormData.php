<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class ListFormData
 *
 * Represents a form structure designed to manage and organize filtering and data controls 
 * within a list UI. This class serves as a container for filter controls (e.g., search fields, 
 * date pickers, checkboxes) and data controls (e.g., display settings, sorting, pagination). 
 * These controls enable users to interact with and manipulate the data displayed in the list.
 * 
 * The `ListFormData` class allows for easy configuration of various filters and data-related
 * controls, such as adding, removing, or modifying filters and data controls dynamically.
 * It also provides methods to manage the controls collectively, ensuring that the UI is 
 * adaptable and user-friendly for operations such as sorting, searching, and displaying data.
 * 
 * **Key Features**:
 * - Manage a list of filter controls, each representing a specific filter UI element.
 * - Manage a list of data controls, each representing a specific data-related UI element.
 * - Dynamic addition of filter and data controls.
 * - Chaining of method calls for adding controls and setting properties.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ListFormData extends ToString
{
    /**
     * An array of filter control elements used for filtering data in the list.
     * Each element is an instance of `ListFilterControl`, which holds metadata
     * about the filter control (e.g., field, label, element type).
     *
     * @var ListFilterControl[]
     */
    public $filterControl;

    /**
     * An array of data control elements used for managing and displaying
     * the actual data in the list. Each element is an instance of `DataControl`,
     * which holds information about the data field and its representation.
     *
     * @var DataControl[]
     */
    public $dataControl;

    /**
     * Constructor for the ListFormData class.
     * 
     * Initializes the filter control and data control arrays as empty.
     * This default constructor is useful when you want to create an object
     * without initializing the controls right away.
     */
    public function __construct()
    {
        // Initialize filterControl and dataControl as empty arrays
        $this->filterControl = [];
        $this->dataControl = [];
    }
    
    /**
     * Adds a filter control to the list of filter controls.
     *
     * @param ListFilterControl $filterControl The filter control to add.
     * @return self Returns the current instance for chaining.
     */
    public function addFilterControl($filterControl)
    {
        $this->filterControl[] = $filterControl;
        return $this;
    }

    /**
     * Adds a data control to the list of data controls.
     *
     * @param DataControl $dataControl The data control to add.
     * @return self Returns the current instance for chaining.
     */
    public function addDataControl($dataControl)
    {
        $this->dataControl[] = $dataControl;
        return $this;
    }

    // Getter and Setter Methods

    /**
     * Gets the array of filter control elements.
     *
     * @return ListFilterControl[] The array of filter control elements.
     */
    public function getFilterControl()
    {
        return $this->filterControl;
    }

    /**
     * Sets the array of filter control elements.
     *
     * @param ListFilterControl[] $filterControl The filter controls to set.
     * @return self Returns the current instance for chaining.
     */
    public function setFilterControl($filterControl)
    {
        $this->filterControl = $filterControl;
        return $this;
    }

    /**
     * Gets the array of data control elements.
     *
     * @return DataControl[] The array of data control elements.
     */
    public function getDataControl()
    {
        return $this->dataControl;
    }

    /**
     * Sets the array of data control elements.
     *
     * @param DataControl[] $dataControl The data controls to set.
     * @return self Returns the current instance for chaining.
     */
    public function setDataControl($dataControl)
    {
        $this->dataControl = $dataControl;
        return $this;
    }
}
