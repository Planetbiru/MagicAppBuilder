const { models } = require('../config/database');

/**
 * Authentication middleware that checks for a valid user session.
 */
const authMiddleware = async (req, res, next) => {
    // Check if user information is stored in the session
    if (req.session && req.session.username) {
        try {
            const user = await models.admin.findOne({ where: { username: req.session.username } });
            if (user) {
                req.user = user; // Attach user object to the request
                return next();
            }
        } catch (error) {
            console.error("Auth middleware error:", error);
            return res.status(500).json({ success: false, message: 'Internal server error during authentication.' });
        }
    }
    
    // If no session or user not found, deny access
    res.status(401).json({ success: false, message: 'Authentication failed. Please log in.' });
};

module.exports = authMiddleware;