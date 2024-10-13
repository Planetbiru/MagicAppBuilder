<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Util\PicoStringUtil;

/**
 * DTO generator for creating Data Transfer Objects (DTOs) from database tables.
 * 
 * This class helps in generating DTOs that can be used for transferring data 
 * via APIs or for serialization into files or databases.
 * 
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDtoGenerator
{
    /**
     * Database connection instance.
     *
     * @var PicoDatabase
     */
    protected $database;

    /**
     * Base directory for saving generated DTO files.
     *
     * @var string
     */
    protected $baseDir = "";

    /**
     * Base namespace for the generated DTOs.
     *
     * @var string
     */
    protected $baseNamespaceDto = "";

    /**
     * Table name to generate DTO for.
     *
     * @var string
     */
    protected $tableName = "";

    /**
     * Name of the entity associated with the DTO.
     *
     * @var string|null
     */
    protected $entityName = null;

    /**
     * Name of the DTO being generated.
     *
     * @var string|null
     */
    protected $dtoName = null;

    /**
     * Base namespace for the entity.
     *
     * @var string|null
     */
    protected $baseNamespaceEntity = null;

    /**
     * Flag to indicate whether to prettify the output.
     *
     * @var bool
     */
    protected $prettify = false;

    /**
     * Constructor for the DTO generator.
     *
     * @param PicoDatabase $database Database connection
     * @param string $baseDir Base directory for generated files
     * @param string $tableName Table name for DTO generation
     * @param string $baseNamespaceDto Base namespace for DTOs
     * @param string $dtoName Name of the DTO
     * @param string $baseNamespaceEntity Base namespace for the entity
     * @param string|null $entityName Name of the entity (optional)
     * @param bool $prettify Flag to prettify output (default: false)
     */
    public function __construct(
        $database,
        $baseDir,
        $tableName,
        $baseNamespaceDto,
        $dtoName,
        $baseNamespaceEntity,
        $entityName = null,
        $prettify = false
    ) {
        $this->database = $database;
        $this->baseDir = $baseDir;
        $this->tableName = $tableName;
        $this->baseNamespaceDto = $baseNamespaceDto;
        $this->dtoName = $dtoName;
        $this->baseNamespaceEntity = $baseNamespaceEntity;
        $this->entityName = $entityName;
        $this->prettify = $prettify;
    }

    /**
     * Create a property with appropriate documentation.
     *
     * @param array $typeMap Mapping of database types to PHP types
     * @param string $columnName Name of the column
     * @param string $columnType Type of the column
     * @return string PHP code for the property with docblock
     */
    protected function createProperty($typeMap, $columnName, $columnType)
    {
        $propertyName = PicoStringUtil::camelize($columnName);
        $docs = [];
        $docStart = "\t/**";
        $docEnd = "\t */";

        $description = $this->getPropertyName($columnName);
        $type = $this->getDataType($typeMap, $columnType);

        $docs[] = $docStart;
        $docs[] = "\t * $description";
        $docs[] = "\t * ";
        $docs[] = "\t * @Label(content=\"$description\")";
        $docs[] = "\t * @var $type";
        $docs[] = $docEnd;
        $prop = "\tprotected \$$propertyName;";

        return implode("\r\n", $docs) . "\r\n" . $prop . "\r\n";
    }

    /**
     * Get a descriptive name for the property from the column name.
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
     * Determine the PHP data type corresponding to the column type.
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
     * Create a static method to construct the DTO from the entity.
     *
     * @param string $picoTableName Table name
     * @param array $rows Data rows from the database
     * @return string PHP code for the valueOf method
     */
    protected function createValueOf($picoTableName, $rows)
    {
        $className = $this->entityName ?? ucfirst(PicoStringUtil::camelize($picoTableName));
        $dtoName = $this->dtoName ?? ucfirst(PicoStringUtil::camelize($picoTableName)) . "Dto";

        $str = "";
        $str .= "    /**\r\n";
        $str .= "     * Construct $dtoName from $className and not copy other properties\r\n";
        $str .= "     * \r\n";
        $str .= "     * @param $className \$input\r\n";
        $str .= "     * @return self\r\n";
        $str .= "     */\r\n";
        $str .= "    public static function valueOf(\$input)\r\n";
        $str .= "    {\r\n";
        $str .= "        \$output = new $dtoName();\r\n";

        foreach ($rows as $row) {
            $columnName = $row['Field'];
            $str .= "        \$output->set" . ucfirst(PicoStringUtil::camelize($columnName)) . "(\$input->get" . ucfirst(PicoStringUtil::camelize($columnName)) . "());\r\n";
        }
        $str .= "        return \$output;\r\n";
        $str .= "    }\r\n";
        return $str;
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
            "time" => "string",
        ];
    }

    /**
     * Generate the DTO and save it to a file.
     *
     * @return int Result of file write operation (number of bytes written or false on failure)
     */
    public function generate()
    {
        $typeMap = $this->getTypeMap();
        $picoTableName = $this->tableName;
        $classNameDto = $this->dtoName ?? ucfirst(PicoStringUtil::camelize($picoTableName)) . "Dto";
        $fileName = $this->baseNamespaceDto . "/" . $classNameDto;
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
                $columnName = $row['Field'];
                $columnType = $row['Type'];
                $prop = $this->createProperty($typeMap, $columnName, $columnType);
                $attrs[] = $prop;
            }
            $prop = $this->createValueOf($picoTableName, $rows);
            $attrs[] = $prop;
        }

        $prettify = $this->prettify ? 'true' : 'false';
        $entityName = $this->entityName;
        $used = "use " . $this->baseNamespaceEntity . "\\" . $this->entityName . ";";

        $classStr = '<?php

namespace ' . $this->baseNamespaceDto . ';

use MagicObject\\SetterGetter;
' . $used . '

/**
 * ' . $classNameDto . ' is a Data Transfer Object used to transfer ' . $entityName . ' via API or to serialize into files or databases.
 * Visit https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md
 *
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=' . $prettify . ')
 */
class ' . $classNameDto . ' extends SetterGetter
{
' . implode("\r\n", $attrs) . '
}';
        
        return file_put_contents($path, $classStr);
    }
}
