<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Util\PicoStringUtil;

/**
 * PicoEntityGenerator is an entity generator for automatically generating PHP code.
 * This class is optimized for the MariaDB database.
 * Users must provide appropriate parameters so that the entity class can be directly used in the application.
 * 
 * @author Kamshory
 * @package MagicObject\Generator
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoEntityGenerator
{
    /**
     * Database connection instance.
     *
     * @var PicoDatabase
     */
    protected $database;

    /**
     * Base directory for generated files.
     *
     * @var string
     */
    protected $baseDir = "";

    /**
     * Base namespace for the entity classes.
     *
     * @var string
     */
    protected $baseNamespace = "";

    /**
     * Table name for which the entity is generated.
     *
     * @var string
     */
    protected $tableName = "";

    /**
     * Name of the entity being generated.
     *
     * @var string|null
     */
    protected $entityName = null;

    /**
     * Flag indicating whether to prettify the output.
     *
     * @var boolean
     */
    protected $prettify = false;

    /**
     * Constructor for the PicoEntityGenerator class.
     *
     * @param PicoDatabase $database Database connection
     * @param string $baseDir Base directory for generated files
     * @param string $tableName Table name for entity generation
     * @param string $baseNamespace Base namespace for the entity classes
     * @param string|null $entityName Name of the entity (optional)
     * @param bool $prettify Flag to prettify output (default: false)
     */
    public function __construct($database, $baseDir, $tableName, $baseNamespace, $entityName = null, $prettify = false)
    {
        $this->database = $database;
        $this->baseDir = $baseDir;
        $this->baseNamespace = $baseNamespace;
        $this->tableName = $tableName;
        $this->entityName = $entityName;
        $this->prettify = $prettify;
    }

    /**
     * Create a property with appropriate documentation based on database metadata.
     *
     * This method generates a PHP property with a docblock based on the given column information 
     * from the database. It includes annotations for the column attributes such as whether it is 
     * a primary key, auto-increment, nullable, etc.
     *
     * @param array $typeMap Mapping of database types to PHP types
     * @param array $columnMap Mapping of database column types to MySQL column types
     * @param array $row Data row from the database, typically from information_schema.columns
     * @param string[]|null $nonupdatables List of column names that are non-updatable, or null
     * @return string PHP code for the property with a docblock, including column attributes and annotations
     */
    protected function createProperty($typeMap, $columnMap, $row, $nonupdatables = null)
    {
        $columnName = $row['Field'];
        $columnType = $row['Type'];
        $columnKey = $row['Key'];
        $columnNull = $row['Null'];
        $columnDefault = $row['Default'];
        $columnExtra = $row['Extra'];

        $propertyName = PicoStringUtil::camelize($columnName);
        $description = $this->getPropertyName($columnName);
        $columnType = $this->getColumnType($columnMap, $columnType);
        $type = $this->getDataType($typeMap, $columnType);

        $docs = array();
        $docStart = "\t/**";
        $docEnd = "\t */";

        $docs[] = $docStart;
        $docs[] = "\t * $description";
        $docs[] = "\t * ";

        if (!empty($columnKey) && stripos($columnKey, "PRI") === 0) {
            $docs[] = "\t * @Id";
            if (stripos($columnExtra, "auto_increment") === false) {
                $docs[] = "\t * @GeneratedValue(strategy=GenerationType.UUID)";
            }
        }

        if (stripos($columnExtra, "auto_increment") !== false) {
            $docs[] = "\t * @GeneratedValue(strategy=GenerationType.IDENTITY)";
        }

        if (strcasecmp($columnNull, 'No') == 0) {
            $docs[] = "\t * @NotNull";
        }

        $attrs = array();
        $attrs[] = "name=\"$columnName\"";
        $attrs[] = "type=\"$columnType\"";
        $length = $this->getDataLength($columnType);
        if ($length > 0) {
            $attrs[] = "length=$length";
        }

        if (!empty($columnDefault)) {
            $attrs[] = "default_value=\"" . $columnDefault . "\"";
        }
        if (!empty($columnNull)) {
            $val = stripos($columnNull, "YES") === 0 ? "true" : "false";
            $attrs[] = "nullable=$val";
        }

        if (is_array($nonupdatables) && in_array($columnName, $nonupdatables)) {
            $attrs[] = "updatable=false";
        }

        if (!empty($columnExtra)) {
            $attrs[] = "extra=\"" . $columnExtra . "\"";
        }

        $docs[] = "\t * @Column(" . implode(", ", $attrs) . ")";
        if (!empty($columnDefault)) {
            $docs[] = "\t * @DefaultColumn(value=\"" . $columnDefault . "\")";
        }

        $docs[] = "\t * @Label(content=\"$description\")";
        $docs[] = "\t * @var $type";
        $docs[] = $docEnd;
        $prop = "\tprotected \$$propertyName;";
        return implode("\r\n", $docs) . "\r\n" . $prop . "\r\n";
    }

    /**
     * Get a descriptive name for the property based on the column name.
     *
     * @param string $name Original column name
     * @return string Formatted property name
     */
    protected function getPropertyName($name)
    {
        $arr = explode("_", $name);
        foreach ($arr as $k => $v) {
            $arr[$k] = ucfirst($v);
            $arr[$k] = str_replace("Id", "ID", $arr[$k]);
            $arr[$k] = str_replace("Ip", "IP", $arr[$k]);
        }
        return implode(" ", $arr);
    }

    /**
     * Get the corresponding PHP data type based on the column type.
     *
     * @param array $typeMap Mapping of database types to PHP types
     * @param string $columnType Database column type
     * @return string Corresponding PHP data type
     */
    protected function getColumnType($typeMap, $columnType)
    {
        $length = "";
        $pos = strpos($columnType, "(");
        if($pos !== false)
        {
            $length = substr($columnType, $pos);
        }
        $type = "";
        foreach ($typeMap as $key => $val) {
            if (stripos($columnType, $key) === 0) {
                $type = $val;
                break;
            }
        }
        return empty($type) ? "text" : $type.$length;
    }

    /**
     * Get the corresponding PHP data type based on the column type.
     *
     * @param array $typeMap Mapping of database types to PHP types
     * @param string $columnType Database column type
     * @return string Corresponding PHP data type
     */
    protected function getDataType($typeMap, $columnType)
    {
        $type = "";
        foreach ($typeMap as $key => $val) {
            if (stripos($columnType, $key) === 0) {
                $type = $val;
                break;
            }
        }
        return empty($type) ? "string" : $type;
    }

    /**
     * Get the length of the column based on its definition.
     *
     * @param string $str Column definition containing length
     * @return int Length of the column
     */
    protected function getDataLength($str)
    {
        $str2 = preg_replace('~\D~', '', $str);
        $length = empty($str2) ? 0 : (int)$str2;

        if (stripos($str, "datetime") !== false || stripos($str, "timestamp") !== false) {
            $length += 20;
            if ($length == 20) {
                $length = 19;
            }
        }
        return $length;
    }

    /**
     * Get a mapping of database types to PHP types for MySQL, PostgreSQL, and SQLite.
     *
     * This method returns an associative array that maps common database column types
     * from MySQL, PostgreSQL, and SQLite to their corresponding PHP types. This mapping
     * is useful for handling database type conversions when interacting with data in PHP.
     *
     * The array provides mappings for numeric types, string types, boolean types, 
     * date/time types, and special cases such as JSON and UUID. It helps in ensuring 
     * proper handling of database types across different database systems.
     *
     * @return array Associative array of type mappings where the keys are database column types
     *               and the values are corresponding PHP types.
     */
    protected function getTypeMap()
    {
        return array(
            // Numeric types
            "double" => "float",             // PostgreSQL: double precision
            "float" => "float",              // PostgreSQL: float
            "bigint" => "int",               // PostgreSQL: bigint
            "smallint" => "int",             // PostgreSQL: smallint
            "tinyint(1)" => "bool",          // MySQL-style, use boolean for tinyint(1)
            "tinyint" => "int",              // PostgreSQL/SQLite: tinyint, handled as INT
            "int" => "int",                  // PostgreSQL/SQLite: integer
            "serial" => "int",               // PostgreSQL: auto-increment integer (equivalent to INT)
            "bigserial" => "int",            // PostgreSQL: bigserial (auto-incrementing large integer, mapped to int in PHP)
            "mediumint" => "int",            // MySQL: mediumint (3-byte integer)
            "smallserial" => "int",          // PostgreSQL: smallserial (auto-incrementing small integer)
            "unsigned" => "int",             // MySQL: unsigned integer (mapped to int in PHP)

            // String types
            "nvarchar" => "string",          // SQLite: variable-length string
            "varchar" => "string",           // PostgreSQL: variable-length string
            "character varying" => "string", // PostgreSQL: character varying (same as varchar)
            "char" => "string",              // PostgreSQL: fixed-length string
            "text" => "string",              // PostgreSQL/SQLite: unlimited length string
            "varchar(255)" => "string",      // PostgreSQL: same as varchar without length
            "citext" => "string",            // PostgreSQL: case-insensitive text (equivalent to string)
            
            // MySQL-style text types (these types are similar to `text`)
            "tinytext" => "string",          // MySQL: tinytext
            "mediumtext" => "string",        // MySQL: mediumtext
            "longtext" => "string",          // MySQL: longtext
            "text" => "string",              // PostgreSQL/SQLite: text (string)

            // Boolean types
            "bool" => "bool",                // PostgreSQL: boolean
            "boolean" => "bool",             // PostgreSQL: boolean (same as bool)

            // Date/Time types
            "timestamp" => "string",         // PostgreSQL: timestamp (datetime)
            "datetime" => "string",          // PostgreSQL/SQLite: datetime
            "date" => "string",              // PostgreSQL/SQLite: date
            "time" => "string",              // PostgreSQL/SQLite: time
            "timestamp with time zone" => "string", // PostgreSQL: timestamp with time zone
            "timestamp without time zone" => "string", // PostgreSQL: timestamp without time zone
            "date" => "string",              // PostgreSQL/SQLite: date
            "time" => "string",              // PostgreSQL/SQLite: time
            "interval" => "string",          // PostgreSQL: interval (for durations)
            "year" => "int",                 // MySQL: year type (usually stored as an integer)

            // SQLite-specific types
            "integer" => "int",              // SQLite: integer
            "real" => "float",               // SQLite: real (floating-point)
            "text" => "string",              // SQLite: text (string)
            "blob" => "resource",            // SQLite: blob (binary data)
            
            // SQLite's special handling
            "BOOLEAN" => "bool",             // SQLite: boolean (same as PostgreSQL)
            
            // Special cases
            "json" => "array",               // PostgreSQL: JSON type, mapped to PHP array
            "jsonb" => "array",              // PostgreSQL: JSONB (binary JSON), mapped to PHP array
            "uuid" => "string",              // PostgreSQL/SQLite: UUID
            "xml" => "string",               // PostgreSQL: XML type
            "cidr" => "string",              // PostgreSQL: CIDR type (IPv4/IPv6)
            "inet" => "string",              // PostgreSQL: Inet type (IPv4/IPv6)
            "macaddr" => "string",           // PostgreSQL: MAC address type
            "point" => "string",             // PostgreSQL: point type (coordinates)
            "polygon" => "string",           // PostgreSQL: polygon type
            "line" => "string",              // PostgreSQL: line type
            "lseg" => "string",              // PostgreSQL: line segment type
            "path" => "string",              // PostgreSQL: path type (geometric shapes)
            "circle" => "string",            // PostgreSQL: circle type (geometric shapes)
            "json" => "array",               // PostgreSQL: JSON data type
            "jsonb" => "array",              // PostgreSQL: Binary JSON type (faster)
        );
    }

    /**
     * Returns a mapping of database column types to MySQL equivalents.
     *
     * This method provides a conversion map from various database column types 
     * (such as those from PostgreSQL or SQLite) to MySQL-compatible column types.
     * The mapping is useful for normalizing column types when migrating data 
     * between different database systems or for general type compatibility.
     *
     * @return array An associative array where keys are column types from other databases 
     *               and values are the corresponding MySQL column types.
     */
    public function getColumnMap()
    {
        return array(
            // Numeric types
            "double" => "float",             // MySQL: DOUBLE precision
            "float" => "float",              // MySQL: FLOAT
            "bigint" => "bigint",            // MySQL: BIGINT
            "smallint" => "smallint",        // MySQL: SMALLINT
            "tinyint(1)" => "tinyint",    // MySQL-style, use boolean for tinyint(1)
            "tinyint" => "tinyint",          // MySQL: TINYINT
            "int" => "int",                  // MySQL: INT
            "serial" => "int",               // MySQL: auto-increment integer (equivalent to INT)
            "bigserial" => "bigint",         // MySQL: Big serial equivalent (use BIGINT)
            "mediumint" => "mediumint",      // MySQL: MEDIUMINT
            "smallserial" => "smallint",     // MySQL: smallserial equivalent (use SMALLINT)
            "unsigned" => "unsigned",        // MySQL: UNSIGNED integer

            // String types
            "nvarchar" => "varchar",         // SQLite: VARCHAR
            "varchar" => "varchar",          // MySQL: VARCHAR
            "character varying" => "varchar", // MySQL: CHARACTER VARYING (same as VARCHAR)
            "char" => "char",                // MySQL: CHAR
            "text" => "text",                // MySQL: TEXT
            "varchar(255)" => "varchar",     // MySQL: VARCHAR with specific length (equivalent to varchar)
            "citext" => "text",              // MySQL: case-insensitive text (MySQL does not have direct CITEXT type)
            
            // MySQL-style text types
            "tinytext" => "tinytext",        // MySQL: TINYTEXT
            "mediumtext" => "mediumtext",    // MySQL: MEDIUMTEXT
            "longtext" => "longtext",        // MySQL: LONGTEXT

            // Boolean types
            "bool" => "tinyint(1)",          // MySQL: BOOLEAN (stored as TINYINT(1))
            "boolean" => "tinyint(1)",       // MySQL: BOOLEAN (same as TINYINT(1))

            // Date/Time types
            "timestamp" => "timestamp",      // MySQL: TIMESTAMP
            "datetime" => "datetime",        // MySQL: DATETIME
            "date" => "date",                // MySQL: DATE
            "time" => "time",                // MySQL: TIME
            "timestamp with time zone" => "timestamp", // MySQL does not support time zone, use regular timestamp
            "timestamp without time zone" => "timestamp", // Same for MySQL (no time zone info)
            "year" => "year",                // MySQL: YEAR type

            // MySQL-specific types
            "json" => "json",                // MySQL: JSON type
            "uuid" => "char(36)",            // MySQL: UUID (store as CHAR(36) string)
            "xml" => "text",                 // MySQL: XML (stored as TEXT)
            "inet" => "varchar(45)",         // MySQL: Inet (for IPv4 and IPv6, store as VARCHAR)
            "macaddr" => "varchar(17)",      // MySQL: MAC address (store as VARCHAR)
            "point" => "point",              // MySQL: POINT (geometric type)
            "polygon" => "polygon",          // MySQL: POLYGON (geometric type)
            "line" => "line",                // MySQL: LINE (geometric type)

            // MySQL also uses the TEXT type for handling large objects
            "blob" => "blob",                // MySQL: BLOB (binary data)

            // Special cases
            "jsonb" => "json",               // MySQL: JSONB can be treated as JSON
        );
    }

    /**
     * Returns a mapping of database column types to the target database type equivalents.
     *
     * This method provides a conversion map from various database column types 
     * (such as those from PostgreSQL or SQLite) to the target database column types.
     * The mapping is useful for normalizing column types when migrating data 
     * between different database systems or for general type compatibility.
     *
     * @param string $targetDb The target database type ('mysql', 'postgresql', or 'sqlite').
     * @return array An associative array where keys are column types from other databases 
     *               and values are the corresponding target database column types.
     */
    public function getColumnMapByType($targetDb)
    {
        $map = array();

        // MySQL column types
        if ($targetDb == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
            $map = array(
                // Numeric types
                "double" => "float",
                "float" => "float",
                "bigint" => "bigint",
                "smallint" => "smallint",
                "tinyint(1)" => "bool",
                "tinyint" => "tinyint",
                "int" => "int",
                "serial" => "int",
                "bigserial" => "bigint",
                "mediumint" => "mediumint",
                "smallserial" => "smallint",
                "unsigned" => "unsigned",
                
                // String types
                "nvarchar" => "varchar",
                "varchar" => "varchar",
                "character varying" => "varchar",
                "char" => "char",
                "text" => "text",
                "varchar(255)" => "varchar",
                "citext" => "text",
                
                // MySQL-style text types
                "tinytext" => "tinytext",
                "mediumtext" => "mediumtext",
                "longtext" => "longtext",
                
                // Boolean types
                "bool" => "tinyint(1)",
                "boolean" => "tinyint(1)",
                
                // Date/Time types
                "timestamp" => "timestamp",
                "datetime" => "datetime",
                "date" => "date",
                "time" => "time",
                "timestamp with time zone" => "timestamp",
                "timestamp without time zone" => "timestamp",
                "year" => "year",
                
                // MySQL-specific types
                "json" => "json",
                "uuid" => "char(36)",
                "xml" => "text",
                "inet" => "varchar(45)",
                "macaddr" => "varchar(17)",
                "point" => "point",
                "polygon" => "polygon",
                "line" => "line",
                "blob" => "blob",
                
                // Special cases
                "jsonb" => "json",
            );
        }

        // PostgreSQL column types
        elseif ($targetDb == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
            $map = array(
                // Numeric types
                "double" => "double precision",
                "float" => "real",
                "bigint" => "bigint",
                "smallint" => "smallint",
                "tinyint(1)" => "boolean",
                "tinyint" => "smallint",
                "int" => "integer",
                "serial" => "serial",
                "bigserial" => "bigserial",
                "mediumint" => "integer",
                "smallserial" => "smallint",
                "unsigned" => "integer",
                
                // String types
                "nvarchar" => "varchar",
                "varchar" => "varchar",
                "character varying" => "varchar",
                "char" => "char",
                "text" => "text",
                "varchar(255)" => "varchar",
                "citext" => "citext",
                
                // PostgreSQL-style text types
                "tinytext" => "text",
                "mediumtext" => "text",
                "longtext" => "text",
                
                // Boolean types
                "bool" => "boolean",
                "boolean" => "boolean",
                
                // Date/Time types
                "timestamp" => "timestamp",
                "datetime" => "timestamp",
                "date" => "date",
                "time" => "time",
                "timestamp with time zone" => "timestamptz",
                "timestamp without time zone" => "timestamp",
                "year" => "date",
                
                // PostgreSQL-specific types
                "json" => "jsonb",
                "uuid" => "uuid",
                "xml" => "xml",
                "inet" => "inet",
                "macaddr" => "macaddr",
                "point" => "point",
                "polygon" => "polygon",
                "line" => "line",
                "blob" => "bytea",
                
                // Special cases
                "jsonb" => "jsonb",
            );
        }

        // SQLite column types
        elseif ($targetDb == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
            $map = array(
                // Numeric types
                "double" => "real",
                "float" => "real",
                "bigint" => "integer",
                "smallint" => "integer",
                "tinyint(1)" => "integer",
                "tinyint" => "integer",
                "int" => "integer",
                "serial" => "integer",
                "bigserial" => "integer",
                "mediumint" => "integer",
                "smallserial" => "integer",
                "unsigned" => "integer",
                
                // String types
                "nvarchar" => "text",
                "varchar" => "text",
                "character varying" => "text",
                "char" => "text",
                "text" => "text",
                "varchar(255)" => "text",
                "citext" => "text",
                
                // SQLite-style text types
                "tinytext" => "text",
                "mediumtext" => "text",
                "longtext" => "text",
                
                // Boolean types
                "bool" => "integer",
                "boolean" => "integer",
                
                // Date/Time types
                "timestamp" => "datetime",
                "datetime" => "datetime",
                "date" => "date",
                "time" => "time",
                "timestamp with time zone" => "datetime",
                "timestamp without time zone" => "datetime",
                "year" => "integer",
                
                // SQLite-specific types
                "json" => "text",
                "uuid" => "text",
                "xml" => "text",
                "inet" => "text",
                "macaddr" => "text",
                "point" => "text",
                "polygon" => "text",
                "line" => "text",
                "blob" => "blob",
                
                // Special cases
                "jsonb" => "json",
            );
        }

        return $map;
    }

    /**
     * Generate the entity class and save it to a file.
     *
     * @param string[]|null $nonupdatables Non-updateable columns
     * @return int Number of bytes written to the file, or false on failure
     */
    public function generate($nonupdatables = null)
    {
        $typeMap = $this->getTypeMap();
        $columnMap = $this->getColumnMap();
        $tableName = $this->tableName;
        $className = isset($this->entityName) ? $this->entityName : ucfirst(PicoStringUtil::camelize($tableName));
        $fileName = $this->baseNamespace . "/" . $className;
        $path = $this->baseDir . "/" . $fileName . ".php";
        $path = str_replace("\\", "/", $path);

        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $rows = PicoColumnGenerator::getColumnList($this->database, $tableName);
        error_log("ROWS");
        error_log(print_r($rows, true));

        $attrs = array();
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $prop = $this->createProperty($typeMap, $columnMap, $row, $nonupdatables);
                $attrs[] = $prop;
            }
        }

        $prettify = $this->prettify ? 'true' : 'false';

        $classStr = '<?php

namespace ' . $this->baseNamespace . ';

use MagicObject\MagicObject;

/**
 * The '.$className.' class represents an entity in the "'.$tableName.'" table.
 *
 * This entity maps to the "'.$tableName.'" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#entity
 * 
 * @package '.$this->baseNamespace.'
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify='.$prettify.')
 * @Table(name="'.$tableName.'")
 */
class ' . $className . ' extends MagicObject
{
' . implode("\r\n", $attrs) . '
}';

        return file_put_contents($path, $classStr);
    }
}
