<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoHtmlElement
 *
 * Represents a basic HTML element, including its tag name (e.g., `div`, `a`),
 * text content, attributes, and optional hyperlink.
 * This can be useful for generating or parsing structured HTML data programmatically.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoHtmlElement extends PicoObjectToString
{
    /**
     * The tag name of the HTML element (e.g., "div", "a", "span").
     *
     * @var string
     */
    protected $tag;
    
    /**
     * The inner text node of the element (text content between the tags).
     *
     * @var string
     */
    protected $textNode;
    
    /**
     * An array of attributes associated with this HTML element.
     *
     * @var PicoHtmlElementAttribute[]
     */
    protected $attributes;
    
    /**
     * An optional link value, used when the element represents a hyperlink.
     *
     * @var string
     */
    protected $link;
    
    /**
     * PicoHtmlElement constructor.
     *
     * Accepts an associative array or another PicoHtmlElement instance.
     * The array can include:
     * - 'tag' => string
     * - 'textNode' => string
     * - 'attributes' => array of ['name' => string, 'value' => string]
     * - 'link' => string
     *
     * @param self|array $element
     */
    public function __construct($element)
    {
        if (isset($element)) {
            if (is_array($element)) {
                if (isset($element['tag'])) {
                    $this->tag = $element['tag'];
                }
                if (isset($element['textNode'])) {
                    $this->textNode = $element['textNode'];
                }
                if (isset($element['attributes']) && is_array($element['attributes'])) {
                    $this->attributes = [];
                    foreach ($element['attributes'] as $attribute) {
                        $this->attributes[] = new PicoHtmlElementAttribute($attribute);
                    }
                }
                if (isset($element['link'])) {
                    $this->link = $element['link'];
                }
            } elseif ($element instanceof self) {
                $this->tag = $element->tag;
                $this->textNode = $element->textNode;
                $this->attributes = $element->attributes;
                $this->link = $element->link;
            }
        }
    }
}
