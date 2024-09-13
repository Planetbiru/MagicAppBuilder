<?php

namespace MagicApp;

use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;

class AppFormBuilder
{
    /**
     * Get instance of the class
     *
     * @return self
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Create select option
     *
     * @param MagicObject $entity
     * @param PicoSpecification $specification
     * @param PicoSortable $sortable
     * @param string $primaryKey
     * @param mixed $valueKey
     * @param mixed $currentValue
     * @param string[] $additionalOutput
     * @return AppFormSelect
     */
    public function createSelectOption($entity, $specification, $sortable, $primaryKey, $valueKey, $currentValue = null, $additionalOutput = null)
    {
        $selectOption = new AppFormSelect();
        $pageData = $entity->findAll($specification, null, $sortable, true, null, MagicObject::FIND_OPTION_NO_FETCH_DATA);
        while($row = $pageData->fetch())
        {
            $value = $row->get($primaryKey);
            $label = $row->get($valueKey);
            $selected = isset($currentValue) && ($currentValue == $value || (is_array($currentValue) && in_array($value, $currentValue)));
            $attrs = $this->createAttributes($additionalOutput, $row);
            $selectOption->add($label, $value, $selected, $attrs, $row);
        }
        return $selectOption;
    }
    
    /**
     * Create attributes
     *
     * @param string[] $additionalOutput
     * @param MagicObject $row
     * @return string[]
     */
    private function createAttributes($additionalOutput, $row)
    {
        $attrs = array();
        if(isset($additionalOutput) && is_array($additionalOutput) && isset($row))
        {
            foreach($additionalOutput as $attr)
            {
                $val = $row->get($attr);
                $attrs[$attr] = $val;
            }
        }
        return $attrs;
    }
    
    /**
     * return selected="selected" if $param1 == $param2 
     *
     * @param mixed $param1 Parameter 1
     * @param mixed $param2 Parameter 2
     * @return string
     */
    public static function selected($param1, $param2)
    {
        return $param1 == $param2 ? ' selected="selected"' : '';
    }
    
    /**
     * return checked="checked" if $param1 == $param2 
     *
     * @param mixed $param1 Parameter 1
     * @param mixed $param2 Parameter 2
     * @return string
     */
    public static function checked($param1, $param2)
    {
        return $param1 == $param2 ? ' checked="checked"' : '';
    }
    
    /**
     * Add class compare data
     *
     * @param boolean $div
     * @return string
     */
    public static function classCompareData($div)
    {
        return $div ? 'compare-data data-different':'compare-data';
    }
}