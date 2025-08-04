const DIALECT_TYPE_MAP = {
    mysql: {
        int: 'INT',
        bigint: 'BIGINT',
        varchar: 'VARCHAR',
        boolean: 'TINYINT(1)',
        tinyint1: 'TINYINT(1)',
        text: 'TEXT',
        datetime: 'DATETIME',
        timestamp: 'TIMESTAMP',
        float: 'FLOAT',
        double: 'DOUBLE',
        decimal: 'DOUBLE',
        enum: 'ENUM',
        set: 'SET',
    },
    postgresql: {
        int: 'INTEGER',
        bigint: 'BIGINT',
        varchar: 'CHARACTER VARYING',
        boolean: 'BOOLEAN',
        tinyint1: 'BOOLEAN',
        text: 'TEXT',
        datetime: 'TIMESTAMP',
        timestamp: 'TIMESTAMP',
        float: 'REAL',
        double: 'DOUBLE PRECISION',
        decimal: 'NUMERIC',
        enum: 'TEXT', // PostgreSQL doesn't support native ENUM in simple SQL
        set: 'TEXT', // no native SET type
    },
    sqlite: {
        int: 'INTEGER',
        bigint: 'INTEGER',
        varchar: 'NVARCHAR',
        boolean: 'BOOLEAN',
        tinyint1: 'BOOLEAN',
        text: 'TEXT',
        datetime: 'DATETIME',
        timestamp: 'TIMESTAMP',
        float: 'REAL',
        double: 'REAL',
        decimal: 'REAL',
        enum: 'TEXT', // SQLite does not have native ENUM type, using TEXT instead
        set: 'TEXT', // SQLite does not have native SET type, using TEXT instead
    },
    sqlserver: {
        int: 'INT',
        bigint: 'BIGINT',
        varchar: 'NVARCHAR',
        boolean: 'BIT',
        tinyint1: 'BIT',
        text: 'NVARCHAR(MAX)', // prefer NVARCHAR for Unicode safety
        datetime: 'DATETIME',
        timestamp: 'DATETIME2', // more precise than DATETIME
        float: 'FLOAT',
        double: 'FLOAT', // SQL Server doesn't have DOUBLE, FLOAT is used
        decimal: 'DECIMAL',
        enum: 'NVARCHAR', // SQL Server does not have native ENUM type, using NVARCHAR instead
        set: 'NVARCHAR', // SQL Server does not have native SET type, using NVARCHAR instead
    },
};

/**
 * Represents a column in a database table.
 * 
 * The Column class is used to define the properties of a column in a database table. 
 * This includes the column's name, type, length, nullable status, default value, 
 * primary key status, auto-increment behavior, and valid values for ENUM or SET types.
 * 
 * @class
 */
class Column {
    /**
     * Constructs a new Column instance representing a database table column.
     *
     * @param {string} name - The name of the column.
     * @param {string} [type="VARCHAR"] - The SQL data type of the column (e.g., "VARCHAR", "INT", "ENUM").
     * @param {string|null} [length=""] - The length or precision of the column (e.g., "255" for VARCHAR, or "10,2" for DECIMAL). Optional.
     * @param {boolean} [nullable=false] - Indicates whether the column allows NULL values.
     * @param {string|null} [defaultValue=""] - The default value assigned to the column. Optional.
     * @param {boolean} [primaryKey=false] - Specifies whether the column is a primary key.
     * @param {boolean} [autoIncrement=false] - Indicates if the column value auto-increments (typically used for numeric primary keys).
     * @param {string|null} [values=""] - Valid values for ENUM/SET types, or value range for numeric types (comma-separated). Optional.
     * @param {string|null} [description=""] - A comment or description for the column. Optional.
     */
    constructor(name, type = "VARCHAR", length = "", nullable = false, defaultValue = "", primaryKey = false, autoIncrement = false, values = "", description = "") //NOSONAR
    {
        if(type.toUpperCase().indexOf('BIGINT') && length == '')
        {
            length = '20';
        }
        this.name = name;
        this.type = type;
        this.length = length;
        this.nullable = nullable;
        this.default = defaultValue;
        this.primaryKey = primaryKey;
        this.autoIncrement = autoIncrement;
        this.values = values;
        this.description = description;
    }
    
    /**
     * Normalizes SQL column type by removing length parameter for BOOLEAN types only.
     *
     * Examples:
     * - BOOLEAN(1) → BOOLEAN
     * - bool(1) → bool
     * - VARCHAR(255) → VARCHAR(255) (unchanged)
     *
     * @param {string} type - The original SQL column type (may include parameters).
     * @returns {string} - The cleaned column type.
     */
    fixColumnType(type) {
        if (!type) return '';

        const match = type.match(/^(\w+)\s*\((\d+)\)$/i); // NOSONAR
        if (match) {
            const baseType = match[1].toLowerCase();
            if (baseType === 'boolean' || baseType === 'bool') {
                return baseType.toUpperCase(); // Normalize to uppercase if preferred
            }
        }

        return type;
    }


    /**
     * Converts the column definition into a valid SQL column definition string
     * for the specified SQL dialect.
     * 
     * This method builds a complete column declaration based on the column's properties:
     * 
     * - Data type (e.g., VARCHAR, INT, ENUM, etc.), mapped to the appropriate SQL dialect.
     * - Length or precision/scale for applicable types (e.g., VARCHAR(255), DECIMAL(10,2)).
     * - Nullable (`NULL` or `NOT NULL`) depending on `nullable` and `primaryKey` status.
     * - Primary key (`PRIMARY KEY`) inline or separately, depending on `separatePrimaryKey`.
     * - Auto-increment/identity column behavior (`AUTO_INCREMENT`, `SERIAL`, `IDENTITY`, etc.).
     * - Default value handling (for boolean, numeric, or string types).
     * - ENUM or SET values for MySQL-specific declarations.
     * 
     * @param {string} dialect - Target SQL dialect. Supported values: "mysql", "postgresql", "sqlite", "sqlserver".
     * @param {boolean} [separatePrimaryKey=false] - If true, skips `PRIMARY KEY` inline in favor of separate definition.
     * @returns {string} The full SQL column definition string based on the column's metadata.
     */
    toSQL(dialect = 'mysql', separatePrimaryKey = false) // NOSONAR
    {
        let typeKey = this.type.toLowerCase();
        if(typeKey == 'tinyint' && this.length == 1)
        {
            typeKey = 'tinyint1';
        }
        let typeMap = DIALECT_TYPE_MAP[dialect] || DIALECT_TYPE_MAP.mysql;
        let mappedType = typeMap[typeKey] || this.type;
        
        mappedType = this.fixColumnType(mappedType);

        if(mappedType == 'BIGINT' && dialect == 'mysql')
        {
            this.length = '20';
        }
        
        

        const isEnumOrSet = ['enum', 'set'].includes(typeKey);
        const isRangeType = ['numeric', 'decimal', 'double', 'float'].includes(typeKey);
        const isLengthType = ['varchar', 'char', 'binary', 'varbinary', 'bit', 'tinyint', 'smallint', 'mediumint', 'int', 'bigint'].includes(typeKey);

        let columnDef = `${this.name} ${mappedType}`;

        // ENUM/SET
        if (isEnumOrSet && this.values) {
            const enums = this.values.split(',').map(v => `'${v.trim()}'`).join(', ');
            if (dialect === 'mysql') {
                columnDef = `${this.name} ${mappedType}(${enums})`;
            }
        }
        // Range types
        else if (isRangeType && this.values) {
            const range = this.values.split(',').map(v => v.trim()).filter(v => /^\d+$/.test(v)).join(', ');
            if (range) columnDef += `(${range})`;
        }
        // Length types
        else if (isLengthType && this.length) {
            if(dialect == 'sqlite' && this.primaryKey && this.autoIncrement && this.type.toLowerCase().indexOf('int') !== -1)
            {
                columnDef += '';
            }
            else
            {
                columnDef += `(${this.length})`;
            }
        }

        // NOT NULL / NULL
        if (!this.primaryKey) {
            columnDef += this.nullable ? ' NULL' : ' NOT NULL';
        } else if(separatePrimaryKey)
        {
            columnDef += ' NOT NULL';
        }
        else
        {
            columnDef += ' NOT NULL PRIMARY KEY';
        }

        // AUTO_INCREMENT
        if (this.autoIncrement) {
            if (dialect === 'mysql') columnDef += ' AUTO_INCREMENT';
            else if (dialect === 'postgresql') columnDef = `${this.name} SERIAL`;
            else if (dialect === 'sqlite') columnDef += ' AUTOINCREMENT';
            else if (dialect === 'sqlserver') columnDef += ' IDENTITY(1,1)';
        }

        // DEFAULT
        if (this.hasDefault()) {
            if (this.isTypeBoolean(this.type, this.length)) {
                columnDef += ` DEFAULT ${this.toBoolean(this.default, dialect)}`;
            } else if (this.isTypeNumeric(this.type, Object.values(DIALECT_TYPE_MAP[dialect])) && !isNaN(this.default)) {
                columnDef += ` DEFAULT ${this.default}`;
            } 
            else if(isNaN(this.default))
            {
                columnDef += '';
            }
            else {
                columnDef += ` DEFAULT ${this.fixDefaultColumnValue(this.default, dialect)}`;
            }
        }

        return columnDef;
    }


    /**
     * Converts a string with quotes into a numeric string without quotes.
     * 
     * This function removes leading and trailing quotes from the input string.
     * If the resulting string is empty, it returns the string '0'.
     *
     * @param {string} value - The input string that may contain quotes.
     * @returns {string} - The numeric string without quotes, or '0' if the string is empty after removing quotes.
     */
    toNumeric(value)
    {
        let result = value;
        result = result.replace(/^"(.*)"$/, '$1');
        result = result.replace(/^'(.*)'$/, '$1');
        if(result == '')
        {
            return '0';
        }
        return result;
    }

    /**
     * Checks if the given type is included in the list of numeric types.
     *
     * This function takes a `type` and checks if it is included in the provided
     * `numericTypes` array. The comparison is case-insensitive.
     *
     * @param {string} type - The type to check (e.g., 'BIGINT', 'FLOAT', etc.).
     * @param {string[]} numericTypes - The list of valid numeric types (e.g., ['BIGINT', 'INT', 'MEDIUMINT', 'SMALLINT', 'TINYINT', 'NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT']).
     * @returns {boolean} - Returns `true` if `type` is included in `numericTypes`, otherwise returns `false`.
     */
    isTypeNumeric(type, numericTypes)
    {
        return numericTypes.includes(type.toUpperCase());
    }

    /**
     * Fixes and normalizes default values in SQL column definitions to ensure they are in the correct format.
     * This function handles various cases, including:
     * - NULL values
     * - Numeric literals (integers and floats)
     * - SQL functions such as CURRENT_TIMESTAMP and NOW()
     * - Date literals (e.g., '2021-01-01')
     * - DateTime literals (e.g., '2021-01-01 00:00:00')
     * - DateTime with microseconds literals (e.g., '2021-01-01 00:00:00.000000')
     * - Boolean literals (TRUE/FALSE)
     * - SQL expressions like CURRENT_TIMESTAMP ON UPDATE and CURRENT_TIMESTAMP ON INSERT
     * - String literals (e.g., 'some text')
     *
     * The function ensures that the value is normalized and consistent with SQL standards.
     *
     * @param {string} defaultValue - The input default value as a string to be fixed and normalized.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sql server".
     * @returns {string|null} - A normalized default value string or null if no valid default value is provided.
     */
    fixDefaultColumnValue(defaultValue, dialect)
    {
        if (defaultValue) {
            // Case 1: Handle 'DEFAULT NULL'
            if (defaultValue.toUpperCase().indexOf('NULL') != -1) {
                defaultValue = 'NULL'; // Correctly treat it as a string "NULL" without quotes
            }
            // Case 2: Handle numbers (integers or floats) and ensure no quotes
            else if (this.isNumber(defaultValue)) {
                defaultValue = "'"+defaultValue.toString()+"'"; // Numeric values are valid as-is (no quotes needed)
            }
            // Case 3: Handle SQL functions like CURRENT_TIMESTAMP
            else if (/^(CURRENT_TIMESTAMP|NOW\(\))$/i.test(defaultValue)) {
                defaultValue = defaultValue.toUpperCase(); // Normalize SQL functions to uppercase
            }
            // Case 4: Handle date/time literals (e.g., '2021-01-01')
            else if (defaultValue.startsWith("'") && defaultValue.endsWith("'") && /\d{4}-\d{2}-\d{2}/.test(defaultValue.slice(1, -1))) {
                defaultValue = "'"+defaultValue.slice(1, -1)+"'"; // Normalize date literals (date only)
            }
            // Case 5: Handle datetime literals (e.g., '2021-01-01 00:00:00')
            else if (/\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/.test(defaultValue)) {
                defaultValue = "'"+defaultValue+"'" // Normalize datetime literals
            }
            // Case 6: Handle datetime with microseconds (e.g., '2021-01-01 00:00:00.000000')
            else if (/\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}\.\d{6}/.test(defaultValue)) {
                defaultValue = "'"+defaultValue+"'" // Normalize datetime with microseconds
            }
            // Case 7: Handle other possible types (e.g., boolean TRUE/FALSE)
            else if (/^(TRUE|FALSE)$/i.test(defaultValue)) {
                defaultValue = defaultValue.toUpperCase(); // Normalize booleans
            }
            // Case 8: Handle CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            else if (/^CURRENT_TIMESTAMP\s+ON\s+UPDATE\s+CURRENT_TIMESTAMP$/i.test(defaultValue)) {
                defaultValue = defaultValue.toUpperCase(); // Normalize the entire expression
            }
            // Case 9: Handle CURRENT_TIMESTAMP ON INSERT CURRENT_TIMESTAMP
            else if (/^CURRENT_TIMESTAMP\s+ON\s+INSERT\s+CURRENT_TIMESTAMP$/i.test(defaultValue)) {
                defaultValue = defaultValue.toUpperCase(); // Normalize the entire expression
            }
            // Case 10: Handle string literals (e.g., 'some text')
            else if (this.isInQuotes(defaultValue)) {
                defaultValue = "'"+defaultValue.slice(1, -1)+"'"; 
            }
        } else {
            defaultValue = null; // If no default value, set it to null
        }
        return defaultValue;
    }

    /**
     * Checks if the given string is enclosed in single quotes.
     * 
     * @param {string} defaultValue - The string to check.
     * @returns {boolean} - Returns true if the string starts and ends with single quotes, otherwise false.
     */
    isInQuotes(defaultValue)
    {
        return defaultValue.startsWith("'") && defaultValue.endsWith("'");
    }

    /**
     * Checks if the given value is a valid number.
     * 
     * @param {string|any} defaultValue - The value to check.
     * @returns {boolean} - Returns true if the value is a number (not NaN) and not an empty string, otherwise false.
     */
    isNumber(defaultValue)
    {
        return !isNaN(defaultValue) && defaultValue !== '';
    }

    /**
     * Converts a given value to a boolean-like string representation based on SQL dialect.
     * 
     * - For "mysql" and "postgresql": returns "TRUE" or "FALSE".
     * - For "sqlite" and "sql server": returns "1" or "0".
     *
     * @param {string} value - The value to be converted to a boolean-like string.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sql server".
     * @returns {string} Boolean representation based on the dialect.
     */
    toBoolean(value, dialect) {
        const val = typeof value === 'string' ? value.trim().toLowerCase() : String(value).toLowerCase();
        const isTrue = val === 'true' || val === '1' || parseInt(val) !== 0;

        const useNumeric = ['sqlite', 'sqlserver'].includes(dialect.toLowerCase());
        
        if (useNumeric) {
            return isTrue ? '1' : '0';
        } else {
            return isTrue ? 'TRUE' : 'FALSE';
        }
    }


    /**
     * Fixes the default value based on the column's type and length.
     * 
     * This method adjusts the default value depending on the column's data type:
     * - For BOOLEAN types, converts values to 'true' or 'false'.
     * - For text types, escapes single quotes.
     * - For numeric types (INTEGER and FLOAT), parses the value accordingly.
     * 
     * @param {string} defaultValue - The default value to fix.
     * @param {string} type - The type of the column.
     * @param {string} length - The length of the column.
     * @returns {string|number} The fixed default value.
     */
    fixDefaultValue(defaultValue, type, length) {
        let result = defaultValue;
    
        if (this.isTypeBoolean(type, length)) {
            result = (defaultValue != 0 && defaultValue.toString().toLowerCase() === 'true') ? 'true' : 'false';
        } else if (this.isNativeValue(defaultValue)) {
            result = defaultValue;
        } else if (this.isTypeText(type)) {
            result = `'${defaultValue.replace(/'/g, "\\'")}'`;
        } else if (this.isTypeInteger(type)) {
            result = parseInt(defaultValue.replace(/[^\d]/g, ''), 10);
        } else if (this.isTypeFloat(type)) {
            result = parseFloat(defaultValue.replace(/[^\d.]/g, ''));
        }
    
        return result;
    }
    
    /**
     * Checks if the given type is a boolean type in MySQL.
     * 
     * @param {string} type - The type to check.
     * @param {string} length - The length of the column (used for TINYINT with length 1).
     * @returns {boolean} True if the type is BOOLEAN, BIT, or TINYINT(1), false otherwise.
     */
    isTypeBoolean(type, length) {
        return type.toLowerCase() === 'boolean' || type.toLowerCase() === 'bool' || type.toLowerCase() === 'bit' || (type.toLowerCase() === 'tinyint' && length == 1);
    }
    
    /**
     * Checks if the given value is a native value (true, false, or null).
     *
     * This function checks if the provided `defaultValue` is a string representing
     * one of the native values: "true", "false", or "null".
     *
     * @param {string} defaultValue The value to check.
     * @return {boolean} True if the value is "true", "false", or "null", false otherwise.
     */
    isNativeValue(defaultValue) {
        return defaultValue.toLowerCase() === 'true' || defaultValue.toLowerCase() === 'false' || defaultValue.toLowerCase() === 'null';
    }

    /**
     * Checks if the given type is a text/string type in MySQL.
     * This includes all text-related types like CHAR, VARCHAR, TEXT, etc.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a text type, false otherwise.
     */
    isTypeText(type) {
        const textTypes = ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext', 'enum', 'set'];
        return textTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the given type is a numeric/integer type in MySQL.
     * This includes all integer-like types such as TINYINT, SMALLINT, INT, BIGINT, etc.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a numeric type, false otherwise.
     */
    isTypeInteger(type) {
        const integerTypes = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'integer'];
        return integerTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the given type is a floating-point type in MySQL.
     * This includes types like FLOAT, DOUBLE, and DECIMAL.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a floating-point type, false otherwise.
     */
    isTypeFloat(type) {
        const floatTypes = ['float', 'double', 'decimal', 'numeric'];
        return floatTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the given type is a date/time type in MySQL.
     * This includes types like DATE, DATETIME, TIMESTAMP, TIME, and YEAR.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a date/time type, false otherwise.
     */
    isTypeDate(type) {
        const dateTypes = ['date', 'datetime', 'timestamp', 'time', 'year'];
        return dateTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the given type is a binary/blob type in MySQL.
     * This includes types like BLOB, TINYBLOB, MEDIUMBLOB, LONGBLOB.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a binary/blob type, false otherwise.
     */
    isTypeBinary(type) {
        const binaryTypes = ['blob', 'tinyblob', 'mediumblob', 'longblob'];
        return binaryTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the column type is one of the range types like NUMERIC, DECIMAL, DOUBLE, FLOAT, and has a value.
     * 
     * @param {Array} withRangeTypes - The list of types that support range values (e.g., NUMERIC, DECIMAL, etc.).
     * @returns {boolean} True if the column type is one of the range types and has a value.
     */
    hasRange(withRangeTypes) {
        return withRangeTypes.includes(this.type) && this.values;
    }

    /**
     * Checks if the column type is one of the value types like ENUM or SET, and has a value.
     * 
     * @param {Array} withValueTypes - The list of types that support specific values (e.g., ENUM, SET).
     * @returns {boolean} True if the column type is one of the value types and has a value.
     */
    hasValue(withValueTypes) {
        return withValueTypes.includes(this.type) && this.values;
    }

    /**
     * Checks if the column type supports length (e.g., VARCHAR, CHAR, etc.) and has a defined length.
     * 
     * @param {Array} withLengthTypes - The list of types that support length (e.g., VARCHAR, CHAR).
     * @returns {boolean} True if the column type supports length and a length is defined.
     */
    hasLength(withLengthTypes) {
        return this.length && withLengthTypes.includes(this.type);
    }

    /**
     * Checks if the column has a valid default value.
     * 
     * @returns {boolean} True if the column has a default value that is not 'null'.
     */
    hasDefault() {
        return this.default && this.default.toLowerCase() !== 'null';
    }
}