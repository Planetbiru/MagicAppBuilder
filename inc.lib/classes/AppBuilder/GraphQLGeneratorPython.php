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
     * Maps database column types to SQLAlchemy column types.
     *
     * @param string $dbType The database column type (e.g., VARCHAR, INT, TIMESTAMP).
     * @return string The corresponding SQLAlchemy type string.
     */
    private function mapDbTypeToSqlalchemyType($dbType, $length = null)
    {
        $dbType = strtolower($dbType);
        if (strpos($dbType, 'varchar') !== false) return "String($length)";
        if (strpos($dbType, 'text') !== false) return "Text";
        if (strpos($dbType, 'timestamp') !== false) return "DateTime";
        if (strpos($dbType, 'date') !== false) return "Date";
        if (strpos($dbType, 'decimal') !== false || strpos($dbType, 'double') !== false) return "Float";
        if (strpos($dbType, 'float') !== false) return "Float";
        if ((strpos($dbType, 'tinyint') !== false && isset($length) && $length == '1') || strpos($dbType, 'bool') !== false || strpos($dbType, 'bit') !== false) return "Boolean";
        if (strpos($dbType, 'int') !== false) return "Integer";
        return "String"; // Default
    }

    /** 
     * Maps database column types to Python native types.
     *
     * @param string $dbType The database column type (e.g., VARCHAR, INT, TIMESTAMP).
     * @return string The corresponding Python type string.
     */
    private function mapDbTypeToPythonType($dbType, $length = null)
    {
        $dbType = strtolower($dbType);
        if (strpos($dbType, 'varchar') !== false || strpos($dbType, 'text') !== false) return "str";
        if (strpos($dbType, 'timestamp') !== false) return "datetime";
        if (strpos($dbType, 'date') !== false) return "date";
        if (strpos($dbType, 'decimal') !== false || strpos($dbType, 'float') !== false || strpos($dbType, 'double') !== false) return "float";
        if ((strpos($dbType, 'tinyint') !== false && isset($length) && $length == '1') || strpos($dbType, 'bool') !== false || strpos($dbType, 'bit') !== false) return "bool";
        if (strpos($dbType, 'int') !== false) return "int";
        return "str"; // Default
    }

    /** 
     * Maps database column types to GraphQL types for the schema.
     *
     * @param string $dbType The database column type (e.g., VARCHAR, INT, TIMESTAMP).
     * @param int|null $length The length of the column.
     * @return string The corresponding GraphQL type string (e.g., 'String', 'Int').
     */
    public function mapDbTypeToPythonGqlType($dbType, $length = null)
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
        $files[] = ['name' => 'main.py', 'content' => $this->generateMainPy()];
        $files[] = ['name' => 'database.py', 'content' => $this->generateDatabasePy()];
        $files[] = ['name' => 'schema.py', 'content' => $this->generateSchemaPy()];

        $allResolvers = [];
        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $pascalName = $this->pascalCase($tableName);
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

APP_HOST=127.0.0.1
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
     * Generates the content for main.py.
     *
     * @return string The content of main.py.
     */
    private function generateMainPy()
    {
        return <<<PYTHON
import os
from contextlib import asynccontextmanager
import uvicorn
from fastapi import FastAPI, Request, Response
from fastapi.staticfiles import StaticFiles
from fastapi.responses import FileResponse
from fastapi.middleware.cors import CORSMiddleware
from ariadne import make_executable_schema, graphql_sync
from ariadne.asgi import GraphQL
from database import engine, Base
from schema import type_defs, resolvers
from dotenv import load_dotenv

load_dotenv()

@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup logic
    async with engine.begin() as conn:
        # Use this to create tables. In production, you might use Alembic migrations.
        # await conn.run_sync(Base.metadata.create_all)
        pass
    # print("Startup complete.")
    yield
    # Shutdown logic
    # print("Shutting down.")

app = FastAPI(lifespan=lifespan)

# CORS configuration
origins = os.getenv("CORS_ALLOWED_ORIGINS", "http://localhost:8000").split(",")

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Determine GraphQL debug mode from environment variable
graphql_debug_mode = os.getenv("GRAPHQL_DEBUG", "False").lower() in ("true", "1", "t", "yes")

executable_schema = make_executable_schema(type_defs, resolvers)
graphql_app = GraphQL(executable_schema, debug=graphql_debug_mode)

# Mount static files directory
app.mount("/assets", StaticFiles(directory="static/assets"), name="assets")
app.mount("/langs", StaticFiles(directory="static/langs"), name="langs")

@app.get("/")
async def read_base():
    return FileResponse('static/index.html')

@app.get("/index.html")
async def read_index():
    return FileResponse('static/index.html')

@app.get("/favicon.ico")
async def read_index():
    return FileResponse('static/favicon.ico')
        
@app.get("/available-language.json")
async def read_index():
    return FileResponse('static/langs/available-language.json')
    
@app.get("/frontend-config.json")
async def read_index():
    return FileResponse('static/config/frontend-config.json')

@app.get("/available-theme.json")
async def get_available_themes():
    themes_path = 'static/assets/themes'
    themes = []
    if os.path.isdir(themes_path):
        for dir_entry in os.scandir(themes_path):
            if dir_entry.is_dir():
                # Check for either style.scss or a compiled style.css
                if os.path.exists(os.path.join(dir_entry.path, 'style.scss')) or os.path.exists(os.path.join(dir_entry.path, 'style.css')):
                    theme_name = dir_entry.name
                    theme_title = theme_name.replace('-', ' ').replace('_', ' ').title()
                    themes.append({'name': theme_name, 'title': theme_title})
    
    response = Response(content=f"[{', '.join([f'{{\"name\": \"{t["name"]}\", \"title\": \"{t["title"]}\"}}' for t in themes])}]", media_type="application/json")
    response.headers["Cache-Control"] = "public, max-age=86400" # Cache for 24 hours
    return response

@app.post("/graphql/")
async def handle_graphql(request: Request):
    return await graphql_app.handle_request(request)

@app.get("/graphql/")
async def handle_graphql_get(request: Request, response: Response):
    return await graphql_app.handle_request(request)

if __name__ == "__main__":
    # Membaca host dan port dari environment variables, dengan nilai default
    app_host = os.getenv("APP_HOST", "127.0.0.1")
    app_port = int(os.getenv("APP_PORT", 8000))
    uvicorn.run("main:app", host=app_host, port=app_port, reload=True)
PYTHON;
    }

    /** 
     * Generates the content for database.py.
     *
     * @return string The content of database.py.
     */
    private function generateDatabasePy()
    {
        return <<<PYTHON
import os
from dotenv import load_dotenv
from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession
from sqlalchemy.orm import sessionmaker, declarative_base

load_dotenv()

# --- Database Connection Settings ---
DB_DRIVER = os.getenv("DB_DRIVER", "sqlite")
DB_HOST = os.getenv("DB_HOST", "localhost")
DB_PORT = os.getenv("DB_PORT")
DB_DATABASE = os.getenv("DB_DATABASE", "database")
DB_FILE = os.getenv("DB_FILE", "database.db")
DB_USERNAME = os.getenv("DB_USERNAME")
DB_PASSWORD = os.getenv("DB_PASSWORD")
DB_ECHO = os.getenv("DB_ECHO", "False").lower() in ("true", "1", "t", "yes")

DATABASE_URL = ""

if DB_DRIVER == "sqlite":
    # For SQLite, DB_DATABASE is the file path. Driver: aiosqlite (built-in with Python 3.8+)
    DATABASE_URL = f"sqlite+aiosqlite:///{DB_FILE}"
elif DB_DRIVER == "mysql":
    # Requires asyncmy: pip install asyncmy
    DATABASE_URL = f"mysql+asyncmy://{DB_USERNAME}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT or 3306}/{DB_DATABASE}"
elif DB_DRIVER == "mariadb":
    # Also uses asyncmy: pip install asyncmy
    DATABASE_URL = f"mysql+asyncmy://{DB_USERNAME}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT or 3306}/{DB_DATABASE}"
elif DB_DRIVER == "postgresql":
    # Requires asyncpg: pip install asyncpg
    DATABASE_URL = f"postgresql+asyncpg://{DB_USERNAME}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT or 5432}/{DB_DATABASE}"
elif DB_DRIVER == "sqlserver":
    # Requires aioodbc and pyodbc: pip install aioodbc pyodbc
    # Also requires the Microsoft ODBC Driver for SQL Server to be installed on the system.
    DATABASE_URL = f"mssql+aioodbc://{DB_USERNAME}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT or 1433}/{DB_DATABASE}?driver=ODBC+Driver+17+for+SQL+Server"

engine = create_async_engine(DATABASE_URL, echo=DB_ECHO)

AsyncSessionLocal = sessionmaker(
    engine, class_=AsyncSession, expire_on_commit=False
)

Base = declarative_base()

async def get_db():
    async with AsyncSessionLocal() as session:
        yield session
PYTHON;
    }

    /** 
     * Generates the content for models/$tableName.py.
     *
     * @param string $tableName The name of the table.
     * @param array $tableInfo Information about the table's columns.
     * @return string The content of the model file.
     */
    private function generateModelFile($tableName, $tableInfo)
    {
        $pascalName = $this->pascalCase($tableName);
        $pk = $tableInfo['primaryKey'];
        $pkType = $this->mapDbTypeToPythonType($tableInfo['columns'][$pk]['type'], $tableInfo['columns'][$pk]['length']);

        $imports = "from sqlalchemy import Column, Integer, String, Float, Boolean, DateTime, Date, Text, ForeignKey, func\n";
        $imports .= "from sqlalchemy.orm import relationship, Mapped\n";
        $imports .= "from database import Base\n";

        $fields = "";
        $has_datetime = false;
        $has_date = false;

        foreach ($tableInfo['columns'] as $colName => $colInfo) {
            $sqlalchemyType = $this->mapDbTypeToSqlalchemyType($colInfo['type'], $colInfo['length']);
            $pythonType = $this->mapDbTypeToPythonType($colInfo['type'], $colInfo['length']);
            if ($pythonType == 'datetime') $has_datetime = true;
            if ($pythonType == 'date') $has_date = true;

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
            if ($otherTable === $tableName) continue;
            foreach ($otherTableInfo['columns'] as $otherCol => $otherColInfo) {
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
    private function generateSchemaPy()
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

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $pascalName = $this->pascalCase($tableName);
            $camelName = $tableName;
            $pluralCamelName = $this->pluralize($tableName);

            // Type
            $gql_schema .= "    type $pascalName {\n";
            foreach ($tableInfo['columns'] as $colName => $colInfo) {
                $gqlType = $this->mapDbTypeToPythonGqlType($colInfo['type'], $colInfo['length']);
                $gql_schema .= "        $colName: $gqlType\n";
                if ($colInfo['isForeignKey']) {
                    $refPascalName = $this->pascalCase($colInfo['references']);
                    $gql_schema .= "        {$colInfo['references']}: $refPascalName\n";
                }
            }
            $gql_schema .= "    }\n\n";

            // Input Type
            $gql_schema .= "    input {$pascalName}Input {\n";
            foreach ($tableInfo['columns'] as $colName => $colInfo) {
                if ($colName === $tableInfo['primaryKey'] && ($colInfo['isAutoIncrement'] || $colInfo['primaryKeyValue'] == 'autogenerated')) {
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
            $query_fields .= "    $camelName(id: ID!): $pascalName\n";
            $query_fields .= "    $pluralCamelName(limit: Int, offset: Int, page: Int, orderBy: [SortInput], filter: [FilterInput]): {$pascalName}Page\n";

            // Mutations
            $mutation_fields .= "    create$pascalName(input: {$pascalName}Input!): $pascalName\n";
            $mutation_fields .= "    update$pascalName(id: ID!, input: {$pascalName}Input!): $pascalName\n";
            $mutation_fields .= "    delete$pascalName(id: ID!): Boolean\n";
            if ($tableInfo['hasActiveColumn']) {
                $activeField = $this->camelCase($this->activeField);
                $mutation_fields .= "    toggle{$pascalName}Active(id: ID!, {$activeField}: Boolean!): $pascalName\n";
            }
        }

        $gql_schema .= "    type Query {\n$query_fields    }\n\n";
        $gql_schema .= "    type Mutation {\n$mutation_fields    }\n\n";
        $gql_schema .= "\"\"\")\n\n";

        $gql_schema .= "resolvers = [query_resolvers, mutation_resolvers]\n";

        return $type_defs . $gql_schema;
    }

    /** 
     * Generates the content for resolvers/$tableName.py.
     *
     * @param string $tableName The name of the table.
     * @param array $tableInfo Information about the table's columns.
     * @return array An associative array with 'content' and 'resolvers' keys.
     */
    private function generateResolverFile($tableName, $tableInfo)
    {
        $pascalName = $this->pascalCase($tableName);
        $camelName = $tableName;
        $pluralCamelName = $this->pluralize($tableName);
        $pk = $tableInfo['primaryKey'];

        $query_resolver_name = "resolve_" . $pluralCamelName;
        $single_query_resolver_name = "resolve_" . $camelName;
        $create_mutation_name = "resolve_create" . $pascalName;
        $update_mutation_name = "resolve_update" . $pascalName;
        $delete_mutation_name = "resolve_delete" . $pascalName;
        $toggle_active_mutation_name = "resolve_toggle" . $pascalName . "Active";

        $content = <<<PYTHON
from sqlalchemy import func
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from models.$tableName import $pascalName
from database import get_db

async def $single_query_resolver_name(obj, info, id):
    db = await anext(get_db())
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()
    if entity is None:
        return None
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
        foreach ($tableInfo['columns'] as $colInfo) {
            if ($colInfo['isForeignKey']) {
                $content .= "    load_options.append(selectinload($pascalName.{$colInfo['references']}))\n";
            }
        }
        $content .= <<<PYTHON
    if load_options:
        stmt = stmt.options(*load_options)

    if filter:
        where_clauses = []
        for f in filter:
            field_name = f.get('field')
            value = f.get('value')
            operator = f.get('operator', 'EQUALS').upper()
            
            column = getattr($pascalName, field_name, None)
            if column is None:
                continue

            if operator == 'EQUALS':
                where_clauses.append(column == value)
            elif operator == 'NOT_EQUALS':
                where_clauses.append(column != value)
            elif operator == 'CONTAINS':
                where_clauses.append(column.like(f"%{value}%"))
            elif operator == 'GREATER_THAN':
                where_clauses.append(column > value)
            elif operator == 'GREATER_THAN_OR_EQUALS':
                where_clauses.append(column >= value)
            elif operator == 'LESS_THAN':
                where_clauses.append(column < value)
            elif operator == 'LESS_THAN_OR_EQUALS':
                where_clauses.append(column <= value)
            # Add more operators as needed (e.g., IN, NOT_IN)

        if where_clauses:
            stmt = stmt.where(*where_clauses)
            count_stmt = count_stmt.where(*where_clauses)

    if orderBy:
        order_clauses = [getattr($pascalName, o['field']).asc() if o.get('direction', 'ASC').upper() == 'ASC' else getattr($pascalName, o['field']).desc() for o in orderBy]
        stmt = stmt.order_by(*order_clauses)

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
    new_entity = $pascalName(**input)
    db.add(new_entity)
    await db.commit()
    await db.refresh(new_entity)
    return new_entity

async def $update_mutation_name(obj, info, id, input):
    db = await anext(get_db())
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()

    if entity is None:
        raise Exception(f"$pascalName with id {id} not found")

    for key, value in input.items():
        setattr(entity, key, value)
    
    await db.commit()
    await db.refresh(entity)
    return entity

async def $delete_mutation_name(obj, info, id):
    db = await anext(get_db())
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()

    if entity is None:
        return False

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
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()

    if entity is None:
        raise Exception(f"$pascalName with id {id} not found")

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
                $refPascalName = $this->pascalCase($refTableName);
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
     * Generates the content for models/__init__.py.
     *
     * @return string The content of models/__init__.py.
     */
    private function generateAuthPy()
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
        $manualContent .= "This document provides examples for all available queries and mutations for your Python FastAPI application.\r\n\r\n";
        
        $manualContent .= "## How to Run the Application\n\n";
        $manualContent .= "Follow these steps to run the backend server.\n\n";

        $manualContent .= "### 1. Create a Virtual Environment\n\n";
        $manualContent .= "It is highly recommended to use a virtual environment to isolate the project's dependencies from other Python projects on your system. This is a best practice to avoid package conflicts.\n\n";
        $manualContent .= "Open a terminal or command prompt in the `backend` directory and run the following command:\n\n";
        $manualContent .= "```bash\n";
        $manualContent .= "python -m venv venv\n";
        $manualContent .= "```\n\n";

        $manualContent .= "### 2. Activate the Virtual Environment\n\n";
        $manualContent .= "Once created, you need to activate it. The command differs depending on your operating system.\n\n";
        $manualContent .= "**On Windows:**\n";
        $manualContent .= "```bash\n";
        $manualContent .= ".\\venv\\Scripts\\activate\n";
        $manualContent .= "```\n\n";
        $manualContent .= "**On macOS/Linux:**\n";
        $manualContent .= "```bash\n";
        $manualContent .= "source venv/bin/activate\n";
        $manualContent .= "```\n\n";
        $manualContent .= "Once activated, you will see `(venv)` at the beginning of your terminal prompt.\n\n";

        $manualContent .= "### 3. Install Dependencies\n\n";
        $manualContent .= "All Python packages required by this application are listed in the `requirements.txt` file. Install them all with a single command:\n\n";
        $manualContent .= "```bash\n";
        $manualContent .= "pip install -r requirements.txt\n";
        $manualContent .= "```\n\n";
        $manualContent .= "> **Note:** This command will install FastAPI, Uvicorn, Ariadne, SQLAlchemy, and all other libraries needed for the server to run.\n\n";

        $manualContent .= "## Database Connection\r\n\r\n";
        $manualContent .= "This API requires a database connection. You must configure the `.env` file in the `backend` root directory. This file is used to store sensitive environment variables like database credentials.\n\n";
        $manualContent .= "Copy or create a new file named `.env` and fill it with your database connection URL. Here are some format examples:\n\n";
        $manualContent .= "```\r\n";
        $manualContent .= "# Example for PostgreSQL\n";
        $manualContent .= "# DATABASE_URL=postgresql+asyncpg://user:password@host:port/dbname\n\n";
        $manualContent .= "# Example for MySQL/MariaDB\n";
        $manualContent .= "# DATABASE_URL=mysql+aiomysql://user:password@host:port/dbname\n\n";
        $manualContent .= "# Example for SQLite\n";
        $manualContent .= "# DATABASE_URL=sqlite+aiosqlite:///./database.db\n\n";
        $manualContent .= "DATABASE_URL=postgresql+asyncpg://your_user:your_password@localhost:5432/your_database_name\n";
        $manualContent .= "```\r\n\r\n";
        $manualContent .= "Make sure to replace the placeholders with your actual database credentials.\n\n";

        $manualContent .= "### 4. Run the Application Server\n\n";
        $manualContent .= "Once everything is set up, run the development server using Uvicorn. Uvicorn is a lightning-fast ASGI (Asynchronous Server Gateway Interface) server.\n\n";
        $manualContent .= "```bash\n";
        $manualContent .= "uvicorn main:app --reload\n";
        $manualContent .= "```\n\n";
        $manualContent .= "- `main:app`: Tells Uvicorn to look for an object named `app` inside the `main.py` file.\n";
        $manualContent .= "- `--reload`: Enables hot-reload mode, which will automatically restart the server whenever you save a code change.\n\n";
        $manualContent .= "Your server is now running at `http://127.0.0.1:8000`. You can access the GraphiQL interface at `http://127.0.0.1:8000/graphql/` to start sending queries.\n\n";

        $manualContent .= "---\r\n\r\n";

        foreach ($this->analyzedSchema as $tableName => $tableInfo) {
            $camelName = $this->camelCase($tableName);
            $pluralCamelName = $this->pluralize($camelName);
            $ucCamelName = ucfirst($camelName);

            $manualContent .= "## " . $ucCamelName . "\r\n\r\n";

            // --- Get Fields for examples ---
            $fieldsString = $this->getFieldsForManual($tableInfo, false);
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

            $manualContent .= "```graphql\r\n";
            $manualContent .= "query Get" . ucfirst($pluralCamelName) . " {\r\n";
            $manualContent .= "  " . $pluralCamelName . "(\r\n    limit: 10, \r\n    offset: 0, \r\n    orderBy: [{field: \"" . $tableInfo['primaryKey'] . "\", direction: DESC}],\r\n    filter: [{field: \"name\", value: \"some-text\", operator: CONTAINS}]\r\n  ) {\r\n";
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
    
}
