const authMiddleware = require('./authMiddleware');

/**
 * A middleware that conditionally applies authentication based on the REQUIRE_LOGIN environment variable.
 * If REQUIRE_LOGIN is true, it protects all routes except for a predefined whitelist.
 *
 * @param {object} req - The Express request object.
 * @param {object} res - The Express response object.
 * @param {function} next - The next middleware function.
 */
const conditionalAuth = (req, res, next) => {
    // If REQUIRE_LOGIN is not 'true', skip authentication for all routes.
    if (process.env.REQUIRE_LOGIN !== 'true') {
        return next();
    }

    // Define public routes that do not require authentication.
    const publicPaths = [
        '/login',
        '/available-language.json',
        '/available-themes.json'
    ];

    // If the requested path is public, skip authentication. Otherwise, apply authMiddleware.
    publicPaths.includes(req.path) ? next() : authMiddleware(req, res, next);
};

module.exports = conditionalAuth;