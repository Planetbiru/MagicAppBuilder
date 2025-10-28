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
     * @throws Exception If the file is not found or invalid.
     */
    public function __construct($schema)
    {
        $this->schema = $schema;
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
            );

            foreach ($entity['columns'] as $column) {
                $columnName = $column['name'];
                $this->analyzedSchema[$tableName]['columns'][$columnName] = array(
                    'type' => $column['type'],
                    'isForeignKey' => false,
                    'references' => null
                );

                if ($columnName === 'active') {
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
                if (!$columnInfo['isForeignKey']) {
                    $gqlType = $this->mapDbTypeToGqlType($columnInfo['type']);
                    $code .= "            '" . $columnName . "' => " . $gqlType . ",\r\n";
                }
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
                $code .= "                'active' => Type::nonNull(Type::boolean())\r\n";
                $code .= "            ),\r\n";
                $code .= "            'resolve' => function (\$root, \$args) use (\$db) {\r\n";
                $code .= "                \$sql = 'UPDATE " . $tableName . " SET active = :active WHERE " . $primaryKey . " = :id';\r\n";
                $code .= "                \$stmt = \$db->prepare(\$sql);\r\n";
                $code .= "                \$stmt->execute(array(':id' => \$args['id'], ':active' => \$args['active'] ? 1 : 0));\r\n";
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
                $code .= "                \$sql = 'UPDATE " . $tableName . " SET active = 0 WHERE " . $primaryKey . " = :id';\r\n";
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
    public function generateFrontendConfigJson($displayField = "name")
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
                'displayField' => $displayField,
                'primaryKey' => $tableInfo['primaryKey'],
                'hasActiveColumn' => $tableInfo['hasActiveColumn'],
                'columns' => $columns,
            );
        }
        return json_encode(['entities' => $frontendConfig], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
     * Generates the HTML file for the frontend application.
     *
     * @return string The HTML content.
     */
    public function generateFrontendHtml()
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GraphQL Frontend</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <link rel="stylesheet" href="style.css">
    <script src="app.js"></script>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
      rel="stylesheet" />
  </head>
  <body>
    <div class="page-wrapper">
      <header class="header">
        <div class="header-left">
          <span class="sidebar-toggle" id="sidebar-toggle">&#9776;</span>
        </div>
        <div class="header-right">
          <div class="header-item">
            <i
              class="fa-solid fa-bell icon"
              data-dropdown="notification-menu"></i>
            <ul id="notification-menu" class="dropdown-menu">
              <li><a href="#">Notification 1</a></li>
              <li><a href="#">Notification 2</a></li>
            </ul>
          </div>
          <div class="header-item">
            <i
              class="fa-solid fa-envelope icon"
              data-dropdown="message-menu"></i>
            <ul id="message-menu" class="dropdown-menu">
              <li><a href="#">Message from John</a></li>
              <li><a href="#">Message from Jane</a></li>
            </ul>
          </div>
          <div class="header-item">
            <i class="fa-solid fa-globe icon" data-dropdown="lang-menu"></i>
            <ul id="lang-menu" class="dropdown-menu">
              <li><a href="#">English</a></li>
              <li><a href="#">Indonesia</a></li>
            </ul>
          </div>
          <div class="header-item">
            <i
              class="fa-solid fa-user-circle icon"
              data-dropdown="profile-menu"></i>
            <ul id="profile-menu" class="dropdown-menu">
              <li><a href="#">Profile</a></li>
              <li><a href="#">Settings</a></li>
              <li><hr style="margin: 5px 0; border-color: #eee" /></li>
              <li><a href="#" id="logout-btn-dropdown" class="logout-link">Logout</a></li>
            </ul>
          </div>
        </div>
      </header>
      <div class="container">
        <nav class="sidebar" id="sidebar-nav">
          <ul id="entity-menu">
            <!-- Menu items will be inserted here by JavaScript -->
          </ul>
        </nav>
        <main class="main-content" id="main-content">
          <h1 id="content-title">Welcome</h1>
          <div id="content-body">
            <p>Please select an entity from the menu to get started.</p>
          </div>
        </main>
      </div>
    </div>

    <!-- Modal for Forms -->
    <div id="form-modal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <span class="close-button" id="form-close-button">&times;</span>
          <h2 id="modal-title">Form</h2>
        </div>
        <div class="modal-body"><form id="entity-form"></form></div>
        <div class="modal-footer"></div>
          </div>
      </div>
    </div>

    <!-- Modal for Login -->
    <div id="login-modal" class="modal">
      <div class="modal-content">
        <form id="login-form"></form>
        <div class="modal-header">
          <span class="close-button" id="login-close-button">&times;</span> 
          <h2 id="login-title">Login</h2>
        </div>
        <div class="modal-body">
        
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required />
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required />
          </div>
          
          <div id="login-error" style="color: red; margin-top: 10px"></div>
        
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Login</button></div>
        </form>
      </div>
    </div>

    
  </body>
</html>

HTML;
    }

    /**
     * Generates the CSS file for the frontend application.
     *
     * @return string The CSS content.
     */
    public function generateFrontendCss()
    {
        return <<<CSS
:root {
  --sidebar-width: 250px;
  --header-height: 60px;
  --primary-color: #3b82f6; /* A brighter, more modern blue */
  --primary-color-hover: #2563eb;
  --background-color: #f9fafb; /* Lighter grey for background */
  --sidebar-bg: #ffffff;
  --header-bg: #ffffff;
  --text-primary: #1f2937;
  --text-secondary: #6b7280;
  --border-color: #e5e7eb;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -2px rgba(0, 0, 0, 0.1);
}

body {
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
    "Helvetica Neue", Arial, sans-serif;
  margin: 0;
  background-color: var(--background-color);
  color: var(--text-primary);
}

.page-wrapper {
  flex-direction: column;
  height: 100vh;
}

.header {
  height: var(--header-height);
  background-color: var(--header-bg);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  border-bottom: 1px solid var(--border-color);
  z-index: 100;
}

.header-left {
  display: flex;
  align-items: center;
}

.sidebar-toggle {
  font-size: 24px;
  cursor: pointer;
  margin-right: 20px;
  color: var(--text-secondary);
}

.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.header-item {
  position: relative;
  cursor: pointer;
}

.header-item .icon {
  font-size: 22px;
  color: var(--text-secondary);
}
h2,
h3 {
  margin: 0;
}
h2 {
  font-size: 18px;
  font-weight: normal;
}

.dropdown-menu {
  display: none;
  position: absolute;
  top: 40px;
  right: 0;
  background-color: var(--header-bg);
  border-radius: 5px;
  box-shadow: var(--shadow-md);
  min-width: 180px;
  z-index: 101;
  list-style: none;
  padding: 5px 0;
  margin: 0;
}

.dropdown-menu.show {
  display: block;
}

.dropdown-menu a {
  display: block;
  padding: 10px 15px;
  color: var(--text-primary);
  text-decoration: none;
  font-size: 14px;
}

.dropdown-menu a:hover {
  background-color: var(--background-color);
}

.container {
  display: flex;
  flex-grow: 1;
  overflow: hidden;
}

.sidebar {
  width: var(--sidebar-width);
  background-color: var(--sidebar-bg);
  padding: 20px;
  border-right: 1px solid var(--border-color);
  flex-shrink: 0;
}
.sidebar-animated {
  transition: margin-left 0.3s ease;
}

.sidebar.collapsed {
  margin-left: calc((-1 * var(--sidebar-width)) - 43px);
}

.sidebar h2 {
  color: var(--primary-color);
  margin-top: 0;
}

#entity-menu {
  list-style: none;
  padding: 0;
  margin: 0px 0 0 0;
}

#entity-menu li a {
  display: block;
  padding: 8px 12px;
  color: var(--text-secondary);
  text-decoration: none;
  border-radius: 5px;
  margin-bottom: 5px;
  transition: background-color 0.2s ease, color 0.2s ease;
}

#entity-menu li a:hover,
#entity-menu li a.active {
  background-color: var(--primary-color);
  color: white;
}

.main-content {
  flex-grow: 1;
  padding: 20px;
  overflow-y: auto;
  transition: margin-left 0.3s ease;
}

.main-content h1 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 28px;
  font-weight: normal;
}

/* Styles from original file, slightly adapted */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0, 0, 0, 0.5);
}
.modal-content {
  background-color: #fefefe;
  margin: 10% auto;
  border: 1px solid #888;
  width: 80%;
  max-width: 550px;
  border-radius: 8px;
  position: relative;
}
.close-button {
  color: #aaa;
  position: absolute;
  top: 10px;
  right: 20px;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}
.modal-header {
  padding: 1rem 1rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-body {
  padding: 1rem;
}
.modal-footer {
  padding: 1rem 1rem;
  border-top: 1px solid var(--border-color);
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
.close-button {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  display: block;
  margin-bottom: 5px;
}
.form-group input[type="text"],
.form-group input[type="password"],
.form-group input[type="email"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  box-sizing: border-box;
  background-color: #fff;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}
.btn {
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s ease, transform 0.1s ease;
}
.btn-sm {
  padding: 5px 10px;
  font-size: 12px;
}
.btn-sm:hover {
}
.btn-primary {
  background-color: var(--primary-color);
  color: white;
}
.btn-primary:hover {
  background-color: var(--primary-color-hover);
}
.btn:active {
  transform: translateY(1px);
}
.btn-warning {
  background-color: #f59e0b;
  color: white;
}
.btn-warning:hover {
  background-color: #d97706;
}
.btn-danger {
  background-color: #ef4444;
  color: white;
}
.btn-danger:hover {
  background-color: #dc2626;
}
.btn-info {
  background-color: #3b82f6;
  color: white;
}
.btn-info:hover {
  background-color: #2563eb;
}
.btn-secondary {
  background-color: #e5e7eb;
  color: var(--text-primary);
}
.btn-secondary:hover {
  background-color: #d1d5db;
}
.logout-link {
  display: block;
  margin-top: 20px;
}
.table-container {
  overflow: auto;
}
.table-container table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 10px;
}
.table-container table td,
.table-container table th {
  border: 1px solid #ddd;
  padding: 5px 8px;
  text-align: left;
}
.table-container table th {
  background-color: #f2f2f2;
  padding: 8px 8px;
}

td.actions {
  white-space: nowrap;
}

.detail-view table tr > td:nth-child(1) {
  width: 35%;
}

.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  margin-top: 10px;
  border-top: 1px solid var(--border-color);
}

.pagination-container span {
  font-size: 14px;
  color: var(--text-secondary);
}

.pagination-container button:disabled {
  background-color: #f3f4f6;
  color: #9ca3af;
  cursor: not-allowed;
}

.list-controls,
.back-controls {
  padding: 0px 0px 8px 0px;
}

CSS;
    }

    /**
     * Generates the JavaScript file for the frontend application.
     *
     * @return string The JavaScript content.
     */
    public function generateFrontendJs()
    {
        // This now returns the full JavaScript application code.
        return <<<'JS'
class GraphQLClientApp {
    constructor(configUrl, apiUrl) {
        this.configUrl = configUrl;
        this.apiUrl = apiUrl;
        this.config = null;
        this.currentEntity = null;
        this.state = {
            page: 1,
            limit: 10,
            filters: [],
            orderBy: [],
        };
        this.dom = {
            menu: document.getElementById('entity-menu'),
            title: document.getElementById('content-title'),
            body: document.getElementById('content-body'),
            modal: document.getElementById('form-modal'),
            modalTitle: document.getElementById('modal-title'),
            form: document.getElementById('entity-form'),
            closeModalBtn: document.querySelector('.close-button'),
            loginModal: document.getElementById('login-modal'),
            loginForm: document.getElementById('login-form'),
            loginCloseBtn: document.getElementById('login-close-button'),
            logoutBtn: document.querySelector('.logout-link'),
        };
        this.init();
    }
    
    initPage() {
        

        const sidebarToggle = document.getElementById("sidebar-toggle");
        const sidebar = document.getElementById("sidebar-nav");

        // Check for saved sidebar state in localStorage on page load
        if (localStorage.getItem("sidebarCollapsed") === "true") {
          sidebar.classList.add("collapsed");
        }

        // Add the transition class after a short delay to avoid animation on load
        setTimeout(() => {
          sidebar.classList.add("sidebar-animated");
        }, 100);

        sidebarToggle.addEventListener("click", () => {
          sidebar.classList.toggle("collapsed");
          // Save the new state to localStorage
          localStorage.setItem( "sidebarCollapsed", sidebar.classList.contains("collapsed") );
        });

        // Dropdown logic
        document.querySelectorAll(".header-item .icon").forEach((icon) => {
          icon.addEventListener("click", (event) => {
            event.stopPropagation();
            const dropdownId = icon.getAttribute("data-dropdown");
            const targetDropdown = document.getElementById(dropdownId);

            // Close other dropdowns
            document
              .querySelectorAll(".dropdown-menu.show")
              .forEach((openDropdown) => {
                if (openDropdown !== targetDropdown) {
                  openDropdown.classList.remove("show");
                }
              });
            targetDropdown.classList.toggle("show");
          });
        });

        // Close dropdowns when clicking outside
        window.addEventListener("click", () => {
          document
            .querySelectorAll(".dropdown-menu.show")
            .forEach((openDropdown) => {
              openDropdown.classList.remove("show");
            });
        });
        
    }

    async init() {
        try {
            await this.loadConfig();
            this.initPage();
            this.buildMenu();
            this.dom.closeModalBtn.onclick = () => this.closeModal();
            window.onclick = (event) => {
                if (event.target == this.dom.modal) this.closeModal();
            };
            // Add event listener for data-dismiss="modal"
            document.addEventListener('click', (event) => {
                const dismissButton = event.target.closest('[data-dismiss="modal"]');
                if (dismissButton) {
                    const modal = dismissButton.closest('.modal');
                    if (modal) {
                        if (modal.id === this.dom.modal.id) this.closeModal();
                        else if (modal.id === this.dom.loginModal.id) this.closeLoginModal();
                    }
                }
            });
            this.dom.loginCloseBtn.onclick = () => this.closeLoginModal();
            this.dom.loginForm.onsubmit = (e) => this.handleLogin(e);
            this.dom.logoutBtn.onclick = (e) => this.handleLogout(e);
            // Handle initial page load and back/forward button clicks
            window.addEventListener('popstate', () => this.handleRouteChange());
            this.handleRouteChange(); // Handle initial route
        } catch (error) {
            console.error('Initialization Error:', error);
            this.dom.body.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
        }
    }

    async loadConfig() { // Simplified loadConfig
        const response = await fetch(this.configUrl);
        if (!response.ok) throw new Error(`Failed to load config from ${this.configUrl}`);
        this.config = await response.json();
        if (this.config.pagination && this.config.pagination.pageSize) {
            this.state.limit = this.config.pagination.pageSize;
        }
    }

    buildMenu() {
        if (!this.config || !this.config.entities) return;
        this.dom.menu.innerHTML = '';
        Object.values(this.config.entities).forEach((entity, index) => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = `#${entity.name}`;
            a.textContent = entity.displayName;
            a.onclick = (e) => {
                e.preventDefault();
                this.navigateTo(entity.name, { limit: this.state.limit });
            };
            li.appendChild(a);
            this.dom.menu.appendChild(li);
        });
    }

    async gqlQuery(query, variables = {}) {
        const response = await fetch(this.apiUrl, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'xmlhttprequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ query, variables }),
        });

        if (response.status === 401) {
            this.openLoginModal();
            throw new Error("Authentication required.");
        }

        const result = await response.json();
        
        if (result.errors) {
            console.error('GraphQL Errors:', result.errors);
            console.error(`Error: ${result.errors[0].message}`);
            throw new Error(result.errors[0].message);
        }
        return result.data;
    }

    // =================================================================
    // RENDER METHODS
    // =================================================================

    navigateTo(entityName, params = {}) {
        const newParams = new URLSearchParams();
        newParams.set('page', params.page || 1);
        newParams.set('limit', params.limit || 10);
        // TODO: Add filters and orderBy to params

        const newUrl = `${window.location.pathname}#${entityName}?${newParams.toString()}`;
        history.pushState({ entityName, params }, '', newUrl);
        this.handleRouteChange();
    }

    navigateToDetail(entityName, id) {
        const newUrl = `${window.location.pathname}#${entityName}/detail/${id}`;
        history.pushState({ entityName, id }, '', newUrl);
        this.handleRouteChange();
    }

    async handleRouteChange() {
        const hash = window.location.hash.substring(1);
        if (!hash) {
            // If no hash, navigate to the first entity
            const firstEntityName = Object.keys(this.config.entities)[0];
            if (firstEntityName) {
                this.navigateTo(firstEntityName, { limit: this.state.limit });
            } else {
                this.dom.body.innerHTML = '<p>No entities configured.</p>';
            }
            return;
        }

        const [path, queryString] = hash.split('?');
        const pathParts = path.split('/');

        const entityName = pathParts[0];
        const viewType = pathParts.length > 1 ? pathParts[1] : 'list';
        const itemId = pathParts.length > 2 ? pathParts[2] : null;

        const params = new URLSearchParams(queryString);

        const entity = this.config.entities[entityName];
        if (!entity) {
            this.dom.body.innerHTML = `<p>Entity "${entityName}" not found.</p>`;
            return;
        }

        this.currentEntity = entity;
        this.state = {
            page: parseInt(params.get('page')) || 1,
            limit: parseInt(params.get('limit')) || 10,
            filters: [], // TODO: Parse from URL
            orderBy: [],   // TODO: Parse from URL
        };

        document.querySelectorAll('#entity-menu a').forEach(link => link.classList.toggle('active', link.getAttribute('href') === `#${entityName}`));

        if (viewType === 'detail' && itemId) {
            await this.renderDetailView(itemId);
        } else {
            await this.renderListView();
        }
    }

    async renderListView() {
        this.dom.title.textContent = `List of ${this.currentEntity.displayName}`;
        this.dom.body.innerHTML = 'Loading...';

        const fields = this.getFieldsForQuery(this.currentEntity, 1); // depth 1
        const offset = (this.state.page - 1) * this.state.limit;

        const query = `
            query Get${this.currentEntity.pluralName}($limit: Int, $offset: Int, $orderBy: [SortInput], $filter: [FilterInput]) {
                ${this.currentEntity.pluralName}(limit: $limit, offset: $offset, orderBy: $orderBy, filter: $filter) {
                    items { ${fields} }
                    total
                    limit
                    page
                    totalPages
                    hasNext
                    hasPrevious
                }
            }
        `;

        try {
            const data = await this.gqlQuery(query, {
                limit: this.state.limit,
                offset: offset,
                orderBy: this.state.orderBy,
                filter: this.state.filters,
            });
            const result = data[this.currentEntity.pluralName];
            this.renderTable(result.items);
            this.renderPagination(result);
        } catch (error) {
            this.dom.body.innerHTML = `<p style="color: red;">${error.message}</p>`;
        }
    }

    renderTable(items) {
        const headers = Object.keys(this.currentEntity.columns);
        let tableHtml = `
            <div class="list-controls">
                <button id="add-new-btn" class="btn btn-primary">Add New ${this.currentEntity.displayName}</button>
                <div><!-- Placeholder for filter/sort controls --></div>
            </div>
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            ${headers.map(h => `<th>${h}</th>`).join('')}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (items.length === 0) {
            tableHtml += `<tr><td colspan="${headers.length + 1}">No items found.</td></tr>`;
        } else {
            items.forEach(item => {
                tableHtml += `<tr>`;
                headers.forEach(header => {
                    const col = this.currentEntity.columns[header];
                    let value = item[header];
                    const relationName = col.references; // Now this is camelCase, e.g., "statusKeanggotaan"
                    

                    if (col.isForeignKey && item[relationName]) {
                        // Display a representative field from the related object, e.g., 'name' or the second field.
                        let relatedEntity = this.config.entities[this.camelCase(relationName)];
                        // const displayField = relatedEntity ? (Object.keys(relatedEntity.columns)[1] || relatedEntity.displayField) : 'name';
                        let displayField = relatedEntity ? relatedEntity.displayField : 'name';
                        value = item[relationName][displayField];
                    }
                    tableHtml += `<td>${value !== null ? value : 'N/A'}</td>`;
                });
                tableHtml += this.renderActionButtons(item);
                tableHtml += `</tr>`;
            });
        }

        tableHtml += `</tbody></table></div>`;
        this.dom.body.innerHTML = tableHtml;

        document.getElementById('add-new-btn').onclick = () => this.renderForm();
        document.querySelectorAll('.btn-detail').forEach(btn => btn.onclick = (e) => this.navigateToDetail(this.currentEntity.name, e.currentTarget.dataset.id));
        document.querySelectorAll('.btn-edit').forEach(btn => btn.onclick = (e) => this.renderForm(e.currentTarget.dataset.id));
        document.querySelectorAll('.btn-delete').forEach(btn => btn.onclick = (e) => this.handleDelete(e.currentTarget.dataset.id));
        document.querySelectorAll('.btn-toggle-active').forEach(btn => btn.onclick = (e) => this.handleToggleActive(e.currentTarget.dataset.id, e.currentTarget.dataset.active === 'true'));
    }
    
    renderActionButtons(item) {
        const id = item[this.currentEntity.primaryKey];
        let buttons = `
            <td class="actions">
                <button class="btn btn-sm btn-info btn-detail" data-id="${id}">View</button>
                <button class="btn btn-sm btn-warning btn-edit" data-id="${id}">Edit</button>
        `;
        if (this.currentEntity.hasActiveColumn) {
            const isActive = item['active'];
            buttons += `<button class="btn btn-sm ${isActive ? 'btn-secondary' : 'btn-success'} btn-toggle-active" data-id="${id}" data-active="${isActive}">${isActive ? 'Deactivate' : 'Activate'}</button>`;
        }
        buttons += `<button class="btn btn-sm btn-danger btn-delete" data-id="${id}">Delete</button></td>`;
        return buttons;
    }

    renderPagination(result) {
        const paginationDiv = document.createElement('div');
        paginationDiv.className = 'pagination pagination-container';
        paginationDiv.innerHTML = `
            <span>Page ${result.page} of ${result.totalPages} (Total: ${result.total})</span>
            <button id="prev-page" class="btn btn-secondary" ${!result.hasPrevious ? 'disabled' : ''}>Previous</button>
            <button id="next-page" class="btn btn-secondary" ${!result.hasNext ? 'disabled' : ''}>Next</button>
        `;
        this.dom.body.appendChild(paginationDiv);
        
        document.getElementById('prev-page').onclick = () => {
            this.navigateTo(this.currentEntity.name, { page: this.state.page - 1, limit: this.state.limit});
        };
        document.getElementById('next-page').onclick = () => {
            this.navigateTo(this.currentEntity.name, { page: this.state.page + 1, limit: this.state.limit});
        };
    }

    async renderDetailView(id) {
        this.dom.title.textContent = `Detail of ${this.currentEntity.displayName}`;
        this.dom.body.innerHTML = 'Loading...';

        const fields = this.getFieldsForQuery(this.currentEntity, 2); // Deeper nesting for details
        const query = `
            query Get${this.currentEntity.name}($id: String!) {
                ${this.currentEntity.name}(id: $id) {
                    ${fields}
                }
            }
        `;

        try {
            const data = await this.gqlQuery(query, { id });
            const item = data[this.currentEntity.name];
            let detailHtml = `<div class="back-controls">
                                <button id="back-to-list" class="btn btn-secondary">Back to List</button>
                              </div>
                <div class="table-container detail-view">
                    <table class="table">
                        <tbody>`;
            for (const key in this.currentEntity.columns) {
                const col = this.currentEntity.columns[key];
                const relationName = col.references;
                detailHtml += `<tr>
                                    <td>${key}</td>
                                    <td>`;
                if (col.isForeignKey && item[relationName]) {
                    detailHtml += `<span>${JSON.stringify(item[relationName], null, 2)}</span>`;
                } else {
                    detailHtml += `<span>${item[key] !== null ? item[key] : 'N/A'}</span>`;
                }
                detailHtml += `</td></tr>`;
            }
            detailHtml += `</tbody></table></div>`;
            this.dom.body.innerHTML = detailHtml;
            document.getElementById('back-to-list').onclick = () => history.back();
        } catch (error) {
            this.dom.body.innerHTML = `<p style="color: red;">Failed to fetch item details.</p>`;
        }
    }

    async renderForm(id = null) {
        this.dom.modalTitle.textContent = id ? `Edit ${this.currentEntity.displayName}` : `Add New ${this.currentEntity.displayName}`;
        this.dom.form.innerHTML = 'Loading form...';
        this.openModal();

        let item = {};
        if (id) {
            const fields = this.getFieldsForQuery(this.currentEntity, 2); // Fetch with relations for edit form
            const query = `query GetForEdit($id: String!) { ${this.currentEntity.name}(id: $id) { ${fields} } }`;
            const data = await this.gqlQuery(query, { id });
            item = data[this.currentEntity.name];
        }

        let formHtml = '';
        for (const colName in this.currentEntity.columns) {
            if (colName === this.currentEntity.primaryKey) continue;
            const col = this.currentEntity.columns[colName];
            let value = '';
            if (col.isForeignKey && item[col.references]) {
                value = item[col.references][this.config.entities[this.camelCase(col.references)].primaryKey];
            } else if (item[colName] !== undefined) {
                value = item[colName];
            }

            formHtml += `<div class="form-group">`;
            formHtml += `<label for="${colName}">${colName}</label>`;
            if (col.isForeignKey) {
                const relationName = col.references;
                const relatedEntity = this.config.entities[this.camelCase(relationName)];
                
                const relatedData = relatedEntity ? (await this.fetchAll(relatedEntity)) : [];
                const displayField = relatedEntity.displayField || 'name';

                formHtml += `<select id="${colName}" name="${colName}">`;
                formHtml += `<option value="">-- Select --</option>`;
                relatedData.forEach(relItem => {
                    const relId = relItem[relatedEntity.primaryKey];
                    const relDisplay = relItem[displayField];
                    formHtml += `<option value="${relId}" ${relId == value ? 'selected' : ''}>${relDisplay}</option>`;
                });
                formHtml += `</select>`;
            } else {
                let inputType = 'text';
                if (col.type.includes('int')) inputType = 'number';
                if (col.type.includes('boolean')) inputType = 'checkbox';
                
                if (inputType === 'checkbox') {
                     formHtml += `<input type="checkbox" id="${colName}" name="${colName}" ${value ? 'checked' : ''}>`;
                } else {
                     formHtml += `<input type="${inputType}" id="${colName}" name="${colName}" value="${value}">`;
                }
            }
            formHtml += `</div>`;
        }
        
        this.dom.form.innerHTML = formHtml;
        
        this.dom.form.closest('.modal').querySelector('.modal-footer').innerHTML = `
        <button type="submit" class="btn btn-primary">Save</button> 
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> `;
        
        this.dom.form.closest('.modal').querySelector('.modal-footer').querySelector('[type="submit"]').addEventListener('click', () => this.handleSave(id));
        
        this.dom.form.onsubmit = (e) => {
            e.preventDefault();
            this.handleSave(id);
        };
    }

    // =================================================================
    // HANDLER METHODS
    // =================================================================

    async handleSave(id) {
        const formData = new FormData(this.dom.form);
        const input = {};
        for (const colName in this.currentEntity.columns) {
             if (colName === this.currentEntity.primaryKey) continue;
             const col = this.currentEntity.columns[colName];
             if (col.type.includes('boolean')) {
                 input[colName] = this.dom.form.querySelector(`[name="${colName}"]`).checked;
             } else if (formData.has(colName)) {
                 let value = formData.get(colName);
                 if (col.type.includes('int') || col.type.includes('float')) {
                     value = value ? Number(value) : null;
                 }
                 input[colName] = value;
             }
        }

        const mutationName = id ? `update${this.currentEntity.name}` : `create${this.currentEntity.name}`;
        const fields = this.getFieldsForQuery(this.currentEntity, 1, true);
        const mutation = `
            mutation Save${this.currentEntity.name}($id: String, $input: ${this.currentEntity.name}Input!) {
                ${mutationName}(${id ? 'id: $id, ' : ''}input: $input) {
                    ${fields}
                }
            }
        `;
        const variables = { input };
        if (id) variables.id = id;

        try {
            await this.gqlQuery(mutation, variables);
            this.closeModal();
            this.renderListView(); // Refresh list
        } catch (error) {
            console.error(`Failed to save: ${error.message}`);
        }
    }

    async handleDelete(id) {
        if (!confirm('Are you sure you want to delete this item?')) return;

        const mutation = `
            mutation Delete${this.currentEntity.name}($id: String!) {
                delete${this.currentEntity.name}(id: $id)
            }
        `;
        try {
            await this.gqlQuery(mutation, { id });
            this.renderListView();
        } catch (error) {
            console.error(`Failed to delete: ${error.message}`);
        }
    }

    async handleToggleActive(id, currentStatus) {
        const newStatus = !currentStatus;
        const action = newStatus ? 'activate' : 'deactivate';
        if (!confirm(`Are you sure you want to ${action} this item?`)) return;

        const mutation = `
            mutation ToggleActive($id: String!, $active: Boolean!) {
                toggle${this.currentEntity.name}Active(id: $id, active: $active) {
                    ${this.currentEntity.primaryKey}
                    active
                }
            }
        `;
        try {
            await this.gqlQuery(mutation, { id, active: newStatus });
            this.renderListView();
        } catch (error) {
            console.error(`Failed to ${action}: ${error.message}`);
        }
    }

    async handleLogin(event) {
        event.preventDefault();
        const formData = new FormData(this.dom.loginForm);
        const loginErrorDiv = document.getElementById('login-error');
        loginErrorDiv.textContent = '';

        try {
            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            // Jika login berhasil, server akan me-redirect atau memberikan respons sukses.
            // Di sini kita asumsikan jika tidak ada URL redirect, login gagal.
            if (response.ok && response.redirected === false) {
                 // Cek jika respons berisi pesan error spesifik
                const text = await response.text();
                if(text.includes("Invalid username or password")) {
                    loginErrorDiv.textContent = 'Invalid username or password.';
                } else {
                    // Jika login berhasil, muat ulang halaman untuk mendapatkan sesi baru
                    this.closeLoginModal();
                    this.handleRouteChange(); // Muat ulang data view saat ini
                }
            } else {
                // Jika login berhasil dan ada redirect, browser akan menanganinya.
                // Jika tidak, muat ulang untuk mengambil state baru.
                window.location.reload();
            }
        } catch (error) {
            loginErrorDiv.textContent = 'An error occurred during login.';
            console.error('Login failed:', error);
        }
    }

    async handleLogout(event) {
        event.preventDefault();
        try {
            const response = await fetch('logout.php');
            if (response.ok) {
                // Clear the main content and title
                this.dom.title.textContent = 'Logged Out';
                this.dom.body.innerHTML = '<p>You have been successfully logged out.</p>';

                // Hide the menu and show the login modal
                this.dom.menu.style.display = 'none';
                this.dom.logoutBtn.style.display = 'none';
                this.openLoginModal();
            }
        } catch (error) {
            console.error('Logout failed:', error);
            alert('Logout failed. Please try again.');
        }
    }

    // =================================================================
    // UTILITY METHODS
    // =================================================================

    camelCase(str) {
        return str.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
    }
    // This function is no longer needed if frontend-config.json is correct,
    // but we keep it for robustness just in case.
    // camelCase(str) { return str.replace(/_([a-z])/g, (g) => g[1].toUpperCase()); }

    getFieldsForQuery(entity, depth = 1, noRelations = false) {
        if (depth < 0) return entity.primaryKey;
        let fields = [];
        for (const colName in entity.columns) {
            const col = entity.columns[colName];
            if (col.isForeignKey && !noRelations) {
                
                // sampai di sini `sub_klasifikasi` dan `jenis_media` masih diprint
                let relationName = col.references; // This is now camelCase, e.g., "statusKeanggotaan"

                let relationNameCamelCase = this.camelCase(relationName);

                // masalahnya mungkin di
                let relatedEntity = this.config.entities[relationName];

                if(!relatedEntity)
                {
                    relatedEntity = this.config.entities[relationNameCamelCase];
                }

                if (relatedEntity) {
                    let query = `${relationName} { ${this.getFieldsForQuery(relatedEntity, depth - 1)} }`;
                    // Use the correct relation name from the entity config for the query
                    fields.push(query);
                }
            } else if (!col.isForeignKey) {
                fields.push(colName);
            }
        }
        return fields.join(' ');
    }
    
    async fetchAll(entity) {
        const fields = this.getFieldsForQuery(entity, 0); // Only simple fields
        const query = `query FetchAll($limit: Int) { ${entity.pluralName}(limit: $limit) { items { ${fields} } } }`;
        try {
            const data = await this.gqlQuery(query, { limit: this.state.limit });
            return data[entity.pluralName].items;
        } catch (error) {
            console.error(`Failed to pre-fetch ${entity.pluralName}:`, error);
            return [];
        }
    }

    openModal() { this.dom.modal.style.display = 'block'; }
    closeModal() { this.dom.modal.style.display = 'none'; this.dom.form.innerHTML = ''; }
    openLoginModal() { this.dom.loginModal.style.display = 'block'; }
    closeLoginModal() { this.dom.loginModal.style.display = 'none'; this.dom.loginForm.reset(); document.getElementById('login-error').textContent = ''; }
}

document.addEventListener('DOMContentLoaded', () => new GraphQLClientApp('frontend-config.json', 'graphql.php'));
JS;
    }

    /**
     * Generates a zip package containing the graphql.php, manual.md, and a nested frontend.zip.
     *
     * @param string $zipFilePath The full path where the final zip file will be saved.
     * @return bool True on success, false on failure.
     * @throws Exception If ZipArchive is not available or fails.
     */
    public function generatePackage($zipFilePath)
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception('ZipArchive class is not found. Please enable the PHP zip extension.');
        }

        // 1. Create frontend.zip in memory
        $frontendZip = new \ZipArchive();
        $tempFrontendZipFile = tempnam(sys_get_temp_dir(), 'frontend') . '.zip';
        if ($frontendZip->open($tempFrontendZipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception("Cannot create temporary frontend zip file: $tempFrontendZipFile");
        }
        $frontendZip->addFromString('index.html', $this->generateFrontendHtml());
        $frontendZip->addFromString('style.css', $this->generateFrontendCss());
        $frontendZip->addFromString('app.js', $this->generateFrontendJs());
        $frontendZip->addFromString('frontend-config.json', $this->generateFrontendConfigJson());
        $frontendZip->close();

        // 2. Create the main package zip
        $mainZip = new \ZipArchive();
        if ($mainZip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            unlink($tempFrontendZipFile); // Clean up temp file
            throw new \Exception("Cannot create main package zip file: $zipFilePath");
        }

        // 3. Add all files to the main package
        $mainZip->addFromString('graphql.php', $this->generate());
        $mainZip->addFromString('manual.md', $this->generateManual());
        $mainZip->addFile($tempFrontendZipFile, 'frontend.zip');

        // 4. Close and clean up
        $mainZip->close();
        unlink($tempFrontendZipFile);

        return file_exists($zipFilePath);
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
        $header .= "require_once __DIR__ . '/database.php';\r\n\r\n";
        
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
