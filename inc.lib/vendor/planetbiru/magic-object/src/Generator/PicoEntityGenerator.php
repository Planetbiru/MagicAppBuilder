<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Util\PicoStringUtil;

/**
 * PicoEntityGenerator is an entity generator for automatically generating PHP code.
 * This class is optimized for the MariaDB database.
 * Users must provide appropriate parameters so that the entity class can be directly used in the application.
 * 
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
     * @param boolean $prettify Flag to prettify output (default: false)
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
     * Create a property with appropriate documentation.
     *
     * @param array $typeMap Mapping of database types to PHP types
     * @param array $row Data row from the database
     * @param string[]|null $nonupdatables Non-updateable columns
     * @return string PHP code for the property with docblock
     */
    protected function createProperty($typeMap, $row, $nonupdatables = null)
    {
        $columnName = $row['Field'];
        $columnType = $row['Type'];
        $columnKey = $row['Key'];
        $columnNull = $row['Null'];
        $columnDefault = $row['Default'];
        $columnExtra = $row['Extra'];

        $propertyName = PicoStringUtil::camelize($columnName);
        $description = $this->getPropertyName($columnName);
        $type = $this->getDataType($typeMap, $columnType);

        $docs = [];
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

        $attrs = [];
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
     * Get a mapping of database types to PHP types.
     *
     * @return array Associative array of type mappings
     */
    protected function getTypeMap()
    {
        return [
            "double" => "double",
            "float" => "double",
            "bigint" => "integer",
            "smallint" => "integer",
            "tinyint(1)" => "boolean",
            "tinyint" => "integer",
            "int" => "integer",
            "varchar" => "string",
            "char" => "string",
            "tinytext" => "string",
            "mediumtext" => "string",
            "longtext" => "string",
            "text" => "string",
            "enum" => "string",
            "bool" => "boolean",
            "boolean" => "boolean",
            "timestamp" => "string",
            "datetime" => "string",
            "date" => "string",
            "time" => "string"
        ];
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
        $picoTableName = $this->tableName;
        $className = $this->entityName ?? ucfirst(PicoStringUtil::camelize($picoTableName));
        $fileName = $this->baseNamespace . "/" . $className;
        $path = $this->baseDir . "/" . $fileName . ".php";
        $path = str_replace("\\", "/", $path);

        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $rows = PicoColumnGenerator::getColumnList($this->database, $picoTableName);

        $attrs = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $prop = $this->createProperty($typeMap, $row, $nonupdatables);
                $attrs[] = $prop;
            }
        }

        $prettify = $this->prettify ? 'true' : 'false';

        $classStr = '<?php

namespace ' . $this->baseNamespace . ';

use MagicObject\MagicObject;

/**
 * ' . $className . ' represents the entity for the table ' . $picoTableName . '.
 * You can join this entity to other entities using the @JoinColumn annotation.
 * 
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#entity
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=' . $prettify . ')
 * @Table(name="' . $picoTableName . '")
 */
class ' . $className . ' extends MagicObject
{
' . implode("\r\n", $attrs) . '
}';

        return file_put_contents($path, $classStr);
    }
}
