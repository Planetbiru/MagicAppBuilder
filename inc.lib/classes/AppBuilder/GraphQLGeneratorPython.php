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

        $files[] = ['name' => 'routers/admin.py', 'content' => $this->generateAdminController()];
        $files[] = ['name' => 'routers/profile.py', 'content' => $this->generateProfileController()];
        $files[] = ['name' => 'routers/auth.py', 'content' => $this->generateAuthController()];
        $files[] = ['name' => 'routers/message.py', 'content' => $this->generateMessageController()];
        $files[] = ['name' => 'routers/notification.py', 'content' => $this->generateNotificationController()];


        $files[] = ['name' => 'utils/query_helpers.py', 'content' => $this->generateQueryHelpersPy()];
        $files[] = ['name' => 'utils/pagination.py', 'content' => $this->generatePagination()];
        $files[] = ['name' => 'constants/constants.py', 'content' => $this->generateConstants()];
        $files[] = ['name' => 'models/core/admin.py', 'content' => $this->generateAdmin()];

        $files[] = ['name' => 'models/core/admin_level.py', 'content' => $this->generateAdminLevel()];
        $files[] = ['name' => 'models/core/message_folder.py', 'content' => $this->generateMessageFolder()];
        $files[] = ['name' => 'models/core/message.py', 'content' => $this->generateMessage()];
        $files[] = ['name' => 'models/core/notification.py', 'content' => $this->generateNotification()];


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
from fastapi import FastAPI, Request, Response, Depends
from fastapi.responses import FileResponse
from fastapi.middleware.cors import CORSMiddleware
from starlette.middleware.sessions import SessionMiddleware
from ariadne import make_executable_schema
from ariadne.asgi import GraphQL
from database import engine, Base


from schema import type_defs, resolvers
from routers import profile, admin, auth, message, notification
from routers.auth import login_required
import html
from fastapi.staticfiles import StaticFiles
from dotenv import load_dotenv

load_dotenv()

@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup logic
    async with engine.begin() as conn:
        # Create all tables registered in Base (including Admin)
        await conn.run_sync(Base.metadata.create_all)
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

# Session Middleware to handle sessions (replaces Flask's session)
# Make sure you set SECRET_KEY in your .env file
app.add_middleware(
    SessionMiddleware, secret_key=os.getenv("SECRET_KEY", "a_very_secret_key")
)

# Determine GraphQL debug mode from environment variable
graphql_debug_mode = os.getenv("GRAPHQL_DEBUG", "False").lower() in ("true", "1", "t", "yes")

executable_schema = make_executable_schema(type_defs, resolvers)
graphql_app = GraphQL(executable_schema, debug=graphql_debug_mode)

# Mount static files directory
app.mount("/assets", StaticFiles(directory="static/assets"), name="assets")
app.mount("/langs", StaticFiles(directory="static/langs"), name="langs")

# --- Include Routers ---
app.include_router(profile.router)
app.include_router(admin.router)
app.include_router(auth.router)
app.include_router(message.router)
app.include_router(notification.router)


@app.get("/frontend-config.json", dependencies=[Depends(login_required)])
async def read_frontend_config(request: Request):
    # Example config data, similar to what's in app.py
    response = FileResponse('static/config/frontend-config.json')
    # Prevent the browser from caching this response
    response.headers["Cache-Control"] = "no-cache, no-store, must-revalidate"
    response.headers["Pragma"] = "no-cache"
    response.headers["Expires"] = "0"
    return response

# --- Endpoint Statis dan Lainnya ---
# Endpoint /frontend-config.json yang lama (statik) kita hapus/nonaktifkan
# because it has been replaced by the dynamic one above.

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

# Protect the GraphQL endpoint
@app.post("/graphql/", dependencies=[Depends(login_required)])
async def handle_graphql(request: Request, username: str = Depends(login_required)):
    return await graphql_app.handle_request(request)

@app.get("/graphql/", dependencies=[Depends(login_required)])
async def handle_graphql_get(request: Request, response: Response, username: str = Depends(login_required)):
    return await graphql_app.handle_request(request)

if __name__ == "__main__":
    # Read host and port from environment variables, with default values
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

def _get_database_url() -> str:
    """Constructs the database URL from environment variables."""
    if DB_DRIVER == "sqlite":
        # For SQLite, DB_DATABASE is the file path. Driver: aiosqlite
        return f"sqlite+aiosqlite:///{DB_FILE}"
    
    # Ensure username and password are not None for other drivers
    username = DB_USERNAME or ""
    password = DB_PASSWORD or ""

    if DB_DRIVER in ("mysql", "mariadb"):
        # Requires aiomysql: pip install aiomysql
        port = DB_PORT or 3306
        return f"mysql+aiomysql://{username}:{password}@{DB_HOST}:{port}/{DB_DATABASE}"
    
    if DB_DRIVER == "postgresql":
        # Requires asyncpg: pip install asyncpg
        port = DB_PORT or 5432
        return f"postgresql+asyncpg://{username}:{password}@{DB_HOST}:{port}/{DB_DATABASE}"
    
    if DB_DRIVER == "sqlserver":
        # Requires aioodbc and pyodbc. Also requires the Microsoft ODBC Driver for SQL Server.
        port = DB_PORT or 1433
        odbc_driver = os.getenv("DB_ODBC_DRIVER", "ODBC Driver 17 for SQL Server").replace(" ", "+")
        return f"mssql+aioodbc://{username}:{password}@{DB_HOST}:{port}/{DB_DATABASE}?driver={odbc_driver}"

    raise ValueError(f"Unsupported DB_DRIVER: {DB_DRIVER}. Supported drivers are: sqlite, mysql, mariadb, postgresql, sqlserver.")

DATABASE_URL = _get_database_url()
engine = create_async_engine(DATABASE_URL, echo=DB_ECHO)

AsyncSessionLocal = sessionmaker(
    engine, class_=AsyncSession, expire_on_commit=False
)

# Define Base first so that models can import it without a circular dependency.
Base = declarative_base()

async def get_db():
    async with AsyncSessionLocal() as session:
        yield session
PYTHON;
    }

    /**
     * Generates the Python code for the admin management controller (router).
     * This controller provides FastAPI endpoints for CRUD operations on the Admin model,
     * including creating, reading, updating, and deleting admin users. It also handles
     * actions like toggling the active status and changing passwords for other admins.
     * The endpoints render HTML fragments for a dynamic, single-page-like interface.
     *
     * @return string The generated Python code for the admin router.
     */
    private function generateAdminController()
    {
        return <<<PYTHON
import hashlib
import html
import math
import uuid
from datetime import datetime
from typing import Optional

from fastapi import APIRouter, Depends, Form, HTTPException, Request, status
from fastapi.responses import HTMLResponse, JSONResponse
from sqlalchemy import func
from sqlalchemy.future import select
from models.core.admin import Admin

from database import get_db

router = APIRouter(prefix="/admin", tags=["Admin Management"])

# Helper to get current admin_id from session
def get_current_admin_id(request: Request):
    return request.session.get("admin_id")

# --- POST Actions Handler ---

@router.post("/")
async def handle_admin_actions(
    request: Request,
    db=Depends(get_db),
    current_admin_id: str = Depends(get_current_admin_id),
    action: str = Form(...),
    adminId: Optional[str] = Form(None),
):
    # This single endpoint mimics the PHP script's POST handling.
    try:
        if action == "create":
            form = await request.form()
            password = form.get("password")
            if not password:
                raise HTTPException(status_code=400, detail="Password is required.")
            
            new_admin = Admin(
                admin_id=str(uuid.uuid4()),
                name=form.get("name"),
                username=form.get("username"),
                email=form.get("email"),
                password=hashlib.sha1(hashlib.sha1(password.encode('utf-8')).hexdigest().encode('utf-8')).hexdigest(),
                admin_level_id=form.get("admin_level_id"),
                active=form.get("active") == "on",
                time_create=datetime.now(),
                admin_create=current_admin_id,
                ip_create=request.client.host
            )
            db.add(new_admin)
            await db.commit()
            return JSONResponse({"success": True, "message": "Admin created successfully."})

        elif action == "update":
            if not adminId:
                raise HTTPException(status_code=400, detail="Admin ID is required.")
            
            form = await request.form()
            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin_to_update = (await db.execute(stmt)).scalar_one_or_none()
            if not admin_to_update:
                raise HTTPException(status_code=404, detail="Admin not found.")

            admin_to_update.name = form.get("name")
            admin_to_update.username = form.get("username")
            admin_to_update.email = form.get("email")
            admin_to_update.time_edit = datetime.now()
            admin_to_update.admin_edit = current_admin_id
            admin_to_update.ip_edit = request.client.host

            # Prevent self-update of level and active status
            if adminId == current_admin_id:
                admin_to_update.active = True
            else:
                admin_to_update.admin_level_id = form.get("admin_level_id")
                admin_to_update.active = form.get("active") == "on"

            await db.commit()
            return JSONResponse({"success": True, "message": "Admin updated successfully."})

        elif action == "toggle_active":
            if not adminId:
                raise HTTPException(status_code=400, detail="Admin ID is required.")
            if adminId == current_admin_id:
                raise HTTPException(status_code=403, detail="Cannot deactivate yourself.")

            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin_to_toggle = (await db.execute(stmt)).scalar_one_or_none()
            if not admin_to_toggle:
                raise HTTPException(status_code=404, detail="Admin not found.")
            
            admin_to_toggle.active = not admin_to_toggle.active
            await db.commit()
            return JSONResponse({"success": True, "message": "Admin status updated."})

        elif action == "change_password":
            if not adminId:
                raise HTTPException(status_code=400, detail="Admin ID is required.")
            
            form = await request.form()
            password = form.get("password")
            if not password:
                raise HTTPException(status_code=400, detail="Password is required.")

            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin_to_update = (await db.execute(stmt)).scalar_one_or_none()
            if not admin_to_update:
                raise HTTPException(status_code=404, detail="Admin not found.")

            admin_to_update.password = hashlib.sha1(hashlib.sha1(password.encode('utf-8')).hexdigest().encode('utf-8')).hexdigest()
            await db.commit()
            return JSONResponse({"success": True, "message": "Password updated successfully."})

        elif action == "delete":
            if not adminId:
                raise HTTPException(status_code=400, detail="Admin ID is required.")
            if adminId == current_admin_id:
                raise HTTPException(status_code=403, detail="Cannot delete yourself.")

            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin_to_delete = (await db.execute(stmt)).scalar_one_or_none()
            if not admin_to_delete:
                raise HTTPException(status_code=404, detail="Admin not found.")

            await db.delete(admin_to_delete)
            await db.commit()
            return JSONResponse({"success": True, "message": "Admin deleted successfully."})

        else:
            raise HTTPException(status_code=400, detail="Invalid action.")

    except Exception as e:
        # Catch potential database errors or other exceptions
        detail = str(e.detail) if isinstance(e, HTTPException) else str(e)
        return JSONResponse(status_code=500, content={"success": False, "message": detail})


# --- GET Views Handler ---

@router.get("/", response_class=HTMLResponse)
async def get_admin_views(
    request: Request,
    db=Depends(get_db),
    current_admin_id: str = Depends(get_current_admin_id),
    view: Optional[str] = None,
    adminId: Optional[str] = None,
    search: Optional[str] = None,
    page: int = 1,
):
    # This single endpoint renders different HTML views based on query params.
    def h(text):
        return html.escape(str(text)) if text is not None else ""

    # In a real app, this would query the admin_level table.
    # For now, we'll mock it.
    async def get_admin_levels(db_session):
        # Mock data, replace with actual query:
        # result = await db_session.execute(select(AdminLevel).where(AdminLevel.active == True).order_by(AdminLevel.sort_order))
        # return result.scalars().all()
        return [
            {"admin_level_id": "1", "name": "Super Admin"},
            {"admin_level_id": "2", "name": "Regular Admin"}
        ]

    if view == "create" or (view == "edit" and adminId):
        admin = None
        if view == "edit":
            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin = (await db.execute(stmt)).scalar_one_or_none()
        
        admin_levels = await get_admin_levels(db)
        
        form_title = "Add New Admin" if view == "create" else "Edit Admin"
        form_action = "create" if view == "create" else "update"
        
        # Build admin level options
        level_options = '<option value="">Select Option</option>'
        for level in admin_levels:
            selected = 'selected' if admin and str(admin.admin_level_id) == str(level['admin_level_id']) else ''
            level_options += f'<option value="{h(level["admin_level_id"])}" {selected}>{h(level["name"])}</option>'

        password_field = ""
        if view == "create":
            password_field = '<tr><td>Password</td><td><input type="password" name="password" required autocomplete="new-password"></td></tr>'

        checked = 'checked' if (view == 'create' or (admin and admin.active)) else ''

        return HTMLResponse(f"""
            <div class="back-controls"><a href="#admin" class="btn btn-secondary">Back to List</a></div>
            <div class="table-container detail-view">
                <h3>{form_title}</h3>
                <form id="admin-form" class="form-group" onsubmit="handleAdminSave(event, '{h(adminId)}', '{form_action}'); return false;">
                    <input type="hidden" name="action" value="{form_action}">
                    {'<input type="hidden" name="adminId" value="' + h(adminId) + '">' if adminId else ''}
                    <table class="table table-borderless">
                        <tr><td>Name</td><td><input type="text" name="name" value="{h(admin.name if admin else '')}" required autocomplete="off"></td></tr>
                        <tr><td>Username</td><td><input type="text" name="username" value="{h(admin.username if admin else '')}" required autocomplete="off"></td></tr>
                        <tr><td>Email</td><td><input type="email" name="email" value="{h(admin.email if admin else '')}" required autocomplete="off"></td></tr>
                        {password_field}
                        <tr><td>Admin Level</td><td><select name="admin_level_id" required>{level_options}</select></td></tr>
                        <tr><td>Active</td><td><input type="checkbox" name="active" {checked}></td></tr>
                        <tr><td></td><td>
                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="#admin" class="btn btn-secondary">Cancel</a>
                        </td></tr>
                    </table>
                </form>
            </div>
        """)

    elif view == "change-password" and adminId:
        return HTMLResponse(f"""
            <div class="back-controls"><a href="#admin?view=detail&adminId={h(adminId)}" class="btn btn-secondary">Back to Detail</a></div>
            <div class="table-container detail-view">
                <h3>Change Password</h3>
                <form id="change-password-form" class="form-group" onsubmit="handleAdminChangePassword(event, '{h(adminId)}'); return false;">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="adminId" value="{h(adminId)}">
                    <table class="table table-borderless">
                        <tr><td>New Password</td><td><input type="password" name="password" required autocomplete="new-password"></td></tr>
                        <tr><td></td><td>
                            <button type="submit" class="btn btn-success">Update</button>
                            <a href="#admin?view=detail&adminId={h(adminId)}" class="btn btn-secondary">Cancel</a>
                        </td></tr>
                    </table>
                </form>
            </div>
        """)

    elif adminId: # Detail View
        # In a real app, you would join with admin_level table.
        stmt = select(Admin).where(Admin.admin_id == adminId)
        admin = (await db.execute(stmt)).scalar_one_or_none()
        if not admin:
            return HTMLResponse("<p>Admin not found</p>")

        buttons = f'<a href="#admin?view=edit&adminId={h(adminId)}" class="btn btn-primary">Edit</a>'
        if admin.admin_id != current_admin_id:
            toggle_text = 'Deactivate' if admin.active else 'Activate'
            toggle_class = 'btn-warning' if admin.active else 'btn-success'
            buttons += f'''
                <a href="#admin?view=change-password&adminId={h(adminId)}" class="btn btn-warning">Change Password</a>
                <button class="btn {toggle_class}" onclick="handleAdminToggleActive('{h(adminId)}')">{toggle_text}</button>
                <button class="btn btn-danger" onclick="handleAdminDelete('{h(adminId)}')">Delete</button>
            '''

        return HTMLResponse(f"""
            <div class="back-controls">
                <a href="#admin" class="btn btn-secondary">Back to List</a>
                {buttons}
            </div>
            <div class="table-container detail-view">
                <table class="table"><tbody>
                    <tr><td><strong>Admin ID</strong></td><td>{h(admin.admin_id)}</td></tr>
                    <tr><td><strong>Name</strong></td><td>{h(admin.name)}</td></tr>
                    <tr><td><strong>Username</strong></td><td>{h(admin.username)}</td></tr>
                    <tr><td><strong>Email</strong></td><td>{h(admin.email)}</td></tr>
                    <tr><td><strong>Admin Level</strong></td><td>{h(admin.admin_level_id)}</td></tr>
                    <tr><td><strong>Status</strong></td><td>{'Active' if admin.active else 'Inactive'}</td></tr>
                    <tr><td><strong>Time Create</strong></td><td>{h(admin.time_create)}</td></tr>
                    <tr><td><strong>Time Edit</strong></td><td>{h(admin.time_edit)}</td></tr>
                </tbody></table>
            </div>
        """)

    else: # List View
        page_size = 20 # from config
        offset = (page - 1) * page_size

        count_stmt = select(func.count()).select_from(Admin)
        stmt = select(Admin)

        if search:
            search_term = f"%{search}%"
            count_stmt = count_stmt.where(Admin.name.ilike(search_term) | Admin.username.ilike(search_term))
            stmt = stmt.where(Admin.name.ilike(search_term) | Admin.username.ilike(search_term))

        total_admins = (await db.execute(count_stmt)).scalar_one()
        total_pages = math.ceil(total_admins / page_size)

        stmt = stmt.order_by(Admin.name).limit(page_size).offset(offset)
        admins = (await db.execute(stmt)).scalars().all()

        rows = ""
        if admins:
            for admin in admins:
                actions = f'<a href="#admin?view=detail&adminId={h(admin.admin_id)}" class="btn btn-sm btn-info">View</a>'
                if admin.admin_id != current_admin_id:
                    toggle_text = 'Deactivate' if admin.active else 'Activate'
                    toggle_class = 'btn-warning' if admin.active else 'btn-success'
                    actions += f'''
                        <a href="#admin?view=edit&adminId={h(admin.admin_id)}" class="btn btn-sm btn-primary">Edit</a>
                        <button class="btn btn-sm {toggle_class}" onclick="handleAdminToggleActive('{h(admin.admin_id)}')">{toggle_text}</button>
                        <button class="btn btn-sm btn-danger" onclick="handleAdminDelete('{h(admin.admin_id)}')">Delete</button>
                    '''
                rows += f"""
                    <tr class="{'' if admin.active else 'inactive'}">
                        <td>{h(admin.name)}</td>
                        <td>{h(admin.username)}</td>
                        <td>{h(admin.email)}</td>
                        <td>{h(admin.admin_level_id)}</td>
                        <td>{'Active' if admin.active else 'Inactive'}</td>
                        <td class="actions">{actions}</td>
                    </tr>
                """
        else:
            rows = '<tr><td colspan="6">No admins found.</td></tr>'

        # Basic pagination for demonstration
        pagination_html = f"Page {page} of {total_pages} ({total_admins} total)"

        return HTMLResponse(f"""
            <div id="filter-container" class="filter-container" style="display: block;">
                <form id="admin-search-form" class="search-form" onsubmit="handleAdminSearch(event)"> 
                    <div class="filter-controls">
                        <div class="form-group">
                            <label for="search_term">Name/Username</label>
                            <input type="text" name="search" id="search_term" placeholder="Name or Username" value="{h(search)}">
                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="#admin?view=create" class="btn btn-primary">Add New Admin</a>
                    </div>
                </form>
            </div>
            <div class="table-container">
                <table class="table table-striped">
                    <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Admin Level</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>{rows}</tbody>
                </table>
            </div>
            <div class="pagination-container">{pagination_html}</div>
        """)
PYTHON;
    }

    /**
     * Generates the Python code for the authentication controller (router).
     * This includes the `/login` and `/logout` endpoints. It also defines the
     * `login_required` dependency, which can be used to protect other endpoints,
     * ensuring that only authenticated users can access them.
     *
     * @return string The generated Python code for the auth router.
     */
    private function generateAuthController()
    {
        return <<<PYTHON
import hashlib

from fastapi import APIRouter, Request, Depends, HTTPException, status, Form

from models.core.admin import Admin
from database import get_db
from sqlalchemy.future import select


async def login_required(request: Request):
    if "username" not in request.session:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="You must be logged in to access this resource.",
            headers={"WWW-Authenticate": "Bearer"},
        )
    return request.session['username']


# --- Login, Logout, and Protected Endpoints ---
router = APIRouter()
@router.post("/login")
async def login(
    username: str = Form(...),
    password: str = Form(...),
    db = Depends(get_db),
    request: Request = None
):
    stmt = select(Admin).where(Admin.username == username)
    result = await db.execute(stmt)
    admin = result.scalar_one_or_none()

    if not admin:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid username or password.")

    # Encrypt the input password with sha1(sha1(password))
    hashed_password_input = hashlib.sha1(hashlib.sha1(password.encode('utf-8')).hexdigest().encode('utf-8')).hexdigest()

    if admin.password == hashed_password_input:
        # Store user information in the session
        request.session['username'] = admin.username
        request.session['admin_id'] = admin.admin_id
        request.session['admin_level_id'] = admin.admin_level_id
        request.session['password_v1'] = hashlib.sha1(password.encode('utf-8')).hexdigest()
        return {"success": True, "message": "Login successful."}
    else:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid username or password.")

@router.post("/logout")
async def logout1(request: Request, username: str = Depends(login_required)):
    request.session.clear()
    return {"success": True, "message": "You have been successfully logged out."}

@router.get("/logout")
async def logout2(request: Request, username: str = Depends(login_required)):
    request.session.clear()
    return {"success": True, "message": "You have been successfully logged out."}
PYTHON;
    }

    /**
     * Generates the Python code for the message controller (router).
     * This controller manages endpoints related to user-to-user messaging.
     * It provides functionality to list messages (inbox), view a single message detail,
     * and perform actions such as deleting a message or marking it as unread.
     *
     * @return string The generated Python code for the message router.
     */
    private function generateMessageController()
    {
        return <<<PYTHON
import html
from datetime import datetime
from typing import Optional
from math import ceil

from fastapi import APIRouter, Depends, Form, HTTPException, Request, status
from fastapi.responses import HTMLResponse, JSONResponse
from sqlalchemy import func, or_, and_
from sqlalchemy.future import select
from sqlalchemy.orm import aliased

from database import get_db
from models.core.admin import Admin
from models.core.message import Message
from routers.auth import login_required

router = APIRouter()


def h(text):
    """Helper to escape HTML and handle None values."""
    return html.escape(str(text)) if text is not None else ""


@router.post("/message")
async def handle_message_action(
    request: Request,
    username: str = Depends(login_required),
    db=Depends(get_db),
):
    """Handles POST actions for messages, such as 'mark_as_unread' or 'delete'."""
    form_data = await request.form()
    action = form_data.get("action")
    message_id = form_data.get("messageId")

    if not message_id:
        raise HTTPException(status_code=400, detail="Message ID is required.")

    # Get the current admin
    admin_stmt = select(Admin).where(Admin.username == username)
    current_admin = (await db.execute(admin_stmt)).scalar_one_or_none()
    if not current_admin:
        raise HTTPException(status_code=404, detail="User not found.")

    # Get the message
    message_stmt = select(Message).where(Message.message_id == message_id)
    message = (await db.execute(message_stmt)).scalar_one_or_none()
    if not message:
        raise HTTPException(status_code=404, detail="Message not found.")

    if action == "mark_as_unread":
        if message.receiver_id != current_admin.admin_id:
            raise HTTPException(status_code=403, detail="Forbidden")
        message.is_read = False
        message.time_read = None
        await db.commit()
        return JSONResponse(
            {"success": True, "message": "Message marked as unread."}
        )

    elif action == "delete":
        if (
            message.sender_id != current_admin.admin_id
            and message.receiver_id != current_admin.admin_id
        ):
            raise HTTPException(status_code=403, detail="Forbidden")
        await db.delete(message)
        await db.commit()
        return JSONResponse(
            {"success": True, "message": "Message deleted successfully."}
        )

    else:
        raise HTTPException(status_code=400, detail="Invalid action.")


@router.get("/message", response_class=HTMLResponse)
async def get_messages(
    request: Request,
    username: str = Depends(login_required),
    db=Depends(get_db),
    messageId: Optional[str] = None,
    page: int = 1,
    search: Optional[str] = None,
):
    """Retrieves a list of messages or a single message detail."""
    admin_stmt = select(Admin).where(Admin.username == username)
    current_admin = (await db.execute(admin_stmt)).scalar_one_or_none()
    if not current_admin:
        raise HTTPException(status_code=404, detail="User not found.")

    current_admin_id = current_admin.admin_id

    if messageId:
        # --- Message Detail View ---
        Sender = aliased(Admin)
        Receiver = aliased(Admin)
        stmt = (
            select(Message, Sender.name, Receiver.name)
            .outerjoin(Sender, Message.sender_id == Sender.admin_id)
            .outerjoin(Receiver, Message.receiver_id == Receiver.admin_id)
            .where(Message.message_id == messageId)
            .where(
                or_(
                    Message.sender_id == current_admin_id,
                    Message.receiver_id == current_admin_id,
                )
            )
        )
        result = (await db.execute(stmt)).first()

        if not result:
            return HTMLResponse('<div class="table-container detail-view">No message found.</div>')

        message, sender_name, receiver_name = result

        # Mark as read
        if message.receiver_id == current_admin_id and not message.is_read:
            message.is_read = True
            message.time_read = datetime.now()
            await db.commit()
            await db.refresh(message)

        status_display = (
            f'<span class="status-read">Read at {h(message.time_read.strftime("%Y-%m-%d %H:%M:%S"))}</span>'
            if message.is_read
            else '<span class="status-unread">Unread</span>'
        )

        buttons_html = ""
        if message.receiver_id == current_admin_id and message.is_read:
            buttons_html = f"""
            <button class="btn btn-primary" onclick="markMessageAsUnread('{message.message_id}', 'detail')">Mark as Unread</button>
            <button class="btn btn-danger" onclick="handleMessageDelete('{message.message_id}')">Delete</button>
            """

        html_content = f"""
        <div class="back-controls">
            <button id="back-to-list" class="btn btn-secondary" onclick="backToList('message')">Back to List</button>
            {buttons_html}
        </div>
        <div class="message-container">
            <div class="message-header">
                <h3>{h(message.subject)}</h3>
                <div class="message-meta">
                    <div><strong>From:</strong> {h(sender_name or 'System')}</div>
                    <div><strong>To:</strong> {h(receiver_name or 'System')}</div>
                    <div><strong>Time:</strong> {h(message.time_create.strftime('%Y-%m-%d %H:%M:%S'))}</div>
                    <div><strong>Status:</strong> {status_display}</div>
                </div>
            </div>
            <div class="message-body">
                {h(message.content).replace(chr(10), '<br>')}
            </div>
        </div>
        """
        return HTMLResponse(content=html_content)

    else:
        # --- Message List View ---
        PAGE_SIZE = 20
        offset = (page - 1) * PAGE_SIZE

        Sender = aliased(Admin)
        Receiver = aliased(Admin)

        base_query = (
            select(Message, Sender.name, Receiver.name)
            .outerjoin(Sender, Message.sender_id == Sender.admin_id)
            .outerjoin(Receiver, Message.receiver_id == Receiver.admin_id)
            .where(
                or_(
                    Message.sender_id == current_admin_id,
                    Message.receiver_id == current_admin_id,
                )
            )
        )

        if search:
            search_term = f"%{search}%"
            base_query = base_query.where(
                or_(
                    Message.subject.ilike(search_term),
                    Message.content.ilike(search_term),
                    Sender.name.ilike(search_term),
                    Receiver.name.ilike(search_term),
                )
            )

        # Count total messages
        count_stmt = select(func.count()).select_from(base_query.subquery())
        total_messages = (await db.execute(count_stmt)).scalar_one()
        total_pages = ceil(total_messages / PAGE_SIZE)

        # Fetch messages for the current page
        messages_stmt = (
            base_query.order_by(Message.time_create.desc())
            .limit(PAGE_SIZE)
            .offset(offset)
        )
        results = (await db.execute(messages_stmt)).all()

        search_query_str = f"&search={html.escape(search)}" if search else ""

        # Render list
        list_items_html = ""
        if not results:
            list_items_html = "<p>No messages found.</p>"
        else:
            for message, sender_name, _ in results:
                is_read_class = "read" if message.is_read else "unread"
                content_snippet = h(message.content[:150] if message.content else "") + ("..." if message.content and len(message.content) > 150 else "")
                
                action_buttons = ""
                if message.receiver_id == current_admin_id and message.is_read:
                    action_buttons = f"""
                    <button class="btn btn-sm btn-secondary" onclick="markMessageAsUnread('{message.message_id}', 'list')">Mark as Unread</button>
                    <button class="btn btn-sm btn-danger" onclick="handleMessageDelete('{message.message_id}')">Delete</button>
                    """

                list_items_html += f"""
                <div class="message-item {is_read_class}">
                    <span class="message-status-indicator"></span>
                    <div class="message-header">
                        <div class="message-link-wrapper">
                            <a href="#message?messageId={message.message_id}" class="message-link">
                                <span class="message-sender">{h(sender_name or 'System')}</span>
                                <span class="message-subject">{h(message.subject)}</span>
                            </a>
                            <span class="message-time">{h(message.time_create.strftime('%Y-%m-%d %H:%M:%S'))}</span>
                            {action_buttons}
                        </div>
                    </div>
                    <div class="message-content">{content_snippet}</div>
                </div>
                """

        # Render pagination
        pagination_html = ""
        if total_pages > 1:
            pagination_html += f"<span>Page {page} of {total_pages} ({total_messages} messages)</span>"
            if page > 1:
                pagination_html += f'<a href="#message?page={page - 1}{search_query_str}" class="btn btn-secondary">Previous</a>'
            # Simplified pagination links for brevity
            for i in range(max(1, page - 2), min(total_pages + 1, page + 3)):
                 btn_class = 'btn-primary' if i == page else 'btn-secondary'
                 pagination_html += f'<a href="#message?page={i}{search_query_str}" class="btn {btn_class}">{i}</a>'
            if page < total_pages:
                pagination_html += f'<a href="#message?page={page + 1}{search_query_str}" class="btn btn-secondary">Next</a>'

        html_content = f"""
        <div id="filter-container" class="filter-container" style="display: block;">
            <form id="message-search-form" class="search-form" onsubmit="handleMessageSearch(event)">
                <div class="filter-controls">
                    <div class="form-group">
                        <label for="search_message">Search</label>
                        <input type="text" name="search" id="search_message" placeholder="Search" value="{h(search or '')}">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
        <div class="message-list-container">{list_items_html}</div>
        <div class="pagination-container">{pagination_html}</div>
        """
        return HTMLResponse(content=html_content)
PYTHON;
            
    }

    /**
     * Generates the Python code for the notification controller (router).
     * This controller handles endpoints for system-wide or user-specific notifications.
     * It allows fetching a list of notifications, viewing a single notification,
     * and performing actions like deleting or marking a notification as unread.
     *
     * @return string The generated Python code for the notification router.
     */
    private function generateNotificationController()
    {
        return <<<PYTHON
import html
from datetime import datetime
from typing import Optional
from math import ceil

from fastapi import APIRouter, Depends, Form, HTTPException, Request
from fastapi.responses import HTMLResponse, JSONResponse
from sqlalchemy import func, or_
from sqlalchemy.future import select

from database import get_db
from models.core.admin import Admin
from models.core.notification import Notification
from routers.auth import login_required

router = APIRouter()


def h(text):
    """Helper to escape HTML and handle None values."""
    return html.escape(str(text)) if text is not None else ""


@router.post("/notification")
async def handle_notification_action(
    request: Request,
    username: str = Depends(login_required),
    db=Depends(get_db),
):
    """Handles POST actions for notifications, such as 'mark_as_unread' or 'delete'."""
    form_data = await request.form()
    action = form_data.get("action")
    notification_id = form_data.get("notificationId")

    if not notification_id:
        raise HTTPException(status_code=400, detail="Notification ID is required.")

    # Get the current admin
    admin_stmt = select(Admin).where(Admin.username == username)
    current_admin = (await db.execute(admin_stmt)).scalar_one_or_none()
    if not current_admin:
        raise HTTPException(status_code=404, detail="User not found.")

    # Get the notification
    stmt = select(Notification).where(Notification.notification_id == notification_id)
    notification = (await db.execute(stmt)).scalar_one_or_none()
    if not notification:
        raise HTTPException(status_code=404, detail="Notification not found.")

    # Check permissions
    is_authorized = (notification.admin_id == current_admin.admin_id) or \
                    (notification.admin_group == current_admin.admin_level_id)
    if not is_authorized:
        raise HTTPException(status_code=403, detail="Forbidden")

    if action == "mark_as_unread":
        notification.is_read = False
        notification.time_read = None
        await db.commit()
        return JSONResponse(
            {"success": True, "message": "Notification marked as unread."}
        )

    elif action == "delete":
        await db.delete(notification)
        await db.commit()
        return JSONResponse(
            {"success": True, "message": "Notification deleted successfully."}
        )

    else:
        raise HTTPException(status_code=400, detail="Invalid action.")


@router.get("/notification", response_class=HTMLResponse)
async def get_notifications(
    request: Request,
    username: str = Depends(login_required),
    db=Depends(get_db),
    notificationId: Optional[str] = None,
    page: int = 1,
    search: Optional[str] = None,
):
    """Retrieves a list of notifications or a single notification detail."""
    admin_stmt = select(Admin).where(Admin.username == username)
    current_admin = (await db.execute(admin_stmt)).scalar_one_or_none()
    if not current_admin:
        raise HTTPException(status_code=404, detail="User not found.")

    current_admin_id = current_admin.admin_id
    current_admin_level_id = current_admin.admin_level_id

    if notificationId:
        # --- Notification Detail View ---
        stmt = (
            select(Notification)
            .where(Notification.notification_id == notificationId)
            .where(
                or_(
                    Notification.admin_id == current_admin_id,
                    Notification.admin_group == current_admin_level_id,
                )
            )
        )
        notification = (await db.execute(stmt)).scalar_one_or_none()

        if not notification:
            return HTMLResponse('<div class="table-container detail-view">No notification found.</div>')

        # Mark as read
        if not notification.is_read:
            notification.is_read = True
            notification.time_read = datetime.now()
            notification.ip_read = request.client.host
            await db.commit()
            await db.refresh(notification)

        status_display = (
            f'<span class="status-read">Read at {h(notification.time_read.strftime("%Y-%m-%d %H:%M:%S"))}</span>'
            if notification.is_read
            else '<span class="status-unread">Unread</span>'
        )

        buttons_html = ""
        if notification.is_read:
            buttons_html = f"""
            <button class="btn btn-primary" onclick="markNotificationAsUnread('{notification.notification_id}', 'detail')">Mark as Unread</button>
            <button class="btn btn-danger" onclick="handleNotificationDelete('{notification.notification_id}')">Delete</button>
            """
        
        link_button = f'<p><a href="{h(notification.link)}" target="_blank" class="btn btn-primary mt-3">More Info</a></p>' if notification.link else ""

        html_content = f"""
        <div class="back-controls">
            <button id="back-to-list" class="btn btn-secondary" onclick="backToList('notification')">Back to List</button>
            {buttons_html}
        </div>
        <div class="notification-container">
            <div class="notification-header">
                <h3>{h(notification.subject)}</h3>
                <div class="message-meta">
                    <div><strong>Time:</strong> {h(notification.time_create.strftime('%Y-%m-%d %H:%M:%S'))}</div>
                    <div><strong>Status:</strong> {status_display}</div>
                </div>
            </div>
            <div class="message-body">
                {h(notification.content).replace(chr(10), '<br>')}
                {link_button}
            </div>
        </div>
        """
        return HTMLResponse(content=html_content)

    else:
        # --- Notification List View ---
        PAGE_SIZE = 20
        offset = (page - 1) * PAGE_SIZE

        where_clause = or_(
            Notification.admin_id == current_admin_id,
            Notification.admin_group == current_admin_level_id,
        )

        if search:
            search_term = f"%{search}%"
            where_clause = or_(
                where_clause,
                Notification.subject.ilike(search_term),
                Notification.content.ilike(search_term),
            )

        # Count total notifications
        count_stmt = select(func.count(Notification.notification_id)).where(where_clause)
        total_notifications = (await db.execute(count_stmt)).scalar_one()
        total_pages = ceil(total_notifications / PAGE_SIZE)

        # Fetch notifications for the current page
        stmt = (
            select(Notification)
            .where(where_clause)
            .order_by(Notification.time_create.desc())
            .limit(PAGE_SIZE)
            .offset(offset)
        )
        notifications = (await db.execute(stmt)).scalars().all()

        search_query_str = f"&search={html.escape(search)}" if search else ""

        # Render list
        list_items_html = ""
        if not notifications:
            list_items_html = "<p>No notifications found.</p>"
        else:
            for notification in notifications:
                is_read_class = "read" if notification.is_read else "unread"
                content_snippet = h(notification.content[:150] if notification.content else "") + ("..." if notification.content and len(notification.content) > 150 else "")
                
                action_buttons = ""
                if notification.is_read:
                    action_buttons = f"""
                    <button class="btn btn-sm btn-secondary" onclick="markNotificationAsUnread('{notification.notification_id}', 'list')">Mark as Unread</button>
                    <button class="btn btn-sm btn-danger" onclick="handleNotificationDelete('{notification.notification_id}')">Delete</button>
                    """

                list_items_html += f"""
                <div class="message-item {is_read_class}">
                    <span class="message-status-indicator"></span>
                    <div class="notification-header">
                        <div class="message-link-wrapper">
                            <a href="#notification?notificationId={notification.notification_id}" class="message-link">
                                <span class="message-subject">{h(notification.subject)}</span>
                            </a>
                            <span class="message-time">{h(notification.time_create.strftime('%Y-%m-%d %H:%M:%S'))}</span>
                            {action_buttons}
                        </div>
                    </div>
                    <div class="message-content">{content_snippet}</div>
                </div>
                """

        # Render pagination
        pagination_html = ""
        if total_pages > 1:
            pagination_html += f"<span>Page {page} of {total_pages} ({total_notifications} notifications)</span>"
            if page > 1:
                pagination_html += f'<a href="#notification?page={page - 1}{search_query_str}" class="btn btn-secondary">Previous</a>'
            for i in range(max(1, page - 2), min(total_pages + 1, page + 3)):
                 btn_class = 'btn-primary' if i == page else 'btn-secondary'
                 pagination_html += f'<a href="#notification?page={i}{search_query_str}" class="btn {btn_class}">{i}</a>'
            if page < total_pages:
                pagination_html += f'<a href="#notification?page={page + 1}{search_query_str}" class="btn btn-secondary">Next</a>'

        html_content = f"""
        <div id="filter-container" class="filter-container" style="display: block;">
            <form id="notification-search-form" class="search-form" onsubmit="handleNotificationSearch(event)">
                <div class="filter-controls">
                    <div class="form-group">
                        <label for="search_notification">Search</label>
                        <input type="text" name="search" id="search_notification" placeholder="Search" value="{h(search or '')}">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
        <div class="message-list-container">{list_items_html}</div>
        <div class="pagination-container">{pagination_html}</div>
        """
        return HTMLResponse(content=html_content)
PYTHON;
    }

    /**
     * Generates the Python code for the user profile controller (router).
     * This controller provides endpoints for the currently logged-in user to manage
     * their own profile. It includes routes to view, edit, and update their personal
     * information, as well as a separate flow for changing their password.
     *
     * @return string The generated Python code for the profile router.
     */
    private function generateProfileController()
    {
        return <<<PYTHON
import hashlib
import html
from datetime import date, datetime
from typing import Optional

from fastapi import APIRouter, Depends, Form, HTTPException, Request, status
from fastapi.responses import HTMLResponse
from pydantic import BaseModel
from sqlalchemy.future import select

from database import get_db
from routers.auth import login_required
from models.core.admin import Admin

router = APIRouter()

# --- Pydantic Models for User Profile ---
class UserProfileUpdate(BaseModel):
    name: Optional[str] = None
    email: Optional[str] = None
    gender: Optional[str] = None
    birth_day: Optional[date] = None
    phone: Optional[str] = None

@router.get("/user-profile", response_class=HTMLResponse)
async def get_user_profile(
    request: Request,
    username: str = Depends(login_required),
    db = Depends(get_db),
    action: Optional[str] = None
):
    """Fetches user profile data and displays it as HTML. Supports ?action=update to show the edit form."""
    stmt = select(Admin).where(Admin.username == username)
    result = await db.execute(stmt)
    admin = result.scalar_one_or_none()

    if not admin:
        raise HTTPException(status_code=404, detail="User not found.")

    # Helper to escape HTML and handle None values
    def h(text):
        return html.escape(str(text)) if text is not None else ""

    if action == 'update':
        # Update Mode: Display the edit form
        birth_day_value = h(admin.birth_day.isoformat() if admin.birth_day else "")
        html_content = f"""
        <div class="table-container detail-view">
            <form id="profile-update-form" class="form-group" onsubmit="handleProfileUpdate(event); return false;">
                <table class="table table-borderless">
                    <tr><td>Admin ID</td><td><input type="text" name="admin_id" class="form-control" value="{h(admin.admin_id)}" autocomplete="off" readonly></td></tr>
                    <tr><td>Name</td><td><input type="text" name="name" class="form-control" value="{h(admin.name)}"></td></tr>
                    <tr><td>Username</td><td><input type="text" name="username" class="form-control" value="{h(admin.username)}" autocomplete="off" readonly></td></tr>
                    <tr>
                        <td>Gender</td>
                        <td>
                            <select name="gender" class="form-control">
                                <option value="M" {'selected' if admin.gender == 'M' else ''}>Male</option>
                                <option value="F" {'selected' if admin.gender == 'F' else ''}>Female</option>
                            </select>
                        </td>
                    </tr>
                    <tr><td>Birthday</td><td><input type="date" name="birth_day" class="form-control" value="{birth_day_value}" autocomplete="off"></td></tr>
                    <tr><td>Phone</td><td><input type="text" name="phone" class="form-control" value="{h(admin.phone)}" autocomplete="off"></td></tr>
                    <tr><td>Email</td><td><input type="email" name="email" class="form-control" value="{h(admin.email)}" autocomplete="off"></td></tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit" class="btn btn-success">Update</button>
                            <button type="button" class="btn btn-secondary" onclick="window.location='#user-profile'">Cancel</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        """
    else:
        # View Mode: Display profile details
        gender_display = "Male" if admin.gender == 'M' else ("Female" if admin.gender == 'F' else "")
        blocked_display = "Yes" if admin.blocked else "No"
        active_display = "Yes" if admin.active else "No"
        
        last_reset_password_display = h(admin.last_reset_password.strftime('%Y-%m-%d %H:%M:%S') if admin.last_reset_password else "")

        html_content = f"""
        <div class="table-container detail-view">
            <form action="" class="form-group">
                <table class="table table-borderless">
                    <tr><td>Admin ID</td><td>{h(admin.admin_id)}</td></tr>
                    <tr><td>Name</td><td>{h(admin.name)}</td></tr>
                    <tr><td>Username</td><td>{h(admin.username)}</td></tr>
                    <tr><td>Gender</td><td>{gender_display}</td></tr>
                    <tr><td>Birthday</td><td>{h(admin.birth_day)}</td></tr>
                    <tr><td>Phone</td><td>{h(admin.phone)}</td></tr>
                    <tr><td>Email</td><td>{h(admin.email)}</td></tr>
                    <tr><td>Admin Level ID</td><td>{h(admin.admin_level_id)}</td></tr>
                    <tr><td>Language ID</td><td>{h(admin.language_id)}</td></tr>
                    <tr><td>Last Reset Password</td><td>{last_reset_password_display}</td></tr>
                    <tr><td>Blocked</td><td>{blocked_display}</td></tr>
                    <tr><td>Active</td><td>{active_display}</td></tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="window.location='#user-profile?action=update'">Edit</button>
                            <button type="button" class="btn btn-warning" onclick="window.location='#update-password'">Update Password</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        """
    
    return HTMLResponse(content=html_content)

@router.post("/user-profile")
async def update_user_profile(
    username: str = Depends(login_required),
    db = Depends(get_db),
    name: str = Form(None),
    email: str = Form(None),
    gender: str = Form(None),
    birth_day: Optional[date] = Form(None),
    phone: str = Form(None)
):
    stmt = select(Admin).where(Admin.username == username)
    result = await db.execute(stmt)
    admin = result.scalar_one_or_none()

    if not admin:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="User not found.")
    
    # Manually create a dictionary of the form data to update
    update_data = {"name": name, "email": email, "gender": gender, "birth_day": birth_day, "phone": phone}
    for key, value in update_data.items():
        if value is not None:
            setattr(admin, key, value)
    
    await db.commit()
    return {"success": True, "message": "Profile updated successfully."}

@router.get("/update-password", response_class=HTMLResponse)
async def get_update_password_form(username: str = Depends(login_required)):
    html_content = """
    <div class="table-container detail-view">
        <form id="password-update-form" class="form-group" onsubmit="handlePasswordUpdate(event); return false">
            <table class="table table-borderless">
                <tr>
                    <td>Current Password</td>
                    <td><input type="password" name="current_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td>New Password</td>
                    <td><input type="password" name="new_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td>Confirm Password</td>
                    <td><input type="password" name="confirm_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" class="btn btn-success">Update</button>
                        <button type="button" class="btn btn-secondary" onclick="window.location='#user-profile'">Cancel</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    """
    return HTMLResponse(content=html_content)

@router.post("/update-password")
async def handle_update_password(
    username: str = Depends(login_required),
    db = Depends(get_db),
    current_password: str = Form(...),
    new_password: str = Form(...),
    confirm_password: str = Form(...)
):
    if not new_password or not current_password:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="All fields must be filled.")

    if new_password != confirm_password:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="New passwords do not match.")

    stmt = select(Admin).where(Admin.username == username)
    result = await db.execute(stmt)
    admin = result.scalar_one_or_none()

    hashed_current_password = hashlib.sha1(hashlib.sha1(current_password.encode('utf-8')).hexdigest().encode('utf-8')).hexdigest()
    if not admin or admin.password != hashed_current_password:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Current password is incorrect.")

    admin.password = hashlib.sha1(hashlib.sha1(new_password.encode('utf-8')).hexdigest().encode('utf-8')).hexdigest()
    admin.last_reset_password = datetime.now()

    await db.commit()
    return {"success": True, "message": "Password updated successfully."}
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
     * Generates the content for resolvers/$tableName.py.
     *
     * @param string $tableName The name of the table.
     * @param array $tableInfo Information about the table's columns.
     * @return array An associative array with 'content' and 'resolvers' keys.
     */
    private function generateResolverFile($tableName, $tableInfo)
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

        $content = <<<PYTHON
from sqlalchemy import func
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from models.$tableName import $pascalName
from utils.query_helpers import apply_filters, apply_ordering
from datetime import datetime, date
from utils.pagination import create_pagination_response
from constants.constants import DATETIME_FORMAT
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
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()

    if entity is None:
        raise Exception(f"$pascalName with id {id} not found")

    for key, value in input.items():
        # Convert datetime/date objects to strings for database compatibility
        if isinstance(value, (datetime, date)):
            value = value.strftime('%Y-%m-%d %H:%M:%S') if isinstance(value, datetime) else value.isoformat()
        setattr(entity, key, value)

$mappingCodeUpdate
    
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
    request = info.context.get("request")
    stmt = select($pascalName).where($pascalName.$pk == id)
    result = await db.execute(stmt)
    entity = result.scalar_one_or_none()

    if entity is None:
        raise Exception(f"$pascalName with id {id} not found")

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

    private function generateAdminLevel() {
        return <<<PYTHON
from sqlalchemy import Column, String, Integer, Boolean
from database import Base

class AdminLevel(Base):
    __tablename__ = 'admin_level'

    admin_level_id = Column(String(40), primary_key=True)
    name = Column(String(100), nullable=False)
    sort_order = Column(Integer, default=0)
    active = Column(Boolean, default=True)
PYTHON;
    }

    /**
     * Generates the SQLAlchemy model class for the 'message_folder' table.
     * This model represents folders for organizing messages, such as 'Inbox' or 'Sent'.
     *
     * @return string The Python code for the MessageFolder model.
     */
    private function generateMessageFolder()
    {
        return <<<PYTHON
from sqlalchemy import Column, String, Integer, Boolean
from database import Base

class MessageFolder(Base):
    __tablename__ = 'message_folder'

    message_folder_id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(100), nullable=False)
    sort_order = Column(Integer, default=0)
    active = Column(Boolean, default=True)
PYTHON;
    }

    /**
     * Generates the SQLAlchemy model class for the 'message' table.
     * This model defines the structure for private messages between users (admins),
     * including relationships to the sender and receiver.
     *
     * @return string The Python code for the Message model.
     */
    private function generateMessage()
    {
        return <<<PYTHON
from sqlalchemy import Column, Integer, String, Text, Boolean, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from database import Base
from datetime import datetime

class Message(Base):
    __tablename__ = 'message'

    message_id = Column(String(40), primary_key=True)
    message_folder_id = Column(Integer, ForeignKey('message_folder.message_folder_id'))
    sender_id = Column(String(40), ForeignKey('admin.admin_id'))
    receiver_id = Column(String(40), ForeignKey('admin.admin_id'))
    subject = Column(String(255), nullable=False)
    content = Column(Text)
    time_create = Column(DateTime, default=datetime.utcnow)
    is_read = Column(Boolean, default=False)
    time_read = Column(DateTime)

    sender = relationship("Admin", foreign_keys=[sender_id])
    receiver = relationship("Admin", foreign_keys=[receiver_id])
PYTHON;
    }

    /**
     * Generates the SQLAlchemy model class for the 'notification' table.
     * This model defines the structure for system notifications, which can be targeted
     * to a specific admin or an entire admin level (group).
     *
     * @return string The Python code for the Notification model.
     */
    private function generateNotification()
    {
        return <<<PYTHON
from sqlalchemy import Column, Integer, String, Text, Boolean, DateTime, ForeignKey
from database import Base
from datetime import datetime

class Notification(Base):
    __tablename__ = 'notification'

    notification_id = Column(String(40), primary_key=True)
    admin_id = Column(Integer, ForeignKey('admin.admin_id'), nullable=True)
    admin_group = Column(String(40), ForeignKey('admin_level.admin_level_id'), nullable=True)
    subject = Column(String(255), nullable=False)
    content = Column(Text)
    link = Column(String(255))
    time_create = Column(DateTime, default=datetime.utcnow)
    is_read = Column(Boolean, default=False)
    time_read = Column(DateTime, nullable=True)
    ip_read = Column(String(50), nullable=True)
PYTHON;
    }

    /**
     * Generates the SQLAlchemy model class for the 'admin_level' table.
     * This model defines the structure and columns for admin roles or levels.
     *
     * @return string The Python code for the AdminLevel model.
     */
    private function generateAdmin() {
        return <<<PYTHON
from sqlalchemy import Column, String, Boolean, Date, Text, DateTime
from database import Base

class Admin(Base):
    __tablename__ = 'admin'

    admin_id = Column(String(40), primary_key=True)
    name = Column(String(100))
    username = Column(String(100), unique=True)
    password = Column(String(512))
    password_version = Column(String(512))
    admin_level_id = Column(String(40))
    gender = Column(String(2))
    birth_day = Column(Date)
    email = Column(String(100))
    phone = Column(String(100))
    language_id = Column(String(40))
    validation_code = Column(Text)
    last_reset_password = Column(DateTime)
    blocked = Column(Boolean, default=False)
    time_create = Column(DateTime)
    time_edit = Column(DateTime)
    admin_create = Column(String(40))
    admin_edit = Column(String(40))
    ip_create = Column(String(50))
    ip_edit = Column(String(50))
    active = Column(Boolean, default=True)
PYTHON;
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
     * Generates the content for constants/constants.py.
     *
     * @return string The content of constants/constants.py.
     */
    private function generateConstants() {
        return <<<PYTHON
"""
Centralized datetime format strings for the application.
"""
DATETIME_FORMAT = '%Y-%m-%d %H:%M:%S'
PYTHON;
    }

    /** 
     * Generates the content for utils/pagination.py.
     *
     * @return string The content of utils/pagination.py.
     */
    private function generatePagination() {
        return <<<PYTHON
from typing import List, Any

def create_pagination_response(items: List[Any], total: int, limit: int, offset: int):
    """
    Creates a standardized pagination response dictionary.

    :param items: The list of items for the current page.
    :param total: The total number of items across all pages.
    :param limit: The number of items per page.
    :param offset: The starting offset for the query.
    :return: A dictionary containing paginated data and metadata.
    """
    page = (offset // limit) + 1 if limit > 0 else 1
    total_pages = (total + limit - 1) // limit if limit > 0 else (1 if total > 0 else 0)

    return {
        "items": items,
        "total": total,
        "limit": limit,
        "page": page,
        "totalPages": total_pages,
        "hasNext": (offset + limit) < total,
        "hasPrevious": offset > 0,
    }
PYTHON;
        
    }

    /** 
     * Generates the content for utils/query_helpers.py.
     *
     * @return string The content of the query helpers file.
     */
    private function generateQueryHelpersPy()
    {
        return <<<PYTHON
def apply_filters(stmt, model, filter_list):
    """Applies a list of filters to a SQLAlchemy query statement."""
    if not filter_list:
        return stmt

    where_clauses = []
    for f in filter_list:
        field_name = f.get('field')
        value = f.get('value')
        operator = f.get('operator', 'EQUALS').upper()
        
        column = getattr(model, field_name, None)
        if column is None:
            continue

        if operator == 'EQUALS':
            where_clauses.append(column == value)
        elif operator == 'NOT_EQUALS':
            where_clauses.append(column != value)
        elif operator == 'CONTAINS':
            where_clauses.append(column.ilike(f"%{value}%"))
        elif operator == 'GREATER_THAN':
            where_clauses.append(column > value)
        elif operator == 'GREATER_THAN_OR_EQUALS':
            where_clauses.append(column >= value)
        elif operator == 'LESS_THAN':
            where_clauses.append(column < value)
        elif operator == 'LESS_THAN_OR_EQUALS':
            where_clauses.append(column <= value)

    if where_clauses:
        stmt = stmt.where(*where_clauses)
    return stmt

def apply_ordering(stmt, model, order_by_list):
    """Applies a list of ordering clauses to a SQLAlchemy query statement."""
    if order_by_list:
        order_clauses = [getattr(model, o['field']).asc() if o.get('direction', 'ASC').upper() == 'ASC' else getattr(model, o['field']).desc() for o in order_by_list]
        stmt = stmt.order_by(*order_clauses)
    return stmt
PYTHON;
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
