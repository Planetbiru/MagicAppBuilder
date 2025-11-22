const express = require('express');
const { Op } = require('sequelize');
const { models } = require('../config/database');
const conditionalAuth = require('../middleware/conditionalAuth');
const multer = require('multer');

const router = express.Router();
const upload = multer();

// Helper function for sanitizing HTML output
const esc = (str) => {
    if (str === null || typeof str === 'undefined') return '';
    return String(str).replace(/[&<>"']/g, (match) => {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match];
    });
};

// --- POST Handler for notification actions ---
router.post('/notification', conditionalAuth, upload.none(), async (req, res) => {
    const { action, notificationId } = req.body;
    const currentAdminId = req.user.admin_id;
    const currentAdminLevelId = req.user.admin_level_id;

    if (!notificationId) {
        return res.status(400).json({ success: false, message: 'Notification ID is required.' });
    }

    const whereClause = {
        notification_id: notificationId,
        [Op.or]: [{ admin_id: currentAdminId }, { admin_group: currentAdminLevelId }]
    };

    try {
        if (action === 'mark_as_unread') {
            await models.notification.update({ is_read: false, time_read: null }, { where: whereClause });
            return res.json({ success: true, message: 'Notification marked as unread.' });
        } else if (action === 'delete') {
            await models.notification.destroy({ where: whereClause });
            return res.json({ success: true, message: 'Notification deleted successfully.' });
        } else {
            return res.status(400).json({ success: false, message: 'Invalid action.' });
        }
    } catch (error) {
        console.error(`Notification POST action '${action}' failed:`, error);
        res.status(500).json({ success: false, message: error.message || 'An internal server error occurred.' });
    }
});

// --- GET Handler for rendering notification views ---
router.get('/notification', conditionalAuth, async (req, res) => {
    const { notificationId, search = '', page = 1 } = req.query;
    const currentAdminId = req.user.admin_id;
    const currentAdminLevelId = req.user.admin_level_id;

    try {
        // --- Detail View ---
        if (notificationId) {
            const whereClause = {
                notification_id: notificationId,
                [Op.or]: [{ admin_id: currentAdminId }, { admin_group: currentAdminLevelId }]
            };
            const notification = await models.notification.findOne({ where: whereClause });

            if (!notification) {
                return res.status(404).send('<div class="table-container detail-view">Notification not found.</div>');
            }

            // Mark as read if it's unread
            if (!notification.is_read) {
                await notification.update({ is_read: true, time_read: new Date(), ip_read: req.ip });
                notification.is_read = true; // Update in-memory object
            }

            const html = `
                <div class="back-controls">
                    <button id="back-to-list" class="btn btn-secondary" onclick="backToList('notification')">Back to List</button>
                    ${notification.is_read ? `
                        <button class="btn btn-primary" onclick="markNotificationAsUnread('${esc(notification.notification_id)}', 'detail')">Mark as Unread</button>
                        <button class="btn btn-danger" onclick="handleNotificationDelete('${esc(notification.notification_id)}')">Delete</button>
                    ` : ''}
                </div>
                <div class="notification-container">
                    <div class="notification-header">
                        <h3>${esc(notification.subject)}</h3>
                        <div class="message-meta">
                            <div><strong>Time:</strong> ${esc(new Date(notification.time_create).toLocaleString())}</div>
                            <div><strong>Status:</strong> 
                                ${notification.is_read ? `<span class="status-read">Read at ${esc(new Date(notification.time_read).toLocaleString())}</span>` : '<span class="status-unread">Unread</span>'}
                            </div>
                        </div>
                    </div>
                    <div class="message-body">
                        ${nl2br(esc(notification.content))}
                        ${notification.link ? `<p><a href="${esc(notification.link)}" target="_blank" class="btn btn-primary mt-3">More Info</a></p>` : ''}
                    </div>
                </div>`;
            
            function nl2br(str) { return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2'); }
            return res.send(html);
        }

        // --- List View (Default) ---
        const limit = 20;
        const offset = (page - 1) * limit;
        const whereClause = {
            [Op.or]: [{ admin_id: currentAdminId }, { admin_group: currentAdminLevelId }],
            ...(search && {
                [Op.or]: [
                    { subject: { [Op.like]: `%${search}%` } },
                    { content: { [Op.like]: `%${search}%` } }
                ]
            })
        };

        const { count, rows: notifications } = await models.notification.findAndCountAll({
            where: whereClause,
            limit,
            offset,
            order: [['time_create', 'DESC']]
        });

        const totalPages = Math.ceil(count / limit);

        let listHtml = `
            <div id="filter-container" class="filter-container" style="display: block;">
                <form id="notification-search-form" class="search-form" onsubmit="handleNotificationSearch(event)">
                    <div class="filter-controls">
                        <div class="form-group">
                            <label for="search_notification">Search</label>
                            <input type="text" name="search" id="search_notification" placeholder="Search" value="${esc(search)}">
                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>

            <div class="message-list-container">
                ${notifications.length === 0 ? '<p>No notifications found.</p>' : notifications.map(notification => `
                    <div class="message-item ${notification.is_read ? 'read' : 'unread'}">
                        <span class="message-status-indicator"></span>
                        <div class="notification-header">
                            <div class="message-link-wrapper">
                                <a href="#notification?notificationId=${notification.notification_id}" class="message-link">
                                    <span class="message-subject">${esc(notification.subject)}</span>
                                </a>
                                <span class="message-time">${esc(new Date(notification.time_create).toLocaleString())}</span>
                                ${notification.is_read ? `
                                    <button class="btn btn-sm btn-secondary" onclick="markNotificationAsUnread('${esc(notification.notification_id)}', 'list')">Mark as Unread</button>
                                    <button class="btn btn-sm btn-danger" onclick="handleNotificationDelete('${esc(notification.notification_id)}')">Delete</button>
                                ` : ''}
                            </div>
                        </div>
                        <div class="message-content">
                            ${esc(notification.content.substring(0, 150))}${notification.content.length > 150 ? '...' : ''}
                        </div>
                    </div>
                `).join('')}
            </div>`;

        // Pagination
        if (totalPages > 1) {
            listHtml += `<div class="pagination-container"><span>Page ${page} of ${totalPages} (Total: ${count})</span><div>`;
            const searchQuery = search ? `&search=${encodeURIComponent(search)}` : '';
            if (page > 1) {
                listHtml += `<a href="#notification?page=${page - 1}${searchQuery}" class="btn btn-secondary">Previous</a>`;
            }
            
            // Simplified pagination links
            for (let i = 1; i <= totalPages; i++) {
                 if (i === page || (i >= page - 2 && i <= page + 2) || i === 1 || i === totalPages) {
                    if (listHtml.slice(-1) !== '>' && i > page + 2 && i < totalPages) listHtml += `<span class="pagination-ellipsis">...</span>`;
                    listHtml += `<a href="#notification?page=${i}${searchQuery}" class="btn ${i == page ? 'btn-primary' : 'btn-secondary'}">${i}</a>`;
                 }
            }

            if (page < totalPages) {
                listHtml += `<a href="#notification?page=${page + 1}${searchQuery}" class="btn btn-secondary">Next</a>`;
            }
            listHtml += `</div></div>`;
        }

        res.send(listHtml);

    } catch (error) {
        console.error(`Notification GET failed:`, error);
        res.status(500).send('An internal server error occurred.');
    }
});

module.exports = router;
