/**
 * Class representing an entity (table) in a database.
 *
 * The Entity class is used to define a database table, its name, and the columns
 * that belong to that table. It allows for adding, removing, and converting
 * the entity (with its columns) into a valid SQL `CREATE TABLE` statement.
 */
class Entity {
    /**
     * Creates an instance of the Entity class.
     *
     * @param {string} name - The name of the entity (table).
     * @param {number} index - The index of the entity (table).
     */
    constructor(name, index) {
        this.index = index;
        this.name = name;
        this.columns = [];
        this.data = [];
        this.description = ''; // Description of the entity
        this.creationDate = null; // Timestamp of creation
        this.modificationDate = null; // Timestamp of last modification
        this.creator = null; // User who created the entity
        this.modifier = null; // User who last modified the entity
    }

    /**
     * Adds a column to the entity.
     *
     * @param {Column} column - An instance of the Column class to be added to the entity.
     */
    addColumn(column) {
        this.columns.push(column);
    }

    /**
     * Removes a column from the entity.
     *
     * @param {number} index - The index of the column to be removed from the entity's column list.
     */
    removeColumn(index) {
        this.columns.splice(index, 1);
    }

    /**
     * Sets the entity's data (rows of values).
     *
     * @param {Array<Object>} data - Array of data objects representing rows.
     */
    setData(data) {
        this.data = data || [];
    }

    /**
     * Converts the entity (with its columns) into a valid SQL `CREATE TABLE` statement.
     *
     * @param {string} dialect - Target SQL dialect: "mysql", "postgresql", "sqlite", "sql server".
     * @returns {string} The SQL statement for creating the entity (table).
     */
    toSQL(dialect = "mysql") {
        let cols = [];
        let sql = `CREATE TABLE IF NOT EXISTS ${this.name} (\r\n`;
        this.columns.forEach(col => {
            cols.push(`\t${col.toSQL(dialect)}`);
        });
        sql += cols.join(",\r\n"); // Remove trailing comma and newline
        sql += "\r\n);\r\n\r\n";
        return sql;
    }

    /**
     * Generates a single SQL INSERT statement with multiple rows.
     *
     * @param {string} dialect - Target SQL dialect: "mysql", "postgresql", "sqlite", "sql server".
     * @returns {string} SQL INSERT statement.
     */
    toSQLInsert(dialect = "mysql") {
        if (!this.data || this.data.length === 0) return '';

        const columnNames = this.columns.map(col => col.name);
        const valuesList = this.data.map(row => {
            return '(' + columnNames.map(name => {
                const column = this.columns.find(col => col.name === name);
                // Get nullable value from column.nullable
                const nullable = column ? column.nullable : false; // Default false if column not found
                return this.formatValue(row[name], column, dialect, nullable);
            }).join(', ') + ')';
        });

        return `INSERT INTO ${this.name} (${columnNames.join(', ')}) VALUES\n${valuesList.join(',\n')};\r\n`;
    }

    /**
     * Generates a single SQL INSERT statement for the given row.
     *
     * @param {Object} row - An object representing a row of data.
     * @param {string[]} columnNames - Array of column names to be inserted.
     * @returns {string} A SQL INSERT statement.
     */
    createInsert(row, columnNames) {
        const columnsPart = columnNames.join(', ');
        // Fix: Here we need to access the column from `this.columns`
        // to get the correct `nullable` and `type` properties.
        const valuesPart = columnNames.map(name => {
            const column = this.columns.find(col => col.name === name);
            const nullable = column ? column.nullable : true;
            return this.formatValue(row[name], column, 'mysql', nullable); // Default dialect mysql
        }).join(', ');
        return `INSERT INTO ${this.name} (${columnsPart}) VALUES (${valuesPart});`;
    }

    /**
     * Formats a value for SQL insertion based on column type and SQL dialect.
     *
     * @param {*} value - The value to format.
     * @param {Column} column - The Column object associated with the value.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sql server".
     * @param {boolean} [nullable=true] - If false, null-like values will be replaced with defaults (0 for numeric, '0'/'false' for boolean, '' for text).
     * @returns {string} SQL-safe representation of the value.
     */
    formatValue(value, column, dialect = 'mysql', nullable = true) {
        const type = column.type.toLowerCase();
        const length = column.length;

        const isText = column.isTypeText(type);
        const isInteger = column.isTypeInteger(type);
        const isFloat = column.isTypeFloat(type);
        const isBoolean = column.isTypeBoolean(type, length);

        const isNullLike = (
            value === null ||
            value === undefined ||
            (typeof value === 'string' && value.trim() === '') ||
            (!isText && String(value).toLowerCase() === 'null') // allow 'null' literal only if not text
        );

        if (isNullLike) {
            if (nullable) {
                return 'null'; // Returns 'null' if null is allowed
            } else {
                // Replaces null-like values with defaults based on type if not nullable
                if (isInteger || isFloat) /*NOSONAR*/ {
                    return '0';
                } else if (isBoolean) {
                    // Call formatBoolean with nullable false
                    return this.formatBoolean(value, dialect, false);
                } else { // Assumed text type
                    return "''"; // Empty string
                }
            }
        }
        let formatted = '';
        if (isBoolean) {
            // Call formatBoolean with the same nullable parameter
            formatted = this.formatBoolean(value, dialect, nullable);
        } else if (isInteger || isFloat) {
            formatted = value.toString();
        } else {
            formatted = this.quoteString(value); // all text types including 'null'
        }

        return formatted;
    }


    /**
     * Converts a boolean-like value into dialect-specific representation.
     *
     * @param {*} value - Input value, possibly boolean, number, or string.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sql server".
     * @param {boolean} [nullable=true] - If false, null-like values will be replaced with defaults ('0'/'false').
     * @returns {string} The formatted boolean value: '1', '0', 'true', 'false', or 'null'.
     */
    formatBoolean(value, dialect = 'mysql', nullable = true) {
        const isNullLike = (
            value === null ||
            value === undefined ||
            (typeof value === 'string' && value.trim().toLowerCase() === 'null')
        );

        if (isNullLike) {
            if (nullable) {
                return 'null';
            } else {
                // Returns default boolean if not nullable
                switch (dialect.toLowerCase()) {
                    case 'sqlite':
                    case 'sqlserver':
                        return '0'; // Default 0 for non-nullable boolean
                    case 'postgresql':
                        return 'false'; // Default 'false' for non-nullable boolean
                    case 'mysql':
                    default:
                        return '0'; // Default 0 for non-nullable boolean
                }
            }
        }

        const val = String(value).toLowerCase().trim();
        const isTrue = val === 'true' || val === '1' || val === 'yes';

        switch (dialect.toLowerCase()) {
            case 'sqlite':
            case 'sqlserver':
                return isTrue ? '1' : '0';

            case 'postgresql':
                return isTrue ? 'true' : 'false';

            case 'mysql':
            default:
                return isTrue ? '1' : '0';
        }
    }

    /**
     * Escapes and quotes a string value for SQL.
     *
     * @param {string} value - The string value to escape and quote.
     * @returns {string} - Escaped and quoted string.
     */
    quoteString(value) {
        const escaped = String(value).replace(/'/g, "''");
        return `'${escaped}'`;
    }
}
