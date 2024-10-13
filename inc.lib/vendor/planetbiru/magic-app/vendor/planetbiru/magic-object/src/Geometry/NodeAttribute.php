<?php

namespace MagicObject\Geometry;

/**
 * Class representing node attributes.
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
     * @param string[] $values An array of attribute values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Convert the node attributes to a string representation.
     *
     * @return string
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
