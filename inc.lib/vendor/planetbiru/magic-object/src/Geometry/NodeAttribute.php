<?php

namespace MagicObject\Geometry;

class NodeAttribute
{
    /**
     * Values
     *
     * @var string[]
     */
    private $values = array();

    /**
     * Constructor
     *
     * @param string[] $values
     * @return self
     */
    public function __costruct($values)
    {
        $this->values = $values;
    }

    public function __toString()
    {
        $attributes = array();
        if(isset($this->values) && is_array($this->values))
        {
            foreach($this->values as $key=>$value)
            {
                $attributes[] = $key . "=\"" . htmlspecialchars($value) . "\"";
            }
        }
        return implode(" ", $attributes);
    }
}