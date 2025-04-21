<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoHtmlElementAttribute
 *
 * Represents a single HTML attribute (e.g., `id="header"` or `class="btn"`),
 * usually used in building or parsing HTML elements programmatically.
 * This class can be instantiated using either an associative array
 * or another instance of itself.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoHtmlElementAttribute extends PicoObjectToString
{
    /**
     * The name of the HTML attribute (e.g., "id", "class", "href").
     *
     * @var string
     */
    protected $name;
    
    /**
     * The value of the HTML attribute (e.g., "header", "btn-primary", "#").
     *
     * @var string
     */
    protected $value;
    
    /**
     * PicoHtmlElementAttribute constructor.
     *
     * Initializes the attribute either from an associative array with 'name' and 'value' keys
     * or from another PicoHtmlElementAttribute instance.
     *
     * @param self|array $attribute Attribute data to populate the object with.
     */
    public function __construct($attribute)
    {
        if (isset($attribute)) {
            if (is_array($attribute)) {
                if (isset($attribute['name'])) {
                    $this->name = $attribute['name'];
                }
                if (isset($attribute['value'])) {
                    $this->value = $attribute['value'];
                }
            }
            if ($attribute instanceof self) {
                $this->name = $attribute->name;
                $this->value = $attribute->value;
            }
        }
    }
}
