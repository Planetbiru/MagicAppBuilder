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