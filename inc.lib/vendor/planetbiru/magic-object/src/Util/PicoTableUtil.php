<?php
namespace MagicObject\Util;

use DOMDocument;
use DOMElement;
use MagicObject\Exceptions\InvalidParameterException;

/**
 * Utility class for handling DOM element operations related to tables.
 *
 * This class provides methods for setting attributes, class names,
 * and identity properties on DOM elements, as well as parsing attribute strings
 * into associative arrays. It is designed to facilitate the management of
 * table-related DOM elements within the MagicObject framework.
 * 
 * @author Kamshory
 * @package MagicObject\Util
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoTableUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Set the class list for a DOMElement.
     *
     * @param DOMElement $node The DOM node to modify.
     * @param array $classList An array of class names to set.
     * @return DOMElement The modified DOM node.
     */
    public static function setClassList($node, $classList)
    {
        if(isset($classList) && is_array($classList))
        {
            $node->setAttribute("class", implode(" ", $classList));
        }
        return $node;
    }

    /**
     * Set attributes for a DOMElement.
     *
     * @param DOMElement $node The DOM node to modify.
     * @param array $annotationAttributes An associative array of attributes to set.
     * @return DOMElement The modified DOM node.
     */
    public static function setAttributes($node, $annotationAttributes)
    {
        if(isset($annotationAttributes) && is_array($annotationAttributes))
        {
            foreach($annotationAttributes as $attributeName=>$attributeValue)
            {
                $node->setAttribute($attributeName, $attributeValue);
            }
        }
        return $node;
    }

    /**
     * Set the identity for a DOMElement.
     *
     * @param DOMElement $node The DOM node to modify.
     * @param PicoGenericObject $identity The identity object.
     * @return DOMElement The modified DOM node.
     */
    public static function setIdentity($node, $identity)
    {
        if(isset($identity) && $identity->issetName())
        {
            $node->setAttribute("name", $identity->getName());
        }
        return $node;
    }

    /**
     * Parse attributes from a string representation.
     *
     * @param string $attributes The string containing attributes.
     * @return array An associative array of parsed attributes.
     * @throws InvalidParameterException If the provided attributes is an array.
     */
    public static function parseElementAttributes($attributes)
    {
        if(PicoStringUtil::isNullOrEmpty($attributes))
        {
            return array();
        }
        if(is_array($attributes))
        {
            throw new InvalidParameterException("Invalid parameter for ".__CLASS__."::parseElementAttributes(). Expected value to be string, array given.");
        }
        if(PicoStringUtil::isNotNullAndNotEmpty($attributes))
        {
            $attributes = trim($attributes);
            $attributes = PicoStringUtil::lTrim($attributes, "(", 1);
            $attributes = PicoStringUtil::rTrim($attributes, ")", 1);
            $doc = new DOMDocument();
            $doc->loadHTML("<html><body><div $attributes></div></body></html>");
            $div = $doc->getElementsByTagName("div")->item(0);
            for ($i = 0; $i < $div->attributes->length; ++$i) {
                $node = $div->attributes->item($i);
                $attrs[$node->nodeName] = $node->nodeValue;
            }
        }
        return $attrs;
    }

    /**
     * Validate the class name of a DOMElement.
     *
     * @param string $className The class name to validate.
     * @return bool true if the class name is valid, false otherwise.
     */
    public static function isValidClassName($className)
    {
        return !empty($className) && strpos($className, " ") === false && strpos($className, ".") === false && strpos($className, ",") === false;
    }
}