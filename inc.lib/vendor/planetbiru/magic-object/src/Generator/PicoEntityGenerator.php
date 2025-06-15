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
    const TYPE_CHARACTER_VARYING = "character varying";
    const TYPE_TINYINT_1 = "tinyint(1)";
    const TYPE_VARCHAR_255 = "varchar(255)";
    const TYPE_TIMESTAMP_WITH_TIME_ZONE = "timestamp with time zone";
    const TYPE_TIMESTAMP_WITHOUT_TIME_ZONE = "timestamp without time zone";

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
     * @var bool
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
     * Generates a PHP property with a docblock based on database column metadata.
     *
     * This method creates a property for a PHP class, including annotations such as `@Id`, `@GeneratedValue`,
     * `@NotNull`, and `@Column`, derived from the given database column details. It supports column attributes
     * like primary key, auto-increment, nullable, and default value, and provides type mappings based on the
     * provided database-to-PHP type mappings.
     *
     * @param array $typeMap Database-to-PHP type mappings (e.g., 'int' => 'integer').
     * @param array $columnMap Database column-to-type mappings (e.g., 'int' => 'INTEGER').
     * @param array $row Column metadata from the database (e.g., from `information_schema.columns`).
     * @param string[]|null $nonupdatables List of non-updatable columns, or null if none.
     * @param bool $prettifyLabel Whether to convert column names to human-readable labels (default is true).
     * @return string PHP code for the property with a docblock, ready to be inserted into a class.
     */
    protected function createProperty($typeMap, $columnMap, $row, $nonupdatables = null, $prettifyLabel = true)
    {
        $columnName = $row['Field'];
        $columnType = $row['Type'];
        $columnKey = $row['Key'];
        $columnNull = $row['Null'];
        $columnDefault = $row['Default'];
        $columnExtra = $row['Extra'];

        $propertyName = PicoStringUtil::camelize($columnName);
        $description = $this->getPropertyName($columnName, $prettifyLabel);
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
            $attrs[] = "defaultValue=\"" . $columnDefault . "\"";
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
     * The column name is converted to a formatted property name, where each part
     * of the column name (split by underscores) is capitalized. Special cases such as 
     * "Id" and "Ip" are handled to be formatted as "ID" and "IP", respectively.
     *
     * @param string $name Original column name (e.g., 'user_id', 'user_ip')
     * @param bool $prettifyLabel Whether to replace 'Id' with 'ID' and 'Ip' with 'IP'
     * @return string Formatted property name (e.g., 'User ID', 'User IP')
     */
    protected function getPropertyName($name, $prettifyLabel = true)
    {
        $arr = explode("_", $name);
        foreach ($arr as $k => $v) {
            $arr[$k] = ucwords($v);
            if ($prettifyLabel) {
                if (strtolower($v) === 'id') {
                    $arr[$k] = 'ID';
                } elseif (strtolower($v) === 'ip') {
                    $arr[$k] = 'IP';
                }
            }
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
     * Get the length of a column based on its data type definition.
     *
     * Special handling for date/time types:
     * - datetime(6) or timestamp(6) => 26
     * - datetime(3) or timestamp(3) => 23
     * - datetime or timestamp       => 26 (default to microseconds precision)
     * - date                        => 10 (e.g., YYYY-MM-DD)
     * - time                        => 8  (e.g., HH:MM:SS)
     *
     * For other types (e.g., varchar(255)), it extracts the numeric length.
     *
     * @param string $dataType Column definition containing type and optional length/precision
     * @return int Length of the column
     */
    protected function getDataLength($dataType)
    {
        $length = 0;

        // Normalize data type to lowercase for consistent comparison
        $dataTypeLower = strtolower($dataType);

        // Handle datetime and timestamp types with optional precision
        if (preg_match('/(datetime|timestamp)(\((\d+)\))?/i', $dataType, $matches)) {
            if (isset($matches[3])) {
                $precision = (int)$matches[3];
                $length = $precision === 3 ? 23 : 26;
            } else {
                $length = 26;
            }
        }
        // Handle date type
        elseif (strpos($dataTypeLower, 'date') === 0) {
            $length = 10; // YYYY-MM-DD
        }
        // Handle time type
        elseif (strpos($dataTypeLower, 'time') === 0) {
            $length = 8; // HH:MM:SS
        }
        // Handle other types like varchar(255)
        else {
            $numeric = preg_replace('~\D~', '', $dataType);
            $length = empty($numeric) ? 0 : (int)$numeric;
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
            self::TYPE_TINYINT_1 => "bool",          // MySQL-style, use boolean for tinyint(1)
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
            self::TYPE_CHARACTER_VARYING => "string", // PostgreSQL: character varying (same as varchar)
            "char" => "string",              // PostgreSQL: fixed-length string
            "text" => "string",              // PostgreSQL/SQLite: unlimited length string
            self::TYPE_VARCHAR_255 => "string",      // PostgreSQL: same as varchar without length
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
            self::TYPE_TIMESTAMP_WITH_TIME_ZONE => "string", // PostgreSQL: timestamp with time zone
            self::TYPE_TIMESTAMP_WITHOUT_TIME_ZONE => "string", // PostgreSQL: timestamp without time zone
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
     * Get a mapping of database column types to MySQL equivalents.
     *
     * Provides a conversion map from various database column types to MySQL-compatible 
     * column types, useful for data migrations or type compatibility.
     *
     * @return array Associative array with column types from other databases mapped to MySQL types.
     */
    public function getColumnMap()
    {
        return array(
            // Numeric types
            "double" => "float",             // MySQL: DOUBLE precision
            "float" => "float",              // MySQL: FLOAT
            "bigint" => "bigint",            // MySQL: BIGINT
            "smallint" => "smallint",        // MySQL: SMALLINT
            self::TYPE_TINYINT_1 => "tinyint",    // MySQL-style, use boolean for tinyint(1)
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
            self::TYPE_CHARACTER_VARYING => "varchar", // MySQL: CHARACTER VARYING (same as VARCHAR)
            "char" => "char",                // MySQL: CHAR
            "text" => "text",                // MySQL: TEXT
            self::TYPE_VARCHAR_255 => "varchar",     // MySQL: VARCHAR with specific length (equivalent to varchar)
            "citext" => "text",              // MySQL: case-insensitive text (MySQL does not have direct CITEXT type)
            
            // MySQL-style text types
            "tinytext" => "tinytext",        // MySQL: TINYTEXT
            "mediumtext" => "mediumtext",    // MySQL: MEDIUMTEXT
            "longtext" => "longtext",        // MySQL: LONGTEXT

            // Boolean types
            "bool" => self::TYPE_TINYINT_1,          // MySQL: BOOLEAN (stored as TINYINT(1))
            "boolean" => self::TYPE_TINYINT_1,       // MySQL: BOOLEAN (same as TINYINT(1))

            // Date/Time types
            "timestamp" => "timestamp",      // MySQL: TIMESTAMP
            "datetime" => "datetime",        // MySQL: DATETIME
            "date" => "date",                // MySQL: DATE
            "time" => "time",                // MySQL: TIME
            self::TYPE_TIMESTAMP_WITH_TIME_ZONE => "timestamp", // MySQL does not support time zone, use regular timestamp
            self::TYPE_TIMESTAMP_WITHOUT_TIME_ZONE => "timestamp", // Same for MySQL (no time zone info)
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
     * Get a mapping of database column types to target database equivalents.
     *
     * Converts column types from one database (MySQL, PostgreSQL, SQLite) to another, 
     * supporting data migrations and compatibility.
     *
     * @param string $targetDb The target database type ('mysql', 'postgresql', or 'sqlite').
     * @return array Associative array of column types from other databases mapped to the target database types.
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
                self::TYPE_TINYINT_1 => "bool",
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
                self::TYPE_CHARACTER_VARYING => "varchar",
                "char" => "char",
                "text" => "text",
                self::TYPE_VARCHAR_255 => "varchar",
                "citext" => "text",
                
                // MySQL-style text types
                "tinytext" => "tinytext",
                "mediumtext" => "mediumtext",
                "longtext" => "longtext",
                
                // Boolean types
                "bool" => self::TYPE_TINYINT_1,
                "boolean" => self::TYPE_TINYINT_1,
                
                // Date/Time types
                "timestamp" => "timestamp",
                "datetime" => "datetime",
                "date" => "date",
                "time" => "time",
                self::TYPE_TIMESTAMP_WITH_TIME_ZONE => "timestamp",
                self::TYPE_TIMESTAMP_WITHOUT_TIME_ZONE => "timestamp",
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
                self::TYPE_TINYINT_1 => "boolean",
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
                self::TYPE_CHARACTER_VARYING => "varchar",
                "char" => "char",
                "text" => "text",
                self::TYPE_VARCHAR_255 => "varchar",
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
                self::TYPE_TIMESTAMP_WITH_TIME_ZONE => "timestamptz",
                self::TYPE_TIMESTAMP_WITHOUT_TIME_ZONE => "timestamp",
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
                self::TYPE_TINYINT_1 => "integer",
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
                self::TYPE_CHARACTER_VARYING => "text",
                "char" => "text",
                "text" => "text",
                self::TYPE_VARCHAR_255 => "text",
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
                self::TYPE_TIMESTAMP_WITH_TIME_ZONE => "datetime",
                self::TYPE_TIMESTAMP_WITHOUT_TIME_ZONE => "datetime",
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
     * Generates an entity class based on database table metadata and saves it to a file.
     *
     * This method creates a PHP class that maps to a database table, including properties for each column.
     * It supports ORM annotations (e.g., `@Entity`, `@Table`, `@JSON`) and handles non-updatable columns. 
     * Optionally, it can prettify property names for human readability.
     *
     * @param string[]|null $nonupdatables List of non-updatable columns, or null if none.
     * @param bool $prettifyLabel Whether to prettify column names into human-readable labels (default is true).
     * @return int|false The number of bytes written to the file, or false on failure.
     */
    public function generate($nonupdatables = null, $prettifyLabel = true)
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

        $attrs = array();
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $prop = $this->createProperty($typeMap, $columnMap, $row, $nonupdatables, $prettifyLabel);
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
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package '.$this->baseNamespace.'
 * @Entity
 * @JSON(propertyNamingStrategy=SNAKE_CASE, prettify='.$prettify.')
 * @Table(name="'.$tableName.'")
 */
class ' . $className . ' extends MagicObject
{
' . implode("\r\n", $attrs) . '
}';

        return file_put_contents($path, $classStr);
    }

    /**
     * Generates a PHP validator class string with annotated properties.
     *
     * This method constructs a PHP class definition as a string. The generated class extends
     * `MagicObject` and contains properties corresponding to fields defined in the validation
     * definition. Each property is annotated with relevant validation rules and data type
     * information.
     *
     * The generated class includes:
     * - Namespace declaration
     * - PHPDoc block summarizing validated properties
     * - Validation annotations (Required, Min, etc.)
     * - Proper data type hinting for each property
     *
     * @param string $namespace             The PHP namespace where the validator class belongs.
     * @param string $className             The base name of the class to be generated.
     * @param string $moduleCode            The code name of the module this validator is for.
     * @param array  $validationDefinition  An array of field definitions, each containing field name, type, and validation rules.
     * @param string $applyKey              Determines which rules to apply, usually 'applyInsert' or 'applyUpdate'.
     *
     * @return string Returns the full PHP source code of the generated class as a string.
     */

    public function generateValidatorClass($namespace, $className, $moduleCode, $validationDefinition, $applyKey) // NOSONAR
    {
        $properties = array();

        $typeMap = $this->getTypeMap();
        $columnMap = $this->getColumnMap();
        $propTypes = array();

        foreach ($validationDefinition as $itemObject) {
            $item = $itemObject->valueArray();
            $field = $item['fieldName'];
            $fieldType = $item['fieldType'];
            $canonicalFieldType = isset($columnMap[$fieldType]) ? $columnMap[$fieldType] : $fieldType;
            $dataType = $this->getDataType($typeMap, $canonicalFieldType);
            
            $camelField = PicoStringUtil::camelize($field);
            $propTypes[$camelField] = $dataType;

            foreach ($item['validation'] as $rule) {
                if (!empty($rule[$applyKey])) {
                    if (!isset($properties[$camelField])) {
                        $properties[$camelField] = array();
                    }

                    $type = $rule['type'];
                    $annotationParts = array();
                    foreach ($rule as $key => $value) {
                        if (in_array($key, ['type', 'applyInsert', 'applyUpdate'])) {
                            continue;
                        }
                        if (is_string($value)) {
                            $annotationParts[] = $key . '="' . $value . '"';
                        } else {
                            $annotationParts[] = $key . '=' . $value;
                        }
                    }

                    $annotation = array();
                    $annotation[] = "\t" . ' * @' . $type . '(' . implode(', ', $annotationParts) . ')';
                    $properties[$camelField][] = implode("\r\n", $annotation);
                }
            }
        }

        $output = "<?php\r\n\r\n";
        if (!empty($namespace)) {
            $output .= "namespace " . $namespace . ";\r\n\r\n";
        }
        $output .= "use MagicObject\\MagicObject;\r\n\r\n";

        // Build class docblock
        $output .= "/**\r\n";
        $output .= " * Represents a validator class for the `" . $moduleCode . "` module.\r\n";
        $output .= " *\r\n";
        $output .= " * This class is auto-generated and intended for " . ($applyKey === 'applyInsert' ? 'insert' : 'update') . " validation.\r\n";
        $output .= " * You can add additional validation rules as needed.\r\n";
        $output .= " *\r\n";
        $output .= " * Validated properties:\r\n";
        $no = 1;
        foreach ($properties as $propertyName => $annotations) {
            $types = array();
            foreach ($annotations as $annotation) {
                if (preg_match('/\*\s+@(\w+)\((.*)\)/', $annotation, $matches)) {
                    $type = $matches[1];
                    $rawAttributes = $matches[2];
                    $attrList = [];

                    // Parse attributes
                    preg_match_all('/(\w+)\s*=\s*(?:"([^"]*)"|([^,]+))/', $rawAttributes, $attrMatches, PREG_SET_ORDER);
                    foreach ($attrMatches as $attr) {
                        $key = $attr[1];
                        $attr3 = isset($attr[3]) ? $attr[3] : '';
                        $rawValue = isset($attr[2]) && $attr[2] !== '' ? $attr[2] : $attr3;

                        if (is_numeric($rawValue)) {
                            $value = $rawValue; // keep numeric as-is
                        } else {
                            $value = '"' . $rawValue . '"'; // wrap string with double quotes
                        }

                        if ($value !== "" && $value != '""') {
                            $attrList[] = "$key=$value";
                        }
                    }

                    $types[] = $type . (!empty($attrList) ? '(' . implode(', ', $attrList) . ')' : '');
                }
            }
            $uniqueTypes = array_unique($types);
            $output .= " * $no. **`\$$propertyName`** ( " . implode(', ', $uniqueTypes) . " )\r\n";
            $no++;
        }
        $output .= " * \r\n * @package $namespace\r\n";
        $output .= " */\r\n";

        $output .= "class " . $className . " extends MagicObject\n{";

        foreach ($properties as $property => $annotations) {
            $output .= "\r\n";
            $output .= "\t/**\r\n";
            foreach ($annotations as $annotation) {
                $output .= $annotation . "\r\n";
            }
            $dataType = $propTypes[$property];
            $output .= "\t" . ' * @var ' . $dataType . "\r\n";
            $output .= "\t */\r\n";
            $output .= "\tprotected \$" . $property . ";\r\n";
        }

        $output .= "}\r\n";

        return $output;
    }


}
