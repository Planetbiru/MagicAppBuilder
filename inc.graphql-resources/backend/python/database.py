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