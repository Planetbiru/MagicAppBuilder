import html
from datetime import datetime
from typing import Optional, Callable
from math import ceil

from fastapi import APIRouter, Depends, Form, HTTPException, Request
from fastapi.responses import HTMLResponse, JSONResponse
from sqlalchemy import func, or_
from sqlalchemy.future import select

from database import get_db
from models.core.admin import Admin
from models.core.notification import Notification
from utils.i18n import get_translator
from routers.auth import login_required
from typing import Callable

router = APIRouter()


def h(text):
    """Helper to escape HTML and handle None values."""
    return html.escape(str(text)) if text is not None else ""


@router.post("/notification")
async def handle_notification_action(
    request: Request,
    username: str = Depends(login_required),
    db=Depends(get_db),
    translator: Callable = Depends(get_translator),
):
    """Handles POST actions for notifications, such as 'mark_as_unread' or 'delete'."""
    form_data = await request.form()
    action = form_data.get("action")
    notification_id = form_data.get("notificationId")

    if not notification_id:
        raise HTTPException(status_code=400, detail=translator("notification_id_required"))

    # Get the current admin
    admin_stmt = select(Admin).where(Admin.username == username)
    current_admin = (await db.execute(admin_stmt)).scalar_one_or_none()
    if not current_admin:
        raise HTTPException(status_code=404, detail=translator("admin_not_found"))

    # Get the notification
    stmt = select(Notification).where(Notification.notification_id == notification_id)
    notification = (await db.execute(stmt)).scalar_one_or_none()
    if not notification:
        raise HTTPException(status_code=404, detail=translator("notification_not_found"))

    # Check permissions
    is_authorized = (notification.admin_id == current_admin.admin_id) or \
                    (notification.admin_group == current_admin.admin_level_id)
    if not is_authorized:
        raise HTTPException(status_code=403, detail=translator("forbidden"))

    if action == "mark_as_unread":
        notification.is_read = False
        notification.time_read = None
        await db.commit()
        return JSONResponse(
            {"success": True, "message": translator("notification_marked_as_unread")}
        )

    elif action == "delete":
        await db.delete(notification)
        await db.commit()
        return JSONResponse(
            {"success": True, "message": translator("notification_deleted_successfully")}
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
    translator: Callable = Depends(get_translator),
):
    """Retrieves a list of notifications or a single notification detail."""
    admin_stmt = select(Admin).where(Admin.username == username)
    current_admin = (await db.execute(admin_stmt)).scalar_one_or_none()
    if not current_admin:
        raise HTTPException(status_code=404, detail=translator("admin_not_found"))

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
            return HTMLResponse(f'<div class="table-container detail-view">{translator("no_notification_found")}</div>')

        # Mark as read
        if not notification.is_read:
            notification.is_read = True
            notification.time_read = datetime.now()
            notification.ip_read = request.client.host
            await db.commit()
            await db.refresh(notification)

        status_display = (
            f'<span class="status-read">{translator("read_at_time", h(notification.time_read.strftime("%Y-%m-%d %H:%M:%S")))}</span>'
            if notification.is_read
            else f'<span class="status-unread">{translator("unread")}</span>'
        )

        buttons_html = ""
        if notification.is_read:
            buttons_html = f"""
            <button class="btn btn-primary" onclick="markNotificationAsUnread('{notification.notification_id}', 'detail')">{translator("mark_as_unread")}</button>
            <button class="btn btn-danger" onclick="handleNotificationDelete('{notification.notification_id}')">{translator("delete")}</button>
            """
        
        link_button = f'<p><a href="{h(notification.link)}" target="_blank" class="btn btn-primary mt-3">{translator("more_info")}</a></p>' if notification.link else ""

        html_content = f"""
        <div class="back-controls">
            <button id="back-to-list" class="btn btn-secondary" onclick="backToList('notification')">{translator("back_to_list")}</button>
            {buttons_html}
        </div>
        <div class="notification-container">
            <div class="notification-header">
                <h3>{h(notification.subject)}</h3>
                <div class="message-meta">
                    <div><strong>{translator("time")}:</strong> {h(notification.time_create.strftime('%Y-%m-%d %H:%M:%S'))}</div>
                    <div><strong>{translator("status")}:</strong> {status_display}</div>
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
            list_items_html = f"<p>{translator('no_notifications_found')}</p>"
        else:
            for notification in notifications:
                is_read_class = "read" if notification.is_read else "unread"
                content_snippet = h(notification.content[:150] if notification.content else "") + ("..." if notification.content and len(notification.content) > 150 else "")
                
                action_buttons = ""
                if notification.is_read:
                    action_buttons = f"""
                    <button class="btn btn-sm btn-secondary" onclick="markNotificationAsUnread('{notification.notification_id}', 'list')">{translator("mark_as_unread")}</button>
                    <button class="btn btn-sm btn-danger" onclick="handleNotificationDelete('{notification.notification_id}')">{translator("delete")}</button>
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
            pagination_html += f"<span>{translator('page_of_notifications', page, total_pages, total_notifications)}</span>"
            if page > 1:
                pagination_html += f'<a href="#notification?page={page - 1}{search_query_str}" class="btn btn-secondary">{translator("previous")}</a>'
            for i in range(max(1, page - 2), min(total_pages + 1, page + 3)):
                btn_class = 'btn-primary' if i == page else 'btn-secondary'
                pagination_html += f'<a href="#notification?page={i}{search_query_str}" class="btn {btn_class}">{i}</a>'
            if page < total_pages:
                pagination_html += f'<a href="#notification?page={page + 1}{search_query_str}" class="btn btn-secondary">{translator("next")}</a>'

        html_content = f"""
        <div id="filter-container" class="filter-container" style="display: block;">
            <form id="notification-search-form" class="search-form" onsubmit="handleNotificationSearch(event)">
                <div class="filter-controls">
                    <div class="form-group">
                        <label for="search_notification">{translator("search")}</label>
                        <input type="text" name="search" id="search_notification" placeholder="{translator('search')}" value="{h(search or '')}">
                    </div>
                    <button type="submit" class="btn btn-primary">{translator("search")}</button>
                </div>
            </form>
        </div>
        <div class="message-list-container">{list_items_html}</div>
        <div class="pagination-container">{pagination_html}</div>
        """
        return HTMLResponse(content=html_content)