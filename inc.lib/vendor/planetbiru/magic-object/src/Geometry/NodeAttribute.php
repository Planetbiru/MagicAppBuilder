<?php

namespace MagicObject\Geometry;

/**
 * Class representing node attributes.
 *
 * This class stores a collection of attribute values associated with a node.
 * It provides methods for initialization and converting the attributes to a string representation,
 * suitable for use in contexts such as HTML attributes.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class NodeAttribute
{
    /**
     * Values of the node attributes.
     *
     * @var string[]
     */
    private $values = [];

    /**
     * Constructor to initialize the NodeAttribute with values.
     *
     * @param string[] $values An array of attribute values.
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Convert the node attributes to a string representation.
     *
     * This method returns the attributes in a format suitable for inclusion in HTML tags,
     * where each attribute is represented as key="value".
     *
     * @return string A string representation of the node attributes.
     */
    public function __toString()
    {
        $attributes = [];
        if (isset($this->values) && is_array($this->values)) {
            foreach ($this->values as $key => $value) {
                $attributes[] = $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        return implode(' ', $attributes);
    }
}
