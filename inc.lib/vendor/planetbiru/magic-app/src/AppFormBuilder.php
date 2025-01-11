<?php

namespace MagicApp;

use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;

/**
 * Class AppFormBuilder
 *
 * This class provides methods for building form elements, specifically select options,
 * by fetching data from a MagicObject entity. It handles the creation of select elements
 * with options based on specified criteria, including attributes and selection states.
 */
class AppFormBuilder
{
    /**
     * Get an instance of the AppFormBuilder class.
     *
     * @return self A new instance of AppFormBuilder
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Create select options for a form element.
     *
     * This method retrieves data from the specified entity using the given specification
     * and sorting parameters. It then builds a select option list based on the retrieved
     * data, marking the current value as selected if applicable.
     *
     * @param MagicObject $entity The entity to fetch data from
     * @param PicoSpecification $specification The specification for the query
     * @param PicoSortable $sortable The sorting parameters for the results
     * @param string $primaryKey The key used for the option values
     * @param mixed $valueKey The key used for the option labels
     * @param mixed|null $currentValue The currently selected value (if any)
     * @param string[]|null $additionalOutput Additional attributes to include in each option
     * @return AppFormSelect The created select option element
     */
    public function createSelectOption($entity, $specification, $sortable, $primaryKey, $valueKey, $currentValue = null, $additionalOutput = null)
    {
        $selectOption = new AppFormSelect();
        $pageData = $entity->findAll($specification, null, $sortable, true, null, MagicObject::FIND_OPTION_NO_FETCH_DATA);
        
        while ($row = $pageData->fetch()) {
            $value = $row->get($primaryKey);
            $label = $row->get($valueKey);
            $selected = isset($currentValue) && ($currentValue == $value || (is_array($currentValue) && in_array($value, $currentValue)));
            $attrs = $this->createAttributes($additionalOutput, $row);
            $selectOption->add($label, $value, $selected, $attrs, $row);
        }
        
        return $selectOption;
    }

    /**
     * Create attributes from additional output keys.
     *
     * This method retrieves specified attributes from a given row and constructs
     * an associative array of attributes to be used in form options.
     *
     * @param string[]|null $additionalOutput Array of additional attribute keys
     * @param MagicObject $row The current row from which to extract attribute values
     * @return string[] An associative array of attributes
     */
    private function createAttributes($additionalOutput, $row)
    {
        $attrs = array();
        if (isset($additionalOutput) && is_array($additionalOutput) && isset($row)) {
            foreach ($additionalOutput as $attr) {
                $val = $row->get($attr);
                $attrs[$attr] = $val;
            }
        }
        return $attrs;
    }

    /**
     * Return 'selected="selected"' if the two parameters are equal.
     *
     * This method is a utility to generate the selected attribute for HTML options.
     *
     * @param mixed $param1 The first parameter for comparison
     * @param mixed $param2 The second parameter for comparison
     * @return string The attribute string if parameters match, empty string otherwise
     */
    public static function selected($param1, $param2)
    {
        return $param1 == $param2 ? ' selected="selected"' : '';
    }

    /**
     * Return 'checked="checked"' if the two parameters are equal.
     *
     * This method is a utility to generate the checked attribute for HTML inputs.
     *
     * @param mixed $param1 The first parameter for comparison
     * @param mixed $param2 The second parameter for comparison
     * @return string The attribute string if parameters match, empty string otherwise
     */
    public static function checked($param1, $param2)
    {
        return $param1 == $param2 ? ' checked="checked"' : '';
    }

    /**
     * Add class compare data based on a boolean flag.
     *
     * This method returns a CSS class string indicating whether the data is different
     * or not based on the given boolean parameter.
     *
     * @param bool $div The boolean flag indicating data comparison
     * @return string The class string for comparison
     */
    public static function classCompareData($div)
    {
        return $div ? 'compare-data data-different' : 'compare-data';
    }
}
