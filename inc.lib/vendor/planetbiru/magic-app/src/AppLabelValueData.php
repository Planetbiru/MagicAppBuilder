<?php

namespace MagicApp;

use stdClass;

/**
 * Class AppLabelValueData
 *
 * This class represents an option element for a form, encapsulating the logic to generate 
 * its HTML representation and convert it to JSON format. It extends the `AppFormOption` class 
 * and allows customization of the option's label, value, selected state, and additional attributes.
 *
 * It provides methods to generate HTML for the option, as well as to convert the option into a JSON 
 * object that includes the label, value, selection state, and attributes.
 *
 * Methods:
 * - toJson(): Generates a JSON object representing the option, including label, value, selected state, 
 *   and attributes.
 *
 * @package MagicApp
 */
class AppLabelValueData extends AppFormOption
{
    /**
     * Get the HTML representation of the option as a string.
     *
     * This method generates the HTML markup for the option element. It includes
     * the value, display text, selection state, and any additional attributes.
     *
     * @return string The HTML option element
     */
    public function toJson()
    {
        $attributes = $this->getAttributes();
        $option = new stdClass;
        $values = $this->getValues();
        if (isset($this->format) && isset($this->params)) {

            $option->label = vsprintf($this->format, $values);
        } else {
            $option->label = htmlspecialchars($this->label);
        }

        $option->value = $this->value;
        $option->selected = $this->selected;
        $option->attributes = $attributes;
        return $option;
    }
}
