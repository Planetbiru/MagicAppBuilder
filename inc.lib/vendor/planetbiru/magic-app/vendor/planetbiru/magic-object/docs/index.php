<?php

use MagicObject\Util\PicoParsedown;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * Class to represent a replacement for null values.
 * This class can be used in cases where you want to explicitly handle
 * or replace null values in a more meaningful way.
 */
class PicoNull
{

}

/**
 * Class to scan PHP files in a directory recursively, parse docblocks, and display them.
 */
class PhpDocumentCreator // NOSONAR
{
    
    const DUPLICATED_WHITESPACE_EXP = '/\s\s+/';

    private $parsedown = null;

    /**
     * Constructor for the class.
     * Initializes the PicoParsedown object to be used within the class.
     */
    public function __construct()
    {
        $this->parsedown = new PicoParsedown();
    }

    /**
     * Scans a directory recursively for PHP files up to a certain depth.
     *
     * @param string $dir The directory to scan.
     * @param int $maxDepth The maximum depth of recursion. Default is PHP_INT_MAX.
     * @param int $currentDepth The current recursion depth. Used internally.
     * @return array Array of paths to PHP files.
     */
    public function scanDirectory($dir, $maxDepth = PHP_INT_MAX, $currentDepth = 0) {
        $phpFiles = [];

        // Avoid deeper scanning if depth exceeds the max limit
        if ($currentDepth > $maxDepth) {
            return $phpFiles;
        }

        // Ensure directory is valid
        if (!is_dir($dir)) {
            return $phpFiles;
        }

        // Scan PHP files in the current directory first
        $files = new DirectoryIterator($dir);
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $phpFiles[] = $file->getRealPath();
            }
        }

        // Recursively scan subdirectories
        $directoryIterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator(
            $directoryIterator,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $phpFiles[] = $file->getRealPath();
            }
        }

        return $phpFiles;
    }

    /**
     * Parses a docblock into a structured array with description and tags.
     *
     * @param string $docblock The raw docblock text to parse.
     * @return array Parsed docblock containing description and tags.
     */
    public function parseDocblock($docblock) {
        $parsed = [
            'description' => '',
            'tags' => []
        ];

        // Trim the docblock and remove comment stars
        $docblock = trim($docblock, "/* \n");
        $docblock = preg_replace('/^\s*\*/m', '', $docblock);

        // Separate the description and tags
        preg_match('/\s*(.*?)\s*(\s+\@.*)?$/s', $docblock, $matches);
        $parsed['description'] = trim($matches[1]);

        // Extract tags from the docblock
        if (isset($matches[2])) {
            preg_match_all('/\s*\@(\w+)\s+([^\n]+)/', $matches[2], $tagMatches, PREG_SET_ORDER);
            foreach ($tagMatches as $tagMatch) {
                $parsed['tags'][] = [
                    'tag' => $tagMatch[1],
                    'description' => trim($tagMatch[2])
                ];
            }
        }

        return $parsed;
    }

    /**
     * Generates a parsed docblock with proper HTML formatting.
     *
     * This method takes a parsed docblock and converts it into an HTML string representation.
     * It processes various docblock sections such as description, parameters, return types, 
     * exceptions thrown, and additional metadata (like packages and authors).
     *
     * @param array $parsedDocblock The parsed docblock data to display.
     * @param string $heading (optional) The HTML heading tag to use for the docblock's title (default is "h3").
     * @return string The HTML-formatted docblock.
     */
    public function generateParsedDocblock($parsedDocblock, $heading = "h3") {   

        $output = '';

        $parameters = $this->parseParameters($parsedDocblock);
        $returns = $this->parseReturns($parsedDocblock);
        $throws = $this->parseThrows($parsedDocblock);

        $output .= $this->displayPackages($parsedDocblock);       
        $output .= $this->displayAuthors($parsedDocblock);
        $output .= $this->displayLinks($parsedDocblock);        
        $output .= $this->displayDescription($parsedDocblock, $heading);
        $output .= $this->displayParameters($parameters);
        $output .= $this->displayReturns($returns);
        $output .= $this->displayThrows($throws);

        return $output;
    }

    /**
     * Displays the description from the parsed docblock.
     *
     * This function checks if a description exists in the parsed docblock and formats it into an HTML element.
     * It uses the `parsedown` library to convert Markdown descriptions into HTML.
     *
     * @param array $parsedDocblock The parsed docblock containing the description.
     * @param string $heading The HTML heading tag to wrap the description.
     * @return string The formatted HTML string with the description.
     */
    private function displayDescription($parsedDocblock, $heading)
    {
        $output = "";
        if ($parsedDocblock['description']) {
            $output .= "<$heading>Description</$heading>\r\n";
            $output .= $this->parsedown->text(trim($parsedDocblock['description'])) . "\n";
        }
        return $output;
    }

    /**
     * Displays the package information from the parsed docblock.
     *
     * This function checks if there is a 'package' tag in the parsed docblock and formats it into an HTML element.
     *
     * @param array $parsedDocblock The parsed docblock containing tags.
     * @return string The formatted HTML string with the package information.
     */
    private function displayPackages($parsedDocblock)
    {
        $output = "";
        $packages = [];
        foreach ($parsedDocblock['tags'] as $tag) {
            if ($tag['tag'] == 'package') {
                $packages[] = $tag['description'];
            }
        }
        if (!empty($packages)) {
            $output .= "<h4>Package</h4>\r\n";
            $output .= $packages[0];
        }
        return $output;
    }

    /**
     * Displays the author information from the parsed docblock.
     *
     * This function checks if there are 'author' tags in the parsed docblock and formats them into an HTML list.
     * It also converts email addresses to clickable mailto links.
     *
     * @param array $parsedDocblock The parsed docblock containing tags.
     * @return string The formatted HTML string with the author information.
     */
    private function displayAuthors($parsedDocblock)
    {
        $output = "";
        $authors = [];
        foreach ($parsedDocblock['tags'] as $tag) {
            if ($tag['tag'] == 'author') {
                $authors[] = $tag['description'];
            }
        }
        if (!empty($authors)) {
            $output .= "<h4>Authors</h4>\r\n";
            $output .= "<ol>\r\n";
            foreach ($authors as $author) {
                $output .= "<li>" . $this->convertEmailToLink($author) . "</li>\r\n";
            }
            $output .= "</ol>\r\n";
        }
        return $output;
    }

    /**
     * Displays the links from the parsed docblock.
     *
     * This function checks if there are 'link' tags in the parsed docblock and formats them into an HTML list.
     * It also converts URLs to clickable links.
     *
     * @param array $parsedDocblock The parsed docblock containing tags.
     * @return string The formatted HTML string with the links.
     */
    private function displayLinks($parsedDocblock)
    {
        $output = "";
        $links = [];
        foreach ($parsedDocblock['tags'] as $tag) {
            if ($tag['tag'] == 'link') {
                $links[] = $tag['description'];
            }
        }
        if (!empty($links)) {
            $output .= "<h4>Links</h4>\r\n";
            $output .= "<ol>\r\n";
            foreach ($links as $link) {
                $output .= "<li>" . $this->convertUrlToLink($link) . "</li>\r\n";
            }
            $output .= "</ol>\r\n";
        }
        return $output;
    }

    /**
     * Displays the parameters section from the parsed docblock.
     *
     * This function checks if there are parameters and displays their names and descriptions in an HTML format.
     *
     * @param array $parameters The list of parameters to display.
     * @return string The formatted HTML string with the parameter details.
     */
    private function displayParameters($parameters)
    {
        $output = "";
        if (!empty($parameters)) {
            $output .= "<h3>Parameters</h3>\r\n";
            foreach ($parameters as $parameter) {
                $output .= "<div class=\"parameter-name\">{$parameter['name']}</div>\r\n";
                $output .= "<div class=\"parameter-description\">{$this->parsedown->text(ltrim($parameter['description']))}</div>\r\n";
            }
        }
        return $output;
    }

    /**
     * Displays the return values section from the parsed docblock.
     *
     * This function checks if there are return values and displays their types and descriptions in an HTML format.
     *
     * @param array $returns The list of return values to display.
     * @return string The formatted HTML string with the return values.
     */
    private function displayReturns($returns)
    {
        $output = "";
        if (!empty($returns)) {
            $output .= "<h3>Return</h3>\r\n";
            foreach ($returns as $return) {
                $output .= "<div class=\"return-type\">{$return['type']}</div>\r\n";
                $output .= "<div class=\"return-description\">{$this->parsedown->text(ltrim($return['description']))}</div>\r\n";
            }
        }
        return $output;
    }

    /**
     * Displays the exceptions thrown section from the parsed docblock.
     *
     * This function checks if there are exceptions (throws) and displays their types and descriptions in an HTML format.
     *
     * @param array $throws The list of exceptions to display.
     * @return string The formatted HTML string with the exceptions thrown.
     */
    private function displayThrows($throws)
    {
        $output = "";
        if (!empty($throws)) {
            $output .= "<h3>Throws</h3>\r\n";
            foreach ($throws as $throw) {
                $output .= "<div class=\"return-type\">{$throw['type']}</div>\r\n";
                $output .= "<div class=\"return-description\">{$this->parsedown->text($throw['description'])}</div>\r\n";
            }
        }
        return $output;
    }

    /**
     * Converts email addresses in the text to mailto links.
     *
     * This function finds email addresses using regex and converts them to clickable mailto links.
     *
     * @param string $text The text that may contain email addresses.
     * @return string The text with email addresses converted to mailto links.
     */
    private function convertEmailToLink($text)
    {
        // Regex pattern to match email addresses
        $pattern = '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/';
        
        // Replace the email addresses with <a> tag
        $replacement = '<a href="mailto:$1">$1</a>';
        
        // Apply the replacement to the text
        return preg_replace($pattern, $replacement, $text);
    }

    /**
     * Converts URLs in the text to clickable links.
     *
     * This function finds URLs using regex and converts them to clickable links with target="_blank".
     *
     * @param string $text The text that may contain URLs.
     * @return string The text with URLs converted to clickable links.
     */
    private function convertUrlToLink($text)
    {
        // Regex pattern to match URLs
        $pattern = '/(https?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(?:\/[^\s]*)?)/';
        
        // Replace the URLs with <a> tag
        $replacement = '<a href="$1" target="_blank">$1</a>';
        
        // Apply the replacement to the text
        return preg_replace($pattern, $replacement, $text);
    }

    /**
     * Parses the `param` tags from a parsed docblock and extracts the parameter names and descriptions.
     *
     * @param array $parsedDocblock The parsed docblock that contains the `param` tags.
     * @return array An array of parameters, each containing 'name' and 'description'.
     */
    private function parseParameters($parsedDocblock)
    {
        $list = [];
        foreach ($parsedDocblock['tags'] as $tag) {
            if ($tag['tag'] == 'param') {
                $list[] = $tag['description'];
            }
        }
        
        $params = [];
        if (!empty($list)) {
            foreach ($list as $line) {
                $desc = trim(preg_replace(self::DUPLICATED_WHITESPACE_EXP, ' ', $line));
                $arr = explode(" ", $desc);
                if (count($arr) > 1 && (substr($arr[1], 0, 1) == '$' || substr($arr[1], 0, 2) == '&$')) {
                    $param = $arr[1];
                } else {
                    $param = $arr[0];
                }
                $description = substr($line, strpos($line, $param) + strlen($param) + 1);
                $params[] = [
                    'name' => $param,
                    'description' => $description
                ];
            }
        }
        return $params;
    }

    /**
     * Parses the `return` tags from a parsed docblock and extracts the return type and description.
     *
     * @param array $parsedDocblock The parsed docblock that contains the `return` tags.
     * @return array An array of return types, each containing 'type' and 'description'.
     */
    private function parseReturns($parsedDocblock)
    {
        $list = [];
        foreach ($parsedDocblock['tags'] as $tag) {
            if ($tag['tag'] == 'return') {
                $list[] = $tag['description'];
            }
        }
        $returns = [];
        if (!empty($list)) {
            foreach ($list as $line) {
                $desc = trim(preg_replace(self::DUPLICATED_WHITESPACE_EXP, ' ', $line));
                $arr = explode(" ", $desc);
                if (count($arr) > 1 && substr($arr[1], 0, 1) == '$') {
                    $type = $arr[1];
                } else {
                    $type = $arr[0];
                }
                $description = substr($line, strpos($line, $type) + strlen($type) + 1);
                $returns[] = [
                    'type' => $type,
                    'description' => $description
                ];
            }
        }
        return $returns;
    }

    /**
     * Parses the `throws` tags from a parsed docblock and extracts the thrown exceptions and their descriptions.
     *
     * @param array $parsedDocblock The parsed docblock that contains the `throws` tags.
     * @return array An array of thrown exceptions, each containing 'type' and 'description'.
     */
    private function parseThrows($parsedDocblock)
    {
        $list = [];
        foreach ($parsedDocblock['tags'] as $tag) {
            if ($tag['tag'] == 'throws') {
                $list[] = $tag['description'];
            }
        }
        $throws = [];
        if (!empty($list)) {
            foreach ($list as $line) {
                $desc = trim(preg_replace(self::DUPLICATED_WHITESPACE_EXP, ' ', $line));
                $arr = explode(" ", $desc);
                if (count($arr) > 1 && substr($arr[1], 0, 1) == '$') {
                    $type = $arr[1];
                } else {
                    $type = $arr[0];
                }
                $description = substr($line, strpos($line, $type) + strlen($type) + 1);
                $throws[] = [
                    'type' => $type,
                    'description' => $description
                ];
            }
        }
        return $throws;
    }
    
    /**
     * Retrieves the class declaration, including the class name, its parent class (if any),
     * and the interfaces it implements.
     * 
     * This function generates the class declaration in PHP syntax, displaying the class name, 
     * its parent class (if it has one), and the interfaces that the class implements.
     *
     * @param ReflectionClass $reflectionClass The ReflectionClass object for the class being analyzed.
     * @return string The formatted class declaration in PHP syntax.
     */
    private function getClassDeclaration($reflectionClass)
    {
        // Get the name of the class
        $className = $reflectionClass->getName();

        // Get the parent class name (if any)
        $parentClass = $reflectionClass->getParentClass();
        $parentClassName = $parentClass ? $parentClass->getName() : null;

        // Get all interfaces implemented by the class
        $interfaces = $reflectionClass->getInterfaces();
        $interfaceNames = array_map(function($interface) {
            return $interface->getName();
        }, $interfaces);

        // Initialize the class declaration string
        $basename = basename($className);
        $str = "<span class=\"php-keyword\">class</span> <span class=\"class-name\">$basename</span>";
        
        // If the class has a parent class, include it in the declaration
        if (isset($parentClassName)) {
            $str .= " <span class=\"php-keyword\">extends</span> <span class=\"class-name\">$parentClassName</span>";
        }
        
        // If the class implements any interfaces, include them in the declaration
        if (!empty($interfaceNames)) {
            $str .= " <span class=\"php-keyword\">implements</span> <span class=\"class-name\">" 
            . implode("</span>, <span class=\"class-name\">", $interfaceNames) 
            . "</span>"; // NOSONAR
        }

        // Close the class declaration and return the result
        $str .= "\r\n{\r\n";
        $str .= "}";

        return $str;
    }

    /**
     * Retrieves docblocks for all classes, properties, and methods in a PHP file, including access levels.
     * 
     * This function parses a PHP file, retrieves the class, property, and method docblocks, and returns them
     * along with other relevant information like access levels, types, and method parameters. It uses PHP reflection
     * to analyze the file and provides a structured HTML output for each element.
     *
     * @param string $file The PHP file to analyze.
     * @return string The HTML output of all parsed docblocks and relevant information.
     */
    public function getAllDocblocks($file) {
        $fileContents = file_get_contents($file);
        include_once $file;

        // Get the namespace, if any
        preg_match('/namespace\s+([a-zA-Z0-9\\\_]+)/', $fileContents, $namespaceMatches);
        $namespace = isset($namespaceMatches[1]) ? $namespaceMatches[1] : '';

        // Extract class names
        preg_match_all('/class\s+(\w+)/', $fileContents, $matches);
        $className = basename($file, '.php');
        $fullClassName = $namespace ? $namespace . '\\' . $className : $className;

        $output = ""; // Initialize the output string

        try {
            $reflection = new ReflectionClass($fullClassName);
            $classId = str_replace("\\", "-", $fullClassName);
            $output .= "<div class=\"class-item\" id=\"$classId\">\r\n";
            $output .= "<h1>{$fullClassName}</h1>\r\n";
            
            $output .= "<h2>Declaration</h2>\r\n";
            
            $declaration = $this->getClassDeclaration($reflection);      
            
            $output .= "<div class=\"class-declaration\">$declaration</div>\r\n";
            
            // Class docblock
            $classDocblock = $reflection->getDocComment();
            
            if ($classDocblock) {
                $parsedClassDocblock = $this->parseDocblock($classDocblock);
                $output .= "<div class=\"docblock\">\r\n";
                $output .= $this->generateParsedDocblock($parsedClassDocblock, "h2");
                $output .= "</div>\r\n"; // NOSONAR
            }
            
            // Constants
            $constants = $reflection->getConstants(); // NOSONAR
            $parentClass = $reflection->getParentClass(); // Get the parent class

            // Filter constants to only display those owned by the current class
            if ($parentClass) {
                $parentConstants = $parentClass->getConstants();
                $constants = array_diff_key($constants, $parentConstants); // Remove constants inherited from the parent class
            }

            $output .= $this->displayConstant($constants);

            // Property docblocks with access level
            $properties = $reflection->getProperties();
            $output .= $this->displayProperties($properties);

            // Method docblocks with access level
            $methods = $reflection->getMethods();
            $parentClass = $reflection->getParentClass(); // Get the parent class

            // Filter methods to only display those owned by the current class
            if ($parentClass) {
                $parentMethods = $parentClass->getMethods();
                
                // Compare methods in the current class with those in the parent class
                $methods = array_filter($methods, function($method) use ($parentMethods) {
                    // Only add methods that are not in the parent class
                    foreach ($parentMethods as $parentMethod) {
                        if ($method->getName() === $parentMethod->getName()) {
                            return false; // If the method is already in the parent class, don't display it
                        }
                    }
                    return true; // If the method is only in the current class, display it
                });
            }

            $output .= $this->displayMethods($methods);

            $output .= "</div>\r\n";

        } catch (ReflectionException $e) {
            $output .= "Could not reflect on class {$fullClassName}: " . $e->getMessage() . "<br>\r\n";
        }

        return $output; // Return the accumulated output
    }
    
    /**
     * Displays constants and their values with formatted HTML output.
     *
     * @param array $constants Array of constants to display.
     * @return string HTML string containing the constants and their values.
     */
    private function displayConstant($constants)
    {
        $str = "";
        if (!empty($constants)) {
            $str .= "<h2>Constants</h2>\r\n";
            foreach ($constants as $name => $value) {

                $constantValue = str_replace(["(\n)", "(\r\n)"], "()", htmlspecialchars(str_replace(["\t", "\r", "\n"], ["\\t", "\\r", "\\n"], var_export($value, true))));
                if(PicoStringUtil::startsWith($constantValue, "'") && PicoStringUtil::endsWith($constantValue, "'"))
                {
                    $constantValue = '"'.str_replace("\"", "\\\"", substr($constantValue, 1, strlen($constantValue) - 2)).'"';
                }
                $str .= "<div class=\"php-constant\">";
                $str .= "<span class=\"php-keyword\">const</span> "
                    ."<span class=\"constant-name\">$name</span> = <span class=\"constant-value\">" 
                    .$constantValue."</span>;";
                $str .= "</div>";
            }
        }
        return $str;
    }
    
    /**
     * Displays properties and their docblocks with formatted HTML output, including access levels.
     *
     * @param array $properties Array of property Reflection objects to display.
     * @return string HTML string containing the properties and their docblocks.
     */
    private function displayProperties($properties)
    {
        $str = "";
        if(!empty($properties))
        {
            $str .= "<h2>Properties</h2>\r\n";
            $defaultProperties = $this->getDefaultProperties($properties);
            foreach ($properties as $index=>$property) {
                $no = $index + 1;
                $propertyDocblock = $property->getDocComment();
                if ($propertyDocblock) {
                    $parsedPropertyDocblock = $this->parseDocblock($propertyDocblock);
                    $accessLevel = $this->getAccessLevel($property->getModifiers());
                    $propertyType = $this->getPropertyType($parsedPropertyDocblock);
                    $propertyType = explode("|", $propertyType)[0];
                    $defaultValue = "";
                    if(isset($defaultProperties) && isset($defaultProperties[$property->getName()]))
                    {               
                        $defaultValue = " = <span class=\"property-default\">"
                        .str_replace([" (\n)", " (\r\n)"], "()", htmlspecialchars(var_export($defaultProperties[$property->getName()], true)))."</span>";
                        
                    }
                    $str .= "<div class=\"property\">\r\n";
                    $str .= "<div class=\"property-identity\">$no. <span class=\"property-label\">{$property->getName()}</span></div>\r\n";
                    $str .= "<h3>Declaration</h3>\r\n";
                    $str .= "<div class=\"property-declaratiopn\">"
                        ."<span class=\"access-level\">{$accessLevel}</span> "
                        ."<span class=\"property-type\">{$propertyType}</span> "
                        ."<span class=\"property-name\">\${$property->getName()}</span>$defaultValue;"
                        ."</div>\r\n";
                    $str .= "<div class=\"docblock\">\r\n";
                    $str .= $this->generateParsedDocblock($parsedPropertyDocblock);
                    $str .= "</div>\r\n";
                    $str .= "</div>\r\n";
                }
            }
        }
        return $str;
    }

    /**
     * Retrieves the default values for all properties in a class.
     *
     * @param array $properties Array of property Reflection objects.
     * @return array Associative array where keys are property names and values are their default values.
     */
    private function getDefaultProperties($properties)
    {
        $reflectionClass = new ReflectionClass($properties[0]->getDeclaringClass()->getName());
        return $reflectionClass->getDefaultProperties();
    }
    
    /**
     * Displays methods and their docblocks with formatted HTML output, including access levels and parameters.
     *
     * @param array $methods Array of method Reflection objects to display.
     * @return string HTML string containing the methods and their docblocks.
     */
    private function displayMethods($methods)
    {
        $str = "";
        if(!empty($methods))
        {
            $str .= "<h2>Methods</h2>\r\n";
            foreach ($methods as $index => $method) {
                $no = $index + 1;
                $methodDocblock = $method->getDocComment();
                if ($methodDocblock) {
                    $parsedMethodDocblock = $this->parseDocblock($methodDocblock);
                    $params = $this->getMethodParams($parsedMethodDocblock, $method);
                    $paramsStr = $this->getMethodParamsFinal($params);
                    $returns = $this->getMethodReturns($parsedMethodDocblock);

                    $returnStr = $this->getMethodReturnsFinal($returns);
                    
                    $static = $this->getMethodStatic($method);

                    $accessLevel = $this->getAccessLevel($method->getModifiers());
                    $str .= "<div class='method'>\r\n";
                    $str .= "<div class=\"method-identity\">$no. <span class=\"method-label\">{$method->getName()}</span></div>\r\n";
                    $str .= "<h3>Declaration</h3>\r\n";
                    $str .= "<div class=\"method-declaratiopn\"><span class=\"access-level\">{$accessLevel}</span> <span class=\"access-level\">{$static}</span> <span class=\"php-keyword\">function</span> <span class=\"method-name\">{$method->getName()}</span>($paramsStr)$returnStr<br>{<br>}</div>\r\n";
                    $str .= "<div class='docblock'>\r\n";
                    $str .= $this->generateParsedDocblock($parsedMethodDocblock);
                    $str .= "</div>\r\n";
                    $str .= "</div>\r\n";
                }
            }
        }
        return $str;
    }

    /**
     * Retrieves the static modifier of a method, if any.
     *
     * @param ReflectionMethod $method The method Reflection object.
     * @return string "static" if the method is static, otherwise an empty string.
     */
    private function getMethodStatic($method)
    {
        $static = "";
        if($method->isStatic())
        {
            $static = "static";
        }
        return $static;
    }

    /**
     * Returns a formatted string for method return types.
     *
     * @param array $returns Array of return types.
     * @return string Formatted string representing return types.
     */
    private function getMethodReturnsFinal($returns)
    {
        if(!empty($returns))
        {
            $returnStr = " : ".implode(", ", $returns);
        }
        else
        {
            $returnStr = "";
        }
        return $returnStr;
    }

    /**
     * Returns a formatted string for method parameters.
     *
     * @param array $params Array of method parameters.
     * @return string Formatted string representing method parameters.
     */
    private function getMethodParamsFinal($params)
    {
        if(!empty($params))
        {
            $paramsStr = "<br>\r\n&nbsp;&nbsp;&nbsp;&nbsp;".implode(", <br>\r\n&nbsp;&nbsp;&nbsp;&nbsp;", $params)."<br>\r\n";
        }
        else
        {
            $paramsStr = ""; 
        }
        return $paramsStr;
    }

    /**
     * Retrieves the property type from the parsed docblock by searching for the `var` tag.
     *
     * @param array $parsedPropertyDocblock The parsed docblock that contains the `var` tag.
     * @return string The type of the property, or an empty string if not found.
     */
    private function getPropertyType($parsedPropertyDocblock)
    {
        $type = "";
        if (isset($parsedPropertyDocblock['tags']) && is_array($parsedPropertyDocblock['tags'])) {
            foreach ($parsedPropertyDocblock['tags'] as $tag) {
                if ($tag['tag'] == 'var') {
                    $type = trim(preg_replace(self::DUPLICATED_WHITESPACE_EXP, ' ', $tag['description']));
                    break;
                }
            }
        }
        return $type;
    }

    /**
     * Retrieves the default values for the parameters of a method.
     *
     * @param \ReflectionMethod $method The method whose parameters' default values are to be fetched.
     * @return array An associative array where the keys are parameter names and the values are the default values.
     */
    private function getParameterDefaults($method)
    {
        $parameters = $method->getParameters();
        $defaults = [];
    
        foreach ($parameters as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $defaults[$parameter->getName()] = $parameter->getDefaultValue() === null ? new PicoNull() : $parameter->getDefaultValue();
            } else {
                $defaults[$parameter->getName()] = null;
            }
        }
    
        return $defaults;
    }

    /**
     * Parses the `param` tags from the parsed method docblock and retrieves the method parameters 
     * with their default values and HTML representation.
     *
     * @param array $parsedMethodDocblock The parsed docblock that contains the `param` tags.
     * @param \ReflectionMethod $method The method whose parameters are being parsed.
     * @return array An array of HTML strings representing the parameters with types, names, and default values.
     */
    private function getMethodParams($parsedMethodDocblock, $method)
    {
        // Get method parameters and their default values
        $defaults = $this->getParameterDefaults($method);

        $defaultValues = $this->getDefaultValues($defaults);

        $params = [];
        if (isset($parsedMethodDocblock['tags']) && is_array($parsedMethodDocblock['tags'])) {
            foreach ($parsedMethodDocblock['tags'] as $tag) {
                if ($tag['tag'] == 'param') {
                    $description = trim(preg_replace(self::DUPLICATED_WHITESPACE_EXP, ' ', $tag['description']));
                    $arr = explode(" ", $description);

                    if (count($arr) > 1 && substr($arr[1], 0, 1) == '$') {
                        $param = ltrim($arr[1], '$');
                        $defaultHtml = $this->getDefaultValueHtml($defaultValues, $param);
                        $paramHtml = $this->getParameterType($arr[0]) . ' <span class="parameter-name">' . $arr[1] . $defaultHtml . '</span>';
                    } else {
                        $param = ltrim($arr[0], '$');
                        $defaultHtml = $this->getDefaultValueHtml($defaultValues, $param);
                        $paramHtml = '<span class="parameter-name">' . $arr[0] . $defaultHtml . '</span>';
                    }
                    $params[] = $paramHtml;
                }
            }
        }
        return $params;
    }
    
    /**
     * Retrieves the default values for the given parameters.
     *
     * This method processes an array of parameter defaults and returns an associative array
     * where the keys are the parameter names and the values are the default values. If a 
     * parameter's default value is `null`, it is explicitly marked as `null`. If the value
     * is an instance of the `PicoNull` class, it is represented as the string "null". Otherwise,
     * the value is exported using `var_export()`.
     *
     * @param array $defaults An associative array where the keys are parameter names and the values are their default values.
     * @return array An associative array of parameter names and their corresponding default values.
     */
    private function getDefaultValues($defaults)
    {
        $defaultValues = [];
        if (!empty($defaults)) {
            foreach ($defaults as $paramName => $defaultValue) {
                if ($defaultValue === null) {
                    $defaultValues[$paramName] = null;
                } else {
                    $defaultValues[$paramName] = $defaultValue instanceof PicoNull ? "null" : var_export($defaultValue, true);
                }
            }
        }
        
        return $defaultValues;
    }

    /**
     * Generates the HTML for displaying the default value of a parameter, if available.
     *
     * @param array $defaultValues An associative array of parameter default values.
     * @param string $param The name of the parameter whose default value needs to be displayed.
     * @return string The HTML string for the default value, or an empty string if no default value exists.
     */
    private function getDefaultValueHtml($defaultValues, $param)
    {
        if (isset($defaultValues[$param])) {
            return ' <span class="parameter-equal-sign">=</span> <span class="parameter-default">' . $defaultValues[$param] . '</span>';
        }
        return "";
    }

    /**
     * Generates HTML to represent the type of a parameter, including handling union types.
     *
     * @param string $type The type (or types) of the parameter, possibly containing union types (separated by '|').
     * @return string The HTML string representing the parameter's type(s).
     */
    private function getParameterType($type)
    {
        $arr = explode("|", $type);
        $types = [];
        foreach ($arr as $tp) {
            $types[] = '<span class="parameter-type">' . $tp . '</span>';
        }    

        return implode("|", $types);
    }

    /**
     * Generates HTML to represent the return type of a method, including handling union types.
     *
     * @param string $type The return type (or types), possibly containing union types (separated by '|').
     * @return string The HTML string representing the return type(s).
     */
    private function getReturnType($type)
    {
        $arr = explode("|", $type);
        $types = [];
        foreach ($arr as $tp) {
            $types[] = '<span class="return-type">' . $tp . '</span>';
        }    

        return implode("|", $types);
    }

    /**
     * Parses the `return` tags from the parsed method docblock and retrieves the return type(s).
     *
     * @param array $parsedMethodDocblock The parsed docblock that contains the `return` tags.
     * @return array An array of HTML strings representing the return type(s).
     */
    private function getMethodReturns($parsedMethodDocblock)
    {
        $returns = [];
        if (isset($parsedMethodDocblock['tags']) && is_array($parsedMethodDocblock['tags'])) {
            foreach ($parsedMethodDocblock['tags'] as $tag) {
                if ($tag['tag'] == 'return') {
                    $description = trim(preg_replace(self::DUPLICATED_WHITESPACE_EXP, ' ', $tag['description']));
                    $arr = explode(" ", $description);
                    if (!empty($arr)) {
                        $return = $this->getReturnType($arr[0]);
                    } else {
                        $return = 'void';
                    }
                    $returns[] = $return;
                }
            }
        }
        return $returns;
    }


    /**
     * Gets the access level (public, private, protected) based on the modifiers of a property or method.
     *
     * @param int $modifiers The modifiers for the property or method.
     * @return string The access level (public, private, or protected).
     */
    private function getAccessLevel($modifiers) {
        $accessLevel = "";
        if ($modifiers & ReflectionMethod::IS_PUBLIC) {
            $accessLevel = 'public';
        } elseif ($modifiers & ReflectionMethod::IS_PRIVATE) {
            $accessLevel = 'private';
        } elseif ($modifiers & ReflectionMethod::IS_PROTECTED) {
            $accessLevel = 'protected';
        }
        return $accessLevel;
    }
    
    /**
     * Scans a directory recursively for PHP files up to a certain depth.
     *
     * @param string $dir The directory to scan.
     * @param int $maxDepth The maximum depth of recursion. Default is PHP_INT_MAX.
     * @param int $currentDepth The current recursion depth. Used internally.
     * @return array A nested array representing the directory structure with PHP files.
     */
    public function scanDirectoryToc($dir, $maxDepth = PHP_INT_MAX, $currentDepth = 0) {
        $structure = [];

        // Avoid deeper scanning if depth exceeds the max limit
        if ($currentDepth > $maxDepth) {
            return $structure;
        }

        // Ensure directory is valid
        if (!is_dir($dir)) {
            return $structure;
        }

        // Scan files in the current directory first (before subdirectories)
        $files = new DirectoryIterator($dir);
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $structure[] = $this->createStdClass(
                    "file",
                    $file->getPathname(),
                    $file->getFilename()
                ); // Add file name to structure
            }
        }
        
        foreach ($files as $file) {
            if ($file->isDir() && $file->getBasename() != "." && $file->getBasename() != "..") {
                $structure[] = $this->createStdClass(
                    "dir", 
                    $file->getPathname(),
                    $file->getFilename(), 
                    $this->scanDirectoryToc($file->getPathname(), $maxDepth = PHP_INT_MAX, $currentDepth + 1)
            ); 
            }
        }

        return $structure;
    }
    
    /**
     * Creates a standard class object for directory or file.
     *
     * @param string $type The type of the item, either 'file' or 'dir'.
     * @param string $pathname The full path to the file or directory.
     * @param string $filename The name of the file or directory.
     * @param array|null $child The child elements (subdirectories or files), if any.
     * @return stdClass The created object with the provided data.
     */
    private function createStdClass($type, $pathname, $filename, $child = null)
    {
        $result = new stdClass;
        $result->type = $type;
        $result->pathname = $pathname;
        $result->filename = $filename;
        if(isset($child))
        {
            $result->child = $child;
        }
        return $result;
    }
    
    /**
     * Renders the directory structure as HTML.
     *
     * @param array $structure The directory structure to render.
     * @param string $baseDir The base directory for generating relative paths.
     * @return string The rendered HTML representation of the directory structure.
     */
    public function renderDirectoryStructure($structure, $baseDir) {
        $html = "<ul>"; // Start with <ul>

        foreach ($structure as $item) {
            if ($item->type == "file") {
                // Generate relative path for file and display as <li>
                $relativePath = "#MagicObject-" . str_replace(["\\", "/", ".php"], ["-", "-", ""], $this->getRelativePath($item->pathname, $baseDir));
                $html .= "<li><a href=\"$relativePath\">" . basename($item->filename, ".php") . "</a></li>";
            } elseif ($item->type == "dir") {
                // Display directory as <li> with nested <ul> for contents
                $html .= "<li>" . basename($item->pathname) . "";
                // Recursively render subdirectories and files
                if (isset($item->child) && !empty($item->child)) {
                    $html .= $this->renderDirectoryStructure($item->child, $baseDir);
                }
                $html .= "</li>";
            }
        }

        $html .= "</ul>"; // Close the <ul>

        return $html;
    }

    /**
     * Gets the relative path of a file or directory from a given base directory.
     *
     * @param string $pathname The full path to the file or directory.
     * @param string $basePath The base directory to compute the relative path from.
     * @return string The relative path, or an empty string if the path is invalid.
     */
    private function getRelativePath($pathname, $basePath) {
        // Ensure both paths are absolute
        $realPathname = realpath($pathname);
        $realBasepath = realpath($basePath);

        // Ensure both paths are valid
        if ($realPathname === false || $realBasepath === false) {
            return ""; // Return empty string if either path is invalid
        }

        // Check if basepath is part of the pathname
        if (strpos($realPathname, $realBasepath) === 0) {
            // Return the part of the pathname after the basepath
            return substr($realPathname, strlen($realBasepath) + 1); // +1 to remove trailing DIRECTORY_SEPARATOR
        } else {
            return ""; // If basepath is not found in pathname
        }
    }

}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MagicObject Documentation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page">

<?php

$srcDir = dirname(__DIR__) . '/src';

if (is_dir($srcDir)) {
    $docCreator = new PhpDocumentCreator();

    $files = $docCreator->scanDirectory($srcDir);
    
    $structure = $docCreator->scanDirectoryToc($srcDir); // Replace with your directory path
    ?>
    <div class="sidebar">
    <h3>Table of Content</h3>
    <?php
    echo $docCreator->renderDirectoryStructure($structure, $srcDir);
    ?>
    </div>
    <div class="mainbar">
    <?php

    $rendered = [];
    foreach ($files as $file) {
        if(!in_array($file, $rendered))
        {
            echo $docCreator->getAllDocblocks($file);
        }
        $rendered[] = $file;
    }
    ?>
    </div>
    <?php
} else {
    echo "The `$srcDir` directory was not found. Ensure this script is run from within the project repository.\n";
}
?>
</div>
<script src="highlight.min.js"></script>
<script>
    // Menyorot kode setelah halaman dimuat
    hljs.highlightAll();

</script>
</body>
</html>
