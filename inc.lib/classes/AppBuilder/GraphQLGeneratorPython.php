<?php

namespace AppBuilder;

/**
 * The `GraphQLGeneratorPython` class is designed to automatically generate a complete Python GraphQL API
 * from a JSON file that defines database entities. It uses FastAPI as the web server,
 * Ariadne for the GraphQL endpoint, and SQLAlchemy 2.0 as the ORM for database interaction.
 * The generated code follows a modular structure, separating models, schemas, and resolvers.
 *
 * @package AppBuilder
 */
class GraphQLGeneratorPython extends GraphQLGeneratorBase
{
    /**
     * Constructor for GraphQLGeneratorPython.
     *
     * @param array      $schema                Decoded JSON schema.
     * @param array|null $reservedColumns       Reserved column definitions.
     * @param array      $backendHandledColumns Columns handled by the backend.
     * @param bool       $useCache              Whether to use in-memory caching for queries.
     */
    public function __construct($schema, $reservedColumns = null, $backendHandledColumns = array(), $useCache = false)
    {
        parent::__construct($schema, $reservedColumns, $backendHandledColumns, $useCache);
    }



    /**
     * Maps database column types to SQLAlchemy column types.
     *
     * @param string $dbType The database column type (e.g., VARCHAR, INT, TIMESTAMP).
     * @return string The corresponding SQLAlchemy type string.
     */
    private function mapDbTypeToSqlalchemyType($dbType, $length = null) // NOSONAR
    {
        $dbType = strtolower($dbType);

        if (strpos($dbType, 'varchar') !== false) {
            return "String($length)";
        }

        if (strpos($dbType, 'text') !== false) {
            return "Text";
        }

        if (strpos($dbType, 'timestamp') !== false) {
            return "String";
        }

        if (strpos($dbType, 'date') !== false) {
            return "String";
        }

        if (strpos($dbType, 'decimal') !== false || strpos($dbType, 'double') !== false) {
            return "Float";
        }

        if (strpos($dbType, 'float') !== false) {
            return "Float";
        }

        if (
            (strpos($dbType, 'tinyint') !== false && isset($length) && $length == '1')
            || strpos($dbType, 'bool') !== false
            || strpos($dbType, 'bit') !== false
        ) {
            return "Boolean";
        }

        if (strpos($dbType, 'int') !== false) {
            return "Integer";
        }

        // Default
        return "String";
    }

    /**
     * Maps database column types to Python native types.
     *
     * @param string $dbType The database column type (e.g., VARCHAR, INT, TIMESTAMP).
     * @return string The corresponding Python type string.
     */
    private function mapDbTypeToPythonType($dbType, $length = null) // NOSONAR
    {
        $dbType = strtolower($dbType);

        if (strpos($dbType, 'varchar') !== false || strpos($dbType, 'text') !== false) {
            return "str";
        }

        if (strpos($dbType, 'timestamp') !== false) {
            return "datetime";
        }

        if (strpos($dbType, 'date') !== false) {
            return "date";
        }

        if (
            strpos($dbType, 'decimal') !== false
            || strpos($dbType, 'float') !== false
            || strpos($dbType, 'double') !== false
        ) {
            return "float";
        }

        if (
            (strpos($dbType, 'tinyint') !== false && isset($length) && $length == '1')
            || strpos($dbType, 'bool') !== false
            || strpos($dbType, 'bit') !== false
        ) {
            return "bool";
        }

        if (strpos($dbType, 'int') !== false) {
            return "int";
        }

        // Default
        return "str";
    }

    /**
     * Maps database column types to GraphQL types for the schema.
     *
     * @param string $dbType The database column type (e.g., VARCHAR, INT, TIMESTAMP).
     * @param int|null $length The length of the column.
     * @return string The corresponding GraphQL type string (e.g., 'String', 'Int').
     */
    public function mapDbTypeToPythonGqlType($dbType, $length = null) // NOSONAR
    {
        $dbType = strtolower($dbType);
        if (strpos($dbType, 'varchar') !== false || strpos($dbType, 'text') !== false || strpos($dbType, 'date') !== false || strpos($dbType, 'timestamp') !== false) {
            return 'String';
        }
        if (strpos($dbType, 'decimal') !== false || strpos($dbType, 'float') !== false || strpos($dbType, 'double') !== false) {
            return 'Float';
        }
        if ((strpos($dbType, 'tinyint') !== false && isset($length) && $length == '1') || strpos($dbType, 'bool') !== false || strpos($dbType, 'bit') !== false) {
            return 'Boolean';
        }
        if (strpos($dbType, 'int') !== false) {
            return 'Int';
        }
        return 'String'; // Default fallback
    }

    /**
     * Generates the complete set of files for the Python GraphQL API.
     *
     * @return array An array of files, each represented as an associative array with 'name' and 'content' keys.
     */
    public function generate()
    {
        $files = [];
        $files[] = ['name' => 'requirements.txt', 'content' => $this->generateRequirementsTxt()];
        $files[] = ['name' => '.env', 'content' => $this->generateEnvFile()];
        $files[] = ['name' => '.env.example', 'content' => $this->generateEnvFile()];
        $files[] = ['name' => 'schema.py', 'content' => $this->generateSchemaPy()];

        $allResolvers = [];
        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $files[] = ['name' => "models/".$tableName.".py", 'content' => $this->generateModelFile($tableName, $tableInfo)];
            $resolverContent = $this->generateResolverFile($tableName, $tableInfo);
            $files[] = ['name' => "resolvers/".$tableName.".py", 'content' => $resolverContent['content']];

            if (!isset($allResolvers['query'])) {
                $allResolvers['query'] = [];
            }
            if (!isset($allResolvers['mutation'])) {
                $allResolvers['mutation'] = [];
            }
            $allResolvers['query'] = array_merge($allResolvers['query'], $resolverContent['resolvers']['query']);
            $allResolvers['mutation'] = array_merge($allResolvers['mutation'], $resolverContent['resolvers']['mutation']);
        }

        $files[] = ['name' => 'resolvers/__init__.py', 'content' => $this->generateResolversInitPy($allResolvers)];
        $files[] = ['name' => 'models/__init__.py', 'content' => $this->generateModelsInitPy()];

        return $files;
    }

    /**
     * Generates the content for requirements.txt.
     *
     * @return string The content of requirements.txt.
     */
    private function generateRequirementsTxt()
    {
        return <<<TXT
fastapi
uvicorn[standard]
ariadne
SQLAlchemy[asyncio]
asyncpg # For PostgreSQL
mysql-connector-python # For MySQL
aiosqlite # For SQLite
python-dotenv
python-multipart  # For form data, useful for login
passlib[bcrypt]
python-jose[cryptography]
TXT;
    }

    /**
     * Generates the content for the .env file.
     *
     * @return string The content of the .env file.
     */
    private function generateEnvFile()
    {
        return <<<ENV
# --- Application Configuration Example ---
# Copy this file to .env and fill in your actual configuration.
# The .env file should NOT be committed to version control.

APP_HOST=localhost
APP_PORT=8000

# --- Database Configuration ---
# Choose one: sqlite, mysql, mariadb, postgresql, sqlserver
DB_DRIVER={DB_DRIVER}

# For other databases, configure host, port, etc.
DB_HOST={DB_HOST}
DB_PORT={DB_PORT}
DB_DATABASE={DB_DATABASE}
DB_FILE={DB_FILE}
DB_USERNAME={DB_USERNAME}
DB_PASSWORD={DB_PASSWORD}

# Set to True to log all SQL statements to the console
DB_ECHO={DB_ECHO}

# --- GraphQL Configuration ---
# Set to True to enable Ariadne's debug mode for detailed logging
GRAPHQL_DEBUG=True

# --- CORS Configuration ---
# Comma-separated list of allowed origins for Cross-Origin Resource Sharing
CORS_ALLOWED_ORIGINS=http://localhost:8000,http://127.0.0.1:8000

SECRET_KEY=your-super-secret-key-change-me
ALGORITHM=HS256
ACCESS_TOKEN_EXPIRE_MINUTES=30
ENV;
    }

    /**
     * Generates the content for models/$tableName.py.
     *
     * @param string $tableName The name of the table.
     * @param array $tableInfo Information about the table's columns.
     * @return string The content of the model file.
     */
    private function generateModelFile($tableName, $tableInfo) // NOSONAR
    {
        $pascalName = $this->pascalCase($tableName);

        $imports = "from sqlalchemy import Column, Integer, String, Float, Boolean, DateTime, Date, Text, ForeignKey, func\n";
        $imports .= "from sqlalchemy.orm import relationship, Mapped\n";
        $imports .= "from database import Base\n";

        $fields = "";
        $has_datetime = false;
        $has_date = false;

        foreach ($tableInfo['columns'] as $colName => $colInfo) {
            $sqlalchemyType = $this->mapDbTypeToSqlalchemyType($colInfo['type'], $colInfo['length']);
            $pythonType = $this->mapDbTypeToPythonType($colInfo['type'], $colInfo['length']);
            if ($pythonType == 'datetime') {$has_datetime = true;}
            if ($pythonType == 'date') {$has_date = true;}

            $field_def = "    $colName = Column($sqlalchemyType";
            if ($colInfo['isPrimaryKey']) {
                $field_def .= ", primary_key=True";
            }
            if ($colInfo['isForeignKey']) {
                $refTableName = $colInfo['references'];
                $refTablePk = $this->analyzedSchema[$refTableName]['primaryKey'];
                $field_def .= ", ForeignKey('$refTableName.$refTablePk')";
            }
            $field_def .= ")\n";
            $fields .= $field_def;
        }

        $relationships = "";
        foreach ($tableInfo['columns'] as $colName => $colInfo) {
            if ($colInfo['isForeignKey']) {
                $refTableName = $colInfo['references'];
                $refPascalName = $this->pascalCase($refTableName);
                $relationships .= "    $refTableName = relationship('$refPascalName', back_populates='" . $this->pluralize($tableName) . "')\n";
            }
        }

        // Add back-population for tables that reference this one
        foreach ($this->analyzedSchema as $otherTable => $otherTableInfo) {
            if ($otherTable === $tableName) {continue;}
            foreach ($otherTableInfo['columns'] as $otherColInfo) {
                if ($otherColInfo['isForeignKey'] && $otherColInfo['references'] === $tableName) {
                    $otherPascalName = $this->pascalCase($otherTable);
                    $relationships .= "    " . $this->pluralize($otherTable) . " = relationship('$otherPascalName', back_populates='$tableName')\n";
                }
            }
        }

        if ($has_datetime || $has_date) {
            $imports = "from datetime import date, datetime\n" . $imports;
        }

        return <<<PYTHON
$imports

class $pascalName(Base):
    __tablename__ = '$tableName'

$fields
$relationships
PYTHON;
    }

    /**
     * Generates the content for schema.py.
     *
     * @return string The content of schema.py.
     */
    private function generateSchemaPy() // NOSONAR
    {
        $type_defs = "from ariadne import gql, QueryType, MutationType, make_executable_schema\n";
        $type_defs .= "from resolvers import query_resolvers, mutation_resolvers\n\n"; //NOSONAR

        $gql_schema = "type_defs = gql(\"\"\"\n";
        $gql_schema .= "    scalar Object\n\n";
        $gql_schema .= "    enum SortDirection {\n        ASC\n        DESC\n    }\n\n";
        $gql_schema .= "    enum FilterOperator {\n        EQUALS\n        NOT_EQUALS\n        CONTAINS\n        GREATER_THAN\n        GREATER_THAN_OR_EQUALS\n        LESS_THAN\n        LESS_THAN_OR_EQUALS\n        IN\n        NOT_IN\n    }\n\n";
        $gql_schema .= "    input SortInput {\n        field: String!\n        direction: SortDirection\n    }\n\n";
        $gql_schema .= "    input FilterInput {\n        field: String!\n        value: Object\n        operator: FilterOperator\n    }\n\n";

        $query_fields = "";
        $mutation_fields = "";

        $backendHandledColumns = $this->getBackendHandledColumnNames();

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $pascalName = $this->pascalCase($tableName);
            $camelName = $this->camelCase($tableName);
            $pluralCamelName = $this->pluralize($camelName);

            $pkType = '';

            // Type
            $gql_schema .= "    type $pascalName {\n";
            foreach ($tableInfo['columns'] as $colName => $colInfo) {
                $gqlType = $this->mapDbTypeToPythonGqlType($colInfo['type'], $colInfo['length']);
                $gql_schema .= "        $colName: $gqlType\n";
                if ($colInfo['isForeignKey']) {
                    $refPascalName = $this->pascalCase($colInfo['references']);
                    $gql_schema .= "        {$colInfo['references']}: $refPascalName\n";
                }
                if ($colInfo['isPrimaryKey'] && empty($pkType)) {
                    $pkType = $gqlType;
                }
            }
            if(empty($pkType))
            {
                $pkType = 'String';
            }
            $gql_schema .= "    }\n\n"; // NOSONAR

            // Input Type
            $gql_schema .= "    input {$pascalName}Input {\n";
            foreach ($tableInfo['columns'] as $colName => $colInfo) {
                if ($colName === $tableInfo['primaryKey'] && ($colInfo['isAutoIncrement'] || $colInfo['primaryKeyValue'] == 'autogenerated')) {
                    continue;
                }
                if(in_array($colName, $backendHandledColumns))
                {
                    continue;
                }
                $gqlType = $this->mapDbTypeToPythonGqlType($colInfo['type'], $colInfo['length']);
                $gql_schema .= "        $colName: $gqlType\n";
            }
            $gql_schema .= "    }\n\n";

            // Page Type
            $gql_schema .= "    type {$pascalName}Page {\n";
            $gql_schema .= "        items: [$pascalName]\n";
            $gql_schema .= "        total: Int\n";
            $gql_schema .= "        limit: Int\n";
            $gql_schema .= "        page: Int\n";
            $gql_schema .= "        totalPages: Int\n";
            $gql_schema .= "        hasNext: Boolean\n";
            $gql_schema .= "        hasPrevious: Boolean\n";
            $gql_schema .= "    }\n\n";

            // Queries
            $query_fields .= "    $camelName(id: $pkType!): $pascalName\n";
            $query_fields .= "    $pluralCamelName(limit: Int, offset: Int, page: Int, orderBy: [SortInput], filter: [FilterInput]): {$pascalName}Page\n";

            // Mutations
            $mutation_fields .= "    create$pascalName(input: {$pascalName}Input!): $pascalName\n";
            $mutation_fields .= "    update$pascalName(id: $pkType!, input: {$pascalName}Input!): $pascalName\n";
            $mutation_fields .= "    delete$pascalName(id: $pkType!): Boolean\n";
            if ($tableInfo['hasActiveColumn']) {
                $activeField = $this->camelCase($this->activeField);
                $mutation_fields .= "    toggle{$pascalName}Active(id: $pkType!, {$activeField}: Boolean!): $pascalName\n";
            }
        }

        $gql_schema .= "    type Query {\n$query_fields    }\n\n";
        $gql_schema .= "    type Mutation {\n$mutation_fields    }\n\n";
        $gql_schema .= "\"\"\")\n\n";

        $gql_schema .= "resolvers = [query_resolvers, mutation_resolvers]\n";

        return $type_defs . $gql_schema;
    }

    /**
     * Generates Python code used to update manual primary keys inside an update resolver.
     *
     * This function inspects the table metadata (`$tableInfo`) to identify columns marked
     * as manually managed primary keys (`primaryKeyValue = 'manual-all'`). For each primary
     * key column that requires manual handling, the function generates a Python code snippet
     * that:
     *
     * 1. Checks whether the incoming GraphQL input contains a new primary key value.
     * 2. Executes a direct SQL UPDATE statement to update the primary key in the database.
     * 3. Commits the database transaction to persist the change.
     * 4. Fetches and returns the updated entity using the new primary key value.
     *
     * The generated snippet references both the table name and the ORM model class,
     * enabling it to be used inside an async SQLAlchemy-based GraphQL resolver
     * (e.g., Strawberry, Ariadne).
     *
     * @param string $tableName   The name of the table for which the update logic is generated.
     * @param string $pascalName  The PascalCase ORM model class name associated with the table.
     * @param array  $tableInfo   Metadata describing the table columns, including primary key
     *                            status and manual-update configuration.
     *
     * @return string A Python code snippet that performs a manual primary key update,
     *                or an empty string if no manual-update primary keys exist.
     */
    private function primaryKeyUpdater($tableName, $pascalName, $tableInfo)
    {
        $primaryKeyUpdater = "";
        foreach($tableInfo['columns'] as $colName => $col)
        {
            $inputColumns[] = $colName;
            if($col['isPrimaryKey'] && $col['primaryKeyValue'] == 'manual-all')
            {
                $primaryKeyUpdater .= "    if input.get(\"{$colName}\") and input.get(\"{$colName}\") != id:\n";
                $primaryKeyUpdater .= "        update_sql = \"\"\"\n";
                $primaryKeyUpdater .= "            UPDATE {$tableName} SET {$colName} = :new_pk WHERE {$colName} = :old_pk\n";
                $primaryKeyUpdater .= "        \"\"\"\n\n";
                $primaryKeyUpdater .= "        await db.execute(\n";
                $primaryKeyUpdater .= "            text(update_sql),\n";
                $primaryKeyUpdater .= "            {\"new_pk\": input.get(\"{$colName}\"), \"old_pk\": id}\n";
                $primaryKeyUpdater .= "        )\n";
                $primaryKeyUpdater .= "        await db.commit()\n";
                $primaryKeyUpdater .= "        stmt2 = select({$pascalName}).where({$pascalName}.{$colName} == input.get(\"{$colName}\"))\n";
                $primaryKeyUpdater .= "        result2 = await db.execute(stmt2)\n";
                $primaryKeyUpdater .= "        updated_entity = result2.scalar_one_or_none()\n";
                $primaryKeyUpdater .= "        return updated_entity\n";
            }
        }
        return $primaryKeyUpdater;
    }

    /**
     * Generates the content for resolvers/$tableName.py.
     *
     * @param string $tableName The name of the table.
     * @param array $tableInfo Information about the table's columns.
     * @return array An associative array with 'content' and 'resolvers' keys.
     */
    private function generateResolverFile($tableName, $tableInfo) // NOSONAR
    {
        $pascalName = $this->pascalCase($tableName);
        $camelName = $this->camelCase($tableName);
        $pluralCamelName = $this->pluralize($camelName);
        $pk = $tableInfo['primaryKey'];

        $query_resolver_name = "resolve_" . $pluralCamelName;
        $single_query_resolver_name = "resolve_" . $camelName;
        $create_mutation_name = "resolve_create" . $pascalName;
        $update_mutation_name = "resolve_update" . $pascalName;
        $delete_mutation_name = "resolve_delete" . $pascalName;
        $toggle_active_mutation_name = "resolve_toggle" . $pascalName . "Active";

        $updatePrimaryKey = $this->primaryKeyUpdater($tableName, $pascalName, $tableInfo);

        $content = <<<PYTHON
from datetime import datetime, date

from sqlalchemy import func
from sqlalchemy import text
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload

from database import get_db
from models.$tableName import $pascalName
from utils.i18n import get_translator
from utils.pagination import create_pagination_response
from utils.query_helpers import apply_filters, apply_ordering
from constants.constants import DATETIME_FORMAT

async def $single_query_resolver_name(obj, info, id):
    db = await anext(get_db())
    request = info.context.get("request")
    translator = get_translator(request)
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()
    if entity is None:
        raise Exception(translator("no_item_found_with_id", "$pascalName", id))
    return entity

async def $query_resolver_name(obj, info, limit=20, offset=0, page=None, orderBy=None, filter=None):
    db = await anext(get_db())

    if page is not None:
        offset = (page - 1) * limit

    stmt = select($pascalName)
    count_stmt = select(func.count()).select_from($pascalName)

    # Eagerly load relationships to prevent async errors
    load_options = []

PYTHON;
        $mappingCodeCreate = "";
        $mappingCodeUpdate = "";
        $skippedColumns = array();
        $inputColumns = array();
        foreach ($tableInfo['columns'] as $colName => $colInfo) {
            if ($colInfo['isForeignKey']) {
                $content .= "    load_options.append(selectinload($pascalName.{$colInfo['references']}))\n";
            }
            $inputColumns[] = $colName;
        }

        foreach($this->backendHandledColumns as $key=>$col)
        {

            $colName = $col['columnName'];
            if(in_array($colName, $inputColumns) || in_array($colName, $skippedColumns))
            {

                if($key == 'timeCreate')
                {
                    $mappingCodeCreate .= "    new_entity.$colName = datetime.now().strftime(DATETIME_FORMAT)\n";
                }
                if($key == 'adminCreate')
                {
                    $mappingCodeCreate .= "    new_entity.$colName = request.session['admin_id']\n";
                }
                if($key == 'ipCreate')
                {
                    $mappingCodeCreate .= "    new_entity.$colName = request.client.host\n";
                }

                if($key == 'timeEdit')
                {
                    $mappingCodeCreate .= "    new_entity.$colName = datetime.now().strftime(DATETIME_FORMAT)\n";
                    $mappingCodeUpdate .= "    entity.$colName = datetime.now().strftime(DATETIME_FORMAT)\n";
                }
                if($key == 'adminEdit')
                {
                    $mappingCodeCreate .= "    new_entity.$colName = request.session['admin_id']\n";
                    $mappingCodeUpdate .= "    entity.$colName = request.session['admin_id']\n";
                }
                if($key == 'ipEdit')
                {
                    $mappingCodeCreate .= "    new_entity.$colName = request.client.host\n";
                    $mappingCodeUpdate .= "    entity.$colName = request.client.host\n";
                }

            }
        }



        $content .= <<<PYTHON
    if load_options:
        stmt = stmt.options(*load_options)

    # Apply filtering
    stmt = apply_filters(stmt, $pascalName, filter)
    count_stmt = apply_filters(count_stmt, $pascalName, filter)

    # Apply ordering
    stmt = apply_ordering(stmt, $pascalName, orderBy)

    total = (await db.execute(count_stmt)).scalar_one()

    stmt = stmt.limit(limit).offset(offset)

    result = await db.execute(stmt)
    items = result.scalars().all()

    return {
        "items": items,
        "total": total,
        "limit": limit,
        "page": (offset // limit) + 1,
        "totalPages": (total + limit - 1) // limit,
        "hasNext": (offset + limit) < total,
        "hasPrevious": offset > 0,
    }

async def $create_mutation_name(obj, info, input):
    db = await anext(get_db())
    request = info.context.get("request")

    # Convert datetime/date objects to strings for database compatibility
    for key, value in input.items():
        if isinstance(value, (datetime, date)):
            input[key] = value.strftime('%Y-%m-%d %H:%M:%S') if isinstance(value, datetime) else value.isoformat()

    new_entity = $pascalName(**input)

$mappingCodeCreate

    db.add(new_entity)
    await db.commit()
    await db.refresh(new_entity)
    return new_entity

async def $update_mutation_name(obj, info, id, input):
    db = await anext(get_db())
    request = info.context.get("request")
    translator = get_translator(request)
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()

    if entity is None:
        raise Exception(translator("no_item_found_with_id", "$pascalName", id))

    for key, value in input.items():
        # Convert datetime/date objects to strings for database compatibility
        if isinstance(value, (datetime, date)):
            value = value.strftime('%Y-%m-%d %H:%M:%S') if isinstance(value, datetime) else value.isoformat()
        setattr(entity, key, value)

$mappingCodeUpdate

    await db.commit()
    await db.refresh(entity)
$updatePrimaryKey
    return entity

async def $delete_mutation_name(obj, info, id):
    db = await anext(get_db())
    request = info.context.get("request")
    translator = get_translator(request)
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()

    if entity is None:
        raise Exception(translator("no_item_found_with_id", "$pascalName", id))

    await db.delete(entity)
    await db.commit()
    return True

PYTHON;

        if ($tableInfo['hasActiveColumn']) {
            $activeField = $this->camelCase($this->activeField);
            $backendHandledColumns = $this->getBackendHandledColumnNames();
            $mappingCode = "";

            if (in_array('time_edit', $backendHandledColumns)) {
                $mappingCode .= "    entity.time_edit = datetime.utcnow()\n";
            }
            // Note: admin_edit and ip_edit would require passing context from the request,
            // which is a more advanced topic involving middleware and dependency injection.
            // For now, we'll just update the time.

            $content .= <<<PYTHON
async def $toggle_active_mutation_name(obj, info, id, $activeField):
    db = await anext(get_db())
    request = info.context.get("request")
    translator = get_translator(request)
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()

    if entity is None:
        raise Exception(translator("no_item_found_with_id", "$pascalName", id))

$mappingCodeUpdate

    setattr(entity, '$activeField', $activeField)
$mappingCode
    await db.commit()
    await db.refresh(entity)
    return entity

PYTHON;
        }


        // Add a placeholder for the relationship resolver
        foreach ($tableInfo['columns'] as $colName => $colInfo) {
            if ($colInfo['isForeignKey']) {
                $refTableName = $colInfo['references'];
                $resolver_name = "resolve_" . $refTableName;
                $content .= "\nasync def $resolver_name(obj, info):\n";
                $content .= "    # This is a relationship resolver. The parent 'obj' is an instance of $pascalName.\n";
                $content .= "    # SQLAlchemy's relationship loading should handle this automatically if configured correctly.\n";
                $content .= "    # You can access the related object via obj.$refTableName\n";
                $content .= "    return obj.$refTableName\n";
            }
        }

        $mutations = [
            ['fieldName' => "create$pascalName", 'resolverName' => $create_mutation_name, 'tableName' => $tableName],
            ['fieldName' => "update$pascalName", 'resolverName' => $update_mutation_name, 'tableName' => $tableName],
            ['fieldName' => "delete$pascalName", 'resolverName' => $delete_mutation_name, 'tableName' => $tableName]
        ];

        if ($tableInfo['hasActiveColumn']) {
            $mutations[] = ['fieldName' => "toggle{$pascalName}Active", 'resolverName' => $toggle_active_mutation_name, 'tableName' => $tableName];
        }

        return [
            'content' => $content,
            'resolvers' => [
                'query' => [
                    ['fieldName' => $camelName, 'resolverName' => $single_query_resolver_name, 'tableName' => $tableName],
                    ['fieldName' => $pluralCamelName, 'resolverName' => $query_resolver_name, 'tableName' => $tableName]
                ],
                'mutation' => $mutations
            ]
        ];
    }

    /**
     * Generates the content for resolvers/__init__.py.
     *
     * @param array $allResolvers An associative array of all resolvers.
     * @return string The content of resolvers/__init__.py.
     */
    private function generateResolversInitPy($allResolvers)
    {
        $imports = "from ariadne import QueryType, MutationType, ObjectType\n";
        $query_bindings = "query_resolvers = QueryType()\n";
        $mutation_bindings = "mutation_resolvers = MutationType()\n";

        $imported_resolvers = []; //NOSONAR

        foreach ($allResolvers['query'] as $resolver) {
            $fieldName = $resolver['fieldName'];
            $resolverName = $resolver['resolverName'];
            $tableName = $resolver['tableName'];
            if (!isset($imported_resolvers[$resolverName])) {
                $imports .= "from .$tableName import $resolverName\n";
                $imported_resolvers[$resolverName] = true;
            }
            $query_bindings .= "@query_resolvers.field(\"$fieldName\")\n";
            $query_bindings .= "def bound_q_$resolverName(*args, **kwargs):\n    return $resolverName(*args, **kwargs)\n\n";
        }

        foreach ($allResolvers['mutation'] as $resolver) {
            $fieldName = $resolver['fieldName'];
            $resolverName = $resolver['resolverName'];
            $tableName = $resolver['tableName'];
            if (!isset($imported_resolvers[$resolverName])) {
                $imports .= "from .$tableName import $resolverName\n";
                $imported_resolvers[$resolverName] = true;
            }
            $mutation_bindings .= "@mutation_resolvers.field(\"$fieldName\")\n";
            $mutation_bindings .= "def bound_m_$resolverName(*args, **kwargs):\n    return $resolverName(*args, **kwargs)\n\n";
        }

        return $imports . "\n" . $query_bindings . "\n" . $mutation_bindings;
    }

    /**
     * Generates the content for models/__init__.py.
     *
     * @return string The content of models/__init__.py.
     */
    private function generateModelsInitPy()
    {
        $content = "# This file makes the 'models' directory a Python package.\n\n";
        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $pascalName = $this->pascalCase($tableName);
            $content .= "from .$tableName import $pascalName\n";
        }
        return $content;
    }

    /**
     * Generates the content for manual.md.
     *
     * @return string The content of manual.md.
     */
    public function generateManual()
    {
        $manualContent = "# GraphQL API Manual (Python/FastAPI)\r\n\r\n";
        $manualContent .= "This document provides examples for all available queries and mutations for your Python FastAPI application.

## How to Run the Application

Follow these steps to run the backend server.

### 1. Create a Virtual Environment

It is highly recommended to use a virtual environment to isolate the project's dependencies from other Python projects on your system. This is a best practice to avoid package conflicts.

Open a terminal or command prompt in the `backend` directory and run the following command:

```bash
python -m venv venv
```

### 2. Activate the Virtual Environment

Once created, you need to activate it. The command differs depending on your operating system.

**On Windows:**
```bash
.\\venv\\Scripts\\activate
```

**On macOS/Linux:**
```bash
source venv/bin/activate

```

Once activated, you will see `(venv)` at the beginning of your terminal prompt.

### 3. Install Dependencies

Run the following commands to install all the required libraries. The first command installs packages listed in the `requirements.txt` file, and the subsequent commands ensure that all dependencies needed for login and specific database features are installed.

```bash
pip install -r requirements.txt
pip install ariadne
pip install sqlalchemy
pip install aiomysql
pip install sqlalchemy[asyncio]
pip install itsdangerous
pip install fastapi
pip install python-multipart
pip install \"uvicorn[standard]\"

```

> **Note:** This command will install FastAPI, Uvicorn, Ariadne, SQLAlchemy, and all other libraries needed for the server to run.

## Database Connection

This API requires a database connection. You must configure the `.env` file in the `backend` root directory. This file is used to store sensitive environment variables like database credentials.

Copy or create a new file named `.env` and fill it with your database connection URL. Here are some format examples:

```
# Example for PostgreSQL
# DATABASE_URL=postgresql+asyncpg://user:password@host:port/dbname

# Example for MySQL/MariaDB
# DATABASE_URL=mysql+aiomysql://user:password@host:port/dbname

# Example for SQLite
# DATABASE_URL=sqlite+aiosqlite:///./database.db

DATABASE_URL=postgresql+asyncpg://your_user:your_password@localhost:5432/your_database_name
```

Make sure to replace the placeholders with your actual database credentials.

### 4. Run the Application Server

Once everything is set up, run the development server using Uvicorn. Uvicorn is a lightning-fast ASGI (Asynchronous Server Gateway Interface) server.

```bash
uvicorn main:app --reload

```

- `main:app`: Tells Uvicorn to look for an object named `app` inside the `main.py` file.
- `--reload`: Enables hot-reload mode, which will automatically restart the server whenever you save a code change.

Your server is now running at `http://127.0.0.1:8000`. You can access the GraphiQL interface at `http://127.0.0.1:8000/graphql/` to start sending queries.

---

";

        $manualContent .= $this->generateExample();

        return $manualContent;
    }

}
