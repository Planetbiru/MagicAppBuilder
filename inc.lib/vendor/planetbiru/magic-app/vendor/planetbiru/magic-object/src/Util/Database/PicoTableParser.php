<?php

namespace MagicObject\Util\Database;

use Exception;

/**
 * The PicoTableParser class is responsible for parsing SQL CREATE TABLE statements to extract
 * table and column information. It validates data types, handles constraints like primary
 * keys and auto-increment fields, and processes table comments and default values.
 * This class can handle multiple SQL statements, parsing them into individual queries, 
 * and extracting structured information about the database schema from SQL scripts.
 * 
 * The class supports parsing complex SQL table definitions, including field types,
 * constraints (e.g., primary keys, auto-increments), and special data types (e.g., ENUM, SET, JSON).
 * Additionally, it provides methods to normalize and format SQL defaults, handle multi-line SQL statements,
 * and retrieve detailed schema information for each parsed table.
 * 
 * Example usage:
 * $parser = new PicoTableParser($sql);
 * $tableInfo = $parser->getResult();
 * 
 * The returned result contains structured information about each table, including
 * columns, their types, constraints, and additional attributes.
 * 
 * @package MagicObject\Util\Database
 */
class PicoTableParser {

    /**
     * List of valid SQL data types used for column validation.
     *
     * @var array
     */
    private $typeList = array();

    /**
     * Holds information about the parsed tables, including columns and their properties.
     *
     * @var array
     */
    private $tableInfo = array();

    /**
     * PicoTableParser constructor.
     *
     * Initializes the PicoTableParser instance and optionally parses an SQL string.
     * 
     * @param string|null $sql Optional SQL string to parse during initialization.
     */
    public function __construct($sql = null) {
        $this->init();

        if ($sql) {
            $this->parseAll($sql);
        }
    }

    /**
     * Initializes the type list for valid SQL column types.
     *
     * This function sets up an array of valid SQL column types, which will be used
     * to validate column data types during parsing.
     */
    private function init() {
        $typeList = 'TIMESTAMPTZ,TIMESTAMP,SERIAL4,BIGSERIAL,INT2,INT4,INT8,TINYINT,BIGINT,LONGTEXT,MEDIUMTEXT,TEXT,NVARCHAR,VARCHAR,ENUM,SET,NUMERIC,DECIMAL,CHAR,REAL,FLOAT,INTEGER,INT,DATETIME,DATE,DOUBLE,BOOLEAN,BOOL,TIME,UUID,MONEY,BLOB,BIT,JSON';
        $this->typeList = explode(',', $typeList);
    }

    /**
     * Checks if a value exists in an array.
     *
     * @param array $haystack The array to search.
     * @param string $needle The value to search for.
     * @return bool Returns true if the needle is found in the haystack, otherwise false.
     */
    private function inArray($haystack, $needle) {
        return in_array($needle, $haystack);
    }

    /**
     * Checks if a field is a primary key.
     *
     * @param string $field The field definition.
     * @return bool True if the field is a primary key, otherwise false.
     */
    private function isPrimaryKey($field) {
        $f = strtoupper(trim(preg_replace('/\s+/', ' ', $field))); // NOSONAR
        return strpos($f, 'PRIMARY KEY') !== false;
    }

    /**
     * Checks if a field is auto-incremented.
     *
     * @param string $line The field definition.
     * @return bool True if the field is auto-incremented, otherwise false.
     */
    private function isAutoIncrement($line) {
        $f = strtoupper(trim(preg_replace('/\s+/', ' ', $line)));
        return strpos($f, 'AUTO_INCREMENT') !== false || 
               strpos($f, 'SERIAL') !== false || 
               strpos($f, 'BIGSERIAL') !== false || 
               strpos($f, 'NEXTVAL') !== false;
    }

    /**
     * Parses a CREATE TABLE SQL statement and extracts table and column information.
     *
     * @param string $sql The SQL string representing a CREATE TABLE statement.
     * @return array An array containing table name, columns, and primary key information.
     */
    public function parseTable($sql) // NOSONAR
    {
        $rg_tb = '/(create\s+table\s+if\s+not\s+exists|create\s+table)\s(?<tb>.*)\s\(/i';
        $rg_fld = '/(\w+\s+key.*|\w+\s+bigserial|\w+\s+serial4|\w+\s+serial8|\w+\s+tinyint.*|\w+\s+bigint.*|\w+\s+longtext.*|\w+\s+mediumtext.*|\w+\s+text.*|\w+\s+nvarchar.*|\w+\s+varchar.*|\w+\s+char.*|\w+\s+real.*|\w+\s+float.*|\w+\s+integer.*|\w+\s+int.*|\w+\s+datetime.*|\w+\s+date.*|\w+\s+double.*|\w+\s+timestamp.*|\w+\s+timestamptz.*|\w+\s+boolean.*|\w+\s+bool.*|\w+\s+enum\s*\(.*\)|\w+\s+set\s*\(.*\)|\w+\s+numeric\s*\(.*\)|\w+\s+decimal\s*\(.*\)|\w+\s+int2.*|\w+\s+int4.*|\w+\s+int8.*|\w+\s+time.*|\w+\s+uuid.*|\w+\s+money.*|\w+\s+blob.*|\w+\s+bit.*|\w+\s+json.*)/i'; // NOSONAR
        $rg_fld2 = '/(?<fname>\w+)\s+(?<ftype>\w+)(?<fattr>.*)/i';
        $rg_enum = '/enum\s*\(([^)]+)\)/i';
        $rg_set = '/set\s*\(([^)]+)\)/i';
        $rg_not_null = '/not\s+null/i';
        $rg_pk = '/primary\s+key/i';
        $rg_fld_def = '/default\s+([^\'"]+|\'[^\']*\'|\"[^\"]*\")\s*(comment\s+\'[^\']*\')?/i';
        $rg_fld_comment = '/COMMENT\s*\'([^\']*)\'/i';
        $rg_pk2 = '/(PRIMARY|UNIQUE) KEY[a-zA-Z_0-9\s]+\(([a-zA-Z_0-9,\s]+)\)/i'; // NOSONAR
        
        preg_match($rg_tb, $sql, $result);
        $tableName = $result['tb'];

        $fieldList = [];
        $primaryKey = null;
        $columnList = [];
        $primaryKeyList = [];

        preg_match_all($rg_fld, $sql, $matches);

        foreach ($matches[0] as $f) {
            $line = $f;
            preg_match($rg_fld2, $f, $fld_def);
            $dataTypeRaw = $fld_def[0];
            $dataType = $fld_def['ftype'];
            $dataTypeOriginal = $dataType;
            $isPk = false;
            $enumValues = null;
            $enumArray = null;

            if (preg_match($rg_enum, $dataTypeRaw, $matches)) {
                $enumValues = $matches[1];
                $enumArray = array_map('trim', explode(',', $enumValues));
            }
            if (preg_match($rg_set, $dataTypeRaw, $matches)) {
                $enumValues = $matches[1];
                $enumArray = array_map('trim', explode(',', $enumValues));
            }

            if ($this->isValidType($dataType) || $this->isValidType($dataTypeOriginal)) {
                $attr = trim(str_replace(',', '', $fld_def['fattr']));
                $nullable = !preg_match($rg_not_null, $attr);
                $attr2 = str_replace($rg_not_null, '', $attr);
                
                $isPk = preg_match($rg_pk, $attr2) || $this->isPrimaryKey($line);
                $isAi = $this->isAutoIncrement($line);

                preg_match($rg_fld_def, $attr2, $def);
                $defaultValue = isset($def[1]) ? trim($def[1]) : null;
                $defaultValue = $this->fixDefaultValue($defaultValue);

                preg_match($rg_fld_comment, $attr2, $cmn);
                $comment = isset($cmn[1]) ? trim($cmn[1]) : null;

                $dataType = trim($dataType);
                $length = $this->getLength($attr);

                $columnName = trim($fld_def['fname']);
                if ($isPk) 
                {
                    $primaryKeyList[] = $columnName;
                }
                if (!in_array($columnName, $columnList)) {
                    $fieldList[] = [
                        'Field' => $columnName,
                        'Type' => $dataType,
                        'Length' => $length,
                        'Key' => $isPk,
                        'Nullable' => $nullable,
                        'Default' => $defaultValue,
                        'AutoIncrement' => $isAi,
                        'EnumValues' => $enumArray,
                        'Comment' => $comment
                    ];
                    $columnList[] = $columnName;
                }
            } else if ($this->isPrimaryKey($line)) {
                preg_match('/\((.*)\)/', $f, $matches);
                if ($primaryKey == null) {
                    $primaryKey = isset($matches[1]) ? $matches[1] : null;
                }
            }

            if ($primaryKey != null) {
                $primaryKey = str_replace(['(', ')'], '', $primaryKey);
                foreach ($fieldList as &$column) // NOSONAR
                {
                    if ($column['Field'] == $primaryKey) {
                        $column['Key'] = true;
                    }
                }
            }

            if (preg_match($rg_pk2, $f) && preg_match($rg_pk, $f)) {
                $x = str_replace(preg_match($rg_pk, $f)[0], '', $f);
                $x = str_replace(['(', ')'], '', $x);
                $pkeys = array_map('trim', explode(',', $x));
                foreach ($fieldList as &$column) {
                    if (in_array($column['Field'], $pkeys)) {
                        $column['Key'] = true;
                    }
                }
            }
        }

        if ($primaryKey == null) {
            $primaryKey = $primaryKeyList[0] ?? null;
        }

        return ['tableName' => $tableName, 'columns' => $fieldList, 'primaryKey' => $primaryKey];
    }

    /**
     * Fixes and normalizes default values in SQL statements.
     *
     * @param string $defaultValue The raw default value from SQL.
     * @return string|null The normalized default value or null if empty.
     */
    private function fixDefaultValue($defaultValue) {
        $defaultValue = trim($defaultValue);
        return empty($defaultValue) || $defaultValue == 'null' ? null : $defaultValue;
    }

    /**
     * Validates if a type is one of the known types.
     *
     * @param string $type The data type to validate.
     * @return bool True if the type is valid, otherwise false.
     */
    private function isValidType($type) {
        return $this->inArray($this->typeList, $type);
    }

    /**
     * Extracts the length for types that require it, such as varchar.
     *
     * @param string $attr The attributes of the field.
     * @return int|null The length of the field or null if not applicable.
     */
    private function getLength($attr) {
        preg_match('/\((\d+)\)/', $attr, $matches);
        return isset($matches[1]) ? (int) trim($matches[1], '()') : null;
    }

    /**
     * Parses multiple SQL statements.
     *
     * @param string $sql The SQL statements.
     */
    public function parseAll($sql) {
        
        $tables = $this->parseSQL($sql);

        foreach ($tables as $table) {
            if (isset($table['query'])) {
                try
                {
                    $info = $this->parseTable($table['query']);

                    $this->tableInfo[] = $info;
                }
                catch(Exception $e)
                {
                    // Do nothing
                }
            }
        }
    }

    /**
     * Parses a SQL text, splits it into individual SQL queries, and processes them by handling 
     * delimiters, comments, and empty lines. It returns an array of queries, each with its delimiter.
     *
     * This method processes the SQL text by:
     * 1. Normalizing newlines.
     * 2. Removing comment lines and empty lines.
     * 3. Splitting the SQL text into queries based on the delimiter.
     * 4. Handling multiple queries that may span across multiple lines.
     * 5. Changing the delimiter if a new one is specified within the SQL text.
     * 
     * @param string $sqlText The input SQL text to be parsed.
     * 
     * @return array An array of queries, each containing the SQL query string and its corresponding delimiter.
     *               Each item in the array is an associative array with two keys:
     *               - 'query': the SQL query string.
     *               - 'delimiter': the delimiter used for that query (e.g., ";").
     */
    public function parseSQL($sqlText)  // NOSONAR
    {
        $sqlText = str_replace("\n", "\r\n", $sqlText);
        $sqlText = str_replace("\r\r\n", "\r\n", $sqlText);
        $arr = explode("\r\n", $sqlText);
        $arr2 = array();
        foreach($arr as $key=>$val)
        {
            $arr[$key] = ltrim($val);
            if (stripos($arr[$key], "-- ") !== 0 && $arr[$key] !== "--" && $arr[$key] !== "") {
                $arr2[] = $arr[$key];
            }
        }
        $arr = $arr2;
        unset($arr2);
        
        $append = 0;
        $skip = 0;
        $start = 1;
        $nquery = -1;
        $delimiter = ";";
        $query_array = array();
        $delimiter_array = array();
        
        foreach($arr as $line=>$text)
        {
            if($text == "" && $append == 1)
            {
                $query_array[$nquery] .= "\r\n";
            }
            if($append == 0)
            {
                if(stripos(ltrim($text, " \t "), "--") === 0)
                {
                    $skip = 1;
                    $nquery++;
                    $start = 1;
                    $append = 0;
                }
                else
                {
                    $skip = 0;
                }
            }
            if($skip == 0)
            {
                if($start == 1)
                {
                    $nquery++;
                    $query_array[$nquery] = "";
                    $delimiter_array[$nquery] = $delimiter;
                    $start = 0;
                }
                $query_array[$nquery] .= $text."\r\n";
                $delimiter_array[$nquery] = $delimiter;
                $text = ltrim($text, " \t ");
                $start = strlen($text)-strlen($delimiter)-1;
                if(stripos(substr($text, $start), $delimiter) !== false || $text == $delimiter)
                {
                    $nquery++;
                    $start = 1;
                    $append = 0;
                }
                else
                {
                    $start = 0;
                    $append = 1;
                }
                $delimiter_array[$nquery] = $delimiter;
                if(stripos($text, "delimiter ") !== false)
                {
                    $text = trim(preg_replace("/\s+/"," ",$text));
                    $arr2 = explode(" ", $text);
                    $delimiter = $arr2[1];
                    $nquery++;
                    $delimiter_array[$nquery] = $delimiter;
                    $start = 1;
                    $append = 0;
                }
            }
        }
        $result = array();
        foreach($query_array as $line=>$sql)
        {
            $delimiter = $delimiter_array[$line];
            if (stripos($sql, "delimiter ") !== 0) {
                $sql = rtrim($sql, " \r\n\t ");
                $sql = substr($sql, 0, strlen($sql) - strlen($delimiter));			
                $result[] = array("query" => $sql, "delimiter" => $delimiter);
            }
        }
        return $result;
    }

    /**
     * Returns the table information parsed from the SQL statements.
     *
     * @return array An array of parsed table information.
     */
    public function getTableInfo() {
        return $this->tableInfo;
    }

     /**
     * Get the value of tableInfo.
     *
     * @return array The table information.
     */ 
    public function getResult()
    {
        return $this->getTableInfo();
    }
}
