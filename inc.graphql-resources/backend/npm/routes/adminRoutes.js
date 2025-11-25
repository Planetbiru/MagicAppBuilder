const express = require('express');
const { Op } = require('sequelize');
const { v4: uuidv4 } = require('uuid');
const crypto = require('crypto');
const { models } = require('../config/database');
const { getTranslator, esc } = require('../utils/i18n'); // Import getTranslator and esc
const conditionalAuth = require('../middleware/conditionalAuth');
const multer = require('multer');

const router = express.Router();
const upload = multer();

// --- POST Handler for all admin actions ---
router.post('/admin', conditionalAuth, upload.none(), async (req, res) => {
    const { action, adminId } = req.body;

    const t = getTranslator(req); // Get translator function

    try {
        switch (action) {
            case 'create': {
                const { name, username, email, admin_level_id, password } = req.body;
                const active = req.body.active ? 1 : 0;

                if (!password) {
                    return res.status(400).json({ success: false, message: t('password_is_required') });
                }
                const hash1 = crypto.createHash('sha1').update(password).digest('hex');
                const hashedPassword = crypto.createHash('sha1').update(hash1).digest('hex');

                await models.admin.create({
                    admin_id: uuidv4(),
                    name,
                    username,
                    email,
                    password: hashedPassword,
                    admin_level_id,
                    active,
                    time_create: new Date(),
                    admin_create: req.user.admin_id,
                    ip_create: req.ip
                });
                return res.json({ success: true, message: t('admin_created_successfully') });
            }

            case 'update': {
                if (!adminId) return res.status(400).json({ success: false, message: t('admin_id_required') });

                let { name, username, email, admin_level_id } = req.body;
                let active = req.body.active ? 1 : 0;

                // Prevent user from changing their own level or active status
                if (adminId === req.user.admin_id) {
                    active = 1; // Self must be active
                    admin_level_id = req.user.admin_level_id; // Self cannot change own level
                }

                await models.admin.update({
                    name,
                    username,
                    email,
                    admin_level_id,
                    active,
                    time_edit: new Date(),
                    admin_edit: req.user.admin_id,
                    ip_edit: req.ip
                }, { where: { admin_id: adminId } });
                return res.json({ success: true, message: t('admin_updated_successfully') });
            }

            case 'toggle_active': {
                if (!adminId) return res.status(400).json({ success: false, message: t('admin_id_required') });
                if (adminId === req.user.admin_id) {
                    return res.status(403).json({ success: false, message: t('cannot_deactivate_self') });
                }
                const admin = await models.admin.findByPk(adminId);
                if (!admin) return res.status(404).json({ success: false, message: t('admin_not_found') });

                await admin.update({ active: !admin.active });
                return res.json({ success: true, message: t('admin_status_updated') });
            }

            case 'change_password': {
                if (!adminId) return res.status(400).json({ success: false, message: t('admin_id_required') });
                const { password } = req.body;
                if (!password) return res.status(400).json({ success: false, message: t('password_is_required') });

                const hash1 = crypto.createHash('sha1').update(password).digest('hex');
                const hashedPassword = crypto.createHash('sha1').update(hash1).digest('hex');

                await models.admin.update({ password: hashedPassword }, { where: { admin_id: adminId } });
                return res.json({ success: true, message: t('password_updated_successfully') });
            }

            case 'delete': {
                if (!adminId) return res.status(400).json({ success: false, message: t('admin_id_required') });
                if (adminId === req.user.admin_id) {
                    return res.status(403).json({ success: false, message: t('cannot_delete_self') });
                }
                await models.admin.destroy({ where: { admin_id: adminId } });
                return res.json({ success: true, message: t('admin_deleted_successfully') });
            }

            default:
                return res.status(400).json({ success: false, message: t('invalid_action_specified') });
        }
    } catch (error) {
        console.error(`Admin POST action '${action}' failed:`, error);
        res.status(500).json({ success: false, message: error.message || t('unexpected_error_occurred') });
    }
});

// --- GET Handler for rendering different admin views ---
router.get('/admin', conditionalAuth, async (req, res) => {
    const { view = 'list', adminId, search = '', page = 1 } = req.query;

    const t = getTranslator(req); // Get translator function

    try {
        // --- Create or Edit View ---
        if (view === 'create' || (view === 'edit' && adminId)) {
            const adminLevels = await models.admin_level.findAll({ where: { active: 1 }, order: [['sort_order', 'ASC']] });
            let admin = null;
            if (view === 'edit') {
                admin = await models.admin.findByPk(adminId);
                if (!admin) return res.status(404).send(`<p>${t('admin_not_found')}</p>`); // Translate not found message
            }

            const formTitle = view === 'create' ? t('add_new_admin') : t('edit_admin');
            let html = `
                <div class="back-controls"><a href="#admin" class="btn btn-secondary">${t('back_to_list')}</a></div>
                <div class="table-container detail-view">
                    <h3>${formTitle}</h3>
                    <form id="admin-form" class="form-group" onsubmit="handleAdminSave(event, '${esc(adminId || '')}'); return false;">
                        <table class="table table-borderless">
                            <tr><td>${t('name')}</td><td><input type="text" name="name" value="${esc(admin?.name || '')}" required autocomplete="off"></td></tr>
                            <tr><td>${t('username')}</td><td><input type="text" name="username" value="${esc(admin?.username || '')}" required autocomplete="off"></td></tr>
                            <tr><td>${t('email')}</td><td><input type="email" name="email" value="${esc(admin?.email || '')}" required autocomplete="off"></td></tr>
                            ${view === 'create' ? `<tr><td>${t('password')}</td><td><input type="password" name="password" required autocomplete="new-password"></td></tr>` : ''}
                            <tr>
                                <td>${t('admin_level')}</td>
                                <td>
                                    <select name="admin_level_id" required>
                                        <option value="">${t('select_option')}</option>
                                        ${adminLevels.map(level => `<option value="${level.admin_level_id}" ${admin?.admin_level_id === level.admin_level_id ? 'selected' : ''}>${esc(level.name)}</option>`).join('')}
                                    </select>
                                </td>
                            </tr>
                            <tr><td>${t('active')}</td><td><input type="checkbox" name="active" ${ (admin?.active || view === 'create') ? 'checked' : ''}></td></tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button type="submit" class="btn btn-success">${t('save')}</button>
                                    <a href="#admin" class="btn btn-secondary">${t('cancel')}</a>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>`;
            return res.send(html);
        }

        // --- Change Password View ---
        if (view === 'change-password' && adminId) {
            let html = `
                <div class="back-controls"><a href="#admin?view=detail&adminId=${esc(adminId)}" class="btn btn-secondary">${t('back_to_detail')}</a></div>
                <div class="table-container detail-view">
                    <h3>${t('update_password')}</h3>
                    <form id="change-password-form" class="form-group" onsubmit="handleAdminChangePassword(event, '${esc(adminId)}'); return false;">
                        <table class="table table-borderless">
                            <tr><td>${t('new_password')}</td><td><input type="password" name="password" required autocomplete="new-password"></td></tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button type="submit" class="btn btn-success">${t('update')}</button>
                                    <a href="#admin?view=detail&adminId=${esc(adminId)}" class="btn btn-secondary">${t('cancel')}</a>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>`;
            return res.send(html);
        }

        // --- Detail View ---
        if (view === 'detail' && adminId) {
            const admin = await models.admin.findOne({
                where: { admin_id: adminId },
                include: { model: models.admin_level, as: 'admin_level' }
            });

            if (!admin) return res.status(404).send(`<p>${t('admin_not_found')}</p>`);

            let html = `
                <div class="back-controls">
                    <a href="#admin" class="btn btn-secondary">${t('back_to_list')}</a>
                    <a href="#admin?view=edit&adminId=${esc(admin.admin_id)}" class="btn btn-primary">${t('edit')}</a>
                    ${admin.admin_id !== req.user.admin_id ? `
                        <a href="#admin?view=change-password&adminId=${esc(admin.admin_id)}" class="btn btn-warning">${t('update_password')}</a>
                        <button class="btn ${admin.active ? 'btn-warning' : 'btn-success'}" onclick="handleAdminToggleActive('${esc(admin.admin_id)}', ${admin.active})">
                            ${admin.active ? t('deactivate') : t('activate')}
                        </button>
                        <button class="btn btn-danger" onclick="handleAdminDelete('${esc(admin.admin_id)}')">${t('delete')}</button>
                    ` : ''}
                </div>
                <div class="table-container detail-view">
                    <table class="table">
                        <tbody>
                            <tr><td><strong>${t('admin_id')}</strong></td><td>${esc(admin.admin_id)}</td></tr>
                            <tr><td><strong>${t('name')}</strong></td><td>${esc(admin.name)}</td></tr>
                            <tr><td><strong>${t('username')}</strong></td><td>${esc(admin.username)}</td></tr>
                            <tr><td><strong>${t('email')}</strong></td><td>${esc(admin.email)}</td></tr>
                            <tr><td><strong>${t('admin_level')}</strong></td><td>${esc(admin.admin_level?.name)}</td></tr>
                            <tr><td><strong>${t('status')}</strong></td><td>${admin.active ? t('active') : t('inactive')}</td></tr>
                            <tr><td><strong>${t('time_create')}</strong></td><td>${esc(admin.time_create)}</td></tr>
                            <tr><td><strong>${t('time_edit')}</strong></td><td>${esc(admin.time_edit)}</td></tr>
                        </tbody>
                    </table>
                </div>`;
            return res.send(html);
        }

        // --- List View (Default) ---
        const limit = 20;
        const offset = (page - 1) * limit;
        const where = search ? {
            [Op.or]: [
                { name: { [Op.like]: `%${search}%` } },
                { username: { [Op.like]: `%${search}%` } }
            ]
        } : {};

        const { count, rows: admins } = await models.admin.findAndCountAll({
            where,
            limit,
            offset,
            include: { model: models.admin_level, as: 'admin_level' },
            order: [['name', 'ASC']]
        });

        const totalPages = Math.ceil(count / limit);

        let listHtml = `
            <div id="filter-container" class="filter-container">
                <form id="admin-search-form" class="search-form" onsubmit="handleAdminSearch(event)">
                    <div class="filter-controls">
                        <div class="form-group">
                            <label for="username">${t('name')}</label>
                            <input type="text" name="search" id="username" placeholder="${t('name')}" value="${esc(search)}">
                        </div>
                        <button type="submit" class="btn btn-primary">${t('search')}</button>
                        <a href="#admin?view=create" class="btn btn-primary">${t('add_new_admin')}</a>
                    </div>
                </form>
            </div>
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>${t('name')}</th><th>${t('username')}</th><th>${t('email')}</th><th>${t('admin_level')}</th><th>${t('status')}</th><th>${t('actions')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${admins.length > 0 ? admins.map(admin => `
                            <tr class="${admin.active ? '' : 'inactive'}">
                                <td>${esc(admin.name)}</td>
                                <td>${esc(admin.username)}</td>
                                <td>${esc(admin.email)}</td>
                                <td>${esc(admin.admin_level?.name)}</td>
                                <td>${admin.active ? t('active') : t('inactive')}</td>
                                <td class="actions">
                                    <a href="#admin?view=detail&adminId=${admin.admin_id}" class="btn btn-sm btn-info">${t('view')}</a>
                                    <a href="#admin?view=edit&adminId=${admin.admin_id}" class="btn btn-sm btn-primary">${t('edit')}</a>
                                    ${admin.admin_id !== req.user.admin_id ? `
                                        <a href="#admin?view=change-password&adminId=${admin.admin_id}" class="btn btn-sm btn-warning">${t('update_password')}</a>
                                        <button class="btn btn-sm ${admin.active ? 'btn-warning' : 'btn-success'}" onclick="handleAdminToggleActive('${admin.admin_id}', ${admin.active})">
                                            ${admin.active ? t('deactivate') : t('activate')}
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="handleAdminDelete('${admin.admin_id}')">${t('delete')}</button>
                                    ` : ''}
                                </td>
                            </tr>
                        `).join('') : `<tr><td colspan="6">${t('no_admins_found')}</td></tr>`}
                    </tbody>
                </table>
            </div>`;

        // Pagination
        if (totalPages > 1) {
            listHtml += `<div class="pagination-container"><span>${t('page_of', page, totalPages, count)}</span><div>`;
            if (page > 1) { 
                listHtml += `<a href="#admin?page=${page - 1}&search=${esc(search)}" class="btn btn-secondary">${t('previous')}</a>`;
            } 
            // Simplified pagination links for brevity
            for (let i = 1; i <= totalPages; i++) {
                if (i === page || (i >= page - 2 && i <= page + 2)) {
                    listHtml += `<a href="#admin?page=${i}&search=${esc(search)}" class="btn ${i === page ? 'btn-primary' : 'btn-secondary'}">${i}</a>`;
                }
            }
            if (page < totalPages) { 
                listHtml += `<a href="#admin?page=${page + 1}&search=${esc(search)}" class="btn btn-secondary">${t('next')}</a>`;
            } 
            listHtml += `</div></div>`;
        }

        res.send(listHtml);

    } catch (error) {
        const t = getTranslator(req);
        console.error(t('failed_to_fetch_details'), error); // Generic error message
        res.status(500).send(t('unexpected_error_occurred'));
    }
});

module.exports = router;