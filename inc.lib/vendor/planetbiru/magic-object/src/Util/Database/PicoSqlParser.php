<?php

namespace MagicObject\Util\Database;

/**
 * Class PicoSqlParser
 * 
 * This class parses SQL `CREATE TABLE` statements to extract table information such as:
 * - Table name
 * - Column names
 * - Data types
 * - Column attributes (nullable, default value, etc.)
 * - Primary keys and other constraints
 *
 * This class is useful for generating database documentation, creating Entity Relationship Diagrams (ERDs), or analyzing database structures from SQL DDL scripts.
 * 
 * Example usage:
 * ```php
 * $parser = new PicoSqlParser($sql);
 * $result = $parser->getResult();
 * ```
 * 
 * @package MagicObject\Util\Database
 * @link https://github.com/Planetbiru/ERD-Maker
 */
class PicoSqlParser
{
    // Constant definitions for keys in the parsed table information
    const KEY_COLUMN_NAME = 'Field';
    const KEY_PRIMARY_KEY = 'Key';
    const KEY_TYPE        = 'Type';
    const KEY_LENGTH      = 'Length';
    const KEY_NULLABLE    = 'Nullable';
    const KEY_DEFAULT     = 'Default';
    
    /**
     * List of valid SQL data types supported by this parser.
     *
     * @var array
     */
    private $typeList = array();

    /**
     * Information about the parsed tables, including columns, data types, and primary keys.
     *
     * @var array
     */
    private $tableInfo = array();

    /**
     * Constructor to initialize the parser.
     * Optionally parses an SQL statement immediately upon instantiation.
     * 
     * @param string|null $sql The SQL statement to parse (optional).
     */
    public function __construct($sql = null)
    {
        $this->init();
        if ($sql !== null) {
            $this->parseAll($sql);
        }
    }

    /**
     * Checks if a specific element exists in the array.
     * 
     * @param array $haystack Array to search in.
     * @param mixed $needle Element to search for.
     * @return bool True if the element is found, false otherwise.
     */
    private function inArray($haystack, $needle)
    {
        return in_array($needle, $haystack);
    }

    /**
     * Parses a CREATE TABLE statement to extract table information.
     *
     * @param string $sql SQL statement to be parsed.
     * @return array Information about the table, columns, and primary key.
     * @throws InvalidArgumentException if the SQL statement is not a valid CREATE TABLE statement.
     */
    public function parseTable($sql) // NOSONAR
    {
        $arr = explode(";", $sql);
        $sql = $arr[0];
        
        $rg_tb = '/(create\s+table\s+if\s+not\s+exists|create\s+table)\s+(?<tb>.*)\s+\(/i';
        $rg_fld = '/(\w+\s+key.*|\w+\s+bigserial|\w+\s+serial4|\w+\s+tinyint.*|\w+\s+bigint.*|\w+\s+text.*|\w+\s+varchar.*|\w+\s+char.*|\w+\s+real.*|\w+\s+float.*|\w+\s+integer.*|\w+\s+int.*|\w+\s+datetime.*|\w+\s+date.*|\w+\s+double.*|\w+\s+bigserial.*|\w+\s+serial.*|\w+\s+timestamp .*)/i'; // NOSONAR
        $rg_fld2 = '/(?<fname>\w+)\s+(?<ftype>\w+)(?<fattr>.*)/i';
        $rg_not_null = '/not\s+null/i';
        $rg_pk = '/primary\s+key/i';
        $rg_fld_def = '/default\s+(.+)/i';
        $rg_pk2 = '/(PRIMARY|UNIQUE) KEY\s+[a-zA-Z_0-9\s]+\(([a-zA-Z_0-9,\s]+)\)/i'; // NOSONAR

        preg_match($rg_tb, $sql, $result);
        $tableName = $result['tb'];

        $fldList = array();
        $primaryKey = null;
        $columnList = array();

        preg_match_all($rg_fld, $sql, $matches);
        foreach ($matches[0] as $f) {
            $rg_fld2_result = array();
            preg_match($rg_fld2, $f, $rg_fld2_result);
            $dataType = $rg_fld2_result[2];
            $is_pk = false;

            if ($this->isValidType(strtolower($dataType))) {
                $attr = trim(str_replace(',', '', $rg_fld2_result['fattr']));
                $nullable = !preg_match($rg_not_null, $attr);
                $attr2 = preg_replace($rg_not_null, '', $attr);
                $is_pk = preg_match($rg_pk, $attr2);

                $def = null;
                preg_match($rg_fld_def, $attr2, $def);
                $comment = null; // NOSONAR

                if ($def) {
                    $def = trim($def[1]);
                    if (stripos($def, 'comment') !== false) {
                        $comment = substr($def, strpos($def, 'comment'));
                    }
                }

                $length = $this->getLength($attr);
                $columnName = trim($rg_fld2_result['fname']);

                if (!$this->inArray($columnList, $columnName)) {
                    if(isset($def) && is_array($def))
                    {
                        $def = null;
                    }
                    $fldList[] = array(
                        self::KEY_COLUMN_NAME => $columnName,
                        self::KEY_TYPE => trim($rg_fld2_result['ftype']),
                        self::KEY_LENGTH => $length,
                        self::KEY_PRIMARY_KEY => $is_pk,
                        self::KEY_NULLABLE => $nullable,
                        self::KEY_DEFAULT => $def
                    );
                    $columnList[] = $columnName;
                }
            } elseif (stripos($f, 'primary') !== false && stripos($f, 'key') !== false) {
                preg_match('/\((.*)\)/', $f, $matches);
                $primaryKey = isset($matches[1]) ? trim($matches[1]) : null;
            }

            if ($primaryKey !== null) {
                foreach ($fldList as &$column) // NOSONAR
                {
                    if ($column[self::KEY_COLUMN_NAME] === $primaryKey) {
                        $column[self::KEY_PRIMARY_KEY] = true;
                    }
                }
            }

            if (preg_match($rg_pk2, $f) && preg_match($rg_pk, $f)) {
                $x = preg_replace('/(PRIMARY|UNIQUE) KEY\s+[a-zA-Z_0-9\s]+/', '', $f);
                $x = str_replace(array('(', ')'), '', $x);
                $pkeys = array_map('trim', explode(',', $x));
                foreach ($fldList as &$column) {
                    if ($this->inArray($pkeys, $column[self::KEY_COLUMN_NAME])) {
                        $column[self::KEY_PRIMARY_KEY] = true;
                    }
                }
            }
        }
        return array(
            'tableName' => $tableName, 
            'columns' => $fldList, 
            'primaryKey' => $primaryKey
        );
    }

    /**
     * Extracts the length of a data type if it is defined in the SQL (e.g., `VARCHAR(100)`).
     * 
     * @param string $text The data type definition, e.g., `VARCHAR(100)`.
     * 
     * @return string|null Returns the length if defined (e.g., `100` for `VARCHAR(100)`), or `null` if not.
     */
    private function getLength($text)
    {
        if (strpos($text, '(') !== false && strpos($text, ')') !== false) {
            preg_match('/\((.*)\)/', $text, $matches);
            return isset($matches[1]) ? $matches[1] : null;
        }
        return '';
    }

    /**
     * Validates whether the provided data type is in the list of supported types.
     * 
     * @param string $dataType The data type to check (e.g., `int`, `varchar`).
     * 
     * @return bool Returns `true` if the data type is valid, `false` otherwise.
     */
    private function isValidType($dataType)
    {
        return in_array($dataType, $this->typeList);
    }

    /**
     * Returns the result of the most recent table parsing.
     * 
     * @return array The parsed table information (name, columns, primary keys).
     */
    public function getResult()
    {
        return $this->getTableInfo();
    }

    /**
     * Initializes the list of valid SQL data types supported by the parser.
     *
     * This method sets the list of data types that the parser recognizes, such as `varchar`, `int`, `timestamp`, `boolean`, 
     * `json`, `uuid`, etc. These data types correspond to common column types in various SQL-based databases like PostgreSQL, 
     * MySQL, and SQLite. The list may be used to validate or process SQL statements when parsing or generating SQL.
     *
     * @return void
     */
    public function init()
    {
        // List of valid SQL data types that the parser will recognize
        $typeList = 'timestamp,serial4,bigserial,int2,int4,int8,tinyint,bigint,text,varchar,char,real,float,integer,int,datetime,date,double,boolean,json,uuid,bytea,money,decimal,numeric,blob,clob';
        
        // Splitting the string into an array of data types
        $this->typeList = explode(',', $typeList);
    }


    /**
     * Parses all `CREATE TABLE` statements in the provided SQL text.
     * 
     * @param string $sql The SQL statements to parse (can contain multiple `CREATE TABLE` statements).
     * 
     * @return array An array of parsed tables with their columns and primary keys.
     */
    public function parseAll($sql)
    {
        $sql = str_replace("`", "", $sql);
        $inf = array();
        $rg_tb = '/(create\s+table\s+if\s+not\s+exists|create\s+table)\s+(?<tb>.*)\s+\(/i';
        
        preg_match_all($rg_tb, $sql, $matches);
        foreach ($matches[0] as $match) {
            $sub = substr($sql, strpos($sql, $match));
            $info = $this->parseTable($sub);
            $inf[] = $info;
        }
        
        $this->tableInfo = $inf;
        return $this->tableInfo;
    }

    /**
     * Returns the list of valid SQL data types that the parser recognizes.
     * 
     * @return array An array of valid SQL data types.
     */
    public function getTypeList()
    {
        return $this->typeList;
    }

    /**
     * Retrieves information about all the tables parsed.
     * 
     * @return array An array containing parsed information for all tables.
     */
    public function getTableInfo()
    {
        return $this->tableInfo;
    }

}
