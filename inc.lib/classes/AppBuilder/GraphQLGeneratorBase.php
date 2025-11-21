<?php 

namespace AppBuilder;

use MagicObject\Util\PicoStringUtil;

/**
 * Base class for GraphQL code generators.
 * 
 * This class provides common functionality for generating GraphQL schemas and resolvers
 * based on a provided database schema in JSON format. It analyzes the schema to identify
 * tables, primary keys, foreign keys, and reserved columns.
 */
class GraphQLGeneratorBase
{
     /**
     * @var array<string, array> List of reserved column definitions.
     */
    protected $reservedColumns = []; 

    /**
     * @var string Name of the 'active' field, e.g., 'aktif'.
     */
    protected $activeField = 'active';

    /**
     * @var array<string, array> List of backend-handled columns.
     */
    protected $backendHandledColumns = [];

    /**
     * @var string Name of the 'display' field, e.g., 'nama'.
     */
    protected $displayField = 'name';

    /**
     * @var array The entire schema definition from the input JSON.
     */
    protected $schema;

    /**
     * @var array<string, array> Analyzed schema with PK, FK, and column info.
     */
    protected $analyzedSchema = array();
    
    /**
     * @var bool Whether to enable caching features in the generated code.
     */
    protected $useCache = false;

    /**
     * Constructor for GraphQLGeneratorBase.
     * 
     * Initializes the generator with the provided schema, reserved columns, backend-handled columns, and caching option.
     *  
     * @param array $schema Decoded JSON schema.
     * @param array $reservedColumns
     * @param array $backendHandledColumns
     * @param bool $useCache Whether to use in-memory caching for queries.
     * @throws Exception If the file is not found or invalid.
     */
    public function __construct($schema, $reservedColumns = null, $backendHandledColumns = array(), $useCache = false)
    {
        $this->schema = $schema;
        $this->backendHandledColumns = $backendHandledColumns;
        $this->useCache = $useCache;

        if(isset($reservedColumns) && isset($reservedColumns['columns']))
        {
          $arr = array();
          foreach($reservedColumns['columns'] as $value)
          {
            $arr[$value['key']] = $value;
            if($value['key'] == 'active')
            {
              $this->activeField = $value['name'];
            }
            if($value['key'] == 'name')
            {
              $this->displayField = $value['name'];
            }
          }
          $this->reservedColumns = $arr;
        }
        else
        {
          $this->reservedColumns = array();
          
        }

        $this->analyzeSchema();
    }

    /**
     * Analyzes the raw JSON schema to identify tables, primary keys, and foreign keys.
     */
    private function analyzeSchema()
    {
        $tableNames = array();
        foreach ($this->schema['entities'] as $entity) {
            $tableNames[] = $entity['name'];
        }

        foreach ($this->schema['entities'] as $entity) {
            $tableName = $entity['name'];
            $primaryKey = $tableName . '_id';

            $this->analyzedSchema[$tableName] = array(
                'name' => $tableName,
                'primaryKey' => $primaryKey,
                'columns' => array(),
                'hasActiveColumn' => false,
                'filters' => isset($entity['filters']) ? $entity['filters'] : array(),
                'filterEntities' => isset($entity['filterEntities']) ? $entity['filterEntities'] : 0,
                'textareaColumns' => isset($entity['textareaColumns']) ? $entity['textareaColumns'] : array(),
                'description' => isset($entity['description']) ? $entity['description'] : null,
                
            );

            foreach ($entity['columns'] as $column) {
                $columnName = $column['name'];
                $this->analyzedSchema[$tableName]['columns'][$columnName] = array(
                    'type' => $column['type'],
                    'length' => $column['length'],
                    'isPrimaryKey' => $column['primaryKey'],
                    'isAutoIncrement' => $column['autoIncrement'],
                    'isForeignKey' => false,
                    'references' => null,
                    'primaryKeyValue' => isset($column['primaryKeyValue']) ? $column['primaryKeyValue'] : null
                );
                if ($columnName === $this->activeField) {
                    $this->analyzedSchema[$tableName]['hasActiveColumn'] = true;
                }

                // Foreign Key Detection Logic (Rule #5)
                if ($columnName !== $primaryKey && substr($columnName, -3) === '_id') {
                    $refTableName = substr($columnName, 0, -3);
                    if (in_array($refTableName, $tableNames)) {
                        // Check if the referenced table has a PK with the same name
                        $refTablePK = $refTableName . '_id';
                        $isRealFk = false;
                        foreach($this->schema['entities'] as $refEntity) {
                            if($refEntity['name'] === $refTableName) {
                                foreach($refEntity['columns'] as $refColumn) {
                                    if($refColumn['name'] === $refTablePK && $refColumn['primaryKey']) {
                                        $isRealFk = true;
                                        break 2;
                                    }
                                }
                            }
                        }

                        if ($isRealFk) {
                            $this->analyzedSchema[$tableName]['columns'][$columnName]['isForeignKey'] = true;
                            $this->analyzedSchema[$tableName]['columns'][$columnName]['references'] = $refTableName;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Gets a list of column names from table information.
     *
     * @param array $tableInfo Table information which must contain a 'columns' key.
     * @return string[] An array containing column names.
     */
    protected function getColumnNames($tableInfo)
    {
        if (!isset($tableInfo) || !isset($tableInfo['columns'])) {
            return [];
        }

        $columnNames = [];
        foreach ($tableInfo['columns'] as $columnName => $col) { //NOSONAR
            $columnNames[] = $columnName;
        }
        return $columnNames;
    }

    /**
     * Retrieves a simple array containing the names of columns handled by the backend.
     *
     * @return string[] An array containing column names.
     */
    protected function backendHandledColumns()
    {
        $backendHandledColumns = [];
        foreach ($this->backendHandledColumns as $col) { //NOSONAR
            $backendHandledColumns[] = $col['columnName'];
        }
        return $backendHandledColumns;
    }

    /**
     * Checks if a table has columns that are handled by the backend.
     *
     * @param array $tableInfo Table information to be checked.
     * @return bool Returns `true` if there is a matching column, otherwise `false`.
     */
    protected function hasBackendHandledColumns($tableInfo)
    {
        if (!isset($tableInfo) || !isset($tableInfo['columns'])) {
            return false;
        }
        $columnNames = $this->getColumnNames($tableInfo);
        $backendHandledColumns = $this->backendHandledColumns();
        foreach ($backendHandledColumns as $columnName) {
            if (in_array($columnName, $columnNames)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Converts a string to camelCase.
     *
     * @param string $str The input string.
     * @return string The converted camelCase string.
     */
    protected function camelCase($str)
    {
        return PicoStringUtil::camelize($str);
    }

    /**
     * Converts a string to lowerCamelCase.
     *
     * @param string $str The input string.
     * @return string The converted lowerCamelCase string.
     */
    protected function upperCamelCase($str)
    {
        return PicoStringUtil::upperCamelize($str);
    }

    /**
     * Converts a string to PascalCase.
     *
     * @param string $str The input string.
     * @return string The converted PascalCase string.
     */
    protected function pascalCase($str)
    {
        return ucfirst($this->camelCase($str));
    }

    /**
     * Converts a camelCase string to snake_case.
     *
     * @param string $str The camelCase string.
     * @return string The converted snake_case string.
     */
    protected function camelCaseToSnakeCase($str) {
        return PicoStringUtil::snakeize($str);
    }

    /**
     * Converts a snake_case string to camelCase.
     *
     * @param string $str The snake_case string.
     * @return string The converted camelCase string.
     */
    protected function snakeCaseToCamelCase($str) {
        $words = explode('_', strtolower($str));
        $camel = array_shift($words);
        foreach ($words as $word) {
            $camel .= ucfirst($word);
        }
        return $camel;
    }

    /**
     * Converts a snake_case string to Title Case.
     *
     * @param string $str The snake_case string.
     * @return string The converted Title Case string.
     */
    protected function snakeCaseToTitleCase($str) {
        $str = str_replace('_', ' ', strtolower($str));
        return $this->titleCase($str);
    }

    /**
     * Converts a string to Title Case.
     *
     * @param string $str The input string.
     * @return string The converted Title Case string.
     */
    protected function titleCase($str) {
        $words = explode(' ', strtolower(trim($str)));
        foreach ($words as &$word) {
            $word = ucfirst($word);
        }
        return implode(' ', $words);
    }

    /**
     * Converts a camelCase string to Title Case.
     *
     * @param string $str The camelCase string.
     * @return string The converted Title Case string.
     */
    protected function camelCaseToTitleCase($str) {
        return $this->snakeCaseToTitleCase($this->camelCaseToSnakeCase($str));
    }

    /**
     * Pluralizes a given string.
     *
     * @param string $str The input string.
     * @return string The pluralized string.
     */
    protected function pluralize($str)
    {
        if (substr($str, -1) === 'y') {
            return substr($str, 0, -1) . 'ies';
        }
        if (substr($str, -1) === 's') {
            return $str . 'es';
        }
        return $str . 's';
    }

    /**
     * Singularizes a given string.
     *
     * @param string $str The input string.
     * @return string The singularized string.
     */
    protected function singularize($str)
    {
        if (substr($str, -3) === 'ies') {
            return substr($str, 0, -3) . 'y';
        }
        if (substr($str, -1) === 's') {
            return substr($str, 0, -1);
        }
        return $str;
    }
    /**
     * Get list of reserved column definitions.
     */
    public function getReservedColumns()
    {
        return $this->reservedColumns;
    }
    
    /**
     * Generates a JSON config file for the frontend.
     * This file contains metadata about entities, fields, and relationships.
     *
     * @return string The JSON formatted content for frontend-config.json.
     */
    public function generateFrontendConfigJson()
    {
        $sortOrder = 1;
        $frontendConfig = array();
        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $camelName = $this->camelCase($tableName);
            $pluralCamelName = $this->pluralize($camelName);
            
            $textareaColumns = array();
            if(isset($tableInfo['textareaColumns']) && is_array($tableInfo['textareaColumns']))
            {
                $textareaColumns = $tableInfo['textareaColumns'];
            }
            $columns = array();
            foreach($tableInfo['columns'] as $colName => $colInfo) {
                $columns[$colName] = array(
                    'type' => $this->mapDbTypeToGqlType($colInfo['type'], $colInfo['length']),
                    'dataType' => $this->normalizeDbType($colInfo['type'], $colInfo['length']),
                    'isPrimaryKey' => $colInfo['isPrimaryKey'],
                    'isForeignKey' => $colInfo['isForeignKey'],
                    'references' => $colInfo['references'] ? $colInfo['references'] : null,
                    'element' => in_array($colName, $textareaColumns) ? 'textarea' : 'input',
                );
                if($colInfo['isPrimaryKey'])
                {
                    $columns[$colName]['primaryKeyValue'] = $colInfo['primaryKeyValue'] ? $colInfo['primaryKeyValue'] : 'autogenerated';
                }
            }

            $frontendConfig[$camelName] = array(
                'name' => $camelName,
                'pluralName' => $pluralCamelName,
                'displayName' => $this->camelCaseToTitleCase($camelName),
                'originalName' => $tableName,
                'displayField' => $this->displayField,
                'activeField' => $this->activeField,
                'primaryKey' => $tableInfo['primaryKey'],
                'hasActiveColumn' => $tableInfo['hasActiveColumn'],
                'sortOrder' => $sortOrder++,
                'menu' => true,
                'columns' => $columns,
                'filters' => isset($tableInfo['filters']) ? $tableInfo['filters'] : [],
                'filterEntities' => isset($tableInfo['filterEntities']) ? $tableInfo['filterEntities'] : 0,
                'backendHandledColumns' => array_column($this->backendHandledColumns, 'columnName'),
                
            );
        }
        
        return json_encode([
            'booleanDisplay' => array(
                'trueLabelKey' => 'yes',
                'falseLabelKey' => 'no'
            ),
            'pagination' => array (
                'pageSize' => 20,
                'maxPageSize' => 100,
                'minPageSize' => 1
            ),
            'entities' => $frontendConfig
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generates a JSON file for frontend language translations.
     * This file contains human-readable names for entities and their fields.
     *
     * @return string The JSON formatted content for frontend-language.json.
     */
    public function generateFrontendLanguageJson()
    {
        $frontendConfig = array();
        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $columns = array();
            foreach($tableInfo['columns'] as $colName => $colInfo) {
                if($colInfo['isForeignKey'] && PicoStringUtil::endsWith($colName, '_id'))
                {
                    $columns[$colName] = $this->snakeCaseToTitleCase(substr($colName, 0, strlen($colName) - 3)); 
                }
                else
                {
                    $columns[$colName] = $this->snakeCaseToTitleCase($colName); 
                }
            }
            $frontendConfig[$tableName]['name'] = trim($tableName);
            $frontendConfig[$tableName]['displayName'] = $this->snakeCaseToTitleCase($tableName);
            $frontendConfig[$tableName]['columns'] = $columns;
        }
        return json_encode(['entities' => $frontendConfig], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * Maps a database type to a GraphQL type.
     *
     * @param string $dbType The database column type (e.g., VARCHAR, INT, TIMESTAMP).
     * @return string The corresponding GraphQL type string.
     */
    public function mapDbTypeToGqlType($dbType, $length = null)
    {
        $dbType = strtolower($dbType);
        if (strpos($dbType, 'varchar') !== false || strpos($dbType, 'text') !== false || strpos($dbType, 'date') !== false || strpos($dbType, 'timestamp') !== false) {
            return 'Type::string()';
        }
        if (strpos($dbType, 'decimal') !== false || strpos($dbType, 'float') !== false || strpos($dbType, 'double') !== false) {
            return 'Type::float()';
        }
        if ((strpos($dbType, 'tinyint') !== false && isset($length) && $length == '1') || strpos($dbType, 'bool') !== false || strpos($dbType, 'bit') !== false) {
            return 'Type::boolean()';
        }
        if (strpos($dbType, 'int') !== false) {
            return 'Type::int()';
        }
        return 'Type::string()'; // Default fallback
    }

    /**
     * Normalize a database-specific column type into a generic type.
     *
     * Supported DBMS: MySQL, MariaDB, PostgreSQL, SQLite, SQL Server
     *
     * Possible return values:
     * - string
     * - integer
     * - float
     * - boolean
     * - date
     * - time
     * - datetime
     * - binary
     * - json
     * - uuid
     * - enum
     * - geometry
     * - unknown
     *
     * @param string $dbType The raw column type from the database (e.g., VARCHAR(255), INT, NUMERIC(10,2), TEXT, etc.)
     * @return string One of the normalized type names listed above.
     */
    protected function normalizeDbType($dbType, $length = null)
    {
        $type = strtolower(trim($dbType));

        if($type == 'tinyint' && isset($length) && $length == '1'){
            return 'boolean';
        }

        // Remove size and precision (e.g. varchar(255) → varchar)
        $type = preg_replace('/\(.+\)/', '', $type);

        // Common integer types
        $integerTypes = [
            'int', 'integer', 'smallint', 'mediumint', 'bigint', 'serial', 'bigserial', 'tinyint'
        ];

        // Common float/decimal types
        $floatTypes = [
            'float', 'double', 'decimal', 'numeric', 'real', 'money', 'smallmoney'
        ];

        // Common string/text types
        $stringTypes = [
            'char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext', 'nchar', 'nvarchar', 'citext', 'uuid'
        ];

        // Common date/time types
        $dateTypes = [
            'date', 'datetime', 'timestamp', 'time', 'year'
        ];

        // Boolean types
        $booleanTypes = [
            'boolean', 'bool', 'bit'
        ];

        // Binary types
        $binaryTypes = [
            'blob', 'binary', 'varbinary', 'image', 'bytea'
        ];

        // JSON types
        $jsonTypes = [
            'json', 'jsonb'
        ];

        // Normalize by matching
        if (in_array($type, $integerTypes, true)) {
            // Detect MySQL TINYINT(1) as boolean
            if (strpos($dbType, 'tinyint(1)') !== false) {
                return 'boolean';
            }
            return 'integer';
        }

        if (in_array($type, $floatTypes, true)) {
            return 'float';
        }

        if (in_array($type, $booleanTypes, true)) {
            return 'boolean';
        }

        if (in_array($type, $stringTypes, true)) {
            // UUID is string-like but semantically different
            return $type === 'uuid' ? 'uuid' : 'string';
        }

        if (in_array($type, $jsonTypes, true)) {
            return 'json';
        }

        if (in_array($type, $binaryTypes, true)) {
            return 'binary';
        }

        if (in_array($type, $dateTypes, true)) {
            if ($type === 'time') return 'time';
            if ($type === 'date') return 'date';
            return 'datetime'; // timestamp, datetime, etc.
        }

        // Default fallback
        return 'string';
    }
    
    /**
     * Helper to get a formatted string of fields for the manual.
     * If $noRelations is true, foreign key relations are not expanded.
     * 
     * @param array $tableInfo The table information.
     * @param bool $noRelations Whether to exclude foreign key relations.
     * @return string The formatted fields string.
     */
    protected function getFieldsForManual($tableInfo, $noRelations = false)
    {
        $fieldsString = "";
        if(isset($tableInfo) && isset($tableInfo['columns']))
        {
            foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
                if (!$columnInfo['isForeignKey']) {
                    $fieldsString .= "    " . $columnName . "\r\n";
                } else if (!$noRelations) {
                    $refTableName = $columnInfo['references'];
                    $fieldsString .= "    " . $refTableName . " {\r\n";
                    $fieldsString .= "      " . $this->analyzedSchema[$refTableName]['primaryKey'] . "\r\n";
                    // Add one more field for context
                    foreach ($this->analyzedSchema[$refTableName]['columns'] as $refColName => $refColInfo) {
                        if (!$refColInfo['isForeignKey'] && $refColName !== $this->analyzedSchema[$refTableName]['primaryKey']) {
                            $fieldsString .= "      " . $refColName . "\r\n";
                            break;
                        }
                    }
                    $fieldsString .= "    }\r\n";
                }
            }
        }
        return $fieldsString;
    }

    /**
     * Helper to get formatted input fields for the manual.
     * @param array $tableInfo The table information.
     * @return array An array with two strings: input fields and example values.
     */
    protected function getInputFieldsForManual($tableInfo)
    {
        $inputExampleString = "";
        
        foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
            if ($columnName === $tableInfo['primaryKey']) continue;
            $gqlType = $this->mapDbTypeToGqlType($columnInfo['type'], $columnInfo['length']);
            $exampleValue = '"string"';
            if ($gqlType === 'Type::int()') $exampleValue = '123';
            if ($gqlType === 'Type::float()') $exampleValue = '123.45';
            if ($gqlType === 'Type::boolean()') $exampleValue = 'true';
            $inputExampleString .= "    " . $columnName . ": " . $exampleValue . "\r\n";
        }
        return array($inputExampleString, $inputExampleString);
    }

    /**
     * Get list of reserved column definitions.
     * @return array<string, array> List of backend-handled columns.
     */
    public function getBackendHandledColumns()
    {
        return $this->backendHandledColumns;
    }

    /**
     * Retrieves a simple array containing the names of columns handled by the backend.
     *
     * @return string[] An array containing column names.
     */
    public function getBackendHandledColumnNames()
    {
        $names = [];
        foreach($this->backendHandledColumns as $col)
        {
            $names[] = $col['columnName'];
        }
        return $names;
    }
}