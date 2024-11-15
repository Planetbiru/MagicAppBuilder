<?php

namespace MagicObject\PicoDataLabel;

use MagicObject\DataLabel\PicoDataLabel;

/**
 * Class representing a collection of data labels.
 *
 * This class allows for the storage and management of multiple data labels.
 *
 * @author Kamshory
 * @package MagicObject\DataLabel
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDataLabels
{
    /**
     * Collection of data labels.
     *
     * @var PicoDataLabel[]
     */
    private $data = array();

    /**
     * Appends a new data label to the collection.
     *
     * @param PicoDataLabel $data The data label to be added.
     * @return self Returns the current instance for method chaining.
     */
    public function append($data)
    {
        $this->data[] = $data;
        return $this;
    }

    /**
     * Generates output based on the collected data labels.
     *
     * This method processes each data label in the collection.
     */
    public function generate()
    {
        foreach ($this->data as $data) //NOSONAR
        {
            // Implementation for processing each data label goes here
        }
    }
}
