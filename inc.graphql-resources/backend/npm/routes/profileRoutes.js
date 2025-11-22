const express = require('express');
const crypto = require('crypto');
const { models } = require('../config/database');
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

        if (!admin) {
            return res.status(404).send('User not found.');
        }

        // Sanitize data to prevent XSS
        const esc = (str) => {
            if (str === null || typeof str === 'undefined') return '';
            return String(str).replace(/[&<>"']/g, (match) => {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match];
            });
        };

        const adminData = admin.get({ plain: true });
        const adminLevelName = adminData.admin_level ? adminData.admin_level.name : '';

        let html;

        if (req.query.action === 'update') {
            // Render the update form
            html = `
            <div class="table-container detail-view">
                <form id="profile-update-form" class="form-group" onsubmit="handleProfileUpdate(event); return false;">
                    <table class="table table-borderless">
                        <tr><td>admin_id</td><td><input type="text" name="admin_id" class="form-control" value="${esc(adminData.admin_id)}" autocomplete="off" readonly></td></tr>
                        <tr><td>name</td><td><input type="text" name="name" class="form-control" value="${esc(adminData.name)}"></td></tr>
                        <tr><td>username</td><td><input type="text" name="username" class="form-control" value="${esc(adminData.username)}" autocomplete="off" readonly></td></tr>
                        <tr>
                            <td>gender</td>
                            <td>
                                <select name="gender" class="form-control">
                                    <option value="M" ${adminData.gender === 'M' ? 'selected' : ''}>Male</option>
                                    <option value="F" ${adminData.gender === 'F' ? 'selected' : ''}>Female</option>
                                </select>
                            </td>
                        </tr>
                        <tr><td>birthday</td><td><input type="date" name="birth_day" class="form-control" value="${esc(adminData.birth_day)}" autocomplete="off"></td></tr>
                        <tr><td>phone</td><td><input type="text" name="phone" class="form-control" value="${esc(adminData.phone)}" autocomplete="off"></td></tr>
                        <tr><td>email</td><td><input type="email" name="email" class="form-control" value="${esc(adminData.email)}" autocomplete="off"></td></tr>
                        <tr>
                            <td></td>
                            <td>
                                <button type="submit" class="btn btn-success">Update</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.hash='#user-profile'">Cancel</button>
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
                        <tr><td>admin_id</td><td>${esc(adminData.admin_id)}</td></tr>
                        <tr><td>name</td><td>${esc(adminData.name)}</td></tr>
                        <tr><td>username</td><td>${esc(adminData.username)}</td></tr>
                        <tr><td>gender</td><td>${adminData.gender === 'M' ? 'Male' : 'Female'}</td></tr>
                        <tr><td>birthday</td><td>${esc(adminData.birth_day)}</td></tr>
                        <tr><td>phone</td><td>${esc(adminData.phone)}</td></tr>
                        <tr><td>email</td><td>${esc(adminData.email)}</td></tr>
                        <tr><td>admin_level_id</td><td>${esc(adminLevelName)}</td></tr>
                        <tr><td>language_id</td><td>${esc(adminData.language_id)}</td></tr>
                        <tr><td>last_reset_password</td><td>${esc(formattedDate)}</td></tr>
                        <tr><td>blocked</td><td>${adminData.blocked ? 'Yes' : 'No'}</td></tr>
                        <tr><td>active</td><td>${adminData.active ? 'Yes' : 'No'}</td></tr>
                        <tr>
                            <td></td>
                            <td>
                                <button type="button" class="btn btn-primary" onclick="window.location.hash='#user-profile?action=update'">Edit</button>
                                <button type="button" class="btn btn-warning" onclick="window.location.hash='#update-password'">Update Password</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>`;
        }

        res.setHeader('Content-Type', 'text/html');
        res.send(html);

    } catch (error) {
        console.error('Error fetching user profile:', error);
        res.status(500).send('An error occurred while fetching the user profile.');
    }
});

// GET update password form (HTML response)
router.get('/update-password', conditionalAuth, (req, res) => {
    const html = `
    <div class="table-container detail-view">
        <form id="password-update-form" class="form-group" onsubmit="handlePasswordUpdate(event); return false">
            <table class="table table-borderless">
                <tr>
                    <td>Current Password</td>
                    <td><input type="password" name="current_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td>New Password</td>
                    <td><input type="password" name="new_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td>Confirm New Password</td>
                    <td><input type="password" name="confirm_password" class="form-control" autocomplete="off" required></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" class="btn btn-success">Update</button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.hash='#user-profile'">Cancel</button>
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
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    const { name, email, gender, birth_day, phone } = req.body;

    try {
        await req.user.update({
            name, email, gender, birth_day, phone
        });
        res.json({ success: true, message: 'Profile updated successfully.' });
    } catch (error) {
        console.error('Error updating profile:', error);
        res.status(500).json({ success: false, message: `Failed to update profile: ${error.message}` });
    }
});

// POST to update user password
router.post('/update-password', conditionalAuth, async (req, res) => {
    if (!req.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    const { current_password, new_password, confirm_password } = req.body;

    if (!new_password || !current_password) {
        return res.status(400).json({ success: false, message: 'All password fields are required.' });
    }
    if (new_password !== confirm_password) {
        return res.status(400).json({ success: false, message: 'New password and confirmation do not match.' });
    }

    try {
        const hash1 = crypto.createHash('sha1').update(current_password).digest('hex');
        const currentPasswordHash = crypto.createHash('sha1').update(hash1).digest('hex');

        if (currentPasswordHash !== req.user.password) {
            return res.status(401).json({ success: false, message: 'Incorrect current password.' });
        }

        const newHash1 = crypto.createHash('sha1').update(new_password).digest('hex');
        const newPasswordHash = crypto.createHash('sha1').update(newHash1).digest('hex');

        await req.user.update({
            password: newPasswordHash,
            last_reset_password: new Date()
        });

        res.json({ success: true, message: 'Password updated successfully.' });
    } catch (error) {
        console.error('Error updating password:', error);
        res.status(500).json({ success: false, message: `Failed to update password: ${error.message}` });
    }
});

module.exports = router;
