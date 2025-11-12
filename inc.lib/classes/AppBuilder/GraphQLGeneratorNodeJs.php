<?php

namespace AppBuilder;

/**
 * The `GraphQLGeneratorNodeJs` class is designed to automatically generate a complete Node.js GraphQL API
 * from a JSON file that defines database entities. It uses Express.js as the web server,
 * `express-graphql` for the GraphQL endpoint, and Sequelize as the ORM for database interaction.
 * The generated code follows a modular structure, separating models, types, resolvers, and the main server logic.
 *
 * @package AppBuilder
 */
class GraphQLGeneratorNodeJs extends GraphQLGeneratorBase
{
    private $schema;
    private $analyzedSchema = array();
    private $reservedColumns = [];
    private $backendHandledColumns = [];
    private $activeField = 'active';
    private $displayField = 'name';
    private $useCache = false; // Caching is not implemented for Node.js version yet

    public function __construct($schema, $reservedColumns = null, $backendHandledColumns = array(), $useCache = false)
    {
        $this->schema = $schema;
        $this->backendHandledColumns = $backendHandledColumns;
        $this->useCache = $useCache;

        if (isset($reservedColumns) && isset($reservedColumns['columns'])) {
            $arr = array();
            foreach ($reservedColumns['columns'] as $value) {
                $arr[$value['key']] = $value;
                if ($value['key'] == 'active') {
                    $this->activeField = $value['name'];
                }
                if ($value['key'] == 'name') {
                    $this->displayField = $value['name'];
                }
            }
            $this->reservedColumns = $arr;
        }

        $this->analyzeSchema();
    }

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
                'hasActiveColumn' => false
            );

            foreach ($entity['columns'] as $column) {
                $columnName = $column['name'];
                if ($column['primaryKey']) {
                    $this->analyzedSchema[$tableName]['primaryKey'] = $columnName;
                    $primaryKey = $columnName;
                }
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

                if ($columnName !== $primaryKey && substr($columnName, -3) === '_id') {
                    $refTableName = substr($columnName, 0, -3);
                    if (in_array($refTableName, $tableNames)) {
                        $this->analyzedSchema[$tableName]['columns'][$columnName]['isForeignKey'] = true;
                        $this->analyzedSchema[$tableName]['columns'][$columnName]['references'] = $refTableName;
                    }
                }
            }
        }
    }

    private function mapDbTypeToSequelizeType($dbType, $length = null)
    {
        $dbType = strtolower($dbType);
        if (strpos($dbType, 'varchar') !== false) {
            return "DataTypes.STRING($length)";
        }
        if (strpos($dbType, 'text') !== false || strpos($dbType, 'timestamp') !== false || strpos($dbType, 'datetime') !== false || strpos($dbType, 'date') !== false) {
            // Treat text and all date/time types as strings to preserve their original format from the database
            return 'DataTypes.STRING';
        }
        if (strpos($dbType, 'decimal') !== false || strpos($dbType, 'double') !== false) {
            return 'DataTypes.DOUBLE';
        }
        if (strpos($dbType, 'float') !== false) {
            return 'DataTypes.FLOAT';
        }
        if ((strpos($dbType, 'tinyint') !== false && isset($length) && $length == '1') || strpos($dbType, 'bool') !== false || strpos($dbType, 'bit') !== false) {
            return 'DataTypes.BOOLEAN';
        }
        if (strpos($dbType, 'int') !== false) {
            return 'DataTypes.INTEGER';
        }
        return 'DataTypes.STRING'; // Default
    }

    private function mapDbTypeToGqlType($dbType, $length = null)
    {
        $dbType = strtolower($dbType);
        if (strpos($dbType, 'varchar') !== false || strpos($dbType, 'text') !== false || strpos($dbType, 'date') !== false || strpos($dbType, 'timestamp') !== false) {
            return 'GraphQLString';
        }
        if (strpos($dbType, 'decimal') !== false || strpos($dbType, 'float') !== false || strpos($dbType, 'double') !== false) {
            return 'GraphQLFloat';
        }
        if ((strpos($dbType, 'tinyint') !== false && isset($length) && $length == '1') || strpos($dbType, 'bool') !== false || strpos($dbType, 'bit') !== false) {
            return 'GraphQLBoolean';
        }
        if (strpos($dbType, 'int') !== false) {
            return 'GraphQLInt';
        }
        return 'GraphQLString'; // Default
    }

    private function camelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    private function pascalCase($string)
    {
        return ucfirst($this->camelCase($string));
    }

    /**
     * Converts a camelCase string to snake_case.
     *
     * @param string $str The camelCase string.
     * @return string The converted snake_case string.
     */
    public function camelCaseToSnakeCase($str) {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
    }

    /**
     * Converts a snake_case string to camelCase.
     *
     * @param string $str The snake_case string.
     * @return string The converted camelCase string.
     */
    public function snakeCaseToCamelCase($str) {
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
    public function snakeCaseToTitleCase($str) {
        $str = str_replace('_', ' ', strtolower($str));
        return $this->titleCase($str);
    }

    /**
     * Converts a string to Title Case.
     *
     * @param string $str The input string.
     * @return string The converted Title Case string.
     */
    public function titleCase($str) {
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
    public function camelCaseToTitleCase($str) {
        return $this->snakeCaseToTitleCase($this->camelCaseToSnakeCase($str));
    }

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

    public function generate()
    {
        $files = [];
        $files[] = ['name' => 'package.json', 'content' => $this->generatePackageJson()];
        $files[] = ['name' => '.env', 'content' => $this->generateEnvFile()];
        $files[] = ['name' => 'server.js', 'content' => $this->generateServerJs()];
        $files[] = ['name' => 'config/database.js', 'content' => $this->generateDatabaseJs()];
        $files[] = ['name' => 'schema/schema.js', 'content' => $this->generateSchemaJs()];
        $files[] = ['name' => 'schema/types.js', 'content' => $this->generateTypesJs()];
        $files[] = ['name' => 'schema/resolvers.js', 'content' => $this->generateResolversJs()];
        $files[] = ['name' => 'config/frontend-config.json', 'content' => $this->generateFrontendConfigJson()];

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $pascalName = $this->pascalCase($tableName);
            $files[] = ['name' => "models/$pascalName.js", 'content' => $this->generateModelFile($tableName, $tableInfo)];
        }

        return $files;
    }

    private function generatePackageJson()
    {
        return <<<JSON
{
  "name": "graphql-nodejs-api",
  "version": "1.0.0",
  "description": "GraphQL API generated for Node.js",
  "main": "server.js",
  "scripts": {
    "start": "node server.js",
    "dev": "nodemon server.js"
  },
  "dependencies": {
    "cors": "^2.8.5",
    "dotenv": "^16.0.3",
    "express": "^4.18.2",
    "express-graphql": "^0.12.0",
    "graphql": "^15.8.0",
    "mysql2": "^3.2.0",
    "pg": "^8.10.0",
    "sequelize": "^6.29.3",
    "sqlite3": "^5.1.6"
  },
  "devDependencies": {
    "nodemon": "^2.0.22"
  }
}
JSON;
    }

    private function generateEnvFile()
    {
        return <<<ENV
# Database Configuration
# Supported dialects: 'mysql', 'postgres', 'sqlite', 'mariadb', 'mssql'
DB_DIALECT={DB_DIALECT}
DB_HOST={DB_HOST}
DB_PORT={DB_PORT}
DB_USER={DB_USER}
DB_PASS={DB_PASS}
DB_NAME={DB_NAME}

# Server Configuration
PORT=4000

# CORS Configuration
# Comma-separated list of allowed origins. Do not use spaces between origins.
CORS_ALLOWED_ORIGINS=http://localhost,http://127.0.0.1,http://localhost:3000,http://localhost:4000,http://127.0.0.1:4000,http://127.0.0.1:3000,http://localhost:8080
ENV;
    }

    private function generateServerJs()
    {
        return <<<JS
require('dotenv').config();
const express = require('express');
const { graphqlHTTP } = require('express-graphql');
const cors = require('cors');
const schema = require('./schema/schema');
const fs = require('fs');
const path = require('path');
const { sequelize } = require('./config/database');

const app = express();

// CORS configuration
const allowedOrigins = process.env.CORS_ALLOWED_ORIGINS ? process.env.CORS_ALLOWED_ORIGINS.split(',') : [];

const corsOptions = {
  origin: (origin, callback) => {
    // Allow requests with no origin (like mobile apps or curl requests)
    if (!origin) return callback(null, true);
    
    if (allowedOrigins.length === 0 || allowedOrigins.indexOf(origin) !== -1) {
      callback(null, true);
    } else {
      callback(new Error('Not allowed by CORS'));
    }
  },
  credentials: true,
};
app.use(cors(corsOptions));

// Endpoint to serve frontend configuration
app.get('/frontend-config', (req, res) => {
    const configPath = path.join(__dirname, 'config', 'frontend-config.json');
    fs.readFile(configPath, 'utf8', (err, data) => {
        if (err) {
            console.error('Error reading frontend-config.json:', err);
            res.status(500).send({ error: 'Could not load frontend configuration.' });
            return;
        }
        res.setHeader('Content-Type', 'application/json');
        res.send(data);
    });
});


app.use('/graphql', graphqlHTTP({
    schema,
    graphiql: true, // Enable GraphiQL interface for testing
}));

const PORT = process.env.PORT || 4000;

sequelize.authenticate()
    .then(() => {
        console.log('Database connection has been established successfully.');
        app.listen(PORT, () => {
            console.log(`Server running on http://localhost:\${PORT}/graphql`);
        });
    })
    .catch(err => {
        console.error('Unable to connect to the database:', err);
    });
JS;
    }

    private function generateDatabaseJs()
    {
        return <<<JS
const { Sequelize } = require('sequelize');

const sequelize = new Sequelize(
    process.env.DB_NAME,
    process.env.DB_USER,
    process.env.DB_PASS,
    {
        host: process.env.DB_HOST,
        port: process.env.DB_PORT,
        dialect: process.env.DB_DIALECT,
        dialectOptions: {
            // Return date/time values as strings, not Date objects.
            dateStrings: true
        },
        logging: false, // Set to console.log to see SQL queries
        define: {
            timestamps: false, // Assuming no `createdAt` and `updatedAt` fields
            // This is important to prevent Sequelize from changing column names to camelCase
            quoteIdentifiers: false,
            freezeTableName: true // Prevent Sequelize from pluralizing table names
        }
    }
);

const models = {};

// Dynamically import all models
const fs = require('fs');
const path = require('path');
const modelsDir = path.join(__dirname, '../models');

fs.readdirSync(modelsDir)
  .filter(file => file.indexOf('.') !== 0 && file.slice(-3) === '.js')
  .forEach(file => {
    const model = require(path.join(modelsDir, file))(sequelize, Sequelize.DataTypes);
    models[model.name] = model;
  });

// Set up associations
Object.keys(models).forEach(modelName => {
  if (models[modelName].associate) {
    models[modelName].associate(models);
  }
});

module.exports = { sequelize, models };
JS;
    }

    private function generateModelFile($tableName, $tableInfo)
    {
        $pascalName = $this->pascalCase($tableName);
        $fields = "";

        foreach ($tableInfo['columns'] as $colName => $colInfo) {
            $sequelizeType = $this->mapDbTypeToSequelizeType($colInfo['type'], $colInfo['length']);
            $fields .= "    '$colName': {\n"; // Use original column name as attribute
            $fields .= "      type: $sequelizeType,\n"; // No need for 'field' mapping anymore
            if ($colInfo['isPrimaryKey']) {
                $fields .= "      primaryKey: true,\n";
            }
            if ($colInfo['isAutoIncrement']) {
                $fields .= "      autoIncrement: true,\n";
            }
            $fields .= "    },\n";
        }

        $associations = "";
        foreach ($tableInfo['columns'] as $colName => $colInfo) {
            if ($colInfo['isForeignKey']) {
                $refTableName = $colInfo['references'];
                $refPascalName = $this->pascalCase($refTableName);
                $associations .= "      $pascalName.belongsTo(models.$refPascalName, { foreignKey: '$colName', as: '$refTableName' });\n";
            }
        }

        return <<<JS
module.exports = (sequelize, DataTypes) => {
  const $pascalName = sequelize.define('$pascalName', {
{$fields}
  }, {
    tableName: '$tableName'
  });

  $pascalName.associate = (models) => {
{$associations}
  };

  return $pascalName;
};
JS;
    }

    private function generateSchemaJs()
    {
        return <<<JS
const { GraphQLSchema, GraphQLObjectType } = require('graphql');
const { RootQuery } = require('./resolvers');
const { RootMutation } = require('./resolvers');

module.exports = new GraphQLSchema({
    query: RootQuery,
    mutation: RootMutation,
});
JS;
    }

    private function generateTypesJs()
    {
        $typeImports = "";
        $typeExports = "";
        $typeDefinitions = "";

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $pascalName = $this->pascalCase($tableName);
            $typeName = $pascalName . 'Type';
            $pageTypeName = $pascalName . 'PageType';
            $inputType = $pascalName . 'InputType';

            $typeExports .= "    $typeName,\n";
            $typeExports .= "    $pageTypeName,\n";
            $typeExports .= "    $inputType,\n";

            // --- Object Type ---
            $fields = "";
            foreach ($tableInfo['columns'] as $colName => $colInfo) {
                $gqlType = $this->mapDbTypeToGqlType($colInfo['type'], $colInfo['length']);
                $fields .= "        $colName: { type: $gqlType },\n";

                if ($colInfo['isForeignKey']) {
                    $refTableName = $colInfo['references'];
                    $refPascalName = $this->pascalCase($refTableName);
                    $refTypeName = $refPascalName . 'Type';
                    $fields .= "        $refTableName: {\n";
                    $fields .= "            type: $refTypeName,\n";
                    $fields .= "            resolve(parent, args) {\n";
                    $fields .= "                return models.$refPascalName.findByPk(parent['$colName']);\n";
                    $fields .= "            }\n";
                    $fields .= "        },\n";
                }
            }

            $typeDefinitions .= "const $typeName = new GraphQLObjectType({\n";
            $typeDefinitions .= "    name: '$pascalName',\n";
            $typeDefinitions .= "    fields: () => ({\n";
            $typeDefinitions .= rtrim($fields, ",\n") . "\n";
            $typeDefinitions .= "    })\n";
            $typeDefinitions .= "});\n\n";

            // --- Page Type ---
            $typeDefinitions .= "const $pageTypeName = new GraphQLObjectType({\n";
            $typeDefinitions .= "    name: '$pageTypeName',\n";
            $typeDefinitions .= "    fields: () => ({\n";
            $typeDefinitions .= "        items: { type: new GraphQLList($typeName) },\n";
            $typeDefinitions .= "        total: { type: GraphQLInt },\n";
            $typeDefinitions .= "        limit: { type: GraphQLInt },\n";
            $typeDefinitions .= "        page: { type: GraphQLInt },\n";
            $typeDefinitions .= "        totalPages: { type: GraphQLInt },\n";
            $typeDefinitions .= "        hasNext: { type: GraphQLBoolean },\n";
            $typeDefinitions .= "        hasPrevious: { type: GraphQLBoolean },\n";
            $typeDefinitions .= "    })\n";
            $typeDefinitions .= "});\n\n";

            // --- Input Type ---
            $inputFields = "";
            foreach ($tableInfo['columns'] as $colName => $colInfo) {
                if ($colName === $tableInfo['primaryKey'] && ($colInfo['isAutoIncrement'] || $colInfo['primaryKeyValue'] == 'autogenerated')) {
                    continue;
                }
                $gqlType = $this->mapDbTypeToGqlType($colInfo['type'], $colInfo['length']);
                $inputFields .= "        $colName: { type: $gqlType },\n";
            }

            $typeDefinitions .= "const $inputType = new GraphQLInputObjectType({\n";
            $typeDefinitions .= "    name: '$inputType',\n";
            $typeDefinitions .= "    fields: () => ({\n";
            $typeDefinitions .= rtrim($inputFields, ",\n") . "\n";
            $typeDefinitions .= "    })\n";
            $typeDefinitions .= "});\n\n";
        }

        return <<<JS
const {
    GraphQLObjectType,
    GraphQLInputObjectType,
    GraphQLString,
    GraphQLInt,
    GraphQLFloat,
    GraphQLBoolean,
    GraphQLList,
    GraphQLNonNull,
    GraphQLEnumType,
    GraphQLScalarType,
    Kind
} = require('graphql');
const { models } = require('../config/database');

// Custom Scalar for generic Object
const ObjectScalar = new GraphQLScalarType({
    name: 'Object',
    description: 'Arbitrary object',
    parseValue: (value) => {
        return value;
    },
    serialize: (value) => {
        return value;
    },
    parseLiteral: (ast) => {
        switch (ast.kind) {
            case Kind.STRING:
                return ast.value;
            case Kind.BOOLEAN:
                return ast.value;
            case Kind.INT:
                return parseInt(ast.value, 10);
            case Kind.FLOAT:
                return parseFloat(ast.value);
            case Kind.OBJECT:
                const value = Object.create(null);
                ast.fields.forEach(field => {
                    value[field.name.value] = this.parseLiteral(field.value);
                });
                return value;
            case Kind.LIST:
                return ast.values.map(n => this.parseLiteral(n));
            default:
                return null;
        }
    }
});

const SortDirectionEnum = new GraphQLEnumType({
    name: 'SortDirection',
    values: {
        ASC: { value: 'ASC' },
        DESC: { value: 'DESC' },
    }
});

const FilterOperatorEnum = new GraphQLEnumType({
    name: 'FilterOperator',
    values: {
        EQUALS: { value: 'EQUALS' },
        NOT_EQUALS: { value: 'NOT_EQUALS' },
        CONTAINS: { value: 'CONTAINS' },
        GREATER_THAN: { value: 'GREATER_THAN' },
        GREATER_THAN_OR_EQUALS: { value: 'GREATER_THAN_OR_EQUALS' },
        LESS_THAN: { value: 'LESS_THAN' },
        LESS_THAN_OR_EQUALS: { value: 'LESS_THAN_OR_EQUALS' },
        IN: { value: 'IN' },
        NOT_IN: { value: 'NOT_IN' },
    }
});

const SortInputType = new GraphQLInputObjectType({
    name: 'SortInput',
    fields: {
        field: { type: new GraphQLNonNull(GraphQLString) },
        direction: { type: SortDirectionEnum, defaultValue: 'ASC' }
    }
});

const FilterInputType = new GraphQLInputObjectType({
    name: 'FilterInput',
    fields: {
        field: { type: new GraphQLNonNull(GraphQLString) },
        value: { type: ObjectScalar },
        operator: { type: FilterOperatorEnum, defaultValue: 'EQUALS' }
    }
});

$typeDefinitions

module.exports = {
$typeExports
    SortInputType,
    FilterInputType
};
JS;
    }

    private function generateResolversJs()
    {
        $queryFields = "";
        $mutationFields = "";

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $camelName = $this->camelCase($tableName);
            $pascalName = $this->pascalCase($tableName);
            $pluralCamelName = $this->pluralize($camelName);
            $primaryKey = $this->camelCase($tableInfo['primaryKey']);

            // --- Query Resolvers ---
            $queryFields .= "        $camelName: {\n";
            $queryFields .= "            type: types.{$pascalName}Type,\n";
            $queryFields .= "            args: { id: { type: new GraphQLNonNull(GraphQLID) } },\n";
            $queryFields .= "            resolve(parent, args) {\n";
            $queryFields .= "                return models.$pascalName.findByPk(args.id);\n";
            $queryFields .= "            }\n";
            $queryFields .= "        },\n";

            $queryFields .= "        $pluralCamelName: {\n";
            $queryFields .= "            type: types.{$pascalName}PageType,\n";
            $queryFields .= "            args: {\n";
            $queryFields .= "                limit: { type: GraphQLInt },\n";
            $queryFields .= "                offset: { type: GraphQLInt },\n";
            $queryFields .= "                page: { type: GraphQLInt },\n";
            $queryFields .= "                orderBy: { type: new GraphQLList(types.SortInputType) },\n";
            $queryFields .= "                filter: { type: new GraphQLList(types.FilterInputType) },\n";
            $queryFields .= "            },\n";
            $queryFields .= "            async resolve(parent, args) {\n";
            $queryFields .= "                const limit = args.limit || 20;\n";
            $queryFields .= "                let offset = args.offset || 0;\n";
            $queryFields .= "                if (args.page) {\n";
            $queryFields .= "                    offset = (args.page - 1) * limit;\n";
            $queryFields .= "                }\n\n";
            $queryFields .= "                const order = args.orderBy ? args.orderBy.map(o => [o.field, o.direction || 'ASC']) : [];\n\n";
            $queryFields .= "                const where = {};\n";
            $queryFields .= "                if (args.filter) {\n";
            $queryFields .= "                    args.filter.forEach(f => {\n";
            $queryFields .= "                        const op = f.operator || 'EQUALS';\n";
            $queryFields .= "                        const field = f.field;\n";
            $queryFields .= "                        const value = f.value;\n";
            $queryFields .= "                        switch (op) {\n";
            $queryFields .= "                            case 'EQUALS': where[field] = { [Op.eq]: value }; break;\n";
            $queryFields .= "                            case 'NOT_EQUALS': where[field] = { [Op.ne]: value }; break;\n";
            $queryFields .= "                            case 'CONTAINS': where[field] = { [Op.like]: `% \${value}%` }; break;\n";
            $queryFields .= "                            case 'GREATER_THAN': where[field] = { [Op.gt]: value }; break;\n";
            $queryFields .= "                            case 'GREATER_THAN_OR_EQUALS': where[field] = { [Op.gte]: value }; break;\n";
            $queryFields .= "                            case 'LESS_THAN': where[field] = { [Op.lt]: value }; break;\n";
            $queryFields .= "                            case 'LESS_THAN_OR_EQUALS': where[field] = { [Op.lte]: value }; break;\n";
            $queryFields .= "                            case 'IN': where[field] = { [Op.in]: value.split(',') }; break;\n";
            $queryFields .= "                            case 'NOT_IN': where[field] = { [Op.notIn]: value.split(',') }; break;\n";
            $queryFields .= "                        }\n";
            $queryFields .= "                    });\n";
            $queryFields .= "                }\n\n";
            $queryFields .= "                const { count, rows } = await models.$pascalName.findAndCountAll({ where, limit, offset, order });\n\n";
            $queryFields .= "                return {\n";
            $queryFields .= "                    items: rows,\n";
            $queryFields .= "                    total: count,\n";
            $queryFields .= "                    limit: limit,\n";
            $queryFields .= "                    page: Math.floor(offset / limit) + 1,\n";
            $queryFields .= "                    totalPages: Math.ceil(count / limit),\n";
            $queryFields .= "                    hasNext: (offset + limit) < count,\n";
            $queryFields .= "                    hasPrevious: offset > 0,\n";
            $queryFields .= "                };\n";
            $queryFields .= "            }\n";
            $queryFields .= "        },\n";

            // --- Mutation Resolvers ---
            $mutationFields .= "        create$pascalName: {\n";
            $mutationFields .= "            type: types.{$pascalName}Type,\n";
            $mutationFields .= "            args: { input: { type: new GraphQLNonNull(types.{$pascalName}InputType) } },\n";
            $mutationFields .= "            resolve(parent, args) {\n";
            $mutationFields .= "                return models.$pascalName.create(args.input);\n";
            $mutationFields .= "            }\n";
            $mutationFields .= "        },\n";

            $mutationFields .= "        update$pascalName: {\n";
            $mutationFields .= "            type: types.{$pascalName}Type,\n";
            $mutationFields .= "            args: {\n";
            $mutationFields .= "                id: { type: new GraphQLNonNull(GraphQLID) },\n";
            $mutationFields .= "                input: { type: new GraphQLNonNull(types.{$pascalName}InputType) }\n";
            $mutationFields .= "            },\n";
            $mutationFields .= "            async resolve(parent, args) {\n";
            $mutationFields .= "                const item = await models.$pascalName.findByPk(args.id);\n";
            $mutationFields .= "                if (!item) throw new Error('$pascalName not found');\n";
            $mutationFields .= "                await item.update(args.input);\n";
            $mutationFields .= "                return item;\n";
            $mutationFields .= "            }\n";
            $mutationFields .= "        },\n";

            $mutationFields .= "        delete$pascalName: {\n";
            $mutationFields .= "            type: GraphQLBoolean,\n";
            $mutationFields .= "            args: { id: { type: new GraphQLNonNull(GraphQLID) } },\n";
            $mutationFields .= "            async resolve(parent, args) {\n";
            $mutationFields .= "                const item = await models.$pascalName.findByPk(args.id);\n";
            $mutationFields .= "                if (!item) throw new Error('$pascalName not found');\n";
            $mutationFields .= "                await item.destroy();\n";
            $mutationFields .= "                return true;\n";
            $mutationFields .= "            }\n";
            $mutationFields .= "        },\n";
        }

        return <<<JS
const { GraphQLObjectType, GraphQLList, GraphQLNonNull, GraphQLString, GraphQLInt, GraphQLID, GraphQLBoolean } = require('graphql');
const { Op } = require('sequelize');
const { models } = require('../config/database');
const types = require('./types');

const RootQuery = new GraphQLObjectType({
    name: 'RootQueryType',
    fields: {
$queryFields
    }
});

const RootMutation = new GraphQLObjectType({
    name: 'Mutation',
    fields: {
$mutationFields
    }
});

module.exports = {
    RootQuery,
    RootMutation
};
JS;
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
            
            $textareaColumns = isset($tableInfo['textareaColumns']) && is_array($tableInfo['textareaColumns']) ? $tableInfo['textareaColumns'] : array();
            
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
                if($colInfo['isForeignKey'] && \MagicObject\Util\PicoStringUtil::endsWith($colName, '_id'))
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
     * Generates a markdown manual with examples for all queries and mutations.
     *
     * @return string The markdown content.
     */
    public function generateManual()
    {
        $manualContent = "# GraphQL API Manual\r\n\r\n";
        $manualContent .= "This document provides examples for all available queries and mutations.\r\n\r\n";
        
        $manualContent .= "## Dependencies\r\n\r\n";
        $manualContent .= "All required dependencies are defined in `package.json`. Run `npm install` to install them.\r\n\r\n";

        $manualContent .= "## Database Connection\r\n\r\n";
        $manualContent .= "This API requires a database connection. You must create and configure a `.env` file in the root of the backend project. Here is an example for connecting to a MySQL database:\r\n\r\n";
        $manualContent .= "```\r\n";
        $manualContent .= "# file: .env\r\n";
        $manualContent .= "\r\n# Database Configuration\r\n";
        $manualContent .= "# Supported dialects: 'mysql', 'postgres', 'sqlite', 'mariadb', 'mssql'\r\n";
        $manualContent .= "DB_DIALECT=mysql\r\n";
        $manualContent .= "DB_HOST=localhost\r\n";
        $manualContent .= "DB_PORT=3306\r\n";
        $manualContent .= "DB_USER=your_username\r\n";
        $manualContent .= "DB_PASS=your_password\r\n";
        $manualContent .= "DB_NAME=your_database_name\r\n";
        $manualContent .= "\r\n# Server Configuration\r\n";
        $manualContent .= "PORT=4000\r\n";
        $manualContent .= "\r\n# CORS Configuration\r\n";
        $manualContent .= "# Comma-separated list of allowed origins. Do not use spaces between origins.\r\n";
        $manualContent .= "CORS_ALLOWED_ORIGINS=http://localhost,http://127.0.0.1,http://localhost:3000\r\n";
        $manualContent .= "```\r\n\r\n";
        $manualContent .= "Make sure to replace `your_database_name`, `your_username`, and `your_password` with your actual database credentials.\r\n\r\n";

        $manualContent .= "---\r\n\r\n";

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $camelName = $this->camelCase($tableName);
            $pascalName = $this->pascalCase($tableName);
            $pluralCamelName = $this->pluralize($camelName);

            $manualContent .= "## " . $pascalName . "\r\n\r\n";

            $fieldsString = $this->getFieldsForManual($tableInfo, false);
            $mutationFieldsString = $this->getFieldsForManual($tableInfo, true);

            $manualContent .= "### Queries\r\n\r\n";
            $manualContent .= "#### Get a single " . $camelName . "\r\n\r\n";
            $manualContent .= "```graphql\r\n";
            $manualContent .= "query Get" . $pascalName . " {\r\n";
            $manualContent .= "  " . $camelName . "(id: \"your-" . $camelName . "-id\") {\r\n";
            $manualContent .= $fieldsString;
            $manualContent .= "  }\r\n";
            $manualContent .= "}\r\n";
            $manualContent .= "```\r\n\r\n";

            $manualContent .= "#### Get a list of " . $pluralCamelName . "\r\n\r\n";
            $manualContent .= "```graphql\r\n";
            $manualContent .= "query Get" . $this->pascalCase($pluralCamelName) . " {\r\n";
            $manualContent .= "  " . $pluralCamelName . "(limit: 10, offset: 0) {\r\n";
            $manualContent .= "    items {\r\n";
            $manualContent .= preg_replace('/^/m', '      ', $fieldsString);
            $manualContent .= "    }\r\n";
            $manualContent .= "    total\r\n";
            $manualContent .= "  }\r\n";
            $manualContent .= "}\r\n";
            $manualContent .= "```\r\n\r\n";

            $manualContent .= "### Mutations\r\n\r\n";
            list($inputFieldsString, $inputExampleString) = $this->getInputFieldsForManual($tableInfo);

            $manualContent .= "#### Create a new " . $camelName . "\r\n\r\n";
            $manualContent .= "```graphql\r\n";
            $manualContent .= "mutation Create" . $pascalName . " {\r\n";
            $manualContent .= "  create" . $pascalName . "(input: {\r\n" . $inputExampleString . "  }) {\r\n";
            $manualContent .= $mutationFieldsString;
            $manualContent .= "  }\r\n";
            $manualContent .= "}\r\n";
            $manualContent .= "```\r\n\r\n";

            $manualContent .= "#### Update an existing " . $camelName . "\r\n\r\n";
            $manualContent .= "```graphql\r\n";
            $manualContent .= "mutation Update" . $pascalName . " {\r\n";
            $manualContent .= "  update" . $pascalName . "(id: \"your-" . $camelName . "-id\", input: {\r\n" . $inputExampleString . "  }) {\r\n";
            $manualContent .= $mutationFieldsString;
            $manualContent .= "  }\r\n";
            $manualContent .= "}\r\n";
            $manualContent .= "```\r\n\r\n";

            $manualContent .= "#### Delete a " . $camelName . "\r\n\r\n";
            $manualContent .= "```graphql\r\n";
            $manualContent .= "mutation Delete" . $pascalName . " {\r\n";
            $manualContent .= "  delete" . $pascalName . "(id: \"your-" . $camelName . "-id\")\r\n";
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

    private function getFieldsForManual($tableInfo, $noRelations = false)
    {
        $fieldsString = "";
        foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
            $camelColName = $this->camelCase($columnName);
            if (!$columnInfo['isForeignKey']) {
                $fieldsString .= "    " . $columnName . "\r\n";
            } else if (!$noRelations) {
                $refTableName = $columnInfo['references'];
                $fieldsString .= "    " . $refTableName . " {\r\n";
                $fieldsString .= "      " . $this->camelCase($this->analyzedSchema[$refTableName]['primaryKey']) . "\r\n";
                $fieldsString .= "    }\r\n";
            }
        }
        return $fieldsString;
    }

    private function getInputFieldsForManual($tableInfo)
    {
        $inputExampleString = "";
        foreach ($tableInfo['columns'] as $columnName => $columnInfo) {
            if ($columnName === $tableInfo['primaryKey'] && ($columnInfo['isAutoIncrement'] || $columnInfo['primaryKeyValue'] == 'autogenerated')) continue;
            
            $gqlType = $this->mapDbTypeToGqlType($columnInfo['type'], $columnInfo['length']);
            $exampleValue = '"string"';
            if ($gqlType === 'GraphQLInt') $exampleValue = '123';
            if ($gqlType === 'GraphQLFloat') $exampleValue = '123.45';
            if ($gqlType === 'GraphQLBoolean') $exampleValue = 'true';
            $inputExampleString .= "    " . $columnName . ": " . $exampleValue . "\r\n";
        }
        return array($inputExampleString, $inputExampleString);
    }
}