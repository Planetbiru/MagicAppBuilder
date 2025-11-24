const { Op } = require('sequelize');
const { sequelize } = require('../config/database'); // Import sequelize instance

/**
 * Builds the 'where' clause for a Sequelize query from a filter array.
 * @param {Array} filter - Array of filter objects from GraphQL arguments.
 * @returns {Object} The 'where' object for Sequelize.
 */
function buildWhereClause(filter) {
    const where = {};
    const dialect = sequelize.getDialect(); // Get the current database dialect
    if (filter) {
        filter.forEach(f => {
            const op = f.operator || 'EQUALS';
            const field = f.field;
            const value = f.value;
            switch (op) {
                case 'EQUALS': where[field] = { [Op.eq]: value }; break;
                case 'NOT_EQUALS': where[field] = { [Op.ne]: value }; break;
                case 'CONTAINS':
                    // Use Op.iLike for Postgres for case-insensitive search,
                    // and Op.like for other databases.
                    const likeOperator = dialect.indexOf('postgre') !== -1 ? Op.iLike : Op.like;
                    where[field] = { [likeOperator]: `%${value}%` };
                    break;
                case 'GREATER_THAN': where[field] = { [Op.gt]: value }; break;
                case 'GREATER_THAN_OR_EQUALS': where[field] = { [Op.gte]: value }; break;
                case 'LESS_THAN': where[field] = { [Op.lt]: value }; break;
                case 'LESS_THAN_OR_EQUALS': where[field] = { [Op.lte]: value }; break;
                case 'IN': where[field] = { [Op.in]: Array.isArray(value) ? value : value.split(',') }; break;
                case 'NOT_IN': where[field] = { [Op.notIn]: Array.isArray(value) ? value : value.split(',') }; break;
            }
        });
    }
    return where;
}

module.exports = { buildWhereClause };