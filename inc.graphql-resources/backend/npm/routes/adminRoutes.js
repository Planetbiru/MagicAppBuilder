const express = require('express');
const { Op } = require('sequelize');
const { v4: uuidv4 } = require('uuid');
const crypto = require('crypto');
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

// --- POST Handler for all admin actions ---
router.post('/admin', conditionalAuth, upload.none(), async (req, res) => {
    const { action, adminId } = req.body;

    try {
        switch (action) {
            case 'create': {
                const { name, username, email, admin_level_id, password } = req.body;
                const active = req.body.active ? 1 : 0;

                if (!password) {
                    return res.status(400).json({ success: false, message: 'Password is required.' });
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
                return res.json({ success: true, message: 'Admin created successfully.' });
            }

            case 'update': {
                if (!adminId) return res.status(400).json({ success: false, message: 'Admin ID is required.' });
                
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
                return res.json({ success: true, message: 'Admin updated successfully.' });
            }

            case 'toggle_active': {
                if (!adminId) return res.status(400).json({ success: false, message: 'Admin ID is required.' });
                if (adminId === req.user.admin_id) {
                    return res.status(403).json({ success: false, message: 'You cannot deactivate your own account.' });
                }
                const admin = await models.admin.findByPk(adminId);
                if (!admin) return res.status(404).json({ success: false, message: 'Admin not found.' });

                await admin.update({ active: !admin.active });
                return res.json({ success: true, message: 'Admin status updated successfully.' });
            }

            case 'change_password': {
                if (!adminId) return res.status(400).json({ success: false, message: 'Admin ID is required.' });
                const { password } = req.body;
                if (!password) return res.status(400).json({ success: false, message: 'Password is required.' });

                const hash1 = crypto.createHash('sha1').update(password).digest('hex');
                const hashedPassword = crypto.createHash('sha1').update(hash1).digest('hex');

                await models.admin.update({ password: hashedPassword }, { where: { admin_id: adminId } });
                return res.json({ success: true, message: 'Password updated successfully.' });
            }

            case 'delete': {
                if (!adminId) return res.status(400).json({ success: false, message: 'Admin ID is required.' });
                if (adminId === req.user.admin_id) {
                    return res.status(403).json({ success: false, message: 'You cannot delete your own account.' });
                }
                await models.admin.destroy({ where: { admin_id: adminId } });
                return res.json({ success: true, message: 'Admin deleted successfully.' });
            }

            default:
                return res.status(400).json({ success: false, message: 'Invalid action specified.' });
        }
    } catch (error) {
        console.error(`Admin POST action '${action}' failed:`, error);
        res.status(500).json({ success: false, message: error.message || 'An internal server error occurred.' });
    }
});

// --- GET Handler for rendering different admin views ---
router.get('/admin', conditionalAuth, async (req, res) => {
    const { view = 'list', adminId, search = '', page = 1 } = req.query;

    try {
        // --- Create or Edit View ---
        if (view === 'create' || (view === 'edit' && adminId)) {
            const adminLevels = await models.admin_level.findAll({ where: { active: 1 }, order: [['sort_order', 'ASC']] });
            let admin = null;
            if (view === 'edit') {
                admin = await models.admin.findByPk(adminId);
            }

            const formTitle = view === 'create' ? 'Add New Admin' : 'Edit Admin';
            let html = `
                <div class="back-controls"><a href="#admin" class="btn btn-secondary">Back to List</a></div>
                <div class="table-container detail-view">
                    <h3>${formTitle}</h3>
                    <form id="admin-form" class="form-group" onsubmit="handleAdminSave(event, '${esc(adminId || '')}'); return false;">
                        <table class="table table-borderless">
                            <tr><td>Name</td><td><input type="text" name="name" value="${esc(admin?.name || '')}" required autocomplete="off"></td></tr>
                            <tr><td>Username</td><td><input type="text" name="username" value="${esc(admin?.username || '')}" required autocomplete="off"></td></tr>
                            <tr><td>Email</td><td><input type="email" name="email" value="${esc(admin?.email || '')}" required autocomplete="off"></td></tr>
                            ${view === 'create' ? '<tr><td>Password</td><td><input type="password" name="password" required autocomplete="new-password"></td></tr>' : ''}
                            <tr>
                                <td>Admin Level</td>
                                <td>
                                    <select name="admin_level_id" required>
                                        <option value="">Select an option...</option>
                                        ${adminLevels.map(level => `<option value="${level.admin_level_id}" ${admin?.admin_level_id === level.admin_level_id ? 'selected' : ''}>${esc(level.name)}</option>`).join('')}
                                    </select>
                                </td>
                            </tr>
                            <tr><td>Active</td><td><input type="checkbox" name="active" ${ (admin?.active || view === 'create') ? 'checked' : ''}></td></tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button type="submit" class="btn btn-success">Save</button>
                                    <a href="#admin" class="btn btn-secondary">Cancel</a>
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
                <div class="back-controls"><a href="#admin?view=detail&adminId=${esc(adminId)}" class="btn btn-secondary">Back to Detail</a></div>
                <div class="table-container detail-view">
                    <h3>Change Password</h3>
                    <form id="change-password-form" class="form-group" onsubmit="handleAdminChangePassword(event, '${esc(adminId)}'); return false;">
                        <table class="table table-borderless">
                            <tr><td>New Password</td><td><input type="password" name="password" required autocomplete="new-password"></td></tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button type="submit" class="btn btn-success">Update</button>
                                    <a href="#admin?view=detail&adminId=${esc(adminId)}" class="btn btn-secondary">Cancel</a>
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

            if (!admin) return res.status(404).send('<p>Admin not found</p>');

            let html = `
                <div class="back-controls">
                    <a href="#admin" class="btn btn-secondary">Back to List</a>
                    <a href="#admin?view=edit&adminId=${esc(admin.admin_id)}" class="btn btn-primary">Edit</a>
                    ${admin.admin_id !== req.user.admin_id ? `
                        <a href="#admin?view=change-password&adminId=${esc(admin.admin_id)}" class="btn btn-warning">Change Password</a>
                        <button class="btn ${admin.active ? 'btn-warning' : 'btn-success'}" onclick="handleAdminToggleActive('${esc(admin.admin_id)}', ${admin.active})">
                            ${admin.active ? 'Deactivate' : 'Activate'}
                        </button>
                        <button class="btn btn-danger" onclick="handleAdminDelete('${esc(admin.admin_id)}')">Delete</button>
                    ` : ''}
                </div>
                <div class="table-container detail-view">
                    <table class="table">
                        <tbody>
                            <tr><td><strong>Admin ID</strong></td><td>${esc(admin.admin_id)}</td></tr>
                            <tr><td><strong>Name</strong></td><td>${esc(admin.name)}</td></tr>
                            <tr><td><strong>Username</strong></td><td>${esc(admin.username)}</td></tr>
                            <tr><td><strong>Email</strong></td><td>${esc(admin.email)}</td></tr>
                            <tr><td><strong>Admin Level</strong></td><td>${esc(admin.admin_level?.name)}</td></tr>
                            <tr><td><strong>Status</strong></td><td>${admin.active ? 'Active' : 'Inactive'}</td></tr>
                            <tr><td><strong>Time Create</strong></td><td>${esc(admin.time_create)}</td></tr>
                            <tr><td><strong>Time Edit</strong></td><td>${esc(admin.time_edit)}</td></tr>
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
            <div id="filter-container" class="filter-container" style="display: block;">
                <form id="admin-search-form" class="search-form" onsubmit="handleAdminSearch(event)">
                    <div class="filter-controls">
                        <div class="form-group">
                            <label for="username">Name</label>
                            <input type="text" name="search" id="username" placeholder="Name" value="${esc(search)}">
                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="#admin?view=create" class="btn btn-primary">Add New Admin</a>
                    </div>
                </form>
            </div>
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th><th>Username</th><th>Email</th><th>Admin Level</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${admins.length > 0 ? admins.map(admin => `
                            <tr class="${admin.active ? '' : 'inactive'}">
                                <td>${esc(admin.name)}</td>
                                <td>${esc(admin.username)}</td>
                                <td>${esc(admin.email)}</td>
                                <td>${esc(admin.admin_level?.name)}</td>
                                <td>${admin.active ? 'Active' : 'Inactive'}</td>
                                <td class="actions">
                                    <a href="#admin?view=detail&adminId=${admin.admin_id}" class="btn btn-sm btn-info">View</a>
                                    <a href="#admin?view=edit&adminId=${admin.admin_id}" class="btn btn-sm btn-primary">Edit</a>
                                    ${admin.admin_id !== req.user.admin_id ? `
                                        <a href="#admin?view=change-password&adminId=${admin.admin_id}" class="btn btn-sm btn-warning">Change Password</a>
                                        <button class="btn btn-sm ${admin.active ? 'btn-warning' : 'btn-success'}" onclick="handleAdminToggleActive('${admin.admin_id}', ${admin.active})">
                                            ${admin.active ? 'Deactivate' : 'Activate'}
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="handleAdminDelete('${admin.admin_id}')">Delete</button>
                                    ` : ''}
                                </td>
                            </tr>
                        `).join('') : '<tr><td colspan="6">No admins found.</td></tr>'}
                    </tbody>
                </table>
            </div>`;

        // Pagination
        if (totalPages > 1) {
            listHtml += `<div class="pagination-container"><span>Page ${page} of ${totalPages} (Total: ${count})</span><div>`;
            if (page > 1) {
                listHtml += `<a href="#admin?page=${page - 1}&search=${esc(search)}" class="btn btn-secondary">Previous</a>`;
            }
            // Simplified pagination links for brevity
            for (let i = 1; i <= totalPages; i++) {
                 if (i === page || (i >= page - 2 && i <= page + 2)) {
                    listHtml += `<a href="#admin?page=${i}&search=${esc(search)}" class="btn ${i === page ? 'btn-primary' : 'btn-secondary'}">${i}</a>`;
                 }
            }
            if (page < totalPages) {
                listHtml += `<a href="#admin?page=${page + 1}&search=${esc(search)}" class="btn btn-secondary">Next</a>`;
            }
            listHtml += `</div></div>`;
        }

        res.send(listHtml);

    } catch (error) {
        console.error(`Admin GET view '${view}' failed:`, error);
        res.status(500).send('An internal server error occurred.');
    }
});

module.exports = router;