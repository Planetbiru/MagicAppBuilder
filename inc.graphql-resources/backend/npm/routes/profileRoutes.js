const express = require('express');
const crypto = require('crypto');
const { models } = require('../config/database');
const { getTranslator, esc } = require('../utils/i18n'); // Import getTranslator and esc
const conditionalAuth = require('../middleware/conditionalAuth');

const router = express.Router();

// GET user profile data (HTML response)
router.get('/user-profile', conditionalAuth, async (req, res) => {
    try {
        // Fetch user with their admin level
        const admin = await models.admin.findOne({
            where: { username: req.user.username },
            include: [{
                model: models.admin_level,
                as: 'admin_level'
            }]
        });

        const t = getTranslator(req); // Get translator function

        if (!admin) {
            return res.status(404).send(t('admin_not_found')); // Use translation
        }

        const adminData = admin.get({ plain: true });
        const adminLevelName = adminData.admin_level ? adminData.admin_level.name : '';

        let html;

        // The `esc` function is now imported from `utils/i18n.js`
        // The `t` function is used for translations

        if (req.query.action === 'update') {
            // Render the update form
            html = `
            <div class="table-container detail-view">
                <form id="profile-update-form" class="form-group" onsubmit="handleProfileUpdate(event); return false;">
                    <table class="table table-borderless">
                        <tr><td>admin_id</td><td><input type="text" name="admin_id" class="form-control" value="${esc(adminData.admin_id)}" autocomplete="off" readonly></td></tr>
                        <tr><td>${t('name')}</td><td><input type="text" name="name" class="form-control" value="${esc(adminData.name)}"></td></tr>
                        <tr><td>${t('username')}</td><td><input type="text" name="username" class="form-control" value="${esc(adminData.username)}" autocomplete="off" readonly></td></tr>
                        <tr>
                            <td>${t('gender')}</td>
                            <td>
                                <select name="gender" class="form-control">
                                    <option value="M" ${adminData.gender === 'M' ? 'selected' : ''}>${t('male')}</option>
                                    <option value="F" ${adminData.gender === 'F' ? 'selected' : ''}>${t('female')}</option>
                                </select>
                            </td>
                        </tr>
                        <tr><td>${t('birthday')}</td><td><input type="date" name="birth_day" class="form-control" value="${esc(adminData.birth_day)}" autocomplete="off"></td></tr>
                        <tr><td>${t('phone')}</td><td><input type="text" name="phone" class="form-control" value="${esc(adminData.phone)}" autocomplete="off"></td></tr>
                        <tr><td>${t('email')}</td><td><input type="email" name="email" class="form-control" value="${esc(adminData.email)}" autocomplete="off"></td></tr>
                        <tr>
                            <td></td>
                            <td>
                                <button type="submit" class="btn btn-success">${t('update')}</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.hash='#user-profile'">${t('cancel')}</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>`;
        } else {
            // Render the display view
            const formattedDate = adminData.last_reset_password ? new Date(adminData.last_reset_password).toLocaleString() : '';
            html = `
            <div class="table-container detail-view">
                <form action="" class="form-group">
                    <table class="table table-borderless">
                        <tr><td>${t('admin_id')}</td><td>${esc(adminData.admin_id)}</td></tr>
                        <tr><td>${t('name')}</td><td>${esc(adminData.name)}</td></tr>
                        <tr><td>${t('username')}</td><td>${esc(adminData.username)}</td></tr>
                        <tr><td>${t('gender')}</td><td>${adminData.gender === 'M' ? t('male') : t('female')}</td></tr>
                        <tr><td>${t('birthday')}</td><td>${esc(adminData.birth_day)}</td></tr>
                        <tr><td>${t('phone')}</td><td>${esc(adminData.phone)}</td></tr>
                        <tr><td>${t('email')}</td><td>${esc(adminData.email)}</td></tr>
                        <tr><td>${t('admin_level')}</td><td>${esc(adminLevelName)}</td></tr>
                        <tr><td>${t('language_id')}</td><td>${esc(adminData.language_id)}</td></tr>
                        <tr><td>${t('last_reset_password')}</td><td>${esc(formattedDate)}</td></tr>
                        <tr><td>${t('blocked')}</td><td>${adminData.blocked ? t('yes') : t('no')}</td></tr>
                        <tr><td>${t('active')}</td><td>${adminData.active ? t('yes') : t('no')}</td></tr>
                        <tr>
                            <td></td>
                            <td>
                                <button type="button" class="btn btn-primary" onclick="window.location.hash='#user-profile?action=update'">${t('edit')}</button>
                                <button type="button" class="btn btn-warning" onclick="window.location.hash='#update-password'">${t('update_password')}</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>`;
        }

        res.setHeader('Content-Type', 'text/html');
        res.send(html);

    } catch (error) {
        const t = getTranslator(req);
        console.error(t('failed_to_fetch_details'), error);
        res.status(500).send(t('failed_to_fetch_details'));
    }
});

// GET update password form (HTML response)
router.get('/update-password', conditionalAuth, (req, res) => {
    const html = `
    <div class="table-container detail-view">
        <form id="password-update-form" class="form-group" onsubmit="handlePasswordUpdate(event); return false">
            <table class="table table-borderless">
                <tr>
                    <td>${t('current_password')}</td>
                    <td><input type="password" name="current_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td>${t('new_password')}</td>
                    <td><input type="password" name="new_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td>${t('confirm_password')}</td>
                    <td><input type="password" name="confirm_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" class="btn btn-success">${t('update')}</button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.hash='#user-profile'">${t('cancel')}</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>`;
    res.setHeader('Content-Type', 'text/html');
    res.send(html);
});

// POST to update user profile
router.post('/user-profile', conditionalAuth, async (req, res) => {
    if (!req.user) {
        const t = getTranslator(req);
        return res.status(401).json({ success: false, message: t('session_expired') }); // Use translation
    }

    const { name, email, gender, birth_day, phone } = req.body;

    try {
        await req.user.update({
            name, email, gender, birth_day, phone
        });
        const t = getTranslator(req);
        res.json({ success: true, message: t('profile_updated_successfully') });
    } catch (error) {
        const t = getTranslator(req);
        console.error(t('failed_to_update_profile', error.message), error);
        res.status(500).json({ success: false, message: t('failed_to_update_profile', error.message) });
    }
});

// POST to update user password
router.post('/update-password', conditionalAuth, async (req, res) => {
    if (!req.user) {
        const t = getTranslator(req);
        return res.status(401).json({ success: false, message: t('session_expired') });
    }

    const { current_password, new_password, confirm_password } = req.body;
    const t = getTranslator(req);

    if (!new_password || !current_password) {
        return res.status(400).json({ success: false, message: t('new_password_required') });
    } else if (new_password !== confirm_password) {
        return res.status(400).json({ success: false, message: t('password_mismatch') });
    }

    try {
        const hash1 = crypto.createHash('sha1').update(current_password).digest('hex');
        const currentPasswordHash = crypto.createHash('sha1').update(hash1).digest('hex');

        if (currentPasswordHash !== req.user.password) {
            return res.status(401).json({ success: false, message: t('incorrect_current_password') });
        }

        const newHash1 = crypto.createHash('sha1').update(new_password).digest('hex');
        const newPasswordHash = crypto.createHash('sha1').update(newHash1).digest('hex');

        await req.user.update({
            password: newPasswordHash,
            last_reset_password: new Date()
        });

        res.json({ success: true, message: t('password_updated_successfully') });
    } catch (error) {
        console.error(t('failed_to_update_password'), error);
        res.status(500).json({ success: false, message: t('failed_to_update_password') });
    }
});

module.exports = router;
