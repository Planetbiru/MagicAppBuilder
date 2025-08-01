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
     * Counts the number of columns marked as primary keys.
     *
     * @returns {number} The number of primary key columns.
     */
    countPrimaryKey() {
        return this.columns.filter(col => col.primaryKey).length;
    }
    
    /**
     * Returns an array of column names that are marked as primary keys.
     *
     * @returns {string[]} Array of primary key column names.
     */
    getPrimaryKeyColumns() {
        return this.columns
            .filter(col => col.primaryKey)
            .map(col => col.name);
    }
    
    /**
     * Returns a comma-separated string of primary key column names,
     * formatted according to the SQL dialect.
     * 
     * - For MySQL: backticks are added (e.g., `id`, `user_id`)
     * - For other dialects: plain comma-separated list (e.g., id, user_id)
     * 
     * @param {string} dialect - SQL dialect (e.g., "mysql", "postgresql", etc.)
     * @returns {string} The formatted primary key columns as a string.
     */
    getPrimaryKeyColumnsAsString(dialect = "mysql") {
        const keys = this.getPrimaryKeyColumns();
        return dialect === "mysql"
            ? keys.map(k => `\`${k}\``).join(', ')
            : keys.join(', ');
    }


    /**
     * Converts the entity (table definition with its columns) into a valid SQL `CREATE TABLE` statement.
     *
     * This method generates a complete `CREATE TABLE` statement based on:
     * - SQL dialect (e.g., MySQL, PostgreSQL, SQLite, SQL Server)
     * - Column definitions and types
     * - Primary key placement (inline or separate based on number of keys)
     * - Handling of auto-increment fields, default values, nullability, etc.
     *
     * If the table has a composite primary key (more than one column),
     * the primary key constraint is placed separately at the end of the column list.
     *
     * @param {string} dialect - Target SQL dialect: "mysql", "postgresql", "sqlite", or "sqlserver".
     * @returns {string} The generated SQL `CREATE TABLE` statement.
     */
    toSQL(dialect = "mysql") {
        let separatePrimaryKey = this.countPrimaryKey() > 1;
        let cols = [];
        let sql = `CREATE TABLE IF NOT EXISTS ${this.name} (\r\n`;
        this.columns.forEach(col => {
            cols.push(`\t${col.toSQL(dialect, separatePrimaryKey)}`);
        });
        if(separatePrimaryKey)
        {
            cols.push(`\tPRIMARY KEY(${this.getPrimaryKeyColumnsAsString(dialect)})`);
        }
        sql += cols.join(",\r\n"); // Remove trailing comma and newline
        sql += "\r\n);\r\n\r\n";
        return sql;
    }

    /**
     * Generates one or more SQL INSERT statements, each containing up to `maxRow` rows.
     *
     * This method splits the data into chunks of up to `maxRow` rows and generates
     * separate INSERT statements for each chunk. It also handles proper value formatting
     * based on the target SQL dialect and column definitions.
     *
     * @param {string} dialect - Target SQL dialect. Supported values: "mysql", "postgresql", "sqlite", "sqlserver".
     * @param {number} maxRow - Maximum number of rows per INSERT statement (default is 100).
     * @returns {string} The generated SQL INSERT statements as a single string.
     */
    toSQLInsert(dialect = "mysql", maxRow = 100) {
        if (!this.data || this.data.length === 0) return '';

        const columnNames = this.columns.map(col => col.name);
        const chunks = [];

        for (let i = 0; i < this.data.length; i += maxRow) {
            const slice = this.data.slice(i, i + maxRow);
            const valuesList = slice.map(row => {
                return '(' + columnNames.map(name => {
                    const column = this.columns.find(col => col.name === name);
                    const nullable = column ? column.nullable : false;
                    return this.formatValue(row[name], column, dialect, nullable);
                }).join(', ') + ')';
            });

            const insertStatement = `INSERT INTO ${this.name} (${columnNames.join(', ')}) VALUES\n${valuesList.join(',\n')};\r\n`;
            chunks.push(insertStatement);
        }

        return chunks.join('\n');
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
     * If the value is null-like (null, undefined, empty string, or "null" literal for non-text types):
     * - If `nullable` is true → returns `'null'`
     * - If `nullable` is false:
     *   - Returns column.default if available
     *   - Otherwise, returns type-specific default (0 for number, '' for text, etc.)
     *
     * @param {*} value - The value to format.
     * @param {Column} column - The Column object associated with the value.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", or "sqlserver".
     * @param {boolean} [nullable=true] - If false, null-like values will be replaced with defaults.
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
            (!isText && String(value).toLowerCase() === 'null')
        );

        if (isNullLike) {
            if (nullable) {
                return 'null';
            }

            // Use defaultValue if defined
            if (column.default !== undefined && column.default !== null) {
                return this.formatValue(column.default, column, dialect, true);
            }

            // Type-specific fallback
            if (isBoolean) {
                return this.formatBoolean(value, dialect, false, column.default); // default false
            } else if (isInteger || isFloat) {
                return '0';
            } else {
                return "''";
            }
        }

        // Format non-null value
        if (isBoolean) {
            return this.formatBoolean(value, dialect, nullable, column.default);
        } else if (isInteger || isFloat) {
            return value.toString();
        } else {
            return this.quoteString(value, dialect);
        }
    }


    /**
     * Converts a boolean-like value into dialect-specific representation.
     *
     * @param {*} value - Input value, possibly boolean, number, or string.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sqlserver".
     * @param {boolean} [nullable=true] - If false, null-like values will be replaced with default false.
     * @param {boolean|string|number|null} [defaultValue=null] - Optional default value to use when value is null-like.
     * @returns {string} The formatted boolean value: '1', '0', 'true', 'false', or 'null'.
     */
    formatBoolean(value, dialect = 'mysql', nullable = true, defaultValue = null) {
        const isNullLike =
            value === null ||
            value === undefined ||
            (typeof value === 'string' && value.trim() === '') ||
            (typeof value === 'string' && value.trim().toLowerCase() === 'null');

        // Handle null-like input
        if (isNullLike) {
            if (nullable) {
                return 'null';
            }

            // Use defaultValue if provided
            if (defaultValue !== null && defaultValue !== undefined) {
                return this.formatBoolean(defaultValue, dialect, false);
            }

            // Fallback default false
            switch (dialect.toLowerCase()) {
                case 'postgresql':
                    return 'false';
                case 'sqlite':
                case 'sqlserver':
                case 'mysql':
                default:
                    return '0';
            }
        }

        // Determine truthiness
        const val = String(value).toLowerCase().trim();
        const isTrue = val === 'true' || val === '1' || val === 'yes' || val === 'on';

        switch (dialect.toLowerCase()) {
            case 'postgresql':
                return isTrue ? 'true' : 'false';
            case 'sqlite':
            case 'sqlserver':
            case 'mysql':
            default:
                return isTrue ? '1' : '0';
        }
    }


    /**
     * Escapes and quotes a string value for SQL.
     *
     * @param {string} value - The string value to escape and quote.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sqlserver".
     * @returns {string} - Escaped and quoted string.
     */
    quoteString(value, dialect = 'mysql') {
        let str = String(value);        

        // Escape single quotes
        str = str.replace(/'/g, "''");

        switch (dialect.toLowerCase()) {
            case 'mysql':
                // Escape backslashes for MySQL
                str = str.replace(/\\/g, '\\\\');
                break;

            case 'postgresql':
                // Escape backslashes and use E'' syntax for PostgreSQL
                str = str.replace(/\\/g, '\\\\');
                return `E'${str}'`;

            case 'sqlite':
                // Backslashes are literal in SQLite, no need to escape
                break;

            case 'sqlserver':
                // Backslashes are literal in SQL Server, no need to escape
                break;

            default:
                // Default to MySQL-style escaping
                str = str.replace(/\\/g, '\\\\');
                break;
        }

        return `'${str}'`;
    }

}
