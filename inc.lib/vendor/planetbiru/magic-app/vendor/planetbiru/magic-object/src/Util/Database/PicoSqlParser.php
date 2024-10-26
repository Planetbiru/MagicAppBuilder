<?php

namespace MagicObject\Util\Database;

/**
 * Class PicoSqlParser
 * 
 * This class is used to parse SQL table definitions and extract information about columns,
 * data types, primary keys, and other attributes from SQL CREATE TABLE statements.
 * 
 * Usage example:
 * $parser = new PicoSqlParser($sql);
 * $result = $parser->getResult();
 * 
 * @property array $typeList List of valid SQL data types supported for parsing.
 * @property array $tableInfo Information about the parsed tables, including 
 * columns and primary keys.
 * @link https://github.com/Planetbiru/ERD-Maker
 */
class PicoSqlParser
{
    const KEY_COLUMN_NAME = 'Column Name';
    const KEY_PRIMARY_KEY = 'Primary Key';
    const KEY_TYPE = 'Type';
    const KEY_LENGTH = 'Length';
    const KEY_NULLABLE = 'Nullable';
    const KEY_DEFAULT = 'Default';
    /**
     * Type list
     *
     * @var array
     */
    private $typeList = [];

    /**
     * Table info
     *
     * @var array
     */
    private $tableInfo = [];

    /**
     * PicoSqlParser constructor.
     *
     * Initializes the parser and optionally parses the provided SQL statement.
     *
     * @param string|null $sql SQL statement to be parsed (optional).
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
    public function parseTable($sql) //NOSONAR
    {
        $arr = explode(";", $sql);
        $sql = $arr[0];
        
        $rg_tb = '/(create\s+table\s+if\s+not\s+exists|create\s+table)\s+(?<tb>.*)\s+\(/i';
        $rg_fld = '/(\w+\s+key.*|\w+\s+bigserial|\w+\s+serial4|\w+\s+tinyint.*|\w+\s+bigint.*|\w+\s+text.*|\w+\s+varchar.*|\w+\s+char.*|\w+\s+real.*|\w+\s+float.*|\w+\s+integer.*|\w+\s+int.*|\w+\s+datetime.*|\w+\s+date.*|\w+\s+double.*|\w+\s+bigserial.*|\w+\s+serial.*|\w+\s+timestamp .*)/i'; //NOSONAR
        $rg_fld2 = '/(?<fname>\w+)\s+(?<ftype>\w+)(?<fattr>.*)/i';
        $rg_not_null = '/not\s+null/i';
        $rg_pk = '/primary\s+key/i';
        $rg_fld_def = '/default\s+(.+)/i';
        $rg_pk2 = '/(PRIMARY|UNIQUE) KEY\s+[a-zA-Z_0-9\s]+\(([a-zA-Z_0-9,\s]+)\)/i'; //NOSONAR

        preg_match($rg_tb, $sql, $result);
        $tableName = $result['tb'];

        $fld_list = [];
        $primaryKey = null;
        $columnList = [];

        preg_match_all($rg_fld, $sql, $matches);
        foreach ($matches[0] as $f) {
            $rg_fld2_result = [];
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
                $comment = null; //NOSONAR

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
                    $fld_list[] = [
                        self::KEY_COLUMN_NAME => $columnName,
                        self::KEY_TYPE => trim($rg_fld2_result['ftype']),
                        self::KEY_LENGTH => $length,
                        self::KEY_PRIMARY_KEY => $is_pk,
                        self::KEY_NULLABLE => $nullable,
                        self::KEY_DEFAULT => $def
                    ];
                    $columnList[] = $columnName;
                }
            } elseif (stripos($f, 'primary') !== false && stripos($f, 'key') !== false) {
                preg_match('/\((.*)\)/', $f, $matches);
                $primaryKey = isset($matches[1]) ? trim($matches[1]) : null;
            }

            if ($primaryKey !== null) {
                foreach ($fld_list as &$column) //NOSONAR
                {
                    if ($column[self::KEY_COLUMN_NAME] === $primaryKey) {
                        $column[self::KEY_PRIMARY_KEY] = true;
                    }
                }
            }

            if (preg_match($rg_pk2, $f) && preg_match($rg_pk, $f)) {
                $x = preg_replace('/(PRIMARY|UNIQUE) KEY\s+[a-zA-Z_0-9\s]+/', '', $f);
                $x = str_replace(['(', ')'], '', $x);
                $pkeys = array_map('trim', explode(',', $x));
                foreach ($fld_list as &$column) {
                    if ($this->inArray($pkeys, $column[self::KEY_COLUMN_NAME])) {
                        $column[self::KEY_PRIMARY_KEY] = true;
                    }
                }
            }
        }
        return [
            'tableName' => $tableName, 
            'columns' => $fld_list, 
            'primaryKey' => $primaryKey
        ];
    }

    /**
     * Gets the length of the column data type if there is a length definition.
     * 
     * @param string $text Text containing the data type definition.
     * @return string|null Length of the data type or null if not present.
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
     * Checks if the data type is valid.
     * 
     * @param string $dataType Data type to check.
     * @return bool True if the data type is valid, false otherwise.
     */
    private function isValidType($dataType)
    {
        return in_array($dataType, $this->typeList);
    }

    /**
     * Returns the result of the table parsing.
     * 
     * @return array Information about the parsed table.
     */
    public function getResult()
    {
        return $this->getTableInfo();
    }

    /**
     * Initializes the list of valid data types.
     */
    public function init()
    {
        $typeList = 'timestamp,serial4,bigserial,int2,int4,int8,tinyint,bigint,text,varchar,char,real,float,integer,int,datetime,date,double';
        $this->typeList = explode(',', $typeList);
    }

    /**
     * Parses all CREATE TABLE statements in the SQL text.
     * 
     * @param string $sql SQL statement to be parsed.
     * @return array
     */
    public function parseAll($sql)
    {
        $inf = [];
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
     * Gets the list of valid data types.
     *
     * @return array An array of valid SQL data types.
     */
    public function getTypeList()
    {
        return $this->typeList;
    }

    /**
     * Gets information about the parsed tables.
     *
     * @return array An array containing information about all parsed tables.
     */
    public function getTableInfo()
    {
        return $this->tableInfo;
    }
}
