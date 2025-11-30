import hashlib
from typing import Callable

from fastapi import APIRouter, Request, Depends, HTTPException, status, Form

from models.core.admin import Admin
from database import get_db
from sqlalchemy.future import select
from utils.i18n import get_translator
from typing import Callable


async def login_required(request: Request, translator: Callable = Depends(get_translator)):
    if "username" not in request.session:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail=translator("session_expired"),
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
    request: Request = None,
    translator: Callable = Depends(get_translator)
):
    stmt = select(Admin).where(Admin.username == username)
    result = await db.execute(stmt)
    admin = result.scalar_one_or_none()

    if not admin:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail=translator("invalid_credentials"))

    # Encrypt the input password with sha1(sha1(password))
    hashed_password_input = hashlib.sha1(hashlib.sha1(password.encode('utf-8')).hexdigest().encode('utf-8')).hexdigest()

    if admin.password == hashed_password_input:
        # Store user information in the session
        request.session['username'] = admin.username
        request.session['admin_id'] = admin.admin_id
        request.session['admin_level_id'] = admin.admin_level_id
        request.session['password_v1'] = hashlib.sha1(password.encode('utf-8')).hexdigest()
        return {"success": True, "message": translator("login_successful")}
    else:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail=translator("invalid_credentials"))

@router.post("/logout")
async def logout1(request: Request, username: str = Depends(login_required), translator: Callable = Depends(get_translator)):
    request.session.clear()
    return {"success": True, "message": translator("logout_success")}

@router.get("/logout")
async def logout2(request: Request, username: str = Depends(login_required), translator: Callable = Depends(get_translator)):
    request.session.clear()
    return {"success": True, "message": translator("logout_success")}