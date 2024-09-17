<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Util\PicoStringUtil;

/**
 * DTO generator
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDtoGenerator
{
    /**
     * Database
     *
     * @var PicoDatabase
     */
    protected $database;
    /**
     * Base directory
     *
     * @var string
     */
    protected $baseDir = "";

    /**
     * Base namespace
     *
     * @var string
     */
    protected $baseNamespaceDto = "";

    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = "";

    /**
     * Entity name
     *
     * @var string
     */
    protected $entityName = null;

    /**
     * DTO name
     *
     * @var string
     */
    protected $dtoName = null;

    /**
     * Base name entity
     *
     * @var string
     */
    protected $baseNamespaceEntity = null;

    /**
     * Prettify
     *
     * @var boolean
     */
    protected $prettify = false;

    /**
     * Constructor
     *
     * @param PicoDatabase $database Database connection
     * @param string $baseDir Base directory
     * @param string $tableName Table name
     * @param string $baseNamespaceDto DTO base namespace
     * @param string $dtoName DTO name
     * @param string $baseNamespaceEntity Entity base namespace
     * @param string $entityName Entity name
     * @param boolean $prettify Flag to prettify
     */
    public function __construct($database, $baseDir, $tableName, $baseNamespaceDto, $dtoName, $baseNamespaceEntity, $entityName = null, $prettify = false) // NOSONAR
    {
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
     * Create property
     *
     * @param array $typeMap Type map
     * @param string $columnName Column name
     * @param string $columnType Column type
     * @return string
     */
    protected function createProperty($typeMap, $columnName, $columnType)
    {
        $propertyName = PicoStringUtil::camelize($columnName);
        $docs = array();
        $docStart = "\t/**";
        $docEnd = "\t */";

        $description = $this->getPropertyName($columnName);
        $type = $this->getDataType($typeMap, $columnType);

        $docs[] = $docStart;
        $docs[] = "\t * ".$description;
        $docs[] = "\t * ";
        $docs[] = "\t * @Label(content=\"$description\")";
        $docs[] = "\t * @var $type";
        $docs[] = $docEnd;
        $prop = "\tprotected \$$propertyName;";
        return implode("\r\n", $docs)."\r\n".$prop."\r\n";
    }

    /**
     * Get property name
     *
     * @param string $name Column name
     * @return string
     */
    protected function getPropertyName($name)
    {
        $arr = explode("_", $name);
        foreach($arr as $k => $v)
        {
            $arr[$k] = ucfirst($v);
            $arr[$k] = str_replace("Id","ID", $arr[$k]);
            $arr[$k] = str_replace("Ip","IP", $arr[$k]);
        }
        return implode(" ", $arr);
    }

    /**
     * Get data type
     *
     * @param array $typeMap Type map
     * @param string $columnType Column type
     * @return string
     */
    protected function getDataType($typeMap, $columnType)
    {
        $type = "";
        foreach($typeMap as $key=>$val)
        {
            if(stripos($columnType, $key) === 0)
            {
                $type = $val;
                break;
            }
        }
        if(empty($type))
        {
            $type = "string";
        }
        return $type;
    }

    /**
     * Value of
     *
     * @param string $picoTableName Table name
     * @param array $rows Data rows
     * @return string
     */
    protected function createValueOf($picoTableName, $rows)
    {
        if($this->entityName != null)
        {
            $className = $this->entityName;
        }
        else
        {
            $className = ucfirst(PicoStringUtil::camelize($picoTableName))."";
        }
        if($this->dtoName != null)
        {
            $dtoName = $this->dtoName;
        }
        else
        {
            $dtoName = ucfirst(PicoStringUtil::camelize($picoTableName))."Dto";
        }

        $str = "";
        $str .="    /**\r\n";
        $str .="     * Construct $dtoName"." from $className and not copy other properties\r\n";
        $str .="     * \r\n";
        $str .="     * @param $className \$input\r\n";
        $str .="     * @return self\r\n";
        $str .="     */\r\n";
        $str .="    public static function valueOf(\$input)\r\n";
        $str .="    {\r\n";
        $str .="        \$output = new $dtoName"."();\r\n";

        foreach($rows as $row)
        {
            $columnName = $row['Field'];
            $str .="        \$output->set".ucfirst(PicoStringUtil::camelize($columnName))."(\$input->get".ucfirst(PicoStringUtil::camelize($columnName))."());\r\n";
        }
        $str .="        return \$output;\r\n";
        $str .="    }\r\n";
        return $str;
    }

    /**
     * Get type map
     *
     * @return array
     */
    protected function getTypeMap()
    {
        return array(
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
        );
    }

    /**
     * Generate DTO
     *
     * @return string
     */
    public function generate()
    {
        $typeMap = $this->getTypeMap();
        $picoTableName = $this->tableName;
        if($this->dtoName != null)
        {
            $classNameDto = $this->dtoName;
        }
        else
        {
            $classNameDto = ucfirst(PicoStringUtil::camelize($picoTableName))."Dto";
        }
        $fileName = $this->baseNamespaceDto."/".$classNameDto;
        $path = $this->baseDir."/".$fileName.".php";
        $path = str_replace("\\", "/", $path);

        $dir = dirname($path);
        if(!file_exists($dir))
        {
            mkdir($dir, 0755, true);
        }

        $rows = PicoColumnGenerator::getColumnList($this->database, $picoTableName);

        $attrs = array();
        if(is_array($rows))
        {
            foreach($rows as $row)
            {
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
        $uses[] = "";

        $used = "use ".$this->baseNamespaceEntity."\\".$this->entityName.";";

        $classStr = '<?php

namespace '.$this->baseNamespaceDto.';

use MagicObject\\SetterGetter;
'.$used.'

/**
 * '.$classNameDto.' is Data Transfer Object to be transfer '.$entityName.' via API or to be serializes into file or database.
 * Visit https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md
 *
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify='.$prettify.')
 */
class '.$classNameDto.' extends SetterGetter
{
'.implode("\r\n", $attrs).'
}';
        return file_put_contents($path, $classStr);
    }
}