import hashlib
import html
from datetime import date, datetime
from typing import Optional, Callable

from fastapi import APIRouter, Depends, Form, HTTPException, Request, status
from fastapi.responses import HTMLResponse
from pydantic import BaseModel
from sqlalchemy.future import select
from utils.i18n import get_translator

from database import get_db
from routers.auth import login_required
from models.core.admin import Admin
from typing import Callable

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
    db=Depends(get_db),
    action: Optional[str] = None,
    translator: Callable = Depends(get_translator),
):
    """Fetches user profile data and displays it as HTML. Supports ?action=update to show the edit form."""
    stmt = select(Admin).where(Admin.username == username)
    result = await db.execute(stmt)
    admin = result.scalar_one_or_none()

    if not admin:
        raise HTTPException(status_code=404, detail=translator("admin_not_found"))

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
                    <tr><td>{translator('admin_id')}</td><td><input type="text" name="admin_id" class="form-control" value="{h(admin.admin_id)}" autocomplete="off" readonly></td></tr>
                    <tr><td>{translator('name')}</td><td><input type="text" name="name" class="form-control" value="{h(admin.name)}"></td></tr>
                    <tr><td>{translator('username')}</td><td><input type="text" name="username" class="form-control" value="{h(admin.username)}" autocomplete="off" readonly></td></tr>
                    <tr>
                        <td>{translator('gender')}</td>
                        <td>
                            <select name="gender" class="form-control">
                                <option value="M" {'selected' if admin.gender == 'M' else ''}>{translator('male')}</option>
                                <option value="F" {'selected' if admin.gender == 'F' else ''}>{translator('female')}</option>
                            </select>
                        </td>
                    </tr>
                    <tr><td>{translator('birthday')}</td><td><input type="date" name="birth_day" class="form-control" value="{birth_day_value}" autocomplete="off"></td></tr>
                    <tr><td>{translator('phone')}</td><td><input type="text" name="phone" class="form-control" value="{h(admin.phone)}" autocomplete="off"></td></tr>
                    <tr><td>{translator('email')}</td><td><input type="email" name="email" class="form-control" value="{h(admin.email)}" autocomplete="off"></td></tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit" class="btn btn-success">{translator('update')}</button>
                            <button type="button" class="btn btn-secondary" onclick="window.location='#user-profile'">{translator('cancel')}</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        """
    else:
        # View Mode: Display profile details
        gender_display = translator('male') if admin.gender == 'M' else (translator('female') if admin.gender == 'F' else "")
        blocked_display = translator('yes') if admin.blocked else translator('no')
        active_display = translator('yes') if admin.active else translator('no')
        
        last_reset_password_display = h(admin.last_reset_password.strftime('%Y-%m-%d %H:%M:%S') if admin.last_reset_password else "")

        html_content = f"""
        <div class="table-container detail-view">
            <form action="" class="form-group">
                <table class="table table-borderless">
                    <tr><td>{translator('admin_id')}</td><td>{h(admin.admin_id)}</td></tr>
                    <tr><td>{translator('name')}</td><td>{h(admin.name)}</td></tr>
                    <tr><td>{translator('username')}</td><td>{h(admin.username)}</td></tr>
                    <tr><td>{translator('gender')}</td><td>{gender_display}</td></tr>
                    <tr><td>{translator('birthday')}</td><td>{h(admin.birth_day)}</td></tr>
                    <tr><td>{translator('phone')}</td><td>{h(admin.phone)}</td></tr>
                    <tr><td>{translator('email')}</td><td>{h(admin.email)}</td></tr>
                    <tr><td>{translator('admin_level_id')}</td><td>{h(admin.admin_level_id)}</td></tr>
                    <tr><td>{translator('language_id')}</td><td>{h(admin.language_id)}</td></tr>
                    <tr><td>{translator('last_reset_password')}</td><td>{last_reset_password_display}</td></tr>
                    <tr><td>{translator('blocked')}</td><td>{blocked_display}</td></tr>
                    <tr><td>{translator('active')}</td><td>{active_display}</td></tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="window.location='#user-profile?action=update'">{translator('edit')}</button>
                            <button type="button" class="btn btn-warning" onclick="window.location='#update-password'">{translator('update_password')}</button>
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
    db=Depends(get_db),
    name: str = Form(None),
    email: str = Form(None),
    gender: str = Form(None),
    birth_day: Optional[date] = Form(None),
    phone: str = Form(None),
    translator: Callable = Depends(get_translator),
):
    stmt = select(Admin).where(Admin.username == username)
    result = await db.execute(stmt)
    admin = result.scalar_one_or_none()

    if not admin:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail=translator("admin_not_found"))
    
    # Manually create a dictionary of the form data to update
    update_data = {"name": name, "email": email, "gender": gender, "birth_day": birth_day, "phone": phone}
    for key, value in update_data.items():
        if value is not None:
            setattr(admin, key, value)
    
    await db.commit()
    return {"success": True, "message": translator("profile_updated_successfully")}

@router.get("/update-password", response_class=HTMLResponse)
async def get_update_password_form(username: str = Depends(login_required), translator: Callable = Depends(get_translator)):
    html_content = f"""
    <div class="table-container detail-view">
        <form id="password-update-form" class="form-group" onsubmit="handlePasswordUpdate(event); return false">
            <table class="table table-borderless">
                <tr>
                    <td>{translator('current_password')}</td>
                    <td><input type="password" name="current_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td>{translator('new_password')}</td>
                    <td><input type="password" name="new_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td>{translator('confirm_password')}</td>
                    <td><input type="password" name="confirm_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" class="btn btn-success">{translator('update')}</button>
                        <button type="button" class="btn btn-secondary" onclick="window.location='#user-profile'">{translator('cancel')}</button>
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