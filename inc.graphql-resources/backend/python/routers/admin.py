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
from utils.i18n import get_translator

from database import get_db
from typing import Callable

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
    adminId: Optional[str] = Form(None), # type: ignore
    translator: Callable = Depends(get_translator),
):
    # This single endpoint mimics the PHP script's POST handling.
    try:
        if action == "create":
            form = await request.form()
            password = form.get("password")
            if not password:
                raise HTTPException(status_code=400, detail=translator("password_is_required"))
            
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
            db.add(new_admin) # type: ignore
            await db.commit()
            return JSONResponse({"success": True, "message": translator("admin_created_successfully")})

        elif action == "update":
            if not adminId:
                raise HTTPException(status_code=400, detail=translator("admin_id_required"))
            
            form = await request.form()
            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin_to_update = (await db.execute(stmt)).scalar_one_or_none()
            if not admin_to_update:
                raise HTTPException(status_code=404, detail=translator("admin_not_found"))

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
            return JSONResponse({"success": True, "message": translator("admin_updated_successfully")})

        elif action == "toggle_active":
            if not adminId:
                raise HTTPException(status_code=400, detail=translator("admin_id_required"))
            if adminId == current_admin_id:
                raise HTTPException(status_code=403, detail=translator("cannot_deactivate_self"))

            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin_to_toggle = (await db.execute(stmt)).scalar_one_or_none()
            if not admin_to_toggle:
                raise HTTPException(status_code=404, detail=translator("admin_not_found"))
            
            admin_to_toggle.active = not admin_to_toggle.active
            await db.commit()
            return JSONResponse({"success": True, "message": translator("admin_status_updated")})

        elif action == "change_password":
            if not adminId:
                raise HTTPException(status_code=400, detail=translator("admin_id_required"))
            
            form = await request.form()
            password = form.get("password")
            if not password:
                raise HTTPException(status_code=400, detail=translator("password_is_required"))

            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin_to_update = (await db.execute(stmt)).scalar_one_or_none()
            if not admin_to_update:
                raise HTTPException(status_code=404, detail=translator("admin_not_found"))

            admin_to_update.password = hashlib.sha1(hashlib.sha1(password.encode('utf-8')).hexdigest().encode('utf-8')).hexdigest()
            await db.commit()
            return JSONResponse({"success": True, "message": translator("password_updated_successfully")})

        elif action == "delete":
            if not adminId:
                raise HTTPException(status_code=400, detail=translator("admin_id_required"))
            if adminId == current_admin_id:
                raise HTTPException(status_code=403, detail=translator("cannot_delete_self"))

            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin_to_delete = (await db.execute(stmt)).scalar_one_or_none()
            if not admin_to_delete:
                raise HTTPException(status_code=404, detail=translator("admin_not_found"))

            await db.delete(admin_to_delete)
            await db.commit()
            return JSONResponse({"success": True, "message": translator("admin_deleted_successfully")})

        else:
            raise HTTPException(status_code=400, detail=translator("invalid_action_specified"))

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
    translator: Callable = Depends(get_translator),
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
            {"admin_level_id": "1", "name": translator("super_admin")},
            {"admin_level_id": "2", "name": translator("regular_admin")}
        ]

    if view == "create" or (view == "edit" and adminId):
        admin = None
        if view == "edit":
            stmt = select(Admin).where(Admin.admin_id == adminId)
            admin = (await db.execute(stmt)).scalar_one_or_none()
        
        admin_levels = await get_admin_levels(db) # type: ignore
        
        form_title = translator("add_new_admin") if view == "create" else translator("edit_admin")
        form_action = "create" if view == "create" else "update"
        
        # Build admin level options
        level_options = f'<option value="">{translator("select_option")}</option>'
        for level in admin_levels:
            selected = 'selected' if admin and str(admin.admin_level_id) == str(level['admin_level_id']) else ''
            level_options += f'<option value="{h(level["admin_level_id"])}" {selected}>{h(level["name"])}</option>'

        password_field = ""
        if view == "create":
            password_field = f'<tr><td>{translator("password")}</td><td><input type="password" name="password" required autocomplete="new-password"></td></tr>'

        checked = 'checked' if (view == 'create' or (admin and admin.active)) else ''

        return HTMLResponse(f"""
            <div class="back-controls"><a href="#admin" class="btn btn-secondary">{translator("back_to_list")}</a></div>
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
            return HTMLResponse(f"<p>{translator('admin_not_found')}</p>")

        buttons = f'<a href="#admin?view=edit&adminId={h(adminId)}" class="btn btn-primary">{translator("edit")}</a>'
        if admin.admin_id != current_admin_id:
            toggle_text = translator('deactivate') if admin.active else translator('activate')
            toggle_class = 'btn-warning' if admin.active else 'btn-success'
            buttons += f'''
                <a href="#admin?view=change-password&adminId={h(adminId)}" class="btn btn-warning">{translator("change_password")}</a>
                <button class="btn {toggle_class}" onclick="handleAdminToggleActive('{h(adminId)}')">{toggle_text}</button>
                <button class="btn btn-danger" onclick="handleAdminDelete('{h(adminId)}')">{translator("delete")}</button>
            '''

        return HTMLResponse(f"""
            <div class="back-controls">
                <a href="#admin" class="btn btn-secondary">{translator("back_to_list")}</a>
                {buttons}
            </div>
            <div class="table-container detail-view">
                <table class="table"><tbody>
                    <tr><td><strong>{translator("admin_id")}</strong></td><td>{h(admin.admin_id)}</td></tr>
                    <tr><td><strong>{translator("name")}</strong></td><td>{h(admin.name)}</td></tr>
                    <tr><td><strong>{translator("username")}</strong></td><td>{h(admin.username)}</td></tr>
                    <tr><td><strong>{translator("email")}</strong></td><td>{h(admin.email)}</td></tr>
                    <tr><td><strong>{translator("admin_level")}</strong></td><td>{h(admin.admin_level_id)}</td></tr>
                    <tr><td><strong>{translator("status")}</strong></td><td>{translator('active') if admin.active else translator('inactive')}</td></tr>
                    <tr><td><strong>{translator("time_create")}</strong></td><td>{h(admin.time_create)}</td></tr>
                    <tr><td><strong>{translator("time_edit")}</strong></td><td>{h(admin.time_edit)}</td></tr>
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
        if admins: # type: ignore
            for admin in admins:
                actions = f'<a href="#admin?view=detail&adminId={h(admin.admin_id)}" class="btn btn-sm btn-info">{translator("view")}</a>'
                if admin.admin_id != current_admin_id:
                    toggle_text = translator('deactivate') if admin.active else translator('activate')
                    toggle_class = 'btn-warning' if admin.active else 'btn-success'
                    actions += f'''
                        <a href="#admin?view=edit&adminId={h(admin.admin_id)}" class="btn btn-sm btn-primary">{translator("edit")}</a>
                        <button class="btn btn-sm {toggle_class}" onclick="handleAdminToggleActive('{h(admin.admin_id)}')">{toggle_text}</button>
                        <button class="btn btn-sm btn-danger" onclick="handleAdminDelete('{h(admin.admin_id)}')">{translator("delete")}</button>
                    '''
                rows += f"""
                    <tr class="{'' if admin.active else 'inactive'}">
                        <td>{h(admin.name)}</td>
                        <td>{h(admin.username)}</td>
                        <td>{h(admin.email)}</td>
                        <td>{h(admin.admin_level_id)}</td> # type: ignore
                        <td>{translator('active') if admin.active else translator('inactive')}</td>
                        <td class="actions">{actions}</td> # type: ignore
                    </tr>
                """
        else:
            rows = f'<tr><td colspan="6">{translator("no_admins_found")}</td></tr>'

        # Basic pagination for demonstration
        pagination_html = translator("page_of", page, total_pages, total_admins)

        return HTMLResponse(f"""
            <div id="filter-container" class="filter-container" style="display: block;">
                <form id="admin-search-form" class="search-form" onsubmit="handleAdminSearch(event)"> 
                    <div class="filter-controls">
                        <div class="form-group">
                            <label for="search_term">{translator("name_username")}</label>
                            <input type="text" name="search" id="search_term" placeholder="{translator("name_or_username")}" value="{h(search)}">
                        </div>
                        <button type="submit" class="btn btn-primary">{translator("search")}</button>
                        <a href="#admin?view=create" class="btn btn-primary">{translator("add_new_admin")}</a>
                    </div>
                </form>
            </div>
            <div class="table-container"> # type: ignore
                <table class="table table-striped"> # type: ignore
                    <thead><tr><th>{translator("name")}</th><th>{translator("username")}</th><th>{translator("email")}</th><th>{translator("admin_level")}</th><th>{translator("status")}</th><th>{translator("actions")}</th></tr></thead>
                    <tbody>{rows}</tbody>
                </table>
            </div>
            <div class="pagination-container">{pagination_html}</div>
        """)