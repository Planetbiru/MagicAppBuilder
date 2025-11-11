<?php

namespace AppBuilder;

use MagicObject\Util\PicoStringUtil;

/**
 * The `GraphQLGenerator` class is a powerful tool designed to automatically generate a complete GraphQL API layer from a JSON file that defines database entities.
 * It inspects the schema to understand tables, columns, primary keys, and foreign key relationships.
 * Based on this analysis, it produces PHP code for GraphQL types, queries, and mutations, as well as a comprehensive API manual in Markdown format.
 * This class streamlines the process of scaffolding a GraphQL API, reducing manual effort and ensuring consistency between the database schema and the API.
 * 
 * @package AppBuilder
 */
class GraphQLGenerator
{
    private $reservedColumns = []; 
    private $activeField = 'active';
    private $backendHandledColumns = [];
    private $displayField = 'name';

    /**
     * @var array Decoded JSON schema.
     */
    private $schema;

    /**
     * @var array Analyzed schema with PK, FK, and column info.
     */
    private $analyzedSchema = array();
    
    /**
     * @var bool Whether to use in-memory caching for queries.
     */
    private $useCache = false;

    /**
     * Constructor.
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
     * Maps a database type to a GraphQL type.
     *
     * @param string $dbType The database column type (e.g., VARCHAR, INT, TIMESTAMP).
     * @return string The corresponding GraphQL type string.
     */
    private function mapDbTypeToGqlType($dbType, $length = null)
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
    function normalizeDbType($dbType, $length = null)
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
     * Generates common Input types for filtering and sorting.
     * @return string
     */
    private function generateUtilityTypes()
    {
        $code = "/**\r\n * ----------------------------------------------------------------------------\r\n * UTILITY TYPE DEFINITIONS (for Filtering & Sorting)\r\n * ----------------------------------------------------------------------------\r\n */\r\n\r\n";

        // Enum for Sort Direction
        $code .= "\$sortDirectionEnum = new EnumType(array(\r\n";
        $code .= "    'name' => 'SortDirection',\r\n";
        $code .= "    'values' => array('ASC', 'DESC')\r\n";
        $code .= "));\r\n\r\n";

        // Input for Sorting
        $code .= "\$sortInputType = new InputObjectType(array(\r\n";
        $code .= "    'name' => 'SortInput',\r\n";
        $code .= "    'fields' => array(\r\n";
        $code .= "        'field' => Type::nonNull(Type::string()),\r\n";
        $code .= "        'direction' => \$sortDirectionEnum\r\n";
        $code .= "    )\r\n";
        $code .= "));\r\n\r\n";

        // Enum for Filter Operators
        $code .= "\$filterOperatorEnum = new EnumType(array(\r\n";
        $code .= "    'name' => 'FilterOperator',\r\n";
        $code .= "    'values' => array('EQUALS', 'NOT_EQUALS', 'CONTAINS', 'GREATER_THAN', 'GREATER_THAN_OR_EQUALS', 'LESS_THAN', 'LESS_THAN_OR_EQUALS', 'IN', 'NOT_IN')\r\n";
        $code .= "));\r\n\r\n";

        // Input for Filtering
        $code .= "\$filterInputType = new InputObjectType(array(\r\n";
        $code .= "    'name' => 'FilterInput',\r\n";
        $code .= "    'fields' => array(\r\n";
        $code .= "        'field' => Type::nonNull(Type::string()),\r\n";
        $code .= "        'value' => Type::nonNull(Type::string()),\r\n";
        $code .= "        'operator' => \$filterOperatorEnum\r\n";
        $code .= "    )\r\n";
        $code .= "));\r\n\r\n";

        return $code;
    }

    /**
     * Generates the PHP code for all GraphQL ObjectTypes.
     *
     * @return string The generated PHP code.
     */
    private function generateTypes()
    {
        $code = "/**\r\n * ----------------------------------------------------------------------------\r\n * GRAPHQL TYPE DEFINITIONS\r\n * ----------------------------------------------------------------------------\r\n */\r\n\r\n";
        
        // Lazy loading requires forward declaration
        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $typeName = $this->camelCase($tableName) . 'Type';
            $code .= "\$" . $this->camelCase($tableName) . "PageType = null;\r\n";
            $code .= "\$" . $typeName . " = null;\r\n";
        }
        $code .= "\r\n";

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $typeName = $this->camelCase($tableName) . 'Type';
            $objectName = ucfirst($this->camelCase($tableName));
            $pageTypeName = $this->camelCase($tableName) . 'PageType';
            $pageObjectName = $objectName . 'Page';

            $code .= "\$" . $typeName . " = new ObjectType(array(\r\n";
            $code .= "    'name' => '" . $objectName . "',\r\n";
            if(!empty($tableInfo['description'])) {
                $description = addslashes($tableInfo['description']);
                $code .= "    'description' => '" . $description . "',\r\n";
            }
            $code .= "    'fields' => function () use (&\$db";
            // Include other types for lazy loading relationships
            foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
                if ($columnInfo['isForeignKey']) {
                    $refTypeName = $this->camelCase($columnInfo['references']) . 'Type';
                    $code .= ", &\$" . $refTypeName;
                }
            }
            $code .= ") {\r\n";
            $code .= "        return array(\r\n";

            foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
                $gqlType = $this->mapDbTypeToGqlType($columnInfo['type'], $columnInfo['length']);
                $code .= "            '" . $columnName . "' => " . $gqlType . ",\r\n";
            }

            // Add relationships
            foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
                if ($columnInfo['isForeignKey']) {
                    $refTableName = $columnInfo['references'];
                    $description = "Get the related " . $refTableName;
                    $refTypeName = $this->camelCase($refTableName) . 'Type';
                    $refPK = $this->analyzedSchema[$refTableName]['primaryKey'];

                    $code .= "            '" . $refTableName . "' => array(\r\n";
                    $code .= "                'type' => \$" . $refTypeName . ",\r\n";
                    $code .= "                'description' => '".$description."',\r\n";
                    $code .= "                'resolve' => function (\$root, \$args) use (\$db) {\r\n";
                    $code .= "                    if (empty(\$root['" . $columnName . "'])) return null;\r\n";


                    if($this->useCache)
                    {        
                        $code .= "                    \$refCacheKey = \"$refTableName:{\$root['$columnName']}\";\r\n";
                        $code .= "                    if (InMemoryCache::has(\$refCacheKey)) {\r\n";
                        $code .= "                        return InMemoryCache::get(\$refCacheKey);\r\n";
                        $code .= "                    }\r\n";
                        $code .= "                    \$stmt = \$db->prepare('SELECT * FROM " . $refTableName . " WHERE " . $refPK . " = :id');\r\n";
                        $code .= "                    \$stmt->execute(array(':id' => \$root['$columnName']));\r\n";
                        $code .= "                    \$refTableNameData = \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
                        $code .= "                    if (isset(\$refTableNameData) && !empty(\$refTableNameData)) {\r\n";
                        $code .= "                        InMemoryCache::set(\$refCacheKey, \$refTableNameData);\r\n";
                        $code .= "                    }\r\n";
                        $code .= "                    return \$refTableNameData;\r\n";                      
                    }
                    else
                    {
                        $code .= "                    \$stmt = \$db->prepare('SELECT * FROM " . $refTableName . " WHERE " . $refPK . " = :id');\r\n";
                        $code .= "                    \$stmt->execute(array(':id' => \$root['" . $columnName . "']));\r\n";
                        $code .= "                    return \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
                    }
                    $code .= "                }\r\n";
                    $code .= "            ),\r\n";
                }
            }

            $code .= "        );\r\n";
            $code .= "    }\r\n";
            $code .= "));\r\n\r\n";
        }

        // Generate Paginated types after main types are declared
        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $typeName = $this->camelCase($tableName) . 'Type';
            $objectName = ucfirst($this->camelCase($tableName));
            $pageTypeName = $this->camelCase($tableName) . 'PageType';
            $pageObjectName = $objectName . 'Page';

            $code .= "\$" . $pageTypeName . " = new ObjectType(array(\r\n";
            $code .= "    'name' => '" . $pageObjectName . "',\r\n";
            $code .= "    'description' => 'Paginated list of " . $this->pluralize($objectName) . "',\r\n";
            $code .= "    'fields' => function() use(&\$" . $typeName . ") {\r\n";
            $code .= "        return array(\r\n";
            $code .= "            'items' => Type::listOf(\$" . $typeName . "),\r\n";
            $code .= "            'total' => Type::int(),\r\n";
            $code .= "            'limit' => Type::int(),\r\n";
            $code .= "            'page' => Type::int(),\r\n";
            $code .= "            'totalPages' => Type::int(),\r\n";
            $code .= "            'hasNext' => Type::boolean(),\r\n";
            $code .= "            'hasPrevious' => Type::boolean(),\r\n";
            $code .= "        );\r\n";
            $code .= "    }\r\n";
            $code .= "));\r\n\r\n";
        }
        return $code;
    }

    /**
     * Generates the PHP code for all GraphQL InputObjectTypes for mutations.
     *
     * @return string The generated PHP code.
     */
    private function generateInputTypes()
    {
        $code = "/**\r\n * ----------------------------------------------------------------------------\r\n * INPUT TYPE DEFINITIONS\r\n * ----------------------------------------------------------------------------\r\n */\r\n\r\n";
        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $inputTypeName = $this->camelCase($tableName) . 'InputType';
            $objectName = ucfirst($this->camelCase($tableName)) . 'Input';

            $code .= "\$" . $inputTypeName . " = new InputObjectType(array(\r\n";
            $code .= "    'name' => '" . $objectName . "',\r\n";
            $code .= "    'fields' => array(\r\n";

            foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
                // Exclude primary key from input
                if ($columnName === $tableInfo['primaryKey'] && $columnInfo['primaryKeyValue'] == 'autogenerated') {
                    continue;
                }
                $gqlType = $this->mapDbTypeToGqlType($columnInfo['type'], $columnInfo['length']);
                $code .= "        '" . $columnName . "' => " . $gqlType . ",\r\n";
            }

            $code .= "    )\r\n";
            $code .= "));\r\n\r\n";
        }
        return $code;
    }

    /**
     * Generates the PHP code for the root Query type.
     *
     * @return string The generated PHP code.
     */
    private function generateQueries()
    {
        $code = "/**\r\n * ----------------------------------------------------------------------------\r\n * QUERY TYPE DEFINITION\r\n * ----------------------------------------------------------------------------\r\n */\r\n\r\n";
        $code .= "\$queryType = new ObjectType(array(\r\n";
        $code .= "    'name' => 'Query',\r\n";
        $code .= "    'fields' => array(\r\n";

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $camelName = $this->camelCase($tableName);
            $pluralCamelName = $this->pluralize($camelName);
            $typeName = $camelName . 'Type';
            $primaryKey = $tableInfo['primaryKey'];
            $pageTypeName = $camelName . 'PageType';

            // Detail Query
            $code .= "        // Query for a single " . $tableName . "\r\n";
            $code .= "        '" . $camelName . "' => [\r\n";
            $code .= "            'type' => \$" . $typeName . ",\r\n";
            $code .= "            'args' => array(\r\n";
            $code .= "                'id' => Type::nonNull(Type::string()),\r\n";
            $code .= "            ),\r\n";
            $code .= "            'resolve' => function (\$root, \$args) use (\$db) {\r\n";
            
            if($this->useCache)
            {        
                $code .= "                \$cacheKey = \"$tableName:{\$args['id']}\";\r\n";
                $code .= "                if (InMemoryCache::has(\$cacheKey)) {\r\n";
                $code .= "                    return InMemoryCache::get(\$cacheKey);\r\n";
                $code .= "                }\r\n";
                $code .= "                \$stmt = \$db->prepare('SELECT * FROM " . $tableName . " WHERE " . $primaryKey . " = :id');\r\n";
                $code .= "                \$stmt->execute(array(':id' => \$args['id']));\r\n";
                $code .= "                \$tableNameData = \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
                $code .= "                if (isset(\$tableNameData) && !empty(\$tableNameData)) {\r\n";
                $code .= "                    InMemoryCache::set(\$cacheKey, \$tableNameData);\r\n";
                $code .= "                }\r\n";
                $code .= "                return \$tableNameData;\r\n";
            
            }
            else
            {
                $code .= "                \$stmt = \$db->prepare('SELECT * FROM " . $tableName . " WHERE " . $primaryKey . " = :id');\r\n";
                $code .= "                \$stmt->execute(array(':id' => \$args['id']));\r\n";
                $code .= "                return \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
            }
            
            $code .= "            },\r\n";
            $code .= "        ],\r\n\r\n";

            // List Query with Filter and Pagination
            $code .= "        // Query for a list of " . $tableName . "s\r\n";
            $code .= "        '" . $pluralCamelName . "' => [\r\n";
            $code .= "            'type' => \$" . $pageTypeName . ",\r\n";
            $code .= "            'args' => array(\r\n";
            $code .= "                'limit' => Type::int(),\r\n";
            $code .= "                'offset' => Type::int(),\r\n";
            $code .= "                'orderBy' => Type::listOf(\$sortInputType),\r\n";
            $code .= "                'filter' => Type::listOf(\$filterInputType)\r\n";
            $code .= "            ),\r\n";
            $code .= "            'resolve' => function (\$root, \$args) use (\$db) {\r\n";
            $code .= "                // Whitelist valid columns for filtering and sorting to prevent SQL injection.\r\n";
            $code .= "                // You can customize these arrays to allow/disallow columns for filtering and sorting.\r\n";
            $code .= "                \$allowedFilterColumns = array('" . implode("', '", array_keys($tableInfo['columns'])) . "');\r\n";
            $code .= "                \$allowedSortColumns   = array('" . implode("', '", array_keys($tableInfo['columns'])) . "');\r\n\r\n";
            $code .= "                \$baseSql = 'FROM " . $tableName . "';\r\n";
            $code .= "                \$countSql = 'SELECT COUNT(*) as total ' . \$baseSql;\r\n";
            $code .= "                \$dataSql = 'SELECT * ' . \$baseSql;\r\n";
            $code .= "                \$where = array();\r\n";
            $code .= "                \$params = array();\r\n";
            $code .= "                \$paramIndex = 0;\r\n\r\n";
            // Filter logic
            $code .= "                if (!empty(\$args['filter'])) {\r\n";
            $code .= "                    foreach (\$args['filter'] as \$filter) {\r\n";
            $code .= "                        if (!in_array(\$filter['field'], \$allowedFilterColumns)) continue;\r\n";
            $code .= "                        \$operator = isset(\$filter['operator']) ? \$filter['operator'] : 'EQUALS';\r\n";
            $code .= "                        \$paramName = ':' . \$filter['field'] . \$paramIndex++;\r\n";
            $code .= "                        switch (\$operator) {\r\n";
            $code .= "                            case 'CONTAINS':\r\n";
            $code .= "                                \$where[] = \$filter['field'] . ' LIKE ' . \$paramName;\r\n";
            $code .= "                                \$params[\$paramName] = '%' . \$filter['value'] . '%';\r\n";
            $code .= "                                break;\r\n";
            $code .= "                            case 'GREATER_THAN_OR_EQUALS':\r\n";
            $code .= "                                \$where[] = \$filter['field'] . ' >= ' . \$paramName;\r\n";
            $code .= "                                \$params[\$paramName] = \$filter['value'];\r\n";
            $code .= "                                break;\r\n";
            $code .= "                            case 'GREATER_THAN':\r\n";
            $code .= "                                \$where[] = \$filter['field'] . ' > ' . \$paramName;\r\n";
            $code .= "                                \$params[\$paramName] = \$filter['value'];\r\n";
            $code .= "                                break;\r\n";
            $code .= "                            case 'LESS_THAN_OR_EQUALS':\r\n";
            $code .= "                                \$where[] = \$filter['field'] . ' <= ' . \$paramName;\r\n";
            $code .= "                                \$params[\$paramName] = \$filter['value'];\r\n";
            $code .= "                                break;\r\n";
            $code .= "                            case 'LESS_THAN':\r\n";
            $code .= "                                \$where[] = \$filter['field'] . ' < ' . \$paramName;\r\n";
            $code .= "                                \$params[\$paramName] = \$filter['value'];\r\n";
            $code .= "                                break;\r\n";
            $code .= "                            case 'IN':\r\n";
            $code .= "                            case 'NOT_IN':\r\n";
            $code .= "                                \$values = array_map('trim', explode(',', \$filter['value']));\r\n";
            $code .= "                                if (!empty(\$values)) {\r\n";
            $code .= "                                    \$inPlaceholders = array();\r\n";
            $code .= "                                    foreach (\$values as \$idx => \$val) {\r\n";
            $code .= "                                        \$inParamName = \$paramName . '_' . \$idx;\r\n";
            $code .= "                                        \$inPlaceholders[] = \$inParamName;\r\n";
            $code .= "                                        \$params[\$inParamName] = \$val;\r\n";
            $code .= "                                    }\r\n";
            $code .= "                                    \$operatorStr = (\$operator === 'NOT_IN') ? 'NOT IN' : 'IN';\r\n";
            $code .= "                                    \$where[] = \$filter['field'] . ' ' . \$operatorStr . ' (' . implode(', ', \$inPlaceholders) . ')';\r\n";
            $code .= "                                }\r\n";
            $code .= "                                break;\r\n";
            $code .= "                            case 'NOT_EQUALS':\r\n";
            $code .= "                                \$where[] = \$filter['field'] . ' != ' . \$paramName;\r\n";
            $code .= "                                \$params[\$paramName] = \$filter['value'];\r\n";
            $code .= "                                break;\r\n";
            $code .= "                            case 'EQUALS':\r\n";
            $code .= "                            default:\r\n";
            $code .= "                                \$where[] = \$filter['field'] . ' = ' . \$paramName;\r\n";
            $code .= "                                \$params[\$paramName] = \$filter['value'];\r\n";
            $code .= "                                break;\r\n";
            $code .= "                        }\r\n";
            $code .= "                    }\r\n";
            $code .= "                }\r\n";
            $code .= "                if (count(\$where) > 0) {\r\n";
            $code .= "                    \$whereClause = ' WHERE ' . implode(' AND ', \$where);\r\n";
            $code .= "                    \$countSql .= \$whereClause;\r\n";
            $code .= "                    \$dataSql .= \$whereClause;\r\n";
            $code .= "                }\r\n\r\n";
            // OrderBy logic
            $code .= "                if (!empty(\$args['orderBy'])) {\r\n";
            $code .= "                    \$orderParts = array();\r\n";
            $code .= "                    foreach (\$args['orderBy'] as \$order) {\r\n";
            $code .= "                        if (in_array(\$order['field'], \$allowedSortColumns)) {\r\n";
            $code .= "                            \$direction = (isset(\$order['direction']) && strtoupper(\$order['direction']) === 'DESC') ? 'DESC' : 'ASC';\r\n";
            $code .= "                            \$orderParts[] = \$order['field'] . ' ' . \$direction;\r\n";
            $code .= "                        }\r\n";
            $code .= "                    }\r\n";
            $code .= "                    if (!empty(\$orderParts)) {\r\n";
            $code .= "                        \$dataSql .= ' ORDER BY ' . implode(', ', \$orderParts);\r\n";
            $code .= "                    }\r\n";
            $code .= "                }\r\n\r\n";
            $code .= "                // Get total count\r\n";
            $code .= "                \$countStmt = \$db->prepare(\$countSql);\r\n";
            $code .= "                \$countStmt->execute(\$params);\r\n";
            $code .= "                \$total = (int)\$countStmt->fetchColumn();\r\n\r\n";
            $code .= "                \$limit = isset(\$args['limit']) ? (int)\$args['limit'] : 10;\r\n";
            $code .= "                \$offset = isset(\$args['offset']) ? (int)\$args['offset'] : 0;\r\n";
            $code .= "                \$page = (\$limit > 0) ? floor(\$offset / \$limit) + 1 : 1;\r\n";
            $code .= "                \$totalPages = (\$limit > 0 && \$total > 0) ? ceil(\$total / \$limit) : 0;\r\n\r\n";
            $code .= "                \$hasNext = (\$offset + \$limit) < \$total;\r\n";
            $code .= "                \$hasPrevious = \$offset > 0;\r\n\r\n";
            // Pagination logic
            $code .= "                if (\$limit > 0) {\r\n";
            $code .= "                    \$dataSql .= ' LIMIT ' . \$limit . ' OFFSET ' . \$offset;\r\n";
            $code .= "                }\r\n";
            $code .= "                \$stmt = \$db->prepare(\$dataSql);\r\n";
            $code .= "                \$stmt->execute(\$params);\r\n";
            $code .= "                \$items = \$stmt->fetchAll(PDO::FETCH_ASSOC);\r\n\r\n";
            $code .= "                return array(\r\n";
            $code .= "                    'total' => \$total,\r\n";
            $code .= "                    'limit' => \$limit,\r\n";
            $code .= "                    'page' => \$page,\r\n";
            $code .= "                    'totalPages' => \$totalPages,\r\n";
            $code .= "                    'hasNext' => \$hasNext,\r\n";
            $code .= "                    'hasPrevious' => \$hasPrevious,\r\n";
            $code .= "                    'items' => \$items,\r\n";
            $code .= "                );\r\n";
            $code .= "            },\r\n";
            $code .= "        ],\r\n\r\n";
        }

        $code = rtrim($code, ",\r\n\r\n") . "\r\n";
        $code .= "    )\r\n";
        $code .= "));\r\n\r\n";

        return $code;
    }

    /**
     * Undocumented function
     *
     * @param [type] $tableInfo
     * @return array
     */
    private function getColumnNames($tableInfo)
    {
        $columnNames = [];
        foreach($tableInfo['columns'] as $columnName => $col) {
            $columnNames[] = $columnName;
        }
        return $columnNames;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    private function backendHandledColumns()
    {
        $backendHandledColumns = [];
        foreach ($this->backendHandledColumns as $backendHandledColumnKey => $col) {
            $backendHandledColumns[] = $col['columnName'];
        }
        return $backendHandledColumns;
    }

    private function hasBackendHandledColumns($tableInfo)
    {
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
     * Generates the PHP code for the root Mutation type.
     *
     * @return string The generated PHP code.
     */
    private function generateMutations()
    {
        $code = "/**\r\n * ----------------------------------------------------------------------------\r\n * MUTATION TYPE DEFINITION\r\n * ----------------------------------------------------------------------------\r\n */\r\n\r\n";
        $code .= "\$mutationType = new ObjectType(array(\r\n";
        $code .= "    'name' => 'Mutation',\r\n";
        $code .= "    'fields' => array(\r\n";

        


        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $camelName = $this->camelCase($tableName);
            $typeName = $camelName . 'Type';
            $inputTypeName = $camelName . 'InputType';
            $primaryKey = $tableInfo['primaryKey'];
            $activeField = $this->activeField;

            $pkCol = null;
            foreach ($tableInfo['columns'] as $columnName => $col) {
                if ($col['isPrimaryKey']) {
                    $pkCol = $col;
                    break;
                }
            }


            $hasBackendHandledColumns = $this->hasBackendHandledColumns($tableInfo);

            // Create Mutation
            $code .= "        'create" . ucfirst($camelName) . "' => array(\r\n";
            $code .= "            'type' => \$" . $typeName . ",\r\n";
            $code .= "            'args' => array('input' => Type::nonNull(\$" . $inputTypeName . ")),\r\n";
            $code .= "            'resolve' => function (\$root, \$args) use (\$db, \$appTimeCreate, \$appTimeEdit, \$appAdminCreate, \$appAdminEdit, \$appIpCreate, \$appIpEdit) {\r\n";
            $code .= "                \$allowedColumns = array('" . implode("', '", array_keys($tableInfo['columns'])) . "');\r\n";
            $code .= "                \$args = normalizeBooleanInputs(\$args, \$db);\r\n";
            $code .= "                \$input = \$args['input'];\r\n";
            $code .= "                \$input = array_filter(\$args['input'], function(\$key) use (\$allowedColumns) {\r\n";
            $code .= "                    return in_array(\$key, \$allowedColumns);\r\n";
            $code .= "                }, ARRAY_FILTER_USE_KEY);\r\n\r\n";
            if ($hasBackendHandledColumns)
            {
                $code .= "\r\n";
                $code .= "                // Handle backend handled columns begin\r\n";
                foreach ($this->backendHandledColumns as $backendHandledColumnKey => $col) {
                    $colName = $col['columnName'];
                    if($backendHandledColumnKey == 'timeCreate')
                    {
                        $code .= "                \$input['$colName'] = \$appTimeCreate;\r\n";
                    }
                    else if($backendHandledColumnKey == 'timeEdit')
                    {
                        $code .= "                \$input['$colName'] = \$appTimeEdit;\r\n";
                    }
                    else if($backendHandledColumnKey == 'adminCreate')
                    {
                        $code .= "                \$input['$colName'] = \$appAdminCreate;\r\n";
                    }
                    else if($backendHandledColumnKey == 'adminEdit')
                    {
                        $code .= "                \$input['$colName'] = \$appAdminEdit;\r\n";
                    }
                    else if($backendHandledColumnKey == 'ipCreate')
                    {
                        $code .= "                \$input['$colName'] = \$appIpCreate;\r\n";
                    }
                    else if($backendHandledColumnKey == 'ipEdit')
                    {
                        $code .= "                \$input['$colName'] = \$appIpEdit;\r\n";
                    }
                }
                $code .= "                // Handle backend handled columns end\r\n\r\n";
            }

            if(isset($pkCol))
            {
                if(isset($pkCol['primaryKeyValue']) && $pkCol['primaryKeyValue'] == 'autogenerated')
                {
                    $code .= "                \$id = generateNewId();\r\n";
                    $code .= "                \$input['" . $primaryKey . "'] = \$id;\r\n";
                }
                else if($pkCol['isAutoIncrement'])
                {
                    $code .= "                \$input['" . $primaryKey . "'] = null;\r\n";
                }
            }
            
            $code .= "                \$columns = array_keys(\$input);\r\n";
            $code .= "                \$placeholders = array_map(function(\$c) { return ':' . \$c; }, \$columns);\r\n";
            $code .= "                \$sql = 'INSERT INTO " . $tableName . " (' . implode(', ', \$columns) . ') VALUES (' . implode(', ', \$placeholders) . ')';\r\n";
            $code .= "                \$stmt = \$db->prepare(\$sql);\r\n";
            $code .= "                \$params = array();\r\n";
            $code .= "                foreach(\$input as \$key => \$value) { \$params[':' . \$key] = \$value; }\r\n";
            $code .= "                \$stmt->execute(\$params);\r\n";
            $code .= "                \$stmt = \$db->prepare('SELECT * FROM " . $tableName . " WHERE " . $primaryKey . " = :id');\r\n";

            if(isset($pkCol) && $pkCol['isAutoIncrement']) 
            {
                $code .= "                \$lastId = getLastInsertId(\$db, '$tableName', '$primaryKey');\r\n";
                $code .= "                \$stmt->execute(array(':id' => \$lastId));\r\n";
            } 
            else
            {
                $code .= "                \$stmt->execute(array(':id' => \$input['" . $primaryKey . "']));\r\n";
            }
            
            $code .= "                return \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
            $code .= "            }\r\n";
            $code .= "        ),\r\n\r\n";

            // Update Mutation
            $code .= "        'update" . ucfirst($camelName) . "' => array(\r\n";
            $code .= "            'type' => \$" . $typeName . ",\r\n";
            $code .= "            'args' => array(\r\n";
            $code .= "                'id' => Type::nonNull(Type::string()),\r\n";
            $code .= "                'input' => Type::nonNull(\$" . $inputTypeName . ")\r\n";
            $code .= "            ),\r\n";
            $code .= "            'resolve' => function (\$root, \$args) use (\$db, \$appTimeCreate, \$appTimeEdit, \$appAdminCreate, \$appAdminEdit, \$appIpCreate, \$appIpEdit) {\r\n";
            
            $code .= "                \$id = \$args['id'];\r\n";

            $code .= "                \$allowedColumns = array('" . implode("', '", array_keys($tableInfo['columns'])) . "');\r\n";
            $code .= "                \$args = normalizeBooleanInputs(\$args, \$db);\r\n";
            $code .= "                \$input = array_filter(\$args['input'], function(\$key) use (\$allowedColumns) {\r\n";
            $code .= "                    return in_array(\$key, \$allowedColumns);\r\n";
            $code .= "                }, ARRAY_FILTER_USE_KEY);\r\n\r\n";
            if ($hasBackendHandledColumns)
            {
                $code .= "\r\n";
                $code .= "                // Handle backend handled columns begin\r\n";
                foreach ($this->backendHandledColumns as $backendHandledColumnKey => $col) {
                    $colName = $col['columnName'];
                    if($backendHandledColumnKey == 'timeEdit')
                    {
                        $code .= "                \$input['$colName'] = \$appTimeEdit;\r\n";
                    }
                    else if($backendHandledColumnKey == 'adminEdit')
                    {
                        $code .= "                \$input['$colName'] = \$appAdminEdit;\r\n";
                    }
                    else if($backendHandledColumnKey == 'ipEdit')
                    {
                        $code .= "                \$input['$colName'] = \$appIpEdit;\r\n";
                    }
                }
                $code .= "                // Handle backend handled columns end\r\n\r\n";
            }

            if(isset($pkCol) && isset($pkCol['primaryKeyValue']) && $pkCol['primaryKeyValue'] == 'manual-all')
            {
                $code .= "                if(!isset(\$input['" . $primaryKey . "']) || \$input['" . $primaryKey . "'] === '') {\r\n";
                $code .= "                    \$input['" . $primaryKey . "'] = \$id;\r\n";
                $code .= "                }\r\n";
                $code .= "                \$newId = \$input['" . $primaryKey . "'];\r\n";
            }
            else
            {
                $code .= "                \$newId = \$id;\r\n";
            }

            $code .= "                \$setPart = array();\r\n";
            $code .= "                \$params = array(':id' => \$id);\r\n";
            $code .= "                foreach (\$input as \$field => \$value) {\r\n";
            $code .= "                    if (\$value !== null) {\r\n";
            $code .= "                        \$setPart[] = \$field . ' = :' . \$field;\r\n";
            $code .= "                        \$params[':' . \$field] = \$value;\r\n";
            $code .= "                    }\r\n";
            $code .= "                }\r\n";
            $code .= "                if (empty(\$setPart)) { throw new Exception('Update input cannot be empty.'); }\r\n";
            $code .= "                \$sql = 'UPDATE " . $tableName . " SET ' . implode(', ', \$setPart) . ' WHERE " . $primaryKey . " = :id';\r\n";
            $code .= "                \$stmt = \$db->prepare(\$sql);\r\n";
            $code .= "                \$stmt->execute(\$params);\r\n";
            $code .= "                \$stmt = \$db->prepare('SELECT * FROM " . $tableName . " WHERE " . $primaryKey . " = :id');\r\n";
            $code .= "                \$stmt->execute(array(':id' => \$newId));\r\n";
            $code .= "                return \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
            $code .= "            }\r\n";
            $code .= "        ),\r\n\r\n";

            // Activate/Deactivate Mutation (if applicable)
            if ($tableInfo['hasActiveColumn']) {
                $code .= "        'toggle" . ucfirst($camelName) . "Active' => array(\r\n";
                $code .= "            'type' => \$" . $typeName . ",\r\n";
                $code .= "            'args' => array(\r\n";
                $code .= "                'id' => Type::nonNull(Type::string()),\r\n";
                $code .= "                '".$activeField."' => Type::nonNull(Type::boolean())\r\n";
                $code .= "            ),\r\n";
                $code .= "            'resolve' => function (\$root, \$args) use (\$db) {\r\n";
                $code .= "                \$sql = 'UPDATE " . $tableName . " SET ".$activeField." = :active WHERE " . $primaryKey . " = :id';\r\n";
                $code .= "                \$stmt = \$db->prepare(\$sql);\r\n";
                $code .= "                \$args['".$activeField."'] = normalizeBoolean(\$args['".$activeField."'], \$db);\r\n";
                $code .= "                \$stmt->execute(array(':id' => \$args['id'], ':active' => \$args['".$activeField."']));\r\n";
                $code .= "                \$stmt = \$db->prepare('SELECT * FROM " . $tableName . " WHERE " . $primaryKey . " = :id');\r\n";
                $code .= "                \$stmt->execute(array(':id' => \$args['id']));\r\n";
                $code .= "                return \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
                $code .= "            }\r\n";
                $code .= "        ),\r\n\r\n";
            }


            // Delete Mutation
            $code .= "        'delete" . ucfirst($camelName) . "' => array(\r\n";
            $code .= "            'type' => Type::boolean(),\r\n";
            $code .= "            'args' => array('id' => Type::nonNull(Type::string())),\r\n";
            $code .= "            'resolve' => function (\$root, \$args) use (\$db) {\r\n";
            if ($tableInfo['hasActiveColumn']) {
                // Soft delete
                $code .= "                \$sql = 'DELETE FROM " . $tableName . " WHERE " . $primaryKey . " = :id';\r\n";
            } else {
                // Hard delete
                $code .= "                \$sql = 'DELETE FROM " . $tableName . " WHERE " . $primaryKey . " = :id';\r\n";
            }
            $code .= "                \$stmt = \$db->prepare(\$sql);\r\n";
            $code .= "                \$stmt->execute(array(':id' => \$args['id']));\r\n";
            $code .= "                return \$stmt->rowCount() > 0;\r\n";
            $code .= "            }\r\n";
            $code .= "        ),\r\n\r\n";
        }

        $code = rtrim($code, ",\r\n\r\n") . "\r\n";
        $code .= "    )\r\n";
        $code .= "));\r\n\r\n";

        return $code;
    }

    /**
     * Generates a markdown manual with examples for all queries and mutations.
     *
     * @return string The markdown content.
     */
    public function generateManual()
    {
        $manualContent = "# GraphQL API Manual\r\n\r\n";
        $manualContent .= "This document provides examples for all available queries and mutations.\r\n\r\n";
        
        $manualContent .= "## Dependency Installation\r\n\r\n";
        $manualContent .= "Before you can run the API, you need to install the required dependencies using Composer. Navigate to the directory where this `graphql.php` file is located and run the following command in your terminal:\r\n\r\n";
        $manualContent .= "```bash\r\n";
        $manualContent .= "composer install\r\n";
        $manualContent .= "composer require webonyx/graphql-php:^14.11\r\n";
        $manualContent .= "```\r\n\r\n";
        $manualContent .= "This will download and install all the necessary libraries listed in the `composer.json` file.\r\n\r\n";

        $manualContent .= "## Database Connection\r\n\r\n";
        $manualContent .= "This API requires a database connection. You must configure the `database.php` file to create a PDO instance. Here is an example for connecting to a MySQL database:\r\n\r\n";
        $manualContent .= "```php\r\n";
        $manualContent .= "// file: database.php\r\n";
        $manualContent .= "\$host         = '127.0.0.1';\r\n";
        $manualContent .= "\$databaseName = 'your_database_name';\r\n";
        $manualContent .= "\$user         = 'your_username';\r\n";
        $manualContent .= "\$pass         = 'your_password';\r\n";
        $manualContent .= "\$charset      = 'utf8mb4';\r\n\r\n";
        $manualContent .= "\$dsn          = \"mysql:host=\$host;dbname=\$databaseName;charset=\$charset\";\r\n";
        $manualContent .= "\$options = [\r\n";
        $manualContent .= "    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\r\n";
        $manualContent .= "    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\r\n";
        $manualContent .= "    PDO::ATTR_EMULATE_PREPARES   => false,\r\n";
        $manualContent .= "];\r\n\r\n";
        $manualContent .= "try {\r\n";
        $manualContent .= "     \$db = new PDO(\$dsn, \$user, \$pass, \$options);\r\n";
        $manualContent .= "} catch (\\PDOException \$e) {\r\n";
        $manualContent .= "     throw new \\PDOException(\$e->getMessage(), (int)\$e->getCode());\r\n";
        $manualContent .= "}\r\n";
        $manualContent .= "```\r\n\r\n";
        $manualContent .= "Make sure to replace `your_database_name`, `your_username`, and `your_password` with your actual database credentials.\r\n\r\n";


        $manualContent .= "### Integration with MagicAppBuilder\r\n\r\n";
        $manualContent .= "If you are integrating with MagicAppBuilder, you can use the database connection from an existing connection:\r\n\r\n";
        $manualContent .= "```php\r\n";
        $manualContent .= "\$db = \$database->getDatabaseConnection();\r\n";
        $manualContent .= "```\r\n\r\n";
        $manualContent .= "where `\$db` is an instance of PDO.\r\n\r\n";

        $manualContent .= "---\r\n\r\n";

        $manualContent .= "## In-Memory Cache\r\n\r\n";
        $manualContent .= "The auto-generated API includes a simple in-memory caching mechanism to improve performance and reduce database load. This cache temporarily stores query results, so that subsequent identical requests can be served from memory instead of accessing the database again. This is particularly effective for queries that fetch a single item by its ID.\r\n\r\n";
        $manualContent .= "### Benefits\r\n\r\n";
        $manualContent .= "- **Reduced Database Load**: Fewer queries are executed on the database.\r\n";
        $manualContent .= "- **Faster Response Times**: Retrieving data from memory is significantly faster than from the database.\r\n\r\n";
        $manualContent .= "### How to Use\r\n\r\n";
        $manualContent .= "You can enable or disable this cache in the generated `graphql.php` file:\r\n\r\n";
        $manualContent .= "```php\r\n";
        $manualContent .= "// Enable the cache\r\n";
        $manualContent .= "InMemoryCache::setEnabled(true);\r\n\r\n";
        $manualContent .= "// Disable the cache\r\n";
        $manualContent .= "InMemoryCache::setEnabled(false);\r\n";
        $manualContent .= "```\r\n\r\n";
        $manualContent .= "By default, the cache is disabled (`false`). The caching logic is provided by the included `inc/InMemoryCache.php` file in the generated project.\r\n\r\n";

        $manualContent .= "---\r\n\r\n";
        
        $manualContent .= "## Security Considerations\r\n\r\n";
        $manualContent .= "For production or any publicly exposed environments, it is strongly recommended to remove files that are not essential for the API's runtime operations. This helps to minimize the attack surface and prevent potential information disclosure.\r\n\r\n";
        $manualContent .= "Please consider deleting the following files from your production server:\r\n\r\n";
        $manualContent .= "- **`manual.md`**: This file contains API documentation and is not needed for runtime.\r\n";
        $manualContent .= "- **`manual.html`**: An HTML version of the API documentation, not needed for runtime.\r\n";
        $manualContent .= "\r\n";
        $manualContent .= "---\r\n\r\n";

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $camelName = $this->camelCase($tableName);
            $pluralCamelName = $this->pluralize($camelName);
            $ucCamelName = ucfirst($camelName);

            $manualContent .= "## " . $ucCamelName . "\r\n\r\n";

            // --- Get Fields for examples ---
            $fieldsString = $this->getFieldsForManual($tableInfo);
            $mutationFieldsString = $this->getFieldsForManual($tableInfo, true); // No relations for mutation return

            // --- Query Examples ---
            $manualContent .= "### Queries\r\n\r\n";

            // Get Single Item
            $manualContent .= "#### Get a single " . $camelName . "\r\n\r\n";
            $manualContent .= "```graphql\r\n";
            $manualContent .= "query Get" . $ucCamelName . " {\r\n";
            $manualContent .= "  " . $camelName . "(id: \"your-" . $camelName . "-id\") {\r\n";
            $manualContent .= $fieldsString;
            $manualContent .= "  }\r\n";
            $manualContent .= "}\r\n";
            $manualContent .= "```\r\n\r\n";

            // Get List
            $manualContent .= "#### Get a list of " . $pluralCamelName . " (with filter & sort)\r\n\r\n";
            $manualContent .= "Supports `limit`, `offset`, `orderBy`, and `filter`.\r\n\r\n";

            // Find a good column for the filter example
            $filterField = $tableInfo['primaryKey'];
            $filterValue = '"your-' . $camelName . '-id"';
            $filterOperator = 'EQUALS';

            // Prefer 'name' or 'title' for a CONTAINS filter
            foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
                if (($columnName === 'name' || $columnName === 'title') && !$columnInfo['isForeignKey']) {
                    $filterField = $columnName;
                    $filterValue = '"some-text"';
                    $filterOperator = 'CONTAINS';
                    break;
                }
            }

            $manualContent .= "```graphql\r\n";
            $manualContent .= "query Get" . ucfirst($pluralCamelName) . " {\r\n";
            $manualContent .= "  " . $pluralCamelName . "(\r\n    limit: 10, \r\n    offset: 0, \r\n    orderBy: [{field: \"" . $tableInfo['primaryKey'] . "\", direction: DESC}],\r\n    filter: [{field: \"" . $filterField . "\", value: " . $filterValue . ", operator: " . $filterOperator . "}]\r\n  ) {\r\n";
            $manualContent .= "    items {\r\n";
            $manualContent .= preg_replace('/^/m', '      ', $fieldsString); // Indent fields
            $manualContent .= "    }\r\n";
            $manualContent .= "    total\r\n";
            $manualContent .= "    limit\r\n";
            $manualContent .= "    page\r\n";
            $manualContent .= "    totalPages\r\n";
            $manualContent .= "    hasNext\r\n";
            $manualContent .= "    hasPrevious\r\n";
            $manualContent .= "  }\r\n";
            $manualContent .= "}\r\n";
            $manualContent .= "```\r\n\r\n";

            // --- Mutation Examples ---
            $manualContent .= "### Mutations\r\n\r\n";

            // Get Input Fields for mutations
            list($inputFieldsString, $inputExampleString) = $this->getInputFieldsForManual($tableInfo);

            // Create
            $manualContent .= "#### Create a new " . $camelName . "\r\n\r\n";
            $manualContent .= "```graphql\r\n";
            $manualContent .= "mutation Create" . $ucCamelName . " {\r\n";
            $manualContent .= "  create" . $ucCamelName . "(input: {\r\n" . $inputExampleString . "  }) {\r\n";
            $manualContent .= $mutationFieldsString;
            $manualContent .= "  }\r\n";
            $manualContent .= "}\r\n";
            $manualContent .= "```\r\n\r\n";

            // Update
            $manualContent .= "#### Update an existing " . $camelName . "\r\n\r\n";
            $manualContent .= "```graphql\r\n";
            $manualContent .= "mutation Update" . $ucCamelName . " {\r\n";
            $manualContent .= "  update" . $ucCamelName . "(id: \"your-" . $camelName . "-id\", input: {\r\n" . $inputExampleString . "  }) {\r\n";
            $manualContent .= $mutationFieldsString;
            $manualContent .= "  }\r\n";
            $manualContent .= "}\r\n";
            $manualContent .= "```\r\n\r\n";

            // Delete
            $manualContent .= "#### Delete a " . $camelName . "\r\n\r\n";
            $manualContent .= "Returns `true` on success.\r\n\r\n";
            $manualContent .= "```graphql\r\n";
            $manualContent .= "mutation Delete" . $ucCamelName . " {\r\n";
            $manualContent .= "  delete" . $ucCamelName . "(id: \"your-" . $camelName . "-id\")\r\n";
            $manualContent .= "}\r\n";
            $manualContent .= "```\r\n\r\n";
        }

        // --- API Reference Guide ---
        $manualContent .= "## API Reference Guide\r\n\r\n";
        $manualContent .= "This section provides a reference for common arguments used in list queries.\r\n\r\n";

        // Filtering
        $manualContent .= "### Filtering (`filter`)\r\n\r\n";
        $manualContent .= "The `filter` argument allows you to narrow down results based on field values. It accepts a list of filter objects, which are combined with `AND` logic.\r\n\r\n";
        $manualContent .= "| Operator       | Description                                      | Example                                                |\r\n";
        $manualContent .= "|----------------|--------------------------------------------------|--------------------------------------------------------|\r\n";
        $manualContent .= "| `EQUALS`       | Finds records where the field exactly matches the value. | `{field: \"status\", value: \"published\"}`                |\r\n";
        $manualContent .= "| `NOT_EQUALS`   | Finds records where the field does not match the value. | `{field: \"status\", value: \"archived\", operator: NOT_EQUALS}` |\r\n";
        $manualContent .= "| `CONTAINS`     | Finds records where the text field contains the value (`LIKE '%value%'`). | `{field: \"title\", value: \"love\", operator: CONTAINS}` |\r\n";
        $manualContent .= "| `GREATER_THAN_OR_EQUALS` | Finds records where the numeric/date field is greater than or equal to the value. | `{field: \"price\", value: \"99.99\", operator: GREATER_THAN_OR_EQUALS}` |\r\n";
        $manualContent .= "| `GREATER_THAN` | Finds records where the numeric/date field is greater than the value. | `{field: \"price\", value: \"100\", operator: GREATER_THAN}` |\r\n";
        $manualContent .= "| `LESS_THAN_OR_EQUALS`    | Finds records where the numeric/date field is less than or equal to the value. | `{field: \"stock\", value: \"10\", operator: LESS_THAN_OR_EQUALS}`   |\r\n";
        $manualContent .= "| `LESS_THAN`    | Finds records where the numeric/date field is less than the value. | `{field: \"stock\", value: \"10\", operator: LESS_THAN}`   |\r\n";
        $manualContent .= "| `IN` / `NOT_IN` | Finds records where the field value is in (or not in) a comma-separated list of values. | `{field: \"category_id\", value: \"1,2,3\", operator: IN}` |\r\n\r\n";

        // Sorting
        $manualContent .= "### Sorting (`orderBy`)\r\n\r\n";
        $manualContent .= "The `orderBy` argument sorts the results. It accepts a list of sort objects.\r\n\r\n";
        $manualContent .= "- `field`: The name of the field to sort by (e.g., `\"name\"`).\r\n";
        $manualContent .= "- `direction`: The sort direction. Can be `ASC` (ascending) or `DESC` (descending). Defaults to `ASC`.\r\n\r\n";
        $manualContent .= "**Example:** `orderBy: [{field: \"release_date\", direction: DESC}]`\r\n\r\n";

        // Pagination
        $manualContent .= "### Pagination (`limit` & `offset`)\r\n\r\n";
        $manualContent .= "- `limit`: Specifies the maximum number of records to return.\r\n";
        $manualContent .= "- `offset`: Specifies the number of records to skip from the beginning.\r\n\r\n";
        $manualContent .= "**Example:** To get the second page of 10 items: `limit: 10, offset: 10`\r\n\r\n";

        return $manualContent;
    }

    /**
     * Helper to get a formatted string of fields for the manual.
     */
    private function getFieldsForManual($tableInfo, $noRelations = false)
    {
        $fieldsString = "";
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
        return $fieldsString;
    }

    /**
     * Helper to get formatted input fields for the manual.
     */
    private function getInputFieldsForManual($tableInfo)
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
     * Generates the content for the composer.json file.
     * This includes the necessary dependencies for running the GraphQL API,
     * specifically the `webonyx/graphql-php` library.
     *
     * @return string The JSON formatted content for composer.json.
     */
    public function generateComposerJson()
    {
        $composerConfig = array(
            'require' => [
                'webonyx/graphql-php' => '^14.11',
                'monolog/monolog' => '^3.5'
            ]
        );
        return json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
    public function camelCaseToSnakeCase($str) {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
    }

    public function snakeCaseToCamelCase($str) {
        $words = explode('_', strtolower($str));
        $camel = array_shift($words);
        foreach ($words as $word) {
            $camel .= ucfirst($word);
        }
        return $camel;
    }

    public function snakeCaseToTitleCase($str) {
        $str = str_replace('_', ' ', strtolower($str));
        return $this->titleCase($str);
    }

    public function camelCaseToTitleCase($str) {
        return $this->snakeCaseToTitleCase($this->camelCaseToSnakeCase($str));
    }

    public function titleCase($str) {
        $words = explode(' ', strtolower(trim($str)));
        foreach ($words as &$word) {
            $word = ucfirst($word);
        }
        return implode(' ', $words);
    }

    /**
     * Generates the PHP code for a helper function that retrieves the last inserted ID
     * in a cross-database compatible way.
     *
     * @return string The PHP code for the `getLastInsertId` function.
     */
    private function generateLastInsertIdFunction()
    {
        return "
/**
 * Retrieves the last inserted ID in a way that is compatible with MySQL, PostgreSQL, and SQL Server.
 *
 * @param PDO \$db The PDO database connection object.
 * @param string \$tableName The name of the table.
 * @param string \$primaryKeyName The name of the primary key column.
 * @return mixed The last inserted ID.
 */
function getLastInsertId(\$db, \$tableName, \$primaryKeyName)
{
    \$driver = \$db->getAttribute(PDO::ATTR_DRIVER_NAME);
    switch (\$driver) {
        case 'pgsql':
            // For PostgreSQL, lastInsertId() requires the sequence name.
            \$sequenceName = \$tableName . '_' . \$primaryKeyName . '_seq';
            return \$db->lastInsertId(\$sequenceName);
        case 'sqlsrv':
            // For SQL Server, SCOPE_IDENTITY() is the most reliable way.
            return \$db->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
        case 'mysql':
        case 'sqlite':
        default:
            // For MySQL, MariaDB, and SQLite, lastInsertId() works without arguments.
            return \$db->lastInsertId();
    }
}";
    }

    private function generateNewIdFunction()
    {
        return "
/**
 * Generate a unique 20-byte ID.
 *
 * This method generates a unique ID by concatenating a 13-character string
 * from `uniqid()` with a 6-character random hexadecimal string, ensuring
 * the resulting string is 20 characters in length.
 *
 * @return string A unique 20-byte identifier.
 */
function generateNewId()
{
    \$uuid = uniqid();
    if ((strlen(\$uuid) % 2) == 1) {
        \$uuid = '0' . \$uuid;
    }
    \$random = sprintf('%06x', mt_rand(0, 16777215));
    return sprintf('%s%s', \$uuid, \$random);
}
";
    }

    private function generateNormalizeBooleanInputsFunction()
    {
        return "/**
 * Normalizes boolean values in input arguments for specific database drivers.
 *
 * This function converts boolean values (true/false) inside `\$args['input']`
 * into integer values (1/0) when using databases that do not natively support
 * boolean types — specifically SQLite and SQL Server.
 *
 * For other drivers (e.g., MySQL, PostgreSQL), the input arguments are returned
 * unchanged.
 *
 * @param array \$args Input arguments, typically containing a key `input` with associative data.
 * @param PDO \$db A valid PDO instance representing the current database connection.
 *
 * @return array The modified (or original) input arguments with normalized boolean values.
 */
function normalizeBooleanInputs(\$args, \$db)
{
    \$driver = strtolower(\$db->getAttribute(PDO::ATTR_DRIVER_NAME));
    if (\$driver !== 'sqlite' && \$driver !== 'sqlsrv') {
        return \$args;
    }
    if(isset(\$args['input']) && is_array(\$args['input']))
    {
        foreach (\$args['input'] as \$key => \$value) {
            if (is_bool(\$value)) {
                \$args['input'][\$key] = \$value ? 1 : 0;
            }
        }
    }
    return \$args;
}";
    }

    private function generateNormalizeBooleanFunction()
    {
        return "/**
 * Normalizes boolean value argument for specific database drivers.
 *
 * This function converts boolean value (true/false) into integer values (1/0) 
 * when using databases that do not natively support
 * boolean types — specifically SQLite and SQL Server.
 *
 * For other drivers (e.g., MySQL, PostgreSQL), the input arguments are returned
 * unchanged.
 *
 * @param boolean \$value Input value, typically containing a key `input` with associative data.
 * @param PDO \$db A valid PDO instance representing the current database connection.
 *
 * @return boolean The modified (or original) input argument with normalized boolean value.
 */
function normalizeBoolean(\$value, \$db)
{
    \$driver = strtolower(\$db->getAttribute(PDO::ATTR_DRIVER_NAME));
    if (\$driver === 'sqlite' || \$driver === 'sqlsrv') {
        return \$value ? 1 : 0;
    }
    return (bool) \$value;
}";
    }

    /**
     * Main function to generate the complete graphql.php file content.
     *
     * @return string The complete PHP code for the GraphQL server.
     */
    public function generate()
    {
        $header = "<?php\r\n\r\n";
        $header .= "use GraphQL\\GraphQL;\r\n";
        $header .= "use GraphQL\\Error\\DebugFlag;\r\n";
        $header .= "use GraphQL\\Type\\Definition\\EnumType;\r\n";
        $header .= "use GraphQL\\Type\\Definition\\InputObjectType;\r\n";
        $header .= "use GraphQL\\Type\\Definition\\ObjectType;\r\n";
        $header .= "use GraphQL\\Type\\Definition\\Type;\r\n";
        $header .= "use GraphQL\\Type\\Schema;\r\n\r\n";
        $header .= "require_once __DIR__ . '/vendor/autoload.php';\r\n";
        $header .= "require_once __DIR__ . '/database.php';\r\n";
        $header .= "require_once __DIR__ . '/auth.php';\r\n";
        
        if($this->useCache)
        {
            $header .= "require_once __DIR__ . '/inc/InMemoryCache.php';\r\n";
        }
        
        $header .= "\r\n";

        if($this->useCache)
        {
            $header .= "//Use your own configuration for caching and development mode\r\n\r\n";
            $header .= "InMemoryCache::setEnabled(false);\r\n";
        }
        $header .= "\$developmentMode = true;\r\n";        
        $header .= "\r\n";

        $header .= $this->generateLastInsertIdFunction() . "\r\n";
        $header .= $this->generateNewIdFunction() . "\r\n";
        $header .= $this->generateNormalizeBooleanInputsFunction() . "\r\n\r\n";
        $header .= $this->generateNormalizeBooleanFunction() . "\r\n\r\n";
        
        $header .= "\r\n";

        $dbConnection = "/**\r\n * ----------------------------------------------------------------------------\r\n * DATABASE CONNECTION\r\n * ----------------------------------------------------------------------------\r\n */\r\n\r\n";

        $variableDeclaration = "";

        $variableDeclaration .= "\$appTimeCreate = date('Y-m-d H:i:s');\r\n";
        $variableDeclaration .= "\$appTimeEdit = date('Y-m-d H:i:s');\r\n";
        $variableDeclaration .= "if(isset(\$appAdmin) && isset(\$appAdmin['admin_id']))\r\n";
        $variableDeclaration .= "{\r\n";
        $variableDeclaration .= "\t\$appAdminCreate = \$appAdmin['admin_id'];\r\n";
        $variableDeclaration .= "\t\$appAdminEdit = \$appAdmin['admin_id'];\r\n";
        $variableDeclaration .= "}\r\n";
        $variableDeclaration .= "else\r\n";
        $variableDeclaration .= "{\r\n";
        $variableDeclaration .= "\t\$appAdminCreate = null;\r\n";
        $variableDeclaration .= "\t\$appAdminEdit = null;\r\n";
        $variableDeclaration .= "}\r\n";
        $variableDeclaration .= "\$appIpCreate = \$_SERVER['REMOTE_ADDR'];\r\n";
        $variableDeclaration .= "\$appIpEdit = \$_SERVER['REMOTE_ADDR'];\r\n\r\n";



        $utilityTypesCode = $this->generateUtilityTypes();
        $typesCode = $this->generateTypes();
        $inputTypesCode = $this->generateInputTypes();
        $queriesCode = $this->generateQueries();
        $mutationsCode = $this->generateMutations();

        $schemaAndExecution = "/**\r\n * ----------------------------------------------------------------------------\r\n * SCHEMA & EXECUTION\r\n * ----------------------------------------------------------------------------\r\n */\r\n";        
        $schemaAndExecution .= "\$schema = new Schema(array(\r\n";
        $schemaAndExecution .= "    'query' => \$queryType,\r\n";
        $schemaAndExecution .= "    'mutation' => \$mutationType,\r\n";
        $schemaAndExecution .= "));\r\n\r\n";
        $schemaAndExecution .= "try {\r\n";
        $schemaAndExecution .= "    \$rawInput = file_get_contents('php://input');\r\n";
        $schemaAndExecution .= "    \$input = json_decode(\$rawInput, true);\r\n";
        $schemaAndExecution .= "    if (!isset(\$input) || !is_array(\$input)) {\r\n";
        $schemaAndExecution .= "        \$input = array();\r\n";
        $schemaAndExecution .= "    }\r\n";
        $schemaAndExecution .= "    \$query = isset(\$input['query']) ? \$input['query'] : null;\r\n";
        $schemaAndExecution .= "    \$variableValues = isset(\$input['variables']) ? \$input['variables'] : null;\r\n\r\n";
        $schemaAndExecution .= "    if (empty(\$query)) {\r\n";
        $schemaAndExecution .= "        throw new Exception('GraphQL query is missing.');\r\n";
        $schemaAndExecution .= "    }\r\n";
        $schemaAndExecution .= "    \r\n";
        $schemaAndExecution .= "    \$rootValue = null; // or your application's root value\r\n";
        $schemaAndExecution .= "    \$context = ['db' => \$db]; // pass database connection to context\r\n";
        $schemaAndExecution .= "    \r\n";
        $schemaAndExecution .= "    \$result = GraphQL::executeQuery(\$schema, \$query, \$rootValue, \$context, \$variableValues);\r\n";
        $schemaAndExecution .= "    // In production, use DebugFlag::NONE;\r\n";
        $schemaAndExecution .= "    // For development, use DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;\r\n";
                
        $schemaAndExecution .= "    if(\$developmentMode == true) {\r\n";
        $schemaAndExecution .= "        \$output = \$result->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE);\r\n";
        $schemaAndExecution .= "    } else {\r\n";
        $schemaAndExecution .= "        \$output = \$result->toArray(DebugFlag::NONE);\r\n";
        $schemaAndExecution .= "    }\r\n";
        
        
        $schemaAndExecution .= "} catch (Exception \$e) {\r\n";
        $schemaAndExecution .= "    \$output = array(\r\n";
        $schemaAndExecution .= "        'errors' => array(\r\n";
        $schemaAndExecution .= "            array('message' => \$e->getMessage())\r\n";
        $schemaAndExecution .= "        )\r\n";
        $schemaAndExecution .= "    );\r\n";
        $schemaAndExecution .= "}\r\n\r\n";
        $schemaAndExecution .= "header('Content-Type: application/json; charset=UTF-8');\r\n";
        $schemaAndExecution .= "header('Access-Control-Allow-Origin: *');\r\n";
        $schemaAndExecution .= "echo json_encode(\$output);\r\n";

        return $header . $dbConnection . $variableDeclaration . $utilityTypesCode . $typesCode . $inputTypesCode . $queriesCode . $mutationsCode . $schemaAndExecution;
    }


    /**
     * Converts snake_case to camelCase.
     *
     * @param string $string The input string.
     * @return string The camelCased string.
     */
    private function camelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    /**
     * A simple pluralizer.
     *
     * @param string $string The singular string.
     * @return string The pluralized string.
     */
    private function pluralize($string)
    {
        if (substr($string, -1) === 'y') {
            return substr($string, 0, -1) . 'ies';
        }
        if (substr($string, -1) === 's') {
            return $string . 'es';
        }
        return $string . 's';
    }
}
