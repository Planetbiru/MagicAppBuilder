<?php

namespace MagicObject\Response\Generated;

use Exception;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\MagicObject;

/**
 * Class PicoSelectOption
 *
 * This class generates HTML `<option>` elements based on data from a MagicObject entity.
 * 
 * @author Kamshory
 * @package MagicObject\Response\Generated
 * @link https://github.com/Planetbiru/Request
 */
class PicoSelectOption
{
    /**
     * Entity object
     *
     * @var MagicObject
     */
    private $object;

    /**
     * Mapping of value and label fields
     *
     * @var array
     */
    private $map = array();

    /**
     * Selected value
     *
     * @var mixed
     */
    private $value;

    /**
     * Additional attributes for options
     *
     * @var array
     */
    private $attributes = array();

    /**
     * Rows for generating options
     *
     * @var array
     */
    private $rows = array();

    /**
     * Sortable options
     *
     * @var PicoSortable
     */
    private $sortable = null;

    /**
     * Constructor
     *
     * Initializes the PicoSelectOption with the provided parameters and fetches active options.
     *
     * @param MagicObject $object Entity to fetch data from
     * @param array $map Mapping for value and label keys
     * @param mixed $value Selected value
     * @param array|null $attributes Additional attributes for the options
     * @param PicoSortable|null $sortable Sortable options for fetching
     */
    public function __construct($object, $map, $value, $attributes = null, $sortable = null)
    {
        $this->object = $object;
        $this->map = $map;
        $this->value = $value;
        if(isset($attributes) && is_array($attributes))
        {
            $this->attributes = $attributes;
        }
        if($sortable != null)
        {
            $this->sortable = $sortable;
        }
        else
        {
            $this->sortable = new PicoSortable('name', PicoSort::ORDER_TYPE_DESC);
        }
        $this->findAllActive();
    }

    /**
     * Create attributes for an option element.
     *
     * @param MagicObject $row Entity representing a row.
     * @param string $attr Attribute name for the option.
     * @param string $value Option value.
     * @return array Attributes for the option element.
     */
    private function createAttributes($row, $attr, $value)
    {
        $optAttributes = array();
        if(is_array($this->attributes))
        {
            foreach($this->attributes as $k=>$v)
            {
                $val = $row->get($v);
                if($val != null)
                {
                    $optAttributes[$k] = $val;
                }
            }
        }
        if($value == $this->value)
        {
            $optAttributes['selected'] = 'selected';
        }
        $optAttributes[$attr] = $value;
        return $optAttributes;
    }

    /**
     * Find all active options from the database
     *
     * @return void
     */
    private function findAllActive()
    {
        try
        {
            $result = $this->object->findByActive(true, null, $this->sortable);
            foreach($result->getResult() as $row)
            {
                $value = $row->get($this->map['value']);
                $label = $row->get($this->map['label']);
                $optAttributes = $this->createAttributes($row, 'value', $value);
                $this->rows[] = array(
                    'attribute'=>$optAttributes,
                    'textNode'=>$label
                );
            }
        }
        catch(Exception $e)
        {
            // do nothing
        }
    }

    /**
     * Convert an array of attributes to an HTML attributes string.
     *
     * @param array $array Attributes to convert.
     * @return string String representation of HTML attributes.
     */
    private function attributeToString($array)
    {
        if($array == null || empty($array))
        {
            return "";
        }
        $optAttributes = array();
        foreach($array as $key=>$value)
        {
            $optAttributes[] = $key."=\"".htmlspecialchars($value)."\"";
        }
        return rtrim(" ".implode(" ", $optAttributes));
    }

    /**
     * Convert the options to HTML `<option>` elements.
     *
     * @return string HTML string of `<option>` elements.
     */
    public function __toString()
    {
        $texts = array();
        foreach($this->rows as $row)
        {
            $optAttributes = $this->attributeToString($row['attribute']);
            $texts[] = "<option".$optAttributes.">".htmlspecialchars($row['textNode'])."</option>";
        }
        return implode("\r\n", $texts);
    }
}