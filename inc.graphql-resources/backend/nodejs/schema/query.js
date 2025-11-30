const { Op } = require('sequelize');
const { sequelize } = require('../config/database');

/**
 * Casts the filter value according to the model attribute type.
 * This ensures string columns always receive string values.
 *
 * @param {string} field - Column name in the Sequelize model.
 * @param {*} value - Original value from GraphQL input.
 * @param {import('sequelize').Model} model - Target Sequelize model.
 * @returns {*} Casted value to match the model column type.
 */
function castValueByModel(field, value, model) {
    const attr = model?.rawAttributes[field];
    if (!attr) return value;

    const type = attr.type.key;

    // Ensure strings remain strings
    if (type === 'STRING' && typeof value !== 'string') {
        return String(value);
    }

    return value;
}

/**
 * Builds an EQUALS condition, including TRIM-based comparison
 * for string/text columns to avoid issues caused by hidden whitespace.
 *
 * @param {string} field - Column name.
 * @param {*} value - Filter value.
 * @param {import('sequelize').Model} model - Sequelize model to inspect column types.
 * @param {Object} where - The where clause being constructed.
 */
function buildEqualsCondition(field, value, model, where) {
    const attr = model?.rawAttributes[field];
    const isString = attr && (attr.type.key === 'STRING' || attr.type.key === 'TEXT');

    if (isString) {
        // Use TRIM for robust string comparison
        const trimCompare = sequelize.where(
            sequelize.fn('TRIM', sequelize.col(field)),
            Op.eq,
            value
        );

        // Attach to AND chain
        where[Op.and] = (where[Op.and] || []).concat(trimCompare);
        return;
    }

    // Basic EQUALS for non-string fields
    where[field] = { [Op.eq]: value };
}

/**
 * Returns the correct LIKE operator depending on the database dialect.
 * PostgreSQL uses iLike for case-insensitive matching.
 *
 * @returns {symbol} Op.iLike or Op.like depending on dialect.
 */
function getLikeOperator() {
    const dialect = sequelize.getDialect().toLowerCase();
    return dialect.includes('postgre') ? Op.iLike : Op.like;
}

/**
 * Splits a comma-separated list into an array for IN/NOT_IN queries.
 *
 * @param {string} value - Comma-separated string.
 * @returns {Array<string>} List of values.
 */
function buildInList(value) {
    return value.split(',');
}

/**
 * Builds a Sequelize 'where' clause object from a GraphQL-style filter array.
 * Designed to stay simple and low-complexity by delegating logic to helpers.
 *
 * @param {Array<Object>} filter - Array of filter objects: { field, operator, value }
 * @param {import('sequelize').Model} model - Sequelize model used to inspect data types.
 * @returns {Object} A Sequelize-compatible where clause.
 */
function buildWhereClause(filter, model) {
    const where = {};
    if (!filter) return where;

    // Loop using for…of for cleaner flow and lower cognitive complexity
    for (const f of filter) {
        const field = f.field;
        const operator = f.operator || 'EQUALS';
        const value = castValueByModel(field, f.value, model);

        // Main operator dispatch logic (kept flat to reduce complexity)
        switch (operator) {
            case 'EQUALS':
                buildEqualsCondition(field, value, model, where);
                break;

            case 'NOT_EQUALS':
                where[field] = { [Op.ne]: value };
                break;

            case 'CONTAINS':
                where[field] = { [getLikeOperator()]: `%${value}%` };
                break;

            case 'GREATER_THAN':
                where[field] = { [Op.gt]: value };
                break;

            case 'GREATER_THAN_OR_EQUALS':
                where[field] = { [Op.gte]: value };
                break;

            case 'LESS_THAN':
                where[field] = { [Op.lt]: value };
                break;

            case 'LESS_THAN_OR_EQUALS':
                where[field] = { [Op.lte]: value };
                break;

            case 'IN':
                where[field] = { [Op.in]: buildInList(value) };
                break;

            case 'NOT_IN':
                where[field] = { [Op.notIn]: buildInList(value) };
                break;
        }
    }

    return where;
}

module.exports = { buildWhereClause };
