<?php

namespace AppBuilder;

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
     * Constructor.
     *
     * @param array $schema Decoded JSON schema.
     * @param array $reservedColumns
     * @throws Exception If the file is not found or invalid.
     */
    public function __construct($schema, $reservedColumns = null)
    {
        $this->schema = $schema;

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
                'filters' => $entity['filters'] ? $entity['filters'] : array(),
            );

            foreach ($entity['columns'] as $column) {
                $columnName = $column['name'];
                $this->analyzedSchema[$tableName]['columns'][$columnName] = array(
                    'type' => $column['type'],
                    'isForeignKey' => false,
                    'references' => null
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
    private function mapDbTypeToGqlType($dbType)
    {
        $dbType = strtoupper($dbType);
        if (strpos($dbType, 'INT') !== false) {
            return 'Type::int()';
        }
        if (strpos($dbType, 'VARCHAR') !== false || strpos($dbType, 'TEXT') !== false || strpos($dbType, 'DATE') !== false || strpos($dbType, 'TIMESTAMP') !== false) {
            return 'Type::string()';
        }
        if (strpos($dbType, 'DECIMAL') !== false || strpos($dbType, 'FLOAT') !== false || strpos($dbType, 'DOUBLE') !== false) {
            return 'Type::float()';
        }
        if (strpos($dbType, 'TINYINT(1)') !== false) {
            return 'Type::boolean()';
        }
        return 'Type::string()'; // Default fallback
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
                $gqlType = $this->mapDbTypeToGqlType($columnInfo['type']);
                $code .= "            '" . $columnName . "' => " . $gqlType . ",\r\n";
            }

            // Add relationships
            foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
                if ($columnInfo['isForeignKey']) {
                    $refTableName = $columnInfo['references'];
                    $refTypeName = $this->camelCase($refTableName) . 'Type';
                    $refPK = $this->analyzedSchema[$refTableName]['primaryKey'];

                    $code .= "            '" . $refTableName . "' => array(\r\n";
                    $code .= "                'type' => \$" . $refTypeName . ",\r\n";
                    $code .= "                'description' => 'Get the related " . $refTableName . "',\r\n";
                    $code .= "                'resolve' => function (\$root, \$args) use (\$db) {\r\n";
                    $code .= "                    if (empty(\$root['" . $columnName . "'])) return null;\r\n";
                    $code .= "                    \$stmt = \$db->prepare('SELECT * FROM " . $refTableName . " WHERE " . $refPK . " = :id');\r\n";
                    $code .= "                    \$stmt->execute(array(':id' => \$root['" . $columnName . "']));\r\n";
                    $code .= "                    return \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
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
                if ($columnName === $tableInfo['primaryKey']) {
                    continue;
                }
                $gqlType = $this->mapDbTypeToGqlType($columnInfo['type']);
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
            $code .= "                \$stmt = \$db->prepare('SELECT * FROM " . $tableName . " WHERE " . $primaryKey . " = :id');\r\n";
            $code .= "                \$stmt->execute(array(':id' => \$args['id']));\r\n";
            $code .= "                return \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
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
            $code .= "                \$allowedSortColumns = array('" . implode("', '", array_keys($tableInfo['columns'])) . "');\r\n\r\n";
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

            // Create Mutation
            $code .= "        'create" . ucfirst($camelName) . "' => array(\r\n";
            $code .= "            'type' => \$" . $typeName . ",\r\n";
            $code .= "            'args' => array('input' => Type::nonNull(\$" . $inputTypeName . ")),\r\n";
            $code .= "            'resolve' => function (\$root, \$args) use (\$db) {\r\n";
            $code .= "                \$input = \$args['input'];\r\n";
            $code .= "                \$id = uniqid(); // Simple unique ID\r\n";
            $code .= "                \$input['" . $primaryKey . "'] = \$id;\r\n";
            $code .= "                \$columns = array_keys(\$input);\r\n";
            $code .= "                \$placeholders = array_map(function(\$c) { return ':' . \$c; }, \$columns);\r\n";
            $code .= "                \$sql = 'INSERT INTO " . $tableName . " (' . implode(', ', \$columns) . ') VALUES (' . implode(', ', \$placeholders) . ')';\r\n";
            $code .= "                \$stmt = \$db->prepare(\$sql);\r\n";
            $code .= "                \$params = array();\r\n";
            $code .= "                foreach(\$input as \$key => \$value) { \$params[':' . \$key] = \$value; }\r\n";
            $code .= "                \$stmt->execute(\$params);\r\n";
            $code .= "                \$stmt = \$db->prepare('SELECT * FROM " . $tableName . " WHERE " . $primaryKey . " = :id');\r\n";
            $code .= "                \$stmt->execute(array(':id' => \$id));\r\n";
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
            $code .= "            'resolve' => function (\$root, \$args) use (\$db) {\r\n";
            $code .= "                \$id = \$args['id'];\r\n";
            $code .= "                \$input = \$args['input'];\r\n";
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
            $code .= "                \$stmt->execute(array(':id' => \$id));\r\n";
            $code .= "                return \$stmt->fetch(PDO::FETCH_ASSOC);\r\n";
            $code .= "            }\r\n";
            $code .= "        ),\r\n\r\n";

            // Activate/Deactivate Mutation (if applicable)
            if ($tableInfo['hasActiveColumn']) {
                $code .= "        'toggle" . ucfirst($camelName) . "Active' => array(\r\n";
                $code .= "            'type' => \$" . $typeName . ",\r\n";
                $code .= "            'args' => array(\r\n";
                $code .= "                'id' => Type::nonNull(Type::string()),\r\n";
                $code .= "                '".$this->activeField."' => Type::nonNull(Type::boolean())\r\n";
                $code .= "            ),\r\n";
                $code .= "            'resolve' => function (\$root, \$args) use (\$db) {\r\n";
                $code .= "                \$sql = 'UPDATE " . $tableName . " SET ".$this->activeField." = :active WHERE " . $primaryKey . " = :id';\r\n";
                $code .= "                \$stmt = \$db->prepare(\$sql);\r\n";
                $code .= "                \$stmt->execute(array(':id' => \$args['id'], ':active' => \$args['".$this->activeField."'] ? 1 : 0));\r\n";
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
        $manualContent .= "\$db           = 'your_database_name';\r\n";
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
        
        $manualContent .= "## Security Considerations\r\n\r\n";
        $manualContent .= "For production or any publicly exposed environments, it is strongly recommended to remove files that are not essential for the API's runtime operations. This helps to minimize the attack surface and prevent potential information disclosure.\r\n\r\n";
        $manualContent .= "Please delete the following files from your production server:\r\n\r\n";
        $manualContent .= "- **`manual.md`**: This file contains detailed API documentation that should not be publicly accessible.\r\n";
        $manualContent .= "- **`composer.phar`**: This is the Composer executable and is not needed for the application to run after dependencies are installed. Leaving it on the server can pose a security risk.\r\n\r\n";
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
            $gqlType = $this->mapDbTypeToGqlType($columnInfo['type']);
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
      
        $frontendConfig = array();
        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $camelName = $this->camelCase($tableName);
            $pluralCamelName = $this->pluralize($camelName);

            $columns = array();
            foreach($tableInfo['columns'] as $colName => $colInfo) {
                $columns[$colName] = array(
                    'type' => $this->mapDbTypeToGqlType($colInfo['type']),
                    'isForeignKey' => $colInfo['isForeignKey'],
                    'references' => $colInfo['references'] ? $colInfo['references'] : null,
                );
            }

            $frontendConfig[$camelName] = array(
                'name' => $camelName,
                'pluralName' => $pluralCamelName,
                'displayName' => $this->camelCaseToTitleCase($camelName),
                'displayField' => $this->displayField,
                'activeField' => $this->activeField,
                'primaryKey' => $tableInfo['primaryKey'],
                'hasActiveColumn' => $tableInfo['hasActiveColumn'],
                'columns' => $columns,
                'filters' => isset($tableInfo['filters']) ? $tableInfo['filters'] : []
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
            $camelName = $this->camelCase($tableName);
            $columns = array();
            foreach($tableInfo['columns'] as $colName => $colInfo) {
                $columns[$colName] = $this->snakeCaseToTitleCase($colName); 
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
        $header .= "require_once __DIR__ . '/auth.php';\r\n\r\n";
        
        $header .= "\$developmentMode = true;\r\n\r\n";

        $dbConnection = "/**\r\n * ----------------------------------------------------------------------------\r\n * DATABASE CONNECTION\r\n * ----------------------------------------------------------------------------\r\n */\r\n\r\n";

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

        return $header . $dbConnection . $utilityTypesCode . $typesCode . $inputTypesCode . $queriesCode . $mutationsCode . $schemaAndExecution;
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
