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
            "tinyint(1)": "BOOLEAN",  // MySQL treats tinyint(1) as boolean
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
            "char": "NVARCHAR",
            "tinytext": "TEXT",
            "mediumtext": "TEXT",
            "longtext": "TEXT",
            "text": "TEXT",
            "datetime": "DATETIME", // SQLite stores datetime as DATETIME in ISO 8601 format
            "timestamp": "TIMESTAMP", // Same as datetime for SQLite
            "date": "DATE",  // SQLite stores dates as DATE in ISO 8601 format
            "time": "TIME", // Same as datetime for SQLite
            "year": "INTEGER", // SQLite stores year as integer
            "boolean": "INTEGER", // SQLite stores boolean as integer (0 for false, 1 for true)
            "json": "TEXT", // SQLite supports JSON as TEXT
            "jsonb": "TEXT", // SQLite doesn't support jsonb, treated as TEXT
            // PostgreSQL specific types mapped to SQLite
            "integer": "INTEGER",
            "serial": "INTEGER",
            "bigserial": "INTEGER",
            "double precision": "REAL",
            "timestamptz": "TIMESTAMP", // Same as timestamp but with timezone in SQLite
        };

        this.dbToMySQL = {
            // SQLite types to MySQL mapping
            "bigint": "BIGINT",
            "mediumint": "MEDIUMINT",
            "smallint": "SMALLINT",
            "integer": "INT",
            "double": "DOUBLE",
            "float": "FLOAT",
            "real": "DOUBLE",
            "decimal": "DECIMAL",
            "numeric": "NUMERIC",
            "tinytext": "TINYTEXT",
            "mediumtext": "MEDIUMTEXT",
            "longtext": "LONGTEXT",
            "text": "TEXT",
            "nvarchar": "VARCHAR",
            "varchar": "VARCHAR",
            "character varying": "VARCHAR",
            "tinyint(1)": "TINYINT(1)",
            "tinyint": "TINYINT",
            "boolean": "TINYINT(1)",
            "int": "INT",
            "datetime": "DATETIME",
            "date": "DATE",
            "timestamptz": "TIMESTAMP",
            "timestamp with time zone": "TIMESTAMP",
            "timestamp without time zone": "DATETIME",
            "timestamp": "TIMESTAMPTZ",
            "json": "JSON",
            "enum": "ENUM",
            "set": "SET",
            "char": "CHAR"
        };
        
        this.dbToPostgreSQL = {
            // SQLite types to PostgreSQL mapping
            "bigint": "BIGINT",
            "mediumint": "INTEGER",
            "smallint": "INTEGER",
            "tinyint(1)": "BOOLEAN",
            "tinyint": "INTEGER",
            "integer": "INTEGER",
            "int": "INTEGER",
            "real": "REAL",
            "longtext": "TEXT",
            "mediumtext": "TEXT",
            "smalltext": "TEXT",
            "tinytext": "TEXT",
            "text": "TEXT",
            "character varying": "CHARACTER VARYING",
            "nvarchar": "CHARACTER VARYING",
            "varchar": "CHARACTER VARYING",
            "char": "CHARACTER",
            "boolean": "BOOLEAN",
            "datetime": "TIMESTAMP WITHOUT TIME ZONE",
            "date": "DATE",
            "timestamptz": "TIMESTAMP WITH TIME ZONE",
            "timestamp": "TIMESTAMP WITH TIME ZONE",
            "time": "TIME",
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
        if(typeof str == 'undefined')
        {
            return null;
        }
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
        if (this.isSQLite(targetType)) {
            return this.toSqliteOut(table, targetType);
        } else if (this.isMySQL(targetType)) {
            return this.toMySQLOut(table, targetType);
        } else if (this.isPGSQL(targetType)) {
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
        let sqliteTable = {
            tableName: table.tableName,
            primaryKey: table.primaryKey,
            columns: table.columns.map(column => {
                let columnCopy = { ...column }; // Using object spread instead of Object.assign
                columnCopy.Type = this.toSqliteType(columnCopy.Type, columnCopy.Length);
                return columnCopy;
            })
        };
        return this.toSqliteTable(sqliteTable, targetType);
    }    

    /**
     * Converts a table schema to MySQL format.
     * @param {Object} table The table object to convert.
     * @param {string} targetType The target database type.
     * @returns {string} The converted MySQL table schema as a string.
     */
    toMySQLOut(table, targetType) {
        let mysqlTable = {
            tableName: table.tableName,
            primaryKey: table.primaryKey,
            columns: table.columns.map(column => {
                let columnCopy = { ...column, Field: column.Field }; // Spread and copy Field directly
                columnCopy.Type = this.toMySQLType(columnCopy.Type, columnCopy.Length);
                return columnCopy;
            })
        };
        return this.toMySQLTable(mysqlTable, targetType);
    }    

    /**
     * Converts a table schema to PostgreSQL format.
     * @param {Object} table The table object to convert.
     * @param {string} targetType The target database type.
     * @returns {string} The converted PostgreSQL table schema as a string.
     */
    toPostgreSQLOut(table, targetType) {
        let pgTable = {
            tableName: table.tableName,
            primaryKey: table.primaryKey,
            columns: table.columns.map(column => {
                let columnCopy = { ...column }; // Create a shallow copy of the column
                columnCopy.Type = this.toPostgreSQLType(columnCopy.Type, columnCopy.Length);
                return columnCopy;
            })
        };
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
     * Checks if the target type is MySQL or MariaDB.
     *
     * @param {string} targetType The database type (e.g., 'mysql', 'mariadb').
     * @returns {boolean} True if the target type is MySQL or MariaDB, otherwise false.
     */
    isMySQL(targetType)
    {
        return targetType === 'mysql' 
            || targetType === 'mariadb';
    }

    /**
     * Checks if the target type is PostgreSQL.
     *
     * @param {string} targetType The database type (e.g., 'pgsql', 'postgresql').
     * @returns {boolean} True if the target type is PostgreSQL, otherwise false.
     */
    isPGSQL(targetType)
    {
        return targetType === 'pgsql' 
            || targetType === 'postgresql';
    }

    /**
     * Checks if the target type is SQLite.
     *
     * @param {string} targetType The database type (e.g., 'sqlite').
     * @returns {boolean} True if the target type is SQLite, otherwise false.
     */
    isSQLite(targetType)
    {
        return targetType === 'sqlite';
    }

    /**
     * Checks if the column type is a real number (FLOAT, DOUBLE, REAL, DECIMAL).
     *
     * @param {string} columnType The column data type (e.g., 'FLOAT', 'DECIMAL').
     * @returns {boolean} True if the column type is a real number type, otherwise false.
     */
    isReal(columnType)
    {
        return columnType.toUpperCase().indexOf('FLOAT') != -1 
            || columnType.toUpperCase().indexOf('DOUBLE') != -1 
            || columnType.toUpperCase().indexOf('REAL') != -1 
            || columnType.toUpperCase().indexOf('DECIMAL') != -1;
    }

    /**
     * Checks if the column type is Boolean (BOOLEAN or TINYINT(1)).
     *
     * @param {string} columnType The column data type (e.g., 'BOOLEAN', 'TINYINT(1)').
     * @returns {boolean} True if the column type is Boolean, otherwise false.
     */
    isBoolean(columnType)
    {
        return columnType.toUpperCase() == 'BOOLEAN' 
            || columnType.toUpperCase() == 'BOOL' 
            || columnType.toUpperCase() == 'TINYINT(1)';
    }

    /**
     * Checks if the column type is TINYINT with a length of 1.
     *
     * This method checks if the given column type is 'TINYINT' and if its length
     * is exactly 1, which is commonly used to represent boolean values in certain databases.
     *
     * @param {string} type The data type of the column (e.g., 'TINYINT').
     * @param {number} length The length of the column (e.g., 1).
     * @returns {boolean} True if the type is 'TINYINT' and length is 1, otherwise false.
     */
    isTinyInt1(type, length)
    {
        return type.toUpperCase() === 'TINYINT' && length == 1;
    }

    /**
     * Checks if the column type is an integer (e.g., TINYINT, SMALLINT, INT, BIGINT).
     *
     * @param {string} type The column data type (e.g., 'INT', 'BIGINT').
     * @returns {boolean} True if the column type is an integer type, otherwise false.
     */
    isInteger(type)
    {
        return type.toUpperCase() === 'TINYINT'
            || type.toUpperCase() === 'SMALLINT'
            || type.toUpperCase() === 'MEDIUMINT'
            || type.toUpperCase() === 'BIGINT'
            || type.toUpperCase() === 'INTEGER'
            || type.toUpperCase() === 'INT';
    }

    /**
     * Determines if a column is auto-incremented for MySQL databases.
     *
     * @param {boolean} autoIncrement Whether the column is set to auto-increment.
     * @param {string} targetType The target database type (e.g., 'mysql', 'mariadb').
     * @returns {boolean} True if the column is auto-incremented in MySQL or MariaDB, otherwise false.
     */
    isAutoIncrement(autoIncrement, targetType)
    {
        return this.isMySQL(targetType) && autoIncrement;
    }

    /**
     * Checks if a value is not empty (not null or an empty string).
     *
     * @param {string} value The value to check.
     * @returns {boolean} True if the value is not empty, otherwise false.
     */
    isNotEmpty(value)
    {
        return value != null && value != '';
    }

    /**
     * Determines if a column has a default value, excluding primary keys.
     *
     * @param {boolean} primaryKey Whether the column is a primary key.
     * @param {string} defaultValue The default value of the column.
     * @returns {boolean} True if the column has a default value, otherwise false.
     */
    hasDefaultValue(primaryKey, defaultValue)
    {
        return !primaryKey && defaultValue !== null && defaultValue !== '';
    }

    /**
     * Fixes the table name according to the target database type.
     * 
     * This method adjusts the table name by removing any database prefix and applying
     * the appropriate syntax for the target database (e.g., quoting for MySQL or PostgreSQL).
     *
     * @param {string} tableName The name of the table to fix.
     * @param {string} targetType The target database type (e.g., 'mysql', 'pgsql').
     * @returns {string} The fixed table name.
     */
    fixTableName(tableName, targetType)
    {
        if (tableName.indexOf('.') !== -1) {
            tableName = tableName.split('.')[1];
        }
        if (this.isMySQL(targetType)) {
            tableName = '`' + tableName + '`';
        }
        else if (this.isPGSQL(targetType)) {
            tableName = '"' + tableName + '"';
        }
        return tableName;
    }

    /**
     * Fixes the column name according to the target database type.
     * 
     * This method applies proper quoting for column names based on the target database
     * (e.g., MySQL uses backticks for column names).
     *
     * @param {string} columnName The name of the column to fix.
     * @param {string} targetType The target database type (e.g., 'mysql').
     * @returns {string} The fixed column name.
     */
    fixColumnName(columnName, targetType)
    {
        if (this.isMySQL(targetType)) {
            columnName = '`' + columnName + '`';
        }
        return columnName;
    }

    /**
     * Generates the default value SQL for a column based on its type.
     * 
     * This method returns the appropriate default value syntax for the column's type,
     * handling different types such as BOOLEAN, INTEGER, and REAL.
     *
     * @param {string} defaultValue The default value to apply to the column.
     * @param {string} columnType The type of the column (e.g., 'BOOLEAN', 'INT').
     * @returns {string} The default value SQL definition for the column.
     */
    getDefaultData(defaultValue, columnType)
    {
        let colDef = "";
        if(defaultValue.toUpperCase() == 'NULL')
        {
            colDef += ' DEFAULT NULL';
        }
        else if(this.isBoolean(columnType))
        {
            colDef += ' DEFAULT ' + this.convertToBolean(defaultValue);
        }
        else if(columnType.toUpperCase().indexOf('INT') != -1)
        {
            colDef += ' DEFAULT ' + this.convertToInteger(defaultValue);
        }
        else if(this.isReal(columnType))
        {
            colDef += ' DEFAULT ' + this.convertToReal(defaultValue);
        }
        else
        {
            colDef += ' DEFAULT ' + defaultValue;
        }
        return colDef;
    }

    /**
     * Converts a table schema to a common table format for SQLite, MySQL, or PostgreSQL.
     * @param {Object} table The table object to convert.
     * @param {string} targetType The target database type.
     * @returns {string} The converted table schema as a string.
     */
    toTable(table, targetType) {
        let tableName = table.tableName;
        
        let lines = [];

        tableName = this.fixTableName(tableName, targetType);
        
        lines.push(`CREATE TABLE IF NOT EXISTS ${tableName} (`);
        let linesCol = [];
        for (let i in table.columns) {
            let columnName = this.fixColumnName(table.columns[i].Field, targetType);
            
            let columnType = table.columns[i].Type;
            let primaryKey = table.columns[i].Field === table.primaryKey;
            let colDef = '\t' + columnName + ' ' + columnType;
            if (primaryKey) {
                colDef += ' PRIMARY KEY';             
                if (this.isAutoIncrement(table.columns[i].AutoIncrement, targetType)) {
                    colDef += ' AUTO_INCREMENT';
                }
                table.columns[i].Nullable = false;
            }
            else if (table.columns[i].Nullable) {
                colDef += ' NULL';
            } else {
                colDef += ' NOT NULL';
            }
            
            let defaultValue = table.columns[i].Default;
            if (this.hasDefaultValue(primaryKey, defaultValue)) {
                defaultValue = this.replaceAll(defaultValue, '::character varying', '');
                defaultValue = this.fixDefaultValue(defaultValue, table.columns[i].Type, targetType);
                if (this.isNotEmpty(defaultValue)) {
                    colDef += this.getDefaultData(defaultValue, columnType);
                }
            }
            
            if(table.columns[i].Comment != null)
            {
                colDef += ` COMMENT '${this.addslashes(table.columns[i].Comment)}'`;
            }

            linesCol.push(colDef);
        }
        lines.push(linesCol.join(',\r\n'));
        lines.push(');');
        return lines.join('\r\n');
    }

    /**
     * Escapes single quotes in a string by replacing them with two single quotes.
     * This is commonly used for SQL-style escaping to prevent errors or SQL injection.
     *
     * @param {string} str - The input string containing single quotes to escape.
     * @returns {string} - A new string with all single quotes replaced by two single quotes (SQL-style).
     */
    addslashes(str) {
        // Replace single quotes with two single quotes (SQL-style escaping)
        return str.replace(/'/g, "''");
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
        let trimmedValue = value.replace(/^'|'$/g, ''); // NOSONAR
        
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
        let trimmedValue = value.replace(/^'|'$/g, ''); // NOSONAR
        
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
     * Converts a given string to a boolean-like string ('TRUE' or 'FALSE').
     * 
     * This function checks if the input string contains the character '1' or the string 'TRUE' (case-insensitive).
     * If either condition is met, it returns the string 'TRUE', otherwise, it returns the string 'FALSE'.
     *
     * @param {string} defaultValue - The input string to be checked and converted to 'TRUE' or 'FALSE'.
     * @returns {string} - Returns 'TRUE' if the string contains '1' or 'TRUE', otherwise returns 'FALSE'.
     */
    convertToBolean(defaultValue)
    {
        return defaultValue.indexOf('1') != -1 || defaultValue.toUpperCase().indexOf('TRUE') != -1 ? 'TRUE' : 'FALSE';
    }

    /**
     * Fixes default value for SQLite.
     * @param {string} defaultValue The default value to fix.
     * @param {string} columnType The coulumn type.
     * @param {string} targetType The target database type.
     * @returns {string} The fixed default value.
     */
    fixDefaultValue(defaultValue, columnType, targetType) {
        console.log(columnType)
        if (this.isSQLite(targetType)) {
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
        if(type.toLowerCase() == 'tinyint' && length == 1)
        {
            return 'BOOLEAN';
        }
        let sqliteType = 'TEXT';
        for (let i in this.dbToSqlite) {
            if (this.dbToSqlite.hasOwnProperty(i)) {
                let key = i.toString();
                if (type.toLowerCase().startsWith(key.toLowerCase())) {
                    sqliteType = this.dbToSqlite[key];
                    break;
                }
            }
        }
        if (type.toUpperCase().indexOf('ENUM') != -1 || type.toUpperCase().indexOf('SET') != -1) {
            const { resultArray, maxLength } = this.parseEnumValue(length); // NOSONAR
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
        if(this.isTinyInt1(type, length))
        {
            return 'TINYINT(1)';
        }
        if(this.isInteger(type) && length > 0)
        {
            return `${type}(${length})`;
        }
        for (let i in this.dbToMySQL) {
            if (this.dbToMySQL.hasOwnProperty(i)) {
                let key = i.toString();
                if (type.toLowerCase().startsWith(key.toLowerCase())) {
                    mysqlType = this.dbToMySQL[key];
                    break;
                }
            }
        }
        mysqlType = this.replaceAll(mysqlType, 'TIMESTAMPTZ', 'TIMESTAMP')
        if (type.toUpperCase().indexOf('ENUM') != -1) {
            const { resultArray, maxLength } = this.parseEnumValue(length); // NOSONAR
            mysqlType = 'ENUM(\'' + (resultArray.join('\',\'')) + '\')';
        } else if (type.toUpperCase().indexOf('SET') != -1) {
            const { resultArray, maxLength } = this.parseEnumValue(length); // NOSONAR
            mysqlType = 'SET(\'' + (resultArray.join('\',\'')) + '\')';
        } else if (type.toUpperCase().indexOf('DECIMAL') != -1) {
            const { resultArray, maxLength } = this.parseNumericType(length); // NOSONAR
            mysqlType = 'DECIMAL(' + (resultArray.join(', ')) + ')';
        } else if (type.toUpperCase().indexOf('NUMERIC') != -1) {
            const { resultArray, maxLength } = this.parseNumericType(length); // NOSONAR
            mysqlType = 'NUMERIC(' + (resultArray.join(', ')) + ')';
        }
        if ((mysqlType === 'VARCHAR' || mysqlType === 'CHAR') && length > 0) {
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
                if (type.toLowerCase().startsWith(key.toLowerCase())) {
                    pgType = this.dbToPostgreSQL[key];
                    break;
                }
            }
        }
        if (type.toUpperCase().indexOf('TINYINT') != -1 && length == 1) {
            pgType = 'BOOLEAN';
        }
        else if (type.toUpperCase().indexOf('ENUM') != -1 || type.toUpperCase().indexOf('SET') != -1) {
            const { resultArray, maxLength } = this.parseEnumValue(length); // NOSONAR
            pgType = 'CHARACTER VARYING(' + (maxLength + 2) + ')';
        }
        else if ((pgType === 'CHARACTER VARYING' || pgType === 'CHARACTER' || pgType === 'CHAR') && length > 0) {
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
     * Parses a numeric type value like DECIMAL(6,3), NUMERIC(10,2), etc.
     * @param {string} inputString The numeric value in string format, like 'DECIMAL(6, 3)'.
     * @returns {Object} An object containing the type (e.g., DECIMAL) and the length (total digits) and scale (digits after the decimal point).
     */
    parseNumericType(inputString) {
        // Regex untuk menangkap nilai yang dipisahkan oleh koma, tanpa tanda kutip
        const regex = /([A-Za-z0-9_]+)/g; // NOSONAR
        let matches;
        let resultArray = [];

        // Menangkap semua kecocokan
        while ((matches = regex.exec(inputString)) !== null) {
            resultArray.push(matches[1]); // matches[1] adalah nilai yang dipisahkan
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
            if(this.isPGSQL(targetType)) {
                tableName = '"' + tableName + '"';
            } else if(this.isMySQL(targetType)) {
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
            input = input.split('.')[1];
        }
        // If there is no dot, it means the input is just the table name
        return input.replace(/[^a-zA-Z0-9_]/g, ''); // NOSONAR
    }

}