const express = require('express');
const { Op } = require('sequelize');
const { models } = require('../config/database');
const conditionalAuth = require('../middleware/conditionalAuth');
const { getTranslator, esc } = require('../utils/i18n'); // Import getTranslator and esc
const multer = require('multer');

const router = express.Router();
const upload = multer();

// --- POST Handler for message actions ---
router.post('/message', conditionalAuth, upload.none(), async (req, res) => {
    const { action, messageId } = req.body;
    const currentAdminId = req.user.admin_id;

    const t = getTranslator(req); // Get translator function

    if (!messageId) {
        return res.status(400).json({ success: false, message: t('message_id_required') });
    }

    try {
        if (action === 'mark_as_unread') {
            await models.message.update(
                { is_read: false, time_read: null },
                { where: { message_id: messageId, receiver_id: currentAdminId } }
            ); 
            return res.json({ success: true, message: t('message_marked_as_unread') });
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
            return res.json({ success: true, message: t('message_deleted_successfully') });
        } else {
            return res.status(400).json({ success: false, message: t('invalid_action_specified') });
        }
    } catch (error) {
        console.error(`Message POST action '${action}' failed:`, error);
        res.status(500).json({ success: false, message: error.message || t('unexpected_error_occurred') });
    }
});

// --- GET Handler for rendering message views ---
router.get('/message', conditionalAuth, async (req, res) => {
    const { messageId, search = '', page = 1 } = req.query;
    const currentAdminId = req.user.admin_id;

    const t = getTranslator(req); // Get translator function

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
                return res.status(404).send(`<div class="table-container detail-view">${t('no_message')}</div>`);
            }

            // Mark as read if current user is receiver and it's unread
            if (message.receiver_id === currentAdminId && !message.is_read) {
                await message.update({ is_read: true, time_read: new Date() });
                message.is_read = true; // Update in-memory object as well
            }

            const html = `
                <div class="back-controls">
                    <button id="back-to-list" class="btn btn-secondary" onclick="backToList('message')">${t('back_to_list')}</button>
                    ${message.receiver_id === currentAdminId && message.is_read ? `
                        <button class="btn btn-primary" onclick="markMessageAsUnread('${esc(message.message_id)}', 'detail')">${t('mark_as_unread')}</button>
                        <button class="btn btn-danger" onclick="handleMessageDelete('${esc(message.message_id)}')">${t('delete')}</button>
                    ` : ''}
                </div>
                <div class="message-container">
                    <div class="message-header">
                        <h3>${esc(message.subject)}</h3>
                        <div class="message-meta">
                            <div><strong>${t('from')}:</strong> ${esc(message.sender?.name || t('system'))}</div>
                            <div><strong>${t('to')}:</strong> ${esc(message.receiver?.name || t('system'))}</div>
                            <div><strong>${t('time')}:</strong> ${esc(new Date(message.time_create).toLocaleString())}</div>
                            <div><strong>${t('status')}:</strong> 
                                ${message.is_read ? `<span class="status-read">${t('read_at')} ${esc(new Date(message.time_read).toLocaleString())}</span>` : `<span class="status-unread">${t('unread')}</span>`}
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
            <div id="filter-container" class="filter-container">
                <form id="message-search-form" class="search-form" onsubmit="handleMessageSearch(event)">
                    <div class="filter-controls">
                        <div class="form-group"><label for="search_message">${t('search')}</label><input type="text" name="search" id="search_message" placeholder="${t('search')}" value="${esc(search)}"></div>
                        <button type="submit" class="btn btn-primary">${t('search')}</button>
                    </div>
                </form>
            </div>
            <div class="message-list-container">
                ${messages.length === 0 ? `<p>${t('no_message')}</p>` : messages.map(message => `
                    <div class="message-item ${message.is_read ? 'read' : 'unread'}">
                        <span class="message-status-indicator"></span>
                        <div class="message-header">
                            <div class="message-link-wrapper">
                                <a href="#message?messageId=${message.message_id}" class="message-link">
                                    <span class="message-sender">${esc(message.sender?.name || t('system'))}</span>
                                    <span class="message-subject">${esc(message.subject)}</span>
                                </a>
                                <span class="message-time">${esc(new Date(message.time_create).toLocaleString())}</span>
                                ${message.receiver_id === currentAdminId && message.is_read ? ` 
                                    <button class="btn btn-sm btn-secondary" onclick="markMessageAsUnread('${esc(message.message_id)}', 'list')">${t('mark_as_unread')}</button>
                                    <button class="btn btn-sm btn-danger" onclick="handleMessageDelete('${esc(message.message_id)}')">${t('delete')}</button>
                                ` : ''}
                            </div>
                        </div>
                        <div class="message-content">${esc(message.content.substring(0, 150))}${message.content.length > 150 ? '...' : ''}</div>
                    </div>
                `).join('')}
            </div>`;

        // Pagination
        if (totalPages > 1) {
            listHtml += `<div class="pagination-container"><span>${t('page_of', page, totalPages, count)}</span><div>`;
            if (page > 1) listHtml += `<a href="#message?page=${page - 1}&search=${esc(search)}" class="btn btn-secondary">${t('previous')}</a>`;
            for (let i = 1; i <= totalPages; i++) {
                if (i === page || (i >= page - 2 && i <= page + 2)) {
                    listHtml += `<a href="#message?page=${i}&search=${esc(search)}" class="btn ${i === page ? 'btn-primary' : 'btn-secondary'}">${i}</a>`;
                }
            }
            if (page < totalPages) listHtml += `<a href="#message?page=${page + 1}&search=${esc(search)}" class="btn btn-secondary">${t('next')}</a>`;
            listHtml += `</div></div>`;
        }

        res.send(listHtml);

    } catch (error) {
        const t = getTranslator(req);
        console.error(t('failed_to_fetch_details'), error);
        res.status(500).send(t('unexpected_error_occurred'));
    }
});

module.exports = router;