<?php

namespace MagicObject\Util;

use MagicObject\Exceptions\YamlException;
use stdClass;

/**
 * Class Spicy
 * 
 * This class is a modification of the Spyc project.
 * The purpose of this modification is to integrate the class with other projects that use Composer,
 * making it easy to use without requiring additional dependencies or code duplication.
 * Additionally, code smells have been fixed to ensure the code remains clean and understandable when using SonarLint,
 * thereby avoiding confusion for users when debugging code errors.
 * 
 * @author Vlad Andersen <vlad.andersen@gmail.com>
 * @author Chris Wanstrath <chris@ozmm.org>
 * @link https://github.com/mustangostang/spyc/
 * @copyright Copyright 2005-2006 Chris Wanstrath, 2006-2011 Vlad Andersen
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Spicy // NOSONAR 
{
    const REMPTY = "\0\0\0\0\0";

    /**
     * If set to true, forces YAML dump to enclose any string value in quotes.
     * False by default.
     *
     * @var bool
     */
    private $dumpForceQuotes = false; // NOSONAR

    /**
     * If set to true, forces YAML load to use the `syck_load` function when possible.
     * False by default.
     *
     * @var bool
     */
    private $emptyHashAsObject = false; // NOSONAR

    /**
     * Indentation level for dumping YAML.
     *
     * @var int
     */
    private $_dumpIndent; // NOSONAR

    /**
     * Word wrap length for dumping YAML.
     *
     * @var int
     */
    private $_dumpWordWrap; // NOSONAR

    /**
     * Indicates if a group anchor is present in the YAML document.
     *
     * @var bool
     */
    private $_containsGroupAnchor = false; // NOSONAR

    /**
     * Indicates if a group alias is present in the YAML document.
     *
     * @var bool
     */
    private $_containsGroupAlias = false; // NOSONAR

    /**
     * Current path being processed in the YAML structure.
     *
     * @var mixed
     */
    private $path;

    /**
     * Result of the YAML parsing or dumping process.
     *
     * @var mixed
     */
    private $result;

    /**
     * Placeholder for YAML literal blocks.
     *
     * @var string
     */
    private $literalPlaceHolder = '___YAML_Literal_Block___';

    /**
     * List of saved groups encountered during YAML parsing.
     *
     * @var array
     */
    private $savedGroups = array();

    /**
     * Current indentation level during parsing or dumping.
     *
     * @var int
     */
    private $indent;

    /**
     * Path modifier to be applied after adding the current element.
     *
     * @var array
     */
    private $delayedPath = array();


    /**
     * Load a valid YAML string to Spicy.
     * @param string $input
     * @return array
     */
    public function load($input)
    {
        return $this->_loadString($input);
    }


    /**
     * Load a YAML file or string and convert it into a PHP array.
     * 
     * This method accepts a file path or a string containing YAML content and 
     * converts it into a PHP array. Options can be set to customize the behavior.
     * 
     * @param string $input Path to the YAML file or a string containing YAML content.
     * @param array  $options Optional settings to modify parsing behavior.
     * @return array The parsed YAML converted to a PHP array.
     */
    public function loadFile($input, $options = array())
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this->_loadFile($input);
    }

    /**
     * Load a YAML string and convert it into a PHP array.
     * 
     * This method accepts a string containing YAML content and converts it into a PHP array.
     * Options can be set to customize the behavior.
     * 
     * @param string $input String containing YAML content.
     * @param array  $options Optional settings to modify parsing behavior.
     * @return array The parsed YAML converted to a PHP array.
     */
    public function loadString($input, $options = array())
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this->_loadString($input);
    }

    /**
     * Convert a PHP array to a YAML string.
     * 
     * This method converts a PHP array into a YAML string. 
     * You can customize the indentation and word wrapping. If not provided, 
     * the default values for indentation and word wrapping are 2 spaces and 40 characters, respectively.
     * 
     * @param array  $array The PHP array to be converted.
     * @param int    $indent Indentation size. Default is 2 spaces. Pass `false` to use the default.
     * @param int    $wordwrap Word wrap limit. Default is 40 characters. Pass `0` for no word wrap.
     * @param bool   $noOpeningDashes Whether to omit the opening dashes (`---`) from the YAML. Default is false.
     * @return string The YAML representation of the provided PHP array.
     */
    public function dump($array, $indent = false, $wordwrap = false, $noOpeningDashes = false)
    {
        // Set indentation and word wrap to default values if not provided.
        $this->_dumpIndent = ($indent === false || !is_numeric($indent)) ? 2 : $indent;
        $this->_dumpWordWrap = ($wordwrap === false || !is_numeric($wordwrap)) ? 40 : $wordwrap;

        // Initialize the YAML string.
        $string = !$noOpeningDashes ? "---\n" : "";

        // Convert the PHP array to YAML format.
        if ($array) {
            $array = (array)$array;
            $previousKey = -1;
            foreach ($array as $key => $value) {
                if (!isset($firstKey)) {
                    $firstKey = $key;
                }
                $string .= $this->_yamlize($key, $value, 0, $previousKey, $firstKey, $array);
                $previousKey = $key;
            }
        }
        return $string;
    }


    /**
     * Converts a key-value pair to a YAML string.
     * 
     * This method attempts to convert a key and its corresponding value into a 
     * YAML-formatted string. It recursively processes arrays and objects.
     * 
     * @param mixed  $key The key name.
     * @param mixed  $value The value associated with the key.
     * @param int    $indent The current indentation level.
     * @param int    $previousKey The previous key in the array (used for sequences).
     * @param int    $firstKey The first key in the current array.
     * @param array|null $sourceArray The source array being processed.
     * @return string The YAML representation of the key-value pair.
     */
    private function _yamlize($key, $value, $indent, $previousKey = -1, $firstKey = 0, $sourceArray = null)
    {
        if (is_object($value)) {
            $value = (array)$value;
        }

        if (is_array($value)) {
            if (empty($value)) {
                return $this->_dumpNode($key, [], $indent, $previousKey, $firstKey, $sourceArray);
            }
            
            // Handle arrays with children by creating a new node and increasing the indent.
            $string = $this->_dumpNode($key, self::REMPTY, $indent, $previousKey, $firstKey, $sourceArray);
            $indent += $this->_dumpIndent;
            $string .= $this->_yamlizeArray($value, $indent);
        } else {
            // Handle scalar values.
            $string = $this->_dumpNode($key, $value, $indent, $previousKey, $firstKey, $sourceArray);
        }

        return $string;
    }

    /**
     * Converts an array to a YAML string with proper indentation.
     * 
     * This method iterates over an array and recursively converts each element 
     * into a YAML-formatted string.
     * 
     * @param array $array The array to be converted.
     * @param int   $indent The current indentation level.
     * @return string The YAML representation of the array.
     */
    private function _yamlizeArray($array, $indent)
    {
        if (is_array($array)) {
            $string = '';
            $previousKey = -1;
            foreach ($array as $key => $value) {
                if (!isset($firstKey)) {
                    $firstKey = $key;
                }
                $string .= $this->_yamlize($key, $value, $indent, $previousKey, $firstKey, $array);
                $previousKey = $key;
            }
            return $string;
        }
        
        return false;
    }

    /**
     * Converts a key-value pair to a YAML node string.
     * 
     * This method formats a key-value pair into a valid YAML node, applying 
     * literal blocks, folding, or quoting when necessary.
     * 
     * @param mixed  $key The key name.
     * @param mixed  $value The value associated with the key.
     * @param int    $indent The current indentation level.
     * @param int    $previousKey The previous key in the array (used for sequences).
     * @param int    $firstKey The first key in the current array.
     * @param array|null $sourceArray The source array being processed.
     * @return string The YAML representation of the key-value pair as a node.
     */
    private function _dumpNode($key, $value, $indent, $previousKey = -1, $firstKey = 0, $sourceArray = null) // NOSONAR
    {
        // Handle special string cases with literal blocks or folding.
        if (
            is_string($value) && (
                strpos($value, "\n") !== false ||
                strpos($value, ": ") !== false ||
                strpos($value, "- ") !== false ||
                strpos($value, "*") !== false ||
                strpos($value, "#") !== false ||
                strpos($value, "<") !== false ||
                strpos($value, ">") !== false ||
                strpos($value, '%') !== false ||
                strpos($value, '  ') !== false ||
                strpos($value, "[") !== false ||
                strpos($value, "]") !== false ||
                strpos($value, "{") !== false ||
                strpos($value, "}") !== false ||
                strpos($value, "&") !== false ||
                strpos($value, "'") !== false ||
                strpos($value, "!") === 0 ||
                substr($value, -1, 1) == ':'
            )
        ) {
            $value = $this->_doLiteralBlock($value, $indent);
        } else {
            $value = $this->_doFolding($value, $indent);
        }

        if ($value === []) {
            $value = '[ ]';
        }
        if ($value === "") {
            $value = '""';
        }
        if ($this->isTranslationWord($value)) {
            $value = $this->_doLiteralBlock($value, $indent);
        }
        if (trim($value) != $value) {
            $value = $this->_doLiteralBlock($value, $indent);
        }
        if (is_bool($value)) {
            $value = $value ? "true" : "false";
        }
        if ($value === null) {
            $value = 'null';
        }
        if ($value === "'" . self::REMPTY . "'") {
            $value = null;
        }

        $spaces = str_repeat(' ', $indent);

        if (is_array($sourceArray) && array_keys($sourceArray) === range(0, count($sourceArray) - 1)) {
            // It's a sequence
            $string = $spaces . '- ' . $value . "\n";
        } else {
            // It's a mapping
            if (strpos($key, ":") !== false || strpos($key, "#") !== false) {
                $key = '"' . $key . '"';
            }
            $string = rtrim($spaces . $key . ': ' . $value) . "\n";
        }

        return $string;
    }



    /**
     * Creates a literal block for a YAML dump.
     * 
     * This method formats a given string as a YAML literal block, adding indentation
     * and handling special characters or line breaks appropriately.
     * 
     * @param string $value The string to be converted into a literal block.
     * @param int    $indent The current indentation level.
     * @return string The YAML-formatted literal block.
     */
    private function _doLiteralBlock($value, $indent) // NOSONAR
    {
        if ($value === "\n") {
            return '\n';
        }

        if (strpos($value, "\n") === false) {
            if (strpos($value, "'") === false) {
                return sprintf("'%s'", $value);
            }
            if (strpos($value, '"') === false) {
                return sprintf('"%s"', $value);
            }
        }

        $exploded = explode("\n", $value);
        $newValue = '|'; // Default block indicator.

        // Check if the first line is a block indicator.
        if (isset($exploded[0]) && in_array($exploded[0], ["|", "|-", ">"], true)) {
            $newValue = $exploded[0];
            unset($exploded[0]);
        }

        $indent += $this->_dumpIndent;
        $spaces = str_repeat(' ', $indent);

        foreach ($exploded as $line) {
            $line = trim($line);

            // Remove surrounding quotes if present.
            if ((strpos($line, '"') === 0 && strrpos($line, '"') === (strlen($line) - 1)) || 
                (strpos($line, "'") === 0 && strrpos($line, "'") === (strlen($line) - 1))) {
                $line = substr($line, 1, -1);
            }

            $newValue .= "\n" . $spaces . $line;
        }

        return $newValue;
    }

    /**
     * Folds a string of text if it exceeds the configured word wrap length.
     * 
     * This method wraps long strings in YAML using a folded style (`>`). If 
     * the word wrap is disabled (set to 0), it returns the string as is.
     * 
     * @param string $value The string to be folded.
     * @param int    $indent The current indentation level.
     * @return string The folded YAML string.
     */
    private function _doFolding($value, $indent)
    {
        // Check if word wrapping is enabled and the string length exceeds the limit.
        if ($this->_dumpWordWrap !== 0 && is_string($value) && strlen($value) > $this->_dumpWordWrap) {
            $indent += $this->_dumpIndent;
            $indentSpaces = str_repeat(' ', $indent);
            $wrapped = wordwrap($value, $this->_dumpWordWrap, "\n$indentSpaces");
            $value = ">\n" . $indentSpaces . $wrapped;
        } else {
            // Force quotes if required or for numeric strings.
            if ($this->dumpForceQuotes && is_string($value) && $value !== self::REMPTY) {
                $value = '"' . $value . '"';
            }
            if (is_numeric($value) && is_string($value)) {
                $value = '"' . $value . '"';
            }
        }

        return $value;
    }

    /**
     * Checks if a value represents a "true" word in YAML.
     * 
     * Recognized words: 'true', 'on', 'yes', 'y'.
     * 
     * @param string $value The value to check.
     * @return bool True if the value matches a "true" word.
     */
    private function isTrueWord($value)
    {
        $words = self::getTranslations(['true', 'on', 'yes', 'y']);
        return in_array($value, $words, true);
    }

    /**
     * Checks if a value represents a "false" word in YAML.
     * 
     * Recognized words: 'false', 'off', 'no', 'n'.
     * 
     * @param string $value The value to check.
     * @return bool True if the value matches a "false" word.
     */
    private function isFalseWord($value)
    {
        $words = self::getTranslations(['false', 'off', 'no', 'n']);
        return in_array($value, $words, true);
    }

    /**
     * Checks if a value represents a "null" word in YAML.
     * 
     * Recognized words: 'null', '~'.
     * 
     * @param string $value The value to check.
     * @return bool True if the value matches a "null" word.
     */
    private function isNullWord($value)
    {
        $words = self::getTranslations(['null', '~']);
        return in_array($value, $words, true);
    }

    /**
     * Checks if a value is a special YAML translation word.
     * 
     * This method checks if the value represents a "true", "false", or "null" word.
     * 
     * @param string $value The value to check.
     * @return bool True if the value is a recognized YAML translation word.
     */
    private function isTranslationWord($value)
    {
        return $this->isTrueWord($value) || $this->isFalseWord($value) || $this->isNullWord($value);
    }


    /**
     * Coerce a string into a native PHP type (boolean, null).
     * 
     * Based on the YAML 1.1 specification for boolean and null values.
     * Reference: http://yaml.org/type/bool.html
     * 
     * @param mixed &$value The value to coerce.
     * @access private
     */
    private function coerceValue(&$value)
    {
        if ($this->isTrueWord($value)) {
            $value = true;
        } else if ($this->isFalseWord($value)) {
            $value = false;
        } else if ($this->isNullWord($value)) {
            $value = null;
        }
    }

    /**
     * Translates a set of words into multiple cases (lowercase, uppercase, capitalized).
     * 
     * This ensures compatibility with YAML 1.1 coercion rules, which allow for 
     * different case variations of certain keywords (e.g., 'True', 'TRUE', 'true').
     * 
     * @param array $words The words to translate.
     * @return array An array of translated words with all case variations.
     * @access private
     */
    private static function getTranslations($words)
    {
        $result = array();
        foreach ($words as $word) {
            $result = array_merge($result, [ucfirst($word), strtoupper($word), strtolower($word)]);
        }
        return $result;
    }

    /**
     * Loads a YAML file from the given path.
     * 
     * @param string $input The file path.
     * @return array The parsed YAML data.
     * @throws YamlException If the file is not found.
     * @access private
     */
    private function _loadFile($input)
    {
        if (file_exists($input)) {
            $string = file_get_contents($input);
            $source = $this->loadFromString($string);
            return $this->loadWithSource($source);
        } else {
            throw new YamlException("File $input not found");
        }
    }

    /**
     * Loads a YAML string and parses it into an array.
     * 
     * @param string $input The YAML string.
     * @return array The parsed YAML data.
     * @access private
     */
    private function _loadString($input)
    {
        $source = $this->loadFromString($input);
        return $this->loadWithSource($source);
    }

    /**
     * Processes a source array of lines and converts it to a PHP array.
     * 
     * @param array $source The source lines.
     * @return array The parsed result as an associative array.
     * @access private
     */
    private function loadWithSource($source) // NOSONAR
    {
        if (empty($source)) {
            return [];
        }

        $this->path = array();
        $this->result = array();

        $cnt = count($source);
        for ($i = 0; $i < $cnt; $i++) {
            $line = $source[$i];
            $this->indent = strlen($line) - strlen(ltrim($line));
            $tempPath = $this->getParentPathByIndent($this->indent);
            $line = self::stripIndent($line, $this->indent);

            if (self::isComment($line) || self::isEmpty($line)) {
                continue;
            }

            $this->path = $tempPath;

            // Handle literal blocks (| or > style)
            $literalBlockStyle = self::startsLiteralBlock($line);
            if ($literalBlockStyle) {
                $line = rtrim($line, $literalBlockStyle . " \n");
                $literalBlock = '';
                $literalBlockIndent = strlen($source[$i + 1]) - strlen(ltrim($source[$i + 1]));
                while (++$i < $cnt && $this->literalBlockContinues($source[$i], $this->indent)) { // NOSONAR
                    $literalBlock = $this->addLiteralLine($literalBlock, $source[$i], $literalBlockStyle, $literalBlockIndent);
                }
                $i--; // NOSONAR
                // Step back one line.
            }

            // Strip inline comments.
            if (strpos($line, '#') !== false) {
                $line = preg_replace('/\s*#([^"\']+)$/', '', $line);
            }

            // Concatenate multiline YAML values.
            while (++$i < $cnt && self::greedilyNeedNextLine($line)) { // NOSONAR
                $line = rtrim($line, " \n\t\r") . ' ' . ltrim($source[$i], " \t");
            }
            $i--; // NOSONAR
            // Step back one line.

            $lineArray = $this->_parseLine($line);

            if ($literalBlockStyle) {
                $lineArray = $this->revertLiteralPlaceHolder($lineArray, $literalBlock);
            }

            $this->addArray($lineArray, $this->indent);

            // Handle delayed paths after adding the array.
            foreach ($this->delayedPath as $indent => $delayedPath) {
                $this->path[$indent] = $delayedPath;
            }

            $this->delayedPath = array();
        }

        return $this->result;
    }

    /**
     * Converts a YAML string into an array of lines, removing any trailing carriage returns.
     * 
     * @param string $input The YAML string.
     * @return array An array of lines from the YAML input.
     * @access private
     */
    private function loadFromString($input)
    {
        $lines = explode("\n", $input);
        foreach ($lines as $k => $_) {
            $lines[$k] = rtrim($_, "\r");
        }
        return $lines;
    }

    /**
     * Parses a single line of YAML and returns the appropriate PHP structure for it.
     *
     * This method determines the type of the line (mapped sequence, mapped value, array element, etc.)
     * and converts it into a structured array representation.
     * 
     * @param string $line A single line from the YAML input.
     * @return array The parsed representation of the line.
     * @access private
     */
    private function _parseLine($line) // NOSONAR
    {
        if (!$line) {
            return [];
        }
        $line = trim($line);
        if (!$line) {
            return [];
        }

        // Handle group nodes if present
        $group = $this->nodeContainsGroup($line);
        if ($group) {
            $this->addGroup($line, $group);
            $line = $this->stripGroup($line, $group);
        }

        // Determine and return the line type
        if ($this->startsMappedSequence($line)) {
            return $this->returnMappedSequence($line);
        }
        if ($this->startsMappedValue($line)) {
            return $this->returnMappedValue($line);
        }
        if ($this->isArrayElement($line)) {
            return $this->returnArrayElement($line);
        }
        if ($this->isPlainArray($line)) {
            return $this->returnPlainArray($line);
        }

        // Default to treating the line as a key-value pair
        return $this->returnKeyValuePair($line);
    }

    /**
     * Converts a YAML value string into the appropriate PHP type.
     *
     * Supports scalar values (strings, integers, floats, booleans, null), arrays, and objects.
     * Handles quoted strings, inline mappings (`{}`), and inline sequences (`[]`).
     * 
     * @param string $value The YAML value to convert.
     * @return mixed The converted value as a native PHP type.
     * @access private
     */
    private function _toType($value) // NOSONAR
    {
        if ($value === '') {
            return "";
        }

        if ($this->emptyHashAsObject && $value === '{}') {
            return new stdClass;
        }

        $firstCharacter = $value[0];
        $lastCharacter = substr($value, -1);

        // Handle quoted strings
        $isQuoted = false;
        if (($firstCharacter === '"' || $firstCharacter === "'") && $lastCharacter === $firstCharacter) {
            $isQuoted = true;
            $value = str_replace('\n', "\n", substr($value, 1, -1));
            return $firstCharacter === "'" 
                ? strtr($value, ["''" => "'", "\\'" => "'"]) 
                : strtr($value, ['\"' => '"', "\\'" => "'"]);
        }

        // Remove inline comments
        if (strpos($value, ' #') !== false && !$isQuoted) {
            $value = preg_replace('/\s+#(.+)$/', '', $value);
        }

        // Handle inline sequences: [val1, val2, val3]
        if ($firstCharacter === '[' && $lastCharacter === ']') {
            $innerValue = trim(substr($value, 1, -1));
            if ($innerValue === '') {
                return [];
            }
            $explode = $this->_inlineEscape($innerValue);
            return array_map([$this, '_toType'], $explode);
        }

        // Handle inline mappings: {key1: value1, key2: value2}
        if ($firstCharacter === '{' && $lastCharacter === '}') {
            $innerValue = trim(substr($value, 1, -1));
            if ($innerValue === '') {
                return [];
            }
            $explode = $this->_inlineEscape($innerValue);
            $array = array();
            foreach ($explode as $v) {
                $subArr = $this->_toType($v);
                if (empty($subArr)) {
                    continue;
                }
                if (is_array($subArr)) {
                    $array[key($subArr)] = current($subArr);
                } else {
                    $array[] = $subArr;
                }
            }
            return $array;
        }

        // Handle null values
        if (strtolower($value) === 'null' || $value === '' || $value === '~') {
            return null;
        }

        // Handle integers
        if (is_numeric($value) && preg_match('/^(-|)[1-9][0-9]*$/', $value)) // NOSONAR
        {
            $intValue = (int)$value;
            if ($intValue != PHP_INT_MAX && $intValue != ~PHP_INT_MAX) {
                return $intValue;
            }
            return $value;
        }

        // Handle hexadecimal values
        if (is_string($value) && preg_match('/^0[xX][0-9a-fA-F]+$/', $value)) {
            return hexdec($value);
        }

        // Coerce boolean and null-like values
        $this->coerceValue($value);

        // Handle floats
        if (is_numeric($value)) {
            return $value === '0' ? 0 : (float)$value;
        }

        // Return as a plain string if no other match
        return $value;
    }



    /**
     * Parses an inline YAML string and splits it into an array of elements.
     * 
     * Handles inline sequences (`[]`), mappings (`{}`), quoted strings, and empty strings,
     * ensuring they are correctly parsed without being broken by commas or special characters.
     * 
     * @param string $inline The inline YAML string.
     * @return array Parsed components as an array.
     * @access private
     */
    private function _inlineEscape($inline) // NOSONAR
    {
        // There's gotta be a cleaner way to do this...
        // While pure sequences seem to be nesting just fine,
        // pure mappings and mappings with sequences inside can't go very
        // deep.  This needs to be fixed.

        $seqs = array();
        $maps = array();
        $savedStrings = array();
        $savedEmpties = array();

        // Check for empty strings
        $regex = '/("")|(\'\')/';
        if (preg_match_all($regex, $inline, $strings)) {
            $savedEmpties = $strings[0];
            $inline  = preg_replace($regex, 'YAMLEmpty', $inline);
        }
        unset($regex);

        // Check for strings
        $regex = '/(?:(")|(?:\'))((?(1)[^"]+|[^\']+))(?(1)"|\')/';
        if (preg_match_all($regex, $inline, $strings)) {
            $savedStrings = $strings[0];
            $inline  = preg_replace($regex, '___YAML_String', $inline);
        }
        unset($regex);

        $i = 0;
        do {

            // Check for sequences
            while (preg_match('/\[([^{}\[\]]+)\]/U', $inline, $matchseqs)) {
                $seqs[] = $matchseqs[0];
                $inline = preg_replace('/\[([^{}\[\]]+)\]/U', ('___YAML_Seq' . (count($seqs) - 1) . 's'), $inline, 1);
            }

            // Check for mappings
            while (preg_match('/{([^\[\]{}]+)}/U', $inline, $matchmaps)) {
                $maps[] = $matchmaps[0];
                $inline = preg_replace('/{([^\[\]{}]+)}/U', ('___YAML_Map' . (count($maps) - 1) . 's'), $inline, 1);
            }

            if ($i++ >= 10) {
                break;
            }
        } while (strpos($inline, '[') !== false || strpos($inline, '{') !== false);

        $explode = explode(',', $inline);
        $explode = array_map('trim', $explode);
        $stringi = 0;
        $i = 0;

        while (1) {

            // Re-add the sequences
            if (!empty($seqs)) {
                foreach ($explode as $key => $value) {
                    if (strpos($value, '___YAML_Seq') !== false) {
                        foreach ($seqs as $seqk => $seq) {
                            $explode[$key] = str_replace(('___YAML_Seq' . $seqk . 's'), $seq, $value);
                            $value = $explode[$key];
                        }
                    }
                }
            }

            // Re-add the mappings
            if (!empty($maps)) {
                foreach ($explode as $key => $value) {
                    if (strpos($value, '___YAML_Map') !== false) {
                        foreach ($maps as $mapk => $map) {
                            $explode[$key] = str_replace(('___YAML_Map' . $mapk . 's'), $map, $value);
                            $value = $explode[$key];
                        }
                    }
                }
            }


            // Re-add the strings
            if (!empty($savedStrings)) {
                foreach ($explode as $key => $value) {
                    while (strpos($value, '___YAML_String') !== false) {
                        $explode[$key] = preg_replace('/___YAML_String/', $savedStrings[$stringi], $value, 1);
                        unset($savedStrings[$stringi]);
                        ++$stringi;
                        $value = $explode[$key];
                    }
                }
            }


            // Re-add the empties
            if (!empty($savedEmpties)) {
                foreach ($explode as $key => $value) {
                    while (strpos($value, 'YAMLEmpty') !== false) {
                        $explode[$key] = preg_replace('/YAMLEmpty/', '', $value, 1);
                        $value = $explode[$key];
                    }
                }
            }

            $finished = true;
            foreach ($explode as $key => $value) {
                if (strpos($value, '___YAML_Seq') !== false) {
                    $finished = false;
                    break;
                }
                if (strpos($value, '___YAML_Map') !== false) {
                    $finished = false;
                    break;
                }
                if (strpos($value, '___YAML_String') !== false) {
                    $finished = false;
                    break;
                }
                if (strpos($value, 'YAMLEmpty') !== false) {
                    $finished = false;
                    break;
                }
            }
            if ($finished) {
                break;
            }

            $i++;
            if ($i > 10) {
                break; // Prevent infinite loops.
            }
        }


        return $explode;
    }

    /**
     * Checks if a literal block should continue on the next line.
     *
     * A literal block continues if the current line is blank or if it is more indented
     * than the block's initial indentation level.
     *
     * @param string $line The current line being processed.
     * @param int $lineIndent The initial indentation level of the block.
     * @return bool Returns true if the literal block should continue; otherwise, false.
     */
    private function literalBlockContinues($line, $lineIndent)
    {
        if (!trim($line)) {
            return true;
        }
        if (strlen($line) - strlen(ltrim($line)) > $lineIndent) {
            return true;
        }
        return false;
    }

    /**
     * Retrieves the value of a YAML reference (alias).
     *
     * The method looks up the alias in the saved groups and traverses the result array
     * using the group path to return the associated value.
     *
     * @param string $alias The alias name to look up.
     * @return mixed The value associated with the alias, or null if not found.
     * @throws Exception If the alias is not found in the saved groups.
     */
    private function referenceContentsByAlias($alias)
    {
        do {
            if (!isset($this->savedGroups[$alias])) {
                break;
            }
            $groupPath = $this->savedGroups[$alias];
            $value = $this->result;
            foreach ($groupPath as $k) {
                $value = $value[$k];
            }
        } while (false);
        return $value;
    }

    /**
     * Adds an array to the current path in an inline manner.
     *
     * This method is used to add multiple key-value pairs from the given array
     * at the specified indentation level while maintaining the current path.
     *
     * @param array $array The array to add.
     * @param int $indent The current indentation level.
     * @return bool Returns true if the array was added; otherwise, false.
     */
    private function addArrayInline($array, $indent)
    {
        $commonGroupPath = $this->path;
        if (empty($array)) 
        {
            return false;
        }

        foreach ($array as $k => $_) {
            $this->addArray(array($k => $_), $indent);
            $this->path = $commonGroupPath;
        }
        return true;
    }

    /**
     * Adds an array to the result at the specified indentation level.
     *
     * This method processes incoming data and integrates it into the result array
     * based on the current path and indentation. It also handles YAML anchors and aliases.
     *
     * @param array $incomingData The data to be added.
     * @param int $incomingIndent The indentation level of the incoming data.
     * @return $this Returns the current instance for method chaining.
     */
    private function addArray($incomingData, $incomingIndent) // NOSONAR
    {


        if (count($incomingData) > 1) {
            $this->addArrayInline($incomingData, $incomingIndent);
            return $this;
        }

        $key = key($incomingData);
        $value = isset($incomingData[$key]) ? $incomingData[$key] : null;
        if ($key === '___YAML_Zero') {
            $key = '0';
        }

        if ($incomingIndent == 0 && !$this->_containsGroupAlias && !$this->_containsGroupAnchor) { // Shortcut for root-level values.
            if ($key || $key === '' || $key === '0') {
                $this->result[$key] = $value;
            } else {
                $this->result[] = $value;
                end($this->result);
                $key = key($this->result);
            }
            $this->path[$incomingIndent] = $key;
            return $this;
        }



        $history = array();
        // Unfolding inner array tree.
        $history[] = $_arr = $this->result;
        foreach ($this->path as $k) {
            $history[] = $_arr = $_arr[$k];
        }

        if ($this->_containsGroupAlias) {
            $value = $this->referenceContentsByAlias($this->_containsGroupAlias);
            $this->_containsGroupAlias = false;
        }


        // Adding string or numeric key to the innermost level or $this->arr.
        if (is_string($key) && $key == '<<') {
            if (!is_array($_arr)) {
                $_arr = array();
            }

            $_arr = array_merge($_arr, $value);
        } else if ($key || $key === '' || $key === '0') {
            if (!is_array($_arr)) {
                $_arr = array($key => $value);
            } else
            {
                $_arr[$key] = $value;
            }
        } else {
            if (!is_array($_arr)) {
                $_arr = array($value);
                $key = 0;
            } else {
                $_arr[] = $value;
                end($_arr);
                $key = key($_arr);
            }
        }

        $reverse_path = array_reverse($this->path);
        $reverse_history = array_reverse($history);
        $reverse_history[0] = $_arr;
        $cnt = count($reverse_history) - 1;
        for ($i = 0; $i < $cnt; $i++) {
            $reverse_history[$i + 1][$reverse_path[$i]] = $reverse_history[$i];
        }
        $this->result = $reverse_history[$cnt];

        $this->path[$incomingIndent] = $key;

        if ($this->_containsGroupAnchor) {
            $this->savedGroups[$this->_containsGroupAnchor] = $this->path;
            if (is_array($value)) {
                $k = key($value);
                if (!is_int($k)) {
                    $this->savedGroups[$this->_containsGroupAnchor][$incomingIndent + 2] = $k;
                }
            }
            $this->_containsGroupAnchor = false;
        }
        return $this;
    }

    /**
     * Check if the line starts a literal block.
     *
     * This function checks if the line ends with a literal block indicator, either `|` or `>`.
     * It also excludes lines that contain HTML tags.
     *
     * @param string $line The line to check.
     * 
     * @return string|false Returns `|` or `>` if it starts a literal block, `false` otherwise.
     */
    private static function startsLiteralBlock($line) // NOSONAR
    {
        $lastChar = substr(trim($line), -1);
        if ($lastChar != '>' && $lastChar != '|') {
            return false;
        }
        if ($lastChar == '|') {
            return $lastChar;
        }
        // HTML tags should not be counted as literal blocks.
        if (preg_match('#<.*?>$#', $line)) {
            return false;
        }
        return $lastChar;
    }

    /**
     * Check if the next line is needed to complete the current one.
     *
     * This function determines whether a following line is required to complete
     * a structure, for instance when the line starts an array or contains a reference to one.
     *
     * @param string $line The line to analyze.
     * 
     * @return bool Returns `true` if the next line is needed, `false` otherwise.
     */
    private static function greedilyNeedNextLine($line) // NOSONAR
    {
        $line = trim($line);
        if (!strlen($line)) {
            return false;
        }
        if (substr($line, -1, 1) == ']') {
            return false;
        }
        if ($line[0] == '[') {
            return true;
        }
        if (preg_match('#^[^:]+?:\s*\[#', $line)) {
            return true;
        }
        return false;
    }

    /**
     * Add a literal line to a literal block.
     *
     * This function appends a line to the literal block. It handles indentation
     * and adjusts the line's content according to the specified literal block style (`|` or `>`).
     *
     * @param string $literalBlock The existing literal block content.
     * @param string $line The line to add.
     * @param string $literalBlockStyle The style of the literal block (`|` or `>`).
     * @param int $indent The indentation level to strip from the line (default: `-1`).
     * 
     * @return string The updated literal block with the added line.
     */
    private function addLiteralLine($literalBlock, $line, $literalBlockStyle, $indent = -1) // NOSONAR
    {
        $line = self::stripIndent($line, $indent);
        if ($literalBlockStyle !== '|') {
            $line = self::stripIndent($line);
        }
        $line = rtrim($line, "\r\n\t ") . "\n";
        if ($literalBlockStyle == '|') {
            return $literalBlock . $line;
        }
        if (strlen($line) == 0) {
            return rtrim($literalBlock, ' ') . "\n";
        }
        if ($line == "\n" && $literalBlockStyle == '>') {
            return rtrim($literalBlock, " \t") . "\n";
        }
        if ($line != "\n") {
            $line = trim($line, "\r\n ") . " ";
        }
        return $literalBlock . $line;
    }

    /**
     * Revert literal placeholders back to the full literal block.
     *
     * This function searches for literal placeholders in the provided lines and 
     * replaces them with the actual content of the literal block.
     *
     * @param array $lineArray The array of lines to process.
     * @param string $literalBlock The literal block content to insert.
     * 
     * @return array The updated line array with placeholders replaced.
     */
    function revertLiteralPlaceHolder($lineArray, $literalBlock) // NOSONAR
    {
        foreach ($lineArray as $k => $_) {
            if (is_array($_)) {
                $lineArray[$k] = $this->revertLiteralPlaceHolder($_, $literalBlock);
            } else if (substr($_, -1 * strlen($this->literalPlaceHolder)) == $this->literalPlaceHolder) {
                $lineArray[$k] = rtrim($literalBlock, " \r\n");
            }
        }
        return $lineArray;
    }

    /**
     * Strip indentation from a line.
     *
     * This function removes leading whitespace from a line based on the provided indentation level.
     *
     * @param string $line The line to strip.
     * @param int $indent The number of spaces to strip (default: `-1`).
     * 
     * @return string The line with indentation removed.
     */
    private static function stripIndent($line, $indent = -1)
    {
        if ($indent == -1) {
            $indent = strlen($line) - strlen(ltrim($line));
        }
        return substr($line, $indent);
    }

    /**
     * Get the parent path for a given indentation level.
     *
     * This function retrieves the path of the parent nodes based on the current indentation level.
     *
     * @param int $indent The current indentation level.
     * 
     * @return array The parent path for the given indentation level.
     */
    private function getParentPathByIndent($indent)
    {
        if ($indent == 0) {
            return array();
        }
        $linePath = $this->path;
        do {
            end($linePath);
            $lastIndentInParentPath = key($linePath);
            if ($indent <= $lastIndentInParentPath) {
                array_pop($linePath);
            }
        } while ($indent <= $lastIndentInParentPath);
        return $linePath;
    }

    /**
     * Check if the line is a comment.
     *
     * This function checks if a line is a comment, either starting with a `#` symbol
     * or containing a YAML document separator (`---`).
     *
     * @param string $line The line to check.
     * 
     * @return bool Returns `true` if the line is a comment, `false` otherwise.
     */
    private static function isComment($line) // NOSONAR
    {
        if (!$line) {
            return false;
        }
        if ($line[0] == '#') {
            return true;
        }
        if (trim($line, " \r\n\t") == '---') {
            return true;
        }
        return false;
    }

    /**
     * Check if the line is empty.
     *
     * This function checks if a line is empty after trimming leading and trailing whitespace.
     *
     * @param string $line The line to check.
     * 
     * @return bool Returns `true` if the line is empty, `false` otherwise.
     */
    private static function isEmpty($line)
    {
        return trim($line) === '';
    }

    /**
     * Check if the line is an array element.
     *
     * This function checks if a line starts with a dash (`- `), which is indicative
     * of an element in a YAML array.
     *
     * @param string $line The line to check.
     * 
     * @return bool Returns `true` if the line is an array element, `false` otherwise.
     */
    private function isArrayElement($line) // NOSONAR
    {
        if (!$line || !is_scalar($line)) {
            return false;
        }
        if (substr($line, 0, 2) != '- ') {
            return false;
        }
        if (strlen($line) > 3 && substr($line, 0, 3) == '---') {
            return false;
        }

        return true;
    }

    /**
     * Remove quotes from a value.
     *
     * This function removes single or double quotes from the start and end of a string, if present.
     *
     * @param string $value The value to unquote.
     * 
     * @return string The value without quotes.
     */
    private static function unquote($value) // NOSONAR
    {
        if (!$value) {
            return $value;
        }
        if (!is_string($value)) {
            return $value;
        }
        if ($value[0] == '\'') {
            return trim($value, '\'');
        }
        if ($value[0] == '"') {
            return trim($value, '"');
        }
        return $value;
    }

    /**
     * Check if the line starts a mapped sequence.
     *
     * This function checks if a line represents the start of a mapped sequence, 
     * indicated by a `-` followed by a key and a colon (`:`).
     *
     * @param string $line The line to check.
     * 
     * @return bool Returns `true` if the line starts a mapped sequence, `false` otherwise.
     */
    private function startsMappedSequence($line)
    {
        return substr($line, 0, 2) == '- ' && substr($line, -1, 1) == ':';
    }

    /**
     * Return the mapped sequence from a line.
     *
     * This function processes the line to extract the key and create a corresponding
     * array for a mapped sequence.
     *
     * @param string $line The line to process.
     * 
     * @return array The array representing the mapped sequence.
     */
    private function returnMappedSequence($line)
    {
        $array = array();
        $key = self::unquote(trim(substr($line, 1, -1)));
        $array[$key] = array();
        $this->delayedPath = array(strpos($line, $key) + $this->indent => $key);
        return array($array);
    }

    /**
     * Check if a value contains multiple keys.
     *
     * This function checks if the value contains more than one key, indicated
     * by the presence of a colon (`:`) in a non-array or non-object structure.
     *
     * @param string $value The value to check.
     * 
     * @throws YamlException If there are too many keys in the value.
     */
    private function checkKeysInValue($value)
    {
        if (strchr('[{"\'', $value[0]) === false && strchr($value, ': ') !== false) {
            throw new YamlException('Too many keys: ' . $value);
        }
    }

    /**
     * Return a mapped value from a line.
     *
     * This function processes the line and returns a key-value pair for a mapped value.
     *
     * @param string $line The line to process.
     * 
     * @return array The array with the key-value pair.
     */
    private function returnMappedValue($line)
    {
        $this->checkKeysInValue($line);
        $array = array();
        $key         = self::unquote(trim(substr($line, 0, -1)));
        $array[$key] = '';
        return $array;
    }


    /**
     * Check if the line represents a mapped value (ends with a colon).
     *
     * This function checks whether the line ends with a colon (`:`), indicating that
     * it is likely a mapped value in the YAML format.
     *
     * @param string $line The line of text to check.
     * 
     * @return bool Returns `true` if the line ends with a colon, `false` otherwise.
     */
    private function startsMappedValue($line)
    {
        return substr($line, -1, 1) == ':';
    }

    /**
     * Check if the line represents a plain array.
     *
     * This function checks whether the line starts with an opening bracket (`[`) 
     * and ends with a closing bracket (`]`), which is characteristic of a plain array 
     * in YAML.
     *
     * @param string $line The line of text to check.
     * 
     * @return bool Returns `true` if the line is a plain array, `false` otherwise.
     */
    private function isPlainArray($line)
    {
        return $line[0] == '[' && substr($line, -1, 1) == ']';
    }

    /**
     * Return the parsed value of a plain array.
     *
     * This function processes the line and converts it to its appropriate data type 
     * based on the provided method `_toType`.
     *
     * @param string $line The line representing the array element to be processed.
     * 
     * @return mixed The parsed value after conversion using `_toType`.
     */
    private function returnPlainArray($line)
    {
        return $this->_toType($line);
    }

    /**
     * Return a key-value pair from a line.
     *
     * This function extracts a key-value pair from a line. It handles cases where 
     * the key is wrapped in quotes and also checks for special cases like `0` as key.
     * The value is processed to determine its data type.
     *
     * @param string $line The line to parse.
     * 
     * @return array Returns an associative array with the key and value.
     */
    private function returnKeyValuePair($line)
    {
        $array = array();
        $key = '';
        if (strpos($line, ': ')) {
            // It's a key/value pair most likely
            // If the key is in double quotes pull it out
            if (($line[0] == '"' || $line[0] == "'") && preg_match('/^(["\'](.*)["\'](\s)*:)/', $line, $matches)) {
                $value = trim(str_replace($matches[1], '', $line));
                $key   = $matches[2];
            } else {
                // Do some guesswork as to the key and the value
                $explode = explode(': ', $line);
                $key     = trim(array_shift($explode));
                $value   = trim(implode(': ', $explode));
                $this->checkKeysInValue($value);
            }
            // Set the type of the value.  Int, string, etc
            $value = $this->_toType($value);

            if ($key === '0') {
                $key = '___YAML_Zero';
            }
            $array[$key] = $value;
        } else {
            $array = array($line);
        }
        return $array;
    }

    /**
     * Return an array element from the line.
     *
     * This function processes the line and trims the first character (usually a dash) 
     * before converting it to its appropriate type. It also checks if the value is 
     * an array and recursively handles nested elements.
     *
     * @param string $line The line to process.
     * 
     * @return array Returns an array with the processed value.
     */
    private function returnArrayElement($line)
    {
        if (strlen($line) <= 1) {
            return array(array()); // Weird %)
        }
        $array = array();
        $value = trim(substr($line, 1));
        $value = $this->_toType($value);
        if ($this->isArrayElement($value)) {
            $value = $this->returnArrayElement($value);
        }
        $array[] = $value;
        return $array;
    }

    /**
     * Check if the line contains a group anchor or alias.
     *
     * This function checks if a line contains a reference to a group anchor (denoted 
     * by `&`) or alias (denoted by `*`). It can match anchors or aliases at the 
     * beginning or end of the line, as well as inline references such as `<<`.
     *
     * @param string $line The line to check for group anchors or aliases.
     * 
     * @return string|false Returns the group reference (anchor or alias) if found, 
     *         or `false` if no group reference is present.
     */
    private function nodeContainsGroup($line) // NOSONAR
    {
        $symbolsForReference = 'A-z0-9_\-';
        if (strpos($line, '&') === false && strpos($line, '*') === false) {
            return false; // Please die fast ;-)
        }
        if ($line[0] == '&' && preg_match('/^(&[' . $symbolsForReference . ']+)/', $line, $matches)) {
            return $matches[1];
        }
        if ($line[0] == '*' && preg_match('/^(\*[' . $symbolsForReference . ']+)/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(&[' . $symbolsForReference . ']+)$/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(\*[' . $symbolsForReference . ']+$)/', $line, $matches)) {
            return $matches[1];
        }
        if (preg_match('#^\s*<<\s*:\s*(\*[^\s]+).*$#', $line, $matches)) {
            return $matches[1];
        }
        return false;
    }


    /**
     * Add a group anchor or alias to the internal properties.
     *
     * This function processes a group identifier (anchor or alias) and stores it in 
     * the appropriate internal property, depending on whether it's an anchor ('&') 
     * or an alias ('*').
     *
     * @param string $line The current line being processed (unused in this method).
     * @param string $group The group string which can be an anchor or alias.
     * 
     * @return void
     */
    private function addGroup($line, $group) // NOSONAR
    {
        if ($group[0] == '&') {
            $this->_containsGroupAnchor = substr($group, 1);
        }
        if ($group[0] == '*') {
            $this->_containsGroupAlias = substr($group, 1);
        }
    }

    /**
     * Strip a group identifier from a line of text.
     *
     * This function removes the specified group identifier (anchor or alias) from 
     * the line and trims any extra spaces from the result.
     *
     * @param string $line The line of text to process.
     * @param string $group The group identifier to remove from the line.
     * 
     * @return string The line with the group identifier removed and trimmed.
     */
    private function stripGroup($line, $group)
    {
        $line = trim(str_replace($group, '', $line));
        return $line;
    }

    /**
     * Get the flag determining whether quotes are forced in the dump.
     *
     * By default, the value is `false`. This flag indicates whether strings should 
     * always be wrapped in quotes during the dumping process.
     *
     * @return bool The current setting of the flag for forced quotes.
     */
    public function getDumpForceQuotes()
    {
        return $this->dumpForceQuotes;
    }

    /**
     * Set the flag for forcing quotes in the dump.
     *
     * This function allows you to specify whether strings should always be enclosed 
     * in quotes during the dumping process.
     *
     * @param bool $dumpForceQuotes The value indicating whether to force quotes 
     *                               (true for forced quotes, false otherwise).
     * 
     * @return self The current instance to allow method chaining.
     */
    public function setDumpForceQuotes($dumpForceQuotes)
    {
        $this->dumpForceQuotes = $dumpForceQuotes;

        return $this;
    }

    /**
     * Get the flag determining whether empty hashes are treated as objects.
     *
     * By default, this value is `false`. This flag specifies whether empty hash 
     * structures should be interpreted as objects instead of arrays.
     *
     * @return bool The current setting of the flag for empty hash as object.
     */
    public function getEmptyHashAsObject()
    {
        return $this->emptyHashAsObject;
    }

    /**
     * Set the flag for treating empty hashes as objects.
     *
     * This function allows you to specify whether empty hash structures should be 
     * considered as objects (true) or as empty arrays (false).
     *
     * @param bool $emptyHashAsObject The value to indicate whether empty hashes 
     *                                should be treated as objects.
     * 
     * @return self The current instance to allow method chaining.
     */
    public function setEmptyHashAsObject($emptyHashAsObject)
    {
        $this->emptyHashAsObject = $emptyHashAsObject;

        return $this;
    }

}
