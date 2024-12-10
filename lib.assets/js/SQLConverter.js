/**
 * SQLConverter is a class that provides methods for converting SQL schema definitions
 * between different database management systems (DBMS). It supports conversion between
 * SQLite, MySQL/MariaDB, and PostgreSQL schemas by mapping data types and structure.
 * The class allows parsing and modifying SQL definitions to match the syntax and data types
 * of the target DBMS.
 */
class SQLConverter {

    /**
     * Creates an instance of SQLConverter.
     * Initializes the mappings for each database type (SQLite, MySQL, PostgreSQL).
     */
    constructor() {
        this.dbToSqlite = {
            // MySQL and PostgreSQL types to SQLite mapping
            "int": "INTEGER",
            "tinyint": "INTEGER",  // MySQL treats tinyint as integer
            "tinyint": "INTEGER",  // MySQL treats tinyint as integer
            "smallint": "INTEGER",
            "mediumint": "INTEGER",
            "bigint": "INTEGER",
            "real": "REAL",
            "float": "REAL",
            "double": "REAL",
            "decimal": "REAL",  // SQLite doesn't have DECIMAL, treated as REAL
            "nvarchar": "NVARCHAR",
            "varchar": "NVARCHAR",
            "character varying": "NVARCHAR",
            "char": "TEXT",
            "tinytext": "TEXT",
            "mediumtext": "TEXT",
            "longtext": "TEXT",
            "text": "TEXT",
            "date": "TEXT",  // SQLite stores dates as TEXT in ISO 8601 format
            "datetime": "TEXT", // SQLite stores datetime as TEXT in ISO 8601 format
            "timestamp": "TEXT", // Same as datetime for SQLite
            "time": "TEXT", // Same as datetime for SQLite
            "year": "INTEGER", // SQLite stores year as integer
            "boolean": "INTEGER", // SQLite stores boolean as integer (0 for false, 1 for true)
            "json": "TEXT", // SQLite supports JSON as TEXT
            "jsonb": "TEXT", // SQLite doesn't support jsonb, treated as TEXT
            // PostgreSQL specific types mapped to SQLite
            "integer": "INTEGER",
            "serial": "INTEGER",
            "bigserial": "INTEGER",
            "double precision": "REAL",
            "timestamptz": "TEXT", // Same as timestamp but with timezone in SQLite
        };

        this.dbToMySQL = {
            // SQLite types to MySQL mapping
            "integer": "INT",
            "int": "INT",
            "real": "FLOAT",
            "tinytext": "TINYTEXT",
            "mediumtext": "MEDIUMTEXT",
            "longtext": "LONGTEXT",
            "text": "TEXT",
            "nvarchar": "VARCHAR",
            "varchar": "VARCHAR",
            "character varying": "VARCHAR",
            "tinyint(1)": "TINYINT(1)",
            "boolean": "TINYINT(1)",
            "datetime": "DATETIME",
            "date": "DATE",
            "timestamptz": "TIMESTAMP",
            "timestamp with time zone": "TIMESTAMP",
            "timestamp without time zone": "DATETIME",
            "timestamp": "TIMESTAMPTZ",
            "json": "JSON"
        };
        
        this.dbToPostgreSQL = {
            // SQLite types to PostgreSQL mapping
            "integer": "INTEGER",
            "real": "REAL",
            "text": "TEXT",
            "tinyint(1)": "BOOLEAN",
            "nvarchar": "CHARACTER VARYING",
            "varchar": "CHARACTER VARYING",
            "boolean": "BOOLEAN",
            "datetime": "TIMESTAMP",
            "date": "DATE",
            "timestamptz": "TIMESTAMP WITH TIME ZONE",
            "timestamp": "TIMESTAMP WITH TIME ZONE",
            "datetime": "TIMESTAMP WITHOUT TIME ZONE",
            "json": "JSONB"
        };
        
    }

    /**
     * Replaces all occurrences of a substring with a replacement string in the provided string.
     * @param {string} str The string to modify.
     * @param {string} search The substring to search for.
     * @param {string} replacement The string to replace the found substring.
     * @returns {string} The modified string with replacements.
     */
    replaceAll(str, search, replacement) {
        const regex = new RegExp(search, 'gi'); // 'i' for case-insensitive, 'g' for global
        return str.replace(regex, replacement);
    }

    /**
     * Translates the SQL schema from one database type to another (e.g., SQLite to MySQL).
     * @param {string} value The SQL schema to translate.
     * @param {string} targetType The target database type (e.g., 'sqlite', 'mysql', 'pgsql').
     * @returns {string} The translated SQL schema.
     */
    translate(value, targetType) {

        let dropTables = [];
        let tableInfo = this.extractDropTableQueries(value, targetType);
        for(let i in tableInfo)
        {
            dropTables.push("-- DROP TABLE IF EXISTS "+tableInfo[i].table+";");
        }

        value = this.replaceAll(value, '`', '');
        value = this.replaceAll(value, ' timestamp with time zone', ' timestamptz');
        value = this.replaceAll(value, ' timestamp without time zone', ' timestamp');
        value = this.replaceAll(value, ' character varying', ' varchar');
        value = this.replaceAll(value, ' COLLATE pg_catalog."default"', '');
        value = this.replaceAll(value, ' TINYINT(1)', ' boolean');
        let tableParser = new TableParser();
        tableParser.parseAll(value);
        let tables = tableParser.getResult();
        let lines = [];
        for (let i in tables) {
            let table = this.convertQuery(tables[i], targetType);
            lines.push(table);
            lines.push('');
        }

        if(dropTables.length > 0)
        {
            dropTables.push("\r\n\r\n");
        }

        let resultTable = dropTables.join("\r\n") + lines.join('\r\n');
        return resultTable;
    }

    /**
     * Converts a table schema to the target database type format (SQLite, MySQL, PostgreSQL).
     * @param {Object} table The table object to convert.
     * @param {string} targetType The target database type ('sqlite', 'mysql', 'pgsql').
     * @returns {string} The converted table schema as a string.
     */
    convertQuery(table, targetType) {
        if (targetType === 'sqlite') {
            return this.toSqliteOut(table, targetType);
        } else if (targetType === 'mysql' || targetType === 'mariadb') {
            return this.toMySQLOut(table, targetType);
        } else if (targetType === 'pgsql' || targetType === 'postgresql') {
            return this.toPostgreSQLOut(table, targetType);
        }
    }

    /**
     * Converts a table schema to SQLite format.
     * @param {Object} table The table object to convert.
     * @param {string} targetType The target database type.
     * @returns {string} The converted SQLite table schema as a string.
     */
    toSqliteOut(table, targetType) {
        let sqliteTable = {};
        sqliteTable.tableName = table.tableName;
        sqliteTable.primaryKey = table.primaryKey;
        sqliteTable.columns = [];
        for (let i in table.columns) {
            let column = Object.assign({}, table.columns[i]);
            column.Type = this.toSqliteType(column.Type, column.Length);
            sqliteTable.columns.push(column);
        }
        return this.toSqliteTable(sqliteTable, targetType);
    }

    /**
     * Converts a table schema to MySQL format.
     * @param {Object} table The table object to convert.
     * @param {string} targetType The target database type.
     * @returns {string} The converted MySQL table schema as a string.
     */
    toMySQLOut(table, targetType) {
        let mysqlTable = {};
        mysqlTable.tableName = table.tableName;
        mysqlTable.primaryKey = table.primaryKey;
        mysqlTable.columns = [];
        for (let i in table.columns) {
            let column = Object.assign({}, table.columns[i]);
            column.Field = column.Field
            column.Type = this.toMySQLType(column.Type, column.Length);
            mysqlTable.columns.push(column);
        }
        return this.toMySQLTable(mysqlTable, targetType);
    }

    /**
     * Converts a table schema to PostgreSQL format.
     * @param {Object} table The table object to convert.
     * @param {string} targetType The target database type.
     * @returns {string} The converted PostgreSQL table schema as a string.
     */
    toPostgreSQLOut(table, targetType) {
        let pgTable = {};
        pgTable.tableName = table.tableName;
        pgTable.primaryKey = table.primaryKey;
        pgTable.columns = [];
        for (let i in table.columns) {
            let column = Object.assign({}, table.columns[i]);
            column.Type = this.toPostgreSQLType(column.Type, column.Length);
            pgTable.columns.push(column);
        }
        return this.toPostgreSQLTable(pgTable, targetType);
    }
    
    /**
     * Converts a table schema to SQLite format.
     * @param {Object} sqliteTable The table object in SQLite format.
     * @param {string} targetType The target database type ('sqlite').
     * @returns {string} The converted SQLite table schema as a string.
     */
    toSqliteTable(sqliteTable, targetType) {
        return this.toTable(sqliteTable, targetType);
    }

    /**
     * Converts a table schema to MySQL format.
     * @param {Object} mysqlTable The table object in MySQL format.
     * @param {string} targetType The target database type ('mysql' or 'mariadb').
     * @returns {string} The converted MySQL table schema as a string.
     */
    toMySQLTable(mysqlTable, targetType) {
        return this.toTable(mysqlTable, targetType);
    }

    /**
     * Converts a table schema to PostgreSQL format.
     * @param {Object} pgTable The table object in PostgreSQL format.
     * @param {string} targetType The target database type ('pgsql' or 'postgresql').
     * @returns {string} The converted PostgreSQL table schema as a string.
     */
    toPostgreSQLTable(pgTable, targetType) {
        return this.toTable(pgTable, targetType);
    }

    /**
     * Converts a table schema to a common table format for SQLite, MySQL, or PostgreSQL.
     * @param {Object} table The table object to convert.
     * @param {string} targetType The target database type.
     * @returns {string} The converted table schema as a string.
     */
    toTable(table, targetType) {
        let tableName = table.tableName;
        if (tableName.indexOf('.') !== -1) {
            tableName = tableName.split('.')[1];
        }
        let lines = [];
        if (targetType === 'mysql' || targetType === 'mariadb') {
            tableName = '`' + tableName + '`';
        }
        else if (targetType === 'pgsql' || targetType === 'postgresql') {
            tableName = '"' + tableName + '"';
        }
        lines.push('CREATE TABLE IF NOT EXISTS ' + tableName);
        lines.push('(');
        let linesCol = [];
        for (let i in table.columns) {
            let columnName = table.columns[i].Field;
            if (targetType === 'mysql' || targetType === 'mariadb') {
                columnName = '`' + columnName + '`';
            }
            let columnType = table.columns[i].Type;
            let primaryKey = table.columns[i].Field === table.primaryKey;
            let colDef = '\t' + columnName + ' ' + columnType;
            if (primaryKey) {
                colDef += ' PRIMARY KEY';
                colDef += ' NOT NULL';
                table.columns[i].Nullable = false;
            }
            else {
                if (table.columns[i].Nullable) {
                    colDef += ' NULL';
                } else {
                    colDef += ' NOT NULL';
                }
            }
            let defaultValue = table.columns[i].Default;
            if (!primaryKey && defaultValue !== '' && defaultValue !== null) {
                defaultValue = this.replaceAll(defaultValue, '::character varying', '');
                defaultValue = this.fixDefaultValue(defaultValue, targetType);
                if (defaultValue != '' && defaultValue != null) {
                    if(defaultValue.toUpperCase() == 'NULL')
                    {
                        colDef += ' DEFAULT NULL';
                    }
                    else if(columnType.toUpperCase() == 'BOOLEAN' || columnType.toUpperCase() == 'TINYINT(1)')
                    {
                        colDef += ' DEFAULT ' + ((defaultValue.indexOf('1') != -1) ? 'TRUE' : 'FALSE');
                    }
                    else if(columnType.toUpperCase().indexOf('INT') != -1)
                    {
                        colDef += ' DEFAULT ' + this.convertToInteger(defaultValue);
                    }
                    else if(columnType.toUpperCase().indexOf('FLOAT') != -1 || columnType.toUpperCase().indexOf('DOUBLE') != -1 || columnType.toUpperCase().indexOf('REAL') != -1 | columnType.toUpperCase().indexOf('DECIMAL') != -1)
                    {
                        colDef += ' DEFAULT ' + this.convertToReal(defaultValue);
                    }
                    else
                    {
                        colDef += ' DEFAULT ' + defaultValue;
                    }
                }
            }
            linesCol.push(colDef);
        }
        lines.push(linesCol.join(',\r\n'));
        lines.push(');');
        return lines.join('\r\n');
    }

    /**
     * Converts a string value (within single quotes) to an integer.
     * If the value is empty or not a valid integer, it returns 0.
     *
     * @param {string} value - The string value to be converted, possibly enclosed in single quotes.
     * @returns {number} The converted integer value, or 0 if conversion is not possible.
     */
    convertToInteger(value) {
        // Remove single quotes if they exist
        let trimmedValue = value.replace(/^'|'$/g, '');
        
        // If the string is empty, return 0, else convert to integer
        return trimmedValue === '' ? 0 : parseInt(trimmedValue, 10);
    }

    /**
     * Converts a string value (within single quotes) to a floating-point number.
     * If the value is empty or not a valid number, it returns 0.
     *
     * @param {string} value - The string value to be converted, possibly enclosed in single quotes.
     * @returns {number} The converted floating-point value, or 0 if conversion is not possible.
     */
    convertToReal(value) {
        // Remove single quotes if they exist
        let trimmedValue = value.replace(/^'|'$/g, '');
        
        // If the string is empty, return 0
        if (trimmedValue === '') {
            return 0;
        }
        
        // Convert to a floating-point number
        let result = parseFloat(trimmedValue);
        
        // If conversion failed (NaN), return 0
        return isNaN(result) ? 0 : result;
    }

    /**
     * Fixes default value for SQLite.
     * @param {string} defaultValue The default value to fix.
     * @param {string} targetType The target database type.
     * @returns {string} The fixed default value.
     */
    fixDefaultValue(defaultValue, targetType) {
        if (targetType === 'sqlite') {
            if (defaultValue.toLowerCase().indexOf('now(') !== -1) {
                defaultValue = '';
            }
        }
        return defaultValue;
    }

    /**
     * Converts a column type to the SQLite type format.
     * @param {string} type The original column type.
     * @param {number} length The column length (optional).
     * @returns {string} The converted SQLite column type.
     */
    toSqliteType(type, length) {
        let sqliteType = 'TEXT';
        for (let i in this.dbToSqlite) {
            if (this.dbToSqlite.hasOwnProperty(i)) {
                let key = i.toString();
                if (type.toLowerCase().indexOf(key.toLowerCase()) === 0) {
                    sqliteType = this.dbToSqlite[key];
                    break;
                }
            }
        }
        if (type.toUpperCase().indexOf('ENUM') != -1) {
            const { resultArray, maxLength } = this.parseEnumValue(length);
            sqliteType = 'NVARCHAR(' + (maxLength + 2) + ')';
        }
        else if ((sqliteType === 'NVARCHAR' || sqliteType === 'INT') && length > 0) {
            sqliteType = sqliteType + '(' + length + ')';
        }
        return sqliteType;
    }

    /**
     * Converts a column type to the MySQL type format.
     * @param {string} type The original column type.
     * @param {number} length The column length (optional).
     * @returns {string} The converted MySQL column type.
     */
    toMySQLType(type, length) {
        let mysqlType = 'TEXT';
        if(type.toUpperCase() === 'TINYINT' && length == 1)
        {
            return 'TINYINT(1)';
        }
        if((
            type.toUpperCase() === 'TINYINT'
            || type.toUpperCase() === 'SMALLINT'
            || type.toUpperCase() === 'MEDIUMINT'
            || type.toUpperCase() === 'BIGINT'
            || type.toUpperCase() === 'INTEGER'
            || type.toUpperCase() === 'INT'
            ) && length > 0)
        {
            return `${type}(${length})`;
        }
        for (let i in this.dbToMySQL) {
            if (this.dbToMySQL.hasOwnProperty(i)) {
                let key = i.toString();
                if (type.toLowerCase().indexOf(key.toLowerCase()) === 0) {
                    mysqlType = this.dbToMySQL[key];
                    break;
                }
            }
        }
        mysqlType = this.replaceAll(mysqlType, 'TIMESTAMPTZ', 'TIMESTAMP')
        if (type.toUpperCase().indexOf('ENUM') != -1) {
            const { resultArray, maxLength } = this.parseEnumValue(length);
            mysqlType = 'enum(\'' + (resultArray.join('\',\'')) + '\')';
        }
        if (mysqlType === 'VARCHAR' && length > 0) {
            mysqlType = mysqlType + '(' + length + ')';
        }
        return mysqlType;
    }

    /**
     * Converts a column type to the PostgreSQL type format.
     * @param {string} type The original column type.
     * @param {number} length The column length (optional).
     * @returns {string} The converted PostgreSQL column type.
     */
    toPostgreSQLType(type, length) {
        let pgType = 'TEXT';
        for (let i in this.dbToPostgreSQL) {
            if (this.dbToPostgreSQL.hasOwnProperty(i)) {
                let key = i.toString();
                if (type.toLowerCase().indexOf(key.toLowerCase()) === 0) {
                    pgType = this.dbToPostgreSQL[key];
                    break;
                }
            }
        }
        if (type.toUpperCase().indexOf('TINYINT') != -1 && length == 1) {
            pgType = 'BOOLEAN';
        }
        else if (type.toUpperCase().indexOf('ENUM') != -1) {
            const { resultArray, maxLength } = this.parseEnumValue(length);
            pgType = 'CHARACTER VARYING(' + (maxLength + 2) + ')';
        }
        else if (pgType === 'CHARACTER VARYING' && length > 0) {
            pgType = pgType + '(' + length + ')';
        }
        return pgType;
    }

    /**
     * Parses an ENUM type value and extracts the values in single quotes, also calculating the maximum length.
     * @param {string} inputString The ENUM values in a string format.
     * @returns {Object} An object containing the result array and maximum length of ENUM values.
     */
    parseEnumValue(inputString) {
        // Regex untuk menangkap teks di dalam single quotes (misalnya, 'A', 'BB', 'CCC')
        const regex = /'([^']+)'/g;
        let matches;
        let resultArray = [];

        // Menangkap semua kecocokan
        while ((matches = regex.exec(inputString)) !== null) {
            resultArray.push(matches[1]); // matches[1] adalah isi di dalam single quotes
        }

        // Menentukan panjang maksimum dari array hasil
        let maxLength = resultArray.reduce((max, current) => {
            return current.length > max ? current.length : max;
        }, 0);

        return { resultArray, maxLength };
    }

    /**
     * Extracts the DROP TABLE IF EXISTS queries from the provided SQL string.
     * 
     * @param {string} sql - The SQL string to be processed.
     * @param {string} targetType - The type of database ('pgsql', 'mysql', or 'mariadb') to format the table names accordingly.
     * @returns {Array} - An array of objects, each containing the name of a table to be dropped.
     */
    extractDropTableQueries(sql, targetType) {
        // Remove backticks (`) from the entire SQL string before processing
        const sqlWithoutBackticks = sql.replace(/`/g, '');
    
        // Regular expression to capture DROP TABLE IF EXISTS command
        const regex = /DROP TABLE IF EXISTS ([^\s]+)/gi;
        let match;
        const result = [];
    
        // Loop through all matches found
        while ((match = regex.exec(sqlWithoutBackticks)) !== null) {
            // Store the result in the desired format

            let tableName = this.extractTableName(match[1]);
            
            // Format the table name based on the target database type
            if(targetType === 'pgsql') {
                tableName = '"' + tableName + '"';
            } else if(targetType === 'mysql' || targetType === 'mariadb') {
                tableName = '`' + tableName + '`';
            }
            result.push({
                table: tableName    // Table name
            });
        }
    
        return result;
    }

    /**
     * Extracts the table name from the input string, removing schema if present.
     * 
     * @param {string} input - The input string (may contain schema.table or just table).
     * @returns {string} - The extracted table name without schema.
     */
    extractTableName(input) {
        // Check if the input contains a dot (indicating a schema)
        if (input.includes('.')) {
            // If there is a dot, take the part after the dot as the table name
            return input.split('.')[1];
        }
        // If there is no dot, it means the input is just the table name
        return input;
    }

}