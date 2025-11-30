import html
from datetime import datetime
from typing import Optional, Callable
from math import ceil

from fastapi import APIRouter, Depends, Form, HTTPException, Request, status
from fastapi.responses import HTMLResponse, JSONResponse
from sqlalchemy import func, or_, and_
from sqlalchemy.future import select
from sqlalchemy.orm import aliased

from database import get_db
from models.core.admin import Admin
from models.core.message import Message
from models.core.message_folder import MessageFolder
from routers.auth import login_required
from utils.i18n import get_translator
from typing import Callable

router = APIRouter()


def h(text):
    """Helper to escape HTML and handle None values."""
    return html.escape(str(text)) if text is not None else ""


@router.post("/message")
async def handle_message_action(
    request: Request,
    username: str = Depends(login_required),
    db=Depends(get_db),
    translator: Callable = Depends(get_translator),
):
    """Handles POST actions for messages, such as 'mark_as_unread' or 'delete'."""
    form_data = await request.form()
    action = form_data.get("action")
    message_id = form_data.get("messageId")

    if not message_id:
        raise HTTPException(status_code=400, detail=translator("message_id_required"))

    # Get the current admin
    admin_stmt = select(Admin).where(Admin.username == username)
    current_admin = (await db.execute(admin_stmt)).scalar_one_or_none()
    if not current_admin:
        raise HTTPException(status_code=404, detail=translator("admin_not_found"))

    # Get the message
    message_stmt = select(Message).where(Message.message_id == message_id)
    message = (await db.execute(message_stmt)).scalar_one_or_none()
    if not message:
        raise HTTPException(status_code=404, detail=translator("message_not_found"))

    if action == "mark_as_unread":
        if message.receiver_id != current_admin.admin_id:
            raise HTTPException(status_code=403, detail=translator("forbidden"))
        message.is_read = False
        message.time_read = None
        await db.commit()
        return JSONResponse(
            {"success": True, "message": translator("message_marked_as_unread")}
        )

    elif action == "delete":
        if (
            message.sender_id != current_admin.admin_id
            and message.receiver_id != current_admin.admin_id
        ):
            raise HTTPException(status_code=403, detail=translator("forbidden"))
        await db.delete(message)
        await db.commit()
        return JSONResponse(
            {"success": True, "message": translator("message_deleted_successfully")}
        )

    else:
        raise HTTPException(status_code=400, detail=translator("invalid_action_specified"))


@router.get("/message", response_class=HTMLResponse)
async def get_messages(
    request: Request,
    username: str = Depends(login_required),
    db=Depends(get_db),
    messageId: Optional[str] = None,
    page: int = 1,
    search: Optional[str] = None,
    translator: Callable = Depends(get_translator),
):
    """Retrieves a list of messages or a single message detail."""
    admin_stmt = select(Admin).where(Admin.username == username)
    current_admin = (await db.execute(admin_stmt)).scalar_one_or_none()
    if not current_admin:
        raise HTTPException(status_code=404, detail=translator("admin_not_found"))

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
            return HTMLResponse(f'<div class="table-container detail-view">{translator("no_message_found")}</div>')

        message, sender_name, receiver_name = result

        # Mark as read
        if message.receiver_id == current_admin_id and not message.is_read:
            message.is_read = True
            message.time_read = datetime.now()
            await db.commit()
            await db.refresh(message)

        status_display = (
            f'<span class="status-read">{translator("read_at_time", h(message.time_read.strftime("%Y-%m-%d %H:%M:%S")))}</span>'
            if message.is_read
            else f'<span class="status-unread">{translator("unread")}</span>'
        )

        buttons_html = ""
        if message.receiver_id == current_admin_id and message.is_read:
            buttons_html = f"""
            <button class="btn btn-primary" onclick="markMessageAsUnread('{message.message_id}', 'detail')">{translator("mark_as_unread")}</button>
            <button class="btn btn-danger" onclick="handleMessageDelete('{message.message_id}')">{translator("delete")}</button>
            """

        html_content = f"""
        <div class="back-controls">
            <button id="back-to-list" class="btn btn-secondary" onclick="backToList('message')">{translator("back_to_list")}</button>
            {buttons_html}
        </div>
        <div class="message-container">
            <div class="message-header">
                <h3>{h(message.subject)}</h3>
                <div class="message-meta">
                    <div><strong>{translator("from")}:</strong> {h(sender_name or translator("system"))}</div>
                    <div><strong>{translator("to")}:</strong> {h(receiver_name or translator("system"))}</div>
                    <div><strong>{translator("time")}:</strong> {h(message.time_create.strftime('%Y-%m-%d %H:%M:%S'))}</div>
                    <div><strong>{translator("status")}:</strong> {status_display}</div>
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
            list_items_html = f"<p>{translator('no_messages_found')}</p>"
        else:
            for message, sender_name, _ in results:
                is_read_class = "read" if message.is_read else "unread"
                content_snippet = h(message.content[:150] if message.content else "") + ("..." if message.content and len(message.content) > 150 else "")

                action_buttons = ""
                if message.receiver_id == current_admin_id and message.is_read:
                    action_buttons = f"""
                    <button class="btn btn-sm btn-secondary" onclick="markMessageAsUnread('{message.message_id}', 'list')">{translator("mark_as_unread")}</button>
                    <button class="btn btn-sm btn-danger" onclick="handleMessageDelete('{message.message_id}')">{translator("delete")}</button>
                    """

                list_items_html += f"""
                <div class="message-item {is_read_class}">
                    <span class="message-status-indicator"></span>
                    <div class="message-header">
                        <div class="message-link-wrapper">
                            <a href="#message?messageId={message.message_id}" class="message-link">
                                <span class="message-sender">{h(sender_name or translator("system"))}</span>
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
            pagination_html += f"<span>{translator('page_of_messages', page, total_pages, total_messages)}</span>"
            if page > 1:
                pagination_html += f'<a href="#message?page={page - 1}{search_query_str}" class="btn btn-secondary">{translator("previous")}</a>'
            # Simplified pagination links for brevity
            for i in range(max(1, page - 2), min(total_pages + 1, page + 3)):
                btn_class = 'btn-primary' if i == page else 'btn-secondary'
                pagination_html += f'<a href="#message?page={i}{search_query_str}" class="btn {btn_class}">{i}</a>'
            if page < total_pages:
                pagination_html += f'<a href="#message?page={page + 1}{search_query_str}" class="btn btn-secondary">{translator("next")}</a>'

        html_content = f"""
        <div id="filter-container" class="filter-container" style="display: block;">
            <form id="message-search-form" class="search-form" onsubmit="handleMessageSearch(event)">
                <div class="filter-controls">
                    <div class="form-group">
                        <label for="search_message">{translator("search")}</label>
                        <input type="text" name="search" id="search_message" placeholder="{translator('search')}" value="{h(search or '')}">
                    </div>
                    <button type="submit" class="btn btn-primary">{translator("search")}</button>
                </div>
            </form>
        </div>
        <div class="message-list-container">{list_items_html}</div>
        <div class="pagination-container">{pagination_html}</div>
        """
        return HTMLResponse(content=html_content)