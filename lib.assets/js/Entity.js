

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
     * @param {string} dialect - Target SQL dialect: "mysql", "postgresql", "sqlite".
     * @returns {string} The SQL statement for creating the entity (table).
     */
    toSQL(dialect = "mysql") {
        let sql = `CREATE TABLE IF NOT EXISTS ${this.name} (\r\n`;
        this.columns.forEach(col => {
            sql += `\t${col.toSQL(dialect)},\r\n`;
        });
        sql = sql.slice(0, -3); // Remove trailing comma
        sql += "\r\n);\r\n\r\n";
        return sql;
    }

    /**
     * Generates a single SQL INSERT statement with multiple rows.
     *
     * @param {string} dialect - Target SQL dialect: "mysql", "postgresql", "sqlite".
     * @returns {string} SQL INSERT statement.
     */
    toSQLInsert(dialect = "mysql") {
        if (!this.data || this.data.length === 0) return '';

        const columnNames = this.columns.map(col => col.name);
        const valuesList = this.data.map(row => {
            return '(' + columnNames.map(name => {
                const column = this.columns.find(col => col.name === name);
                return this.formatValue(row[name], column, dialect);
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
        const valuesPart = columnNames.map(name => this.formatValue(row[name])).join(', ');
        return `INSERT INTO ${this.name} (${columnsPart}) VALUES (${valuesPart});`;
    }

    /**
     * Formats a value for SQL insertion based on column type and SQL dialect.
     * 
     * @param {*} value - The value to format.
     * @param {Column} column - The Column object associated with the value.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sql server".
     * @returns {string} SQL-safe representation of the value.
     */
    formatValue(value, column, dialect = 'mysql') {
        const type = column.type.toLowerCase();
        const length = column.length;
        let formatted = 'null';

        const isText = column.isTypeText(type);

        const isNullLike = (
            value === null ||
            value === undefined ||
            (typeof value === 'string' && value.trim() === '') ||
            (!isText && String(value).toLowerCase() === 'null') // allow 'null' literal only if not text
        );

        if (isNullLike) {
            return formatted;
        }

        const isInteger = column.isTypeInteger(type);
        const isFloat = column.isTypeFloat(type);
        const isBoolean = column.isTypeBoolean(type, length);
        if (isBoolean) {
            formatted = this.formatBoolean(value, dialect);
        } else if (isInteger || isFloat) {
            formatted = value.toString();
        } else {
            formatted = this.quoteString(value); // all text types including 'null'
        }

        return formatted;
    }



    /**
     * Formats a boolean value for SQL based on the given dialect.
     * 
     * @param {*} value - The value to format as boolean.
     * @param {string} dialect - The SQL dialect (e.g., 'postgresql').
     * @returns {string} - Boolean value as SQL string.
     */
    /**
     * Converts a boolean-like value into dialect-specific representation.
     * 
     * @param {*} value - Input value, possibly boolean, number, or string.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sql server".
     * @returns {string} The formatted boolean value: '1', '0', 'true', 'false', or 'null'.
     */
    formatBoolean(value, dialect = 'mysql') {
        if (
            value === null ||
            value === undefined ||
            (typeof value === 'string' && value.trim().toLowerCase() === 'null')
        ) {
            return 'null';
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