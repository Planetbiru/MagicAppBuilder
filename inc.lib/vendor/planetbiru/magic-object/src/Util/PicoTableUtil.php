<?php
namespace MagicObject\Util;

use DOMDocument;
use DOMElement;
use MagicObject\Exceptions\InvalidParameterException;

class PicoTableUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Set class
     *
     * @param DOMElement $node DOM node
     * @param array $classList Class list
     * @return DOMElement
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
     * Set attributes
     *
     * @param DOMElement $node DOM node
     * @param array $annotationClass Annotation class
     * @return DOMElement
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
     * Set identity
     *
     * @param DOMElement $node DOM node
     * @param PicoGenericObject $identity Identity
     * @return DOMElement
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
     * Parse attribute
     *
     * @param string $attributes Attributes
     * @return array
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
     * Validate class name of DOMElement
     *
     * @param string $className Class name
     * @return boolean
     */
    public static function isValidClassName($className)
    {
        return !empty($className) && strpos($className, " ") === false && strpos($className, ".") === false && strpos($className, ",") === false;
    }
}