<?php

namespace MagicObject\Util;

use SimpleXMLElement;

/**
 * XmlToJsonParser
 *
 * Utility class for converting between XML and JSON-friendly PHP arrays.
 * - Converts XML strings to PHP arrays, supporting flattening specified child elements into arrays,
 *   preserving attributes, and casting values to appropriate types.
 * - Converts PHP arrays back to indented XML strings, supporting custom root and item names.
 */
class XmlToJsonParser
{
    /**
     * @var array List of element names to be treated as array items.
     */
    private $arrayItemNames;

    /**
     * Constructor
     *
     * @param array $arrayItemNames Names of XML elements to be flattened as arrays.
     */
    public function __construct($arrayItemNames = ['item'])
    {
        $this->arrayItemNames = $arrayItemNames;
    }

    /**
     * Parse XML string into PHP array.
     *
     * @param string $xmlString XML content as string.
     * @return mixed Parsed array structure.
     */
    public function parse($xmlString)
    {
        // Load XML string into SimpleXMLElement
        $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
        return $this->convertElement($xml);
    }

    /**
     * Recursively convert SimpleXMLElement to array.
     *
     * @param SimpleXMLElement $element
     * @return mixed
     */
    private function convertElement($element)
    {
        $result = [];

        // Store attributes as "@attrName"
        foreach ($element->attributes() as $attrName => $attrValue) {
            $result["@{$attrName}"] = $this->castValue((string)$attrValue);
        }

        // Group children by element name
        $childrenByName = [];
        foreach ($element->children() as $childName => $child) {
            $childrenByName[$childName][] = $this->convertElement($child);
        }

        // Process children
        foreach ($childrenByName as $childName => $childValues) {
            // If only one child group and it's in arrayItemNames, flatten to array
            if (count($childrenByName) === 1 && in_array($childName, $this->arrayItemNames, true)) {
                $result = $childValues;
            } elseif (count($childValues) === 1) {
                $result[$childName] = $childValues[0];
            } else {
                $result[$childName] = $childValues;
            }
        }

        // Get text node if present and not just whitespace
        $text = trim((string)$element);
        if ($text !== "") {
            if (count($result) > 0) // NOSONAR
            {
                $result["#text"] = $this->castValue($text);
            } else {
                // If only text, return casted value directly
                return $this->castValue($text);
            }
        }

        // If truly empty (no attributes, no children, no text), return null
        if (empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Cast string value to appropriate PHP type.
     *
     * @param string $value
     * @return mixed
     */
    private function castValue($value) // NOSONAR
    {
        if (is_numeric($value)) {
            return $value + 0;
        }

        $lower = strtolower($value);
        if ($lower === "true") 
        {
            return true;
        }
        if ($lower === "false") 
        {
            return false;
        }
        if ($lower === "null") 
        {
            return null;
        }
        return $value;
    }
    
    /**
     * Converts a PHP array to an XML string.
     *
     * @param array  $array     The input array to convert.
     * @param string $rootName  The name of the root XML element.
     * @param string $itemName  The name to use for array items.
     * @return string           The resulting XML string.
     */
    public function toXml($array, $rootName = "root", $itemName = "item")
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><{$rootName}></{$rootName}>");
        $this->arrayToXml($array, $xml, $rootName, $itemName);

        // Format output with indentation using DOMDocument
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        return $dom->saveXML();
    }

    /**
     * Recursively adds array data to a SimpleXMLElement.
     *
     * @param mixed            $data        The data to convert (array or scalar).
     * @param SimpleXMLElement &$xmlElement The XML element to append to.
     * @param string           $currentName The current element name.
     * @param string           $itemName    The name to use for array items.
     * @return void
     *
     * This function traverses the input array or scalar value and appends it to the given XML element.
     * - If the value is an array, it checks if it is associative or sequential.
     * - Sequential arrays are wrapped using the provided itemName.
     * - Associative arrays are processed by key, handling text nodes ("#text") and attributes ("@attr").
     * - Scalar values are added as text nodes.
     * - Boolean false is converted to string "false" instead of null.
     */
    private function arrayToXml($data, &$xmlElement, $currentName, $itemName) // NOSONAR
    {
        if (is_array($data)) {
            $isAssoc = array_keys($data) !== range(0, count($data) - 1);

            if (!$isAssoc) {
                // Sequential array → wrap using itemName parameter
                foreach ($data as $value) {
                    $child = $xmlElement->addChild($itemName);
                    $this->arrayToXml($value, $child, $itemName, $itemName);
                }
            } else {
                foreach ($data as $key => $value) {
                    if ($key === "#text") {
                        // Add text node
                        $xmlElement[0] = htmlspecialchars($value === false ? "false" : (string)$value);
                    } elseif (strpos($key, "@") === 0) {
                        // Add attribute
                        $xmlElement->addAttribute(substr($key, 1), $value === false ? "false" : (string)$value);
                    } else {
                        if (is_array($value)) {
                            $isAssocChild = array_keys($value) !== range(0, count($value) - 1);
                            if ($isAssocChild) {
                                $child = $xmlElement->addChild($key);
                                $this->arrayToXml($value, $child, $key, $itemName);
                            } else {
                                foreach ($value as $v) {
                                    $child = $xmlElement->addChild($key);
                                    $this->arrayToXml($v, $child, $key, $itemName);
                                }
                            }
                        } else {
                            // Add child element with value, convert false to string "false"
                            $xmlElement->addChild($key, htmlspecialchars($value === false ? "false" : (string)$value));
                        }
                    }
                }
            }
        } elseif ($data !== null) {
            // Add scalar value, convert false to string "false"
            $xmlElement[0] = htmlspecialchars($data === false ? "false" : (string)$data);
        }
    }
}