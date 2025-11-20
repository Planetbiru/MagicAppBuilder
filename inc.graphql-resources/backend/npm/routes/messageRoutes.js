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

// --- POST Handler for message actions ---
router.post('/message', conditionalAuth, upload.none(), async (req, res) => {
    const { action, messageId } = req.body;
    const currentAdminId = req.user.admin_id;

    if (!messageId) {
        return res.status(400).json({ success: false, message: 'Message ID is required.' });
    }

    try {
        if (action === 'mark_as_unread') {
            await models.message.update(
                { is_read: false, time_read: null },
                { where: { message_id: messageId, receiver_id: currentAdminId } }
            );
            return res.json({ success: true, message: 'Message marked as unread.' });
        } else if (action === 'delete') {
            await models.message.destroy({
                where: {
                    message_id: messageId,
                    [Op.or]: [
                        { sender_id: currentAdminId },
                        { receiver_id: currentAdminId }
                    ]
                }
            });
            return res.json({ success: true, message: 'Message deleted successfully.' });
        } else {
            return res.status(400).json({ success: false, message: 'Invalid action.' });
        }
    } catch (error) {
        console.error(`Message POST action '${action}' failed:`, error);
        res.status(500).json({ success: false, message: error.message || 'An internal server error occurred.' });
    }
});

// --- GET Handler for rendering message views ---
router.get('/message', conditionalAuth, async (req, res) => {
    const { messageId, search = '', page = 1 } = req.query;
    const currentAdminId = req.user.admin_id;

    try {
        // --- Detail View ---
        if (messageId) {
            const message = await models.message.findOne({
                where: {
                    message_id: messageId,
                    [Op.or]: [{ sender_id: currentAdminId }, { receiver_id: currentAdminId }]
                },
                include: [
                    { model: models.admin, as: 'sender' },
                    { model: models.admin, as: 'receiver' }
                ]
            });

            if (!message) {
                return res.status(404).send('<div class="table-container detail-view">Message not found.</div>');
            }

            // Mark as read if current user is receiver and it's unread
            if (message.receiver_id === currentAdminId && !message.is_read) {
                await message.update({ is_read: true, time_read: new Date() });
                message.is_read = true; // Update in-memory object as well
            }

            const html = `
                <div class="back-controls">
                    <button id="back-to-list" class="btn btn-secondary" onclick="backToList('message')">Back to List</button>
                    ${message.receiver_id === currentAdminId && message.is_read ? `
                        <button class="btn btn-primary" onclick="markMessageAsUnread('${esc(message.message_id)}', 'detail')">Mark as Unread</button>
                        <button class="btn btn-danger" onclick="handleMessageDelete('${esc(message.message_id)}')">Delete</button>
                    ` : ''}
                </div>
                <div class="message-container">
                    <div class="message-header">
                        <h3>${esc(message.subject)}</h3>
                        <div class="message-meta">
                            <div><strong>From:</strong> ${esc(message.sender?.name || 'System')}</div>
                            <div><strong>To:</strong> ${esc(message.receiver?.name || 'System')}</div>
                            <div><strong>Time:</strong> ${esc(new Date(message.time_create).toLocaleString())}</div>
                            <div><strong>Status:</strong> 
                                ${message.is_read ? `<span class="status-read">Read at ${esc(new Date(message.time_read).toLocaleString())}</span>` : '<span class="status-unread">Unread</span>'}
                            </div>
                        </div>
                    </div>
                    <div class="message-body">${nl2br(esc(message.content))}</div>
                </div>`;
            
            function nl2br(str) { return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2'); }
            return res.send(html);
        }

        // --- List View (Default) ---
        const limit = 20;
        const offset = (page - 1) * limit;
        const whereClause = {
            [Op.or]: [{ sender_id: currentAdminId }, { receiver_id: currentAdminId }],
            ...(search && {
                [Op.or]: [
                    { subject: { [Op.like]: `%${search}%` } },
                    { content: { [Op.like]: `%${search}%` } },
                    { '$sender.name$': { [Op.like]: `%${search}%` } },
                    { '$receiver.name$': { [Op.like]: `%${search}%` } }
                ]
            })
        };

        const { count, rows: messages } = await models.message.findAndCountAll({
            where: whereClause,
            include: [
                { model: models.admin, as: 'sender', attributes: ['name'] },
                { model: models.admin, as: 'receiver', attributes: ['name'] }
            ],
            limit,
            offset,
            order: [['time_create', 'DESC']]
        });

        const totalPages = Math.ceil(count / limit);

        let listHtml = `
            <div id="filter-container" class="filter-container" style="display: block;">
                <form id="message-search-form" class="search-form" onsubmit="handleMessageSearch(event)">
                    <div class="filter-controls">
                        <div class="form-group"><label for="search_message">Search</label><input type="text" name="search" id="search_message" placeholder="Search" value="${esc(search)}"></div>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
            <div class="message-list-container">
                ${messages.length === 0 ? '<p>No messages found.</p>' : messages.map(message => `
                    <div class="message-item ${message.is_read ? 'read' : 'unread'}">
                        <span class="message-status-indicator"></span>
                        <div class="message-header">
                            <div class="message-link-wrapper">
                                <a href="#message?messageId=${message.message_id}" class="message-link">
                                    <span class="message-sender">${esc(message.sender?.name || 'System')}</span>
                                    <span class="message-subject">${esc(message.subject)}</span>
                                </a>
                                <span class="message-time">${esc(new Date(message.time_create).toLocaleString())}</span>
                                ${message.receiver_id === currentAdminId && message.is_read ? `
                                    <button class="btn btn-sm btn-secondary" onclick="markMessageAsUnread('${esc(message.message_id)}', 'list')">Mark as Unread</button>
                                    <button class="btn btn-sm btn-danger" onclick="handleMessageDelete('${esc(message.message_id)}')">Delete</button>
                                ` : ''}
                            </div>
                        </div>
                        <div class="message-content">${esc(message.content.substring(0, 150))}${message.content.length > 150 ? '...' : ''}</div>
                    </div>
                `).join('')}
            </div>`;

        // Pagination
        if (totalPages > 1) {
            listHtml += `<div class="pagination-container"><span>Page ${page} of ${totalPages} (Total: ${count})</span><div>`;
            if (page > 1) listHtml += `<a href="#message?page=${page - 1}&search=${esc(search)}" class="btn btn-secondary">Previous</a>`;
            for (let i = 1; i <= totalPages; i++) {
                if (i === page || (i >= page - 2 && i <= page + 2)) {
                    listHtml += `<a href="#message?page=${i}&search=${esc(search)}" class="btn ${i == page ? 'btn-primary' : 'btn-secondary'}">${i}</a>`;
                }
            }
            if (page < totalPages) listHtml += `<a href="#message?page=${page + 1}&search=${esc(search)}" class="btn btn-secondary">Next</a>`;
            listHtml += `</div></div>`;
        }

        res.send(listHtml);

    } catch (error) {
        console.error(`Message GET failed:`, error);
        res.status(500).send('An internal server error occurred.');
    }
});

module.exports = router;