/**
 * Class to parse SQL CREATE TABLE statements and extract information about tables and columns.
 * It handles various SQL types and constraints such as primary keys, data types, not null, default values, and more.
 */
class TableParser {

    /**
     * Constructor initializes the type list and parses the given SQL if provided.
     * @param {string} [sql] Optional SQL string to parse upon initialization.
     */
    constructor(sql) {
        this.tableInfo = [];
        this.init();

        if (sql != null) {
            this.parseAll(sql);
        }
    }

    /**
     * Initializes the type list for valid SQL column types.
     */
    init() {
        const typeList = 'TIMESTAMPTZ,TIMESTAMP,SERIAL4,BIGSERIAL,INT2,INT4,INT8,TINYINT,BIGINT,LONGTEXT,MEDIUMTEXT,TEXT,NVARCHAR,VARCHAR,ENUM,SET,NUMERIC,DECIMAL,CHAR,REAL,FLOAT,INTEGER,INT,DATETIME,DATE,DOUBLE,BOOLEAN,BOOL,TIME,UUID,MONEY,BLOB,BIT,JSON';
        this.typeList = typeList.split(',');
    }

    /**
     * Helper function to check if an element exists in an array.
     * @param {Array} haystack The array to search in.
     * @param {string} needle The element to search for.
     * @returns {boolean} Returns true if the element exists in the array, otherwise false.
     */
    inArray(haystack, needle) {
        return haystack.includes(needle);
    }

    /**
     * Checks if a field is a primary key.
     * @param {string} field The field definition.
     * @returns {boolean} True if the field is a primary key, otherwise false.
     */
    isPrimaryKey(field) {
        const f = field.toUpperCase().replace(/\s+/g, ' ').trim();
        return f.includes('PRIMARY KEY');
    }

    /**
     * Checks if a field is auto-incremented.
     * 
     * @param {string} line The field definition.
     * @returns {boolean} True if the field is auto-incremented, otherwise false.
     */
    isAutoIncrement(line) {
        const f = line.toUpperCase().replace(/\s+/g, ' ').trim();
        let ai = false;
        // Check for MySQL/MariaDB's AUTO_INCREMENT
        ai = f.includes('AUTO_INCREMENT');
        
        // Check for PostgreSQL's SERIAL, BIGSERIAL, or nextval() function
        if(!ai)
        {
            ai = f.includes('SERIAL') || f.includes('BIGSERIAL') || f.includes('NEXTVAL');
        }

        return ai; 
    }

    /**
     * Parses a CREATE TABLE SQL statement and extracts table and column information.
     * @param {string} sql The SQL string representing a CREATE TABLE statement.
     * @returns {Object} An object containing table name and columns, along with primary key information.
     */
    parseTable(sql) // NOSONAR
    { 
        let rg_tb = /(create\s+table\s+if\s+not\s+exists|create\s+table)\s(?<tb>.*)\s\(/gim;
        let rg_fld = /(\w+\s+key.*|\w+\s+bigserial|\w+\s+serial4|\w+\s+serial8|\w+\s+tinyint.*|\w+\s+bigint.*|\w+\s+longtext.*|\w+\s+mediumtext.*|\w+\s+text.*|\w+\s+nvarchar.*|\w+\s+varchar.*|\w+\s+char.*|\w+\s+real.*|\w+\s+float.*|\w+\s+integer.*|\w+\s+int.*|\w+\s+datetime.*|\w+\s+date.*|\w+\s+double.*|\w+\s+timestamp.*|\w+\s+timestamptz.*|\w+\s+boolean.*|\w+\s+bool.*|\w+\s+enum\s*\(([^)]+)\)|\w+\s+set\s*\(([^)]+)\)|\w+\s+numeric\s*\(([^)]+)\)|\w+\s+decimal\s*\(([^)]+)\)|\w+\s+float\s*\(([^)]+)\)|\w+\s+int2.*|\w+\s+int4.*|\w+\s+int8.*|\w+\s+time.*|\w+\s+uuid.*|\w+\s+money.*|\w+\s+blob.*|\w+\s+bit.*|\w+\s+json.*)/gim; // NOSONAR
        let rg_fld2 = /(?<fname>\w+)\s+(?<ftype>\w+)(?<fattr>.*)/gi;
        let rg_enum = /enum\s*\(([^)]+)\)/i;
        let rg_set = /set\s*\(([^)]+)\)/i;
        let rg_numeric = /numeric\s*\(([^)]+)\)/i;
        let rg_decimal = /decimal\s*\(([^)]+)\)/i;
        let rg_not_null = /not\s+null/i;
        let rg_pk = /primary\s+key/i;
        let rg_fld_def = /default\s+([^'"]+|'[^']*'|\"[^\"]*\")\s*(comment\s+'[^']*')?/i; // NOSONAR
        let rg_fld_comment = /COMMENT\s*'([^']*)'/i; // NOSONAR
        let rg_pk2 = /(PRIMARY|UNIQUE) KEY[a-zA-Z_0-9\s]+\(([a-zA-Z_0-9,\s]+)\)/gi; // NOSONAR
    
        let result = rg_tb.exec(sql);
        let tableName = result.groups.tb;
    
        let fieldList = [];
        let primaryKey = null;
        let columnList = [];
        let primaryKeyList = [];
    
        while ((result = rg_fld.exec(sql)) != null) {
            let f = result[0];
            let line = f;

            line = line.replace(/[\r\n]+/g, ' ');
    
            // Reset regex for field parsing
            rg_fld2.lastIndex = 0;
            let fld_def = rg_fld2.exec(f);
            
            let dataType = fld_def[2]; // NOSONAR
            let dataTypeOriginal = dataType;
            let isPk = false;
            let enumValues = null;
            let enumArray = null;
            let columnName = fld_def.groups.fname.trim();

            if (rg_enum.test(line)) {
                enumValues = rg_enum.exec(line)[1];
                enumArray = enumValues.split(',').map(val => val.trim().replace(/['"]/g, ''));
            }
            
            if (enumArray == null && rg_set.test(line)) {
                enumValues = rg_set.exec(line)[1];
                enumArray = enumValues.split(',').map(val => val.trim().replace(/['"]/g, ''));
            }

            if (enumArray == null && rg_numeric.test(line)) {
                enumValues = rg_numeric.exec(line)[1];
                enumArray = enumValues.split(',').map(val => val.trim().replace(/['"]/g, ''));
            }

            if (enumArray == null && rg_decimal.test(line)) {
                enumValues = rg_decimal.exec(line)[1];
                enumArray = enumValues.split(',').map(val => val.trim().replace(/['"]/g, ''));
            }

            if (this.isValidType(dataType.toString()) || this.isValidType(dataTypeOriginal.toString())) {
                
                let attr = fld_def.groups.fattr.replace(',', '').trim();
                let nullable = !rg_not_null.test(attr);
                let attr2 = attr.replace(rg_not_null, '');
    
                isPk = rg_pk.test(attr2) || this.isPrimaryKey(line);
                let isAi = this.isAutoIncrement(line);
    
                let def = rg_fld_def.exec(attr2);
                let defaultValue = def && def[1] ? def[1].trim() : null; // NOSONAR
                let length = this.getLength(attr);

                if(length == '' && enumArray != null)
                {
                    length = '\'' + (enumArray.join('\',\'')) + '\'';
                }

                defaultValue = this.fixDefaultValue(defaultValue, dataType, length);
    
                let cmn = rg_fld_comment.exec(attr2);
                let comment = cmn && cmn[1] ? cmn[1].trim() : null; // NOSONAR

                dataType = dataType.trim();
                
                if (isPk) 
                {
                    primaryKeyList.push(columnName);
                }
                if (!this.inArray(columnList, columnName)) {
                    let column = {
                        'Field': columnName,
                        'Type': dataType,
                        'Length': length,
                        'Key': isPk,
                        'Nullable': nullable,
                        'Default': defaultValue, // Only include the default value (no COMMENT)
                        'AutoIncrement': isAi,
                        'EnumValues': enumArray,
                        'Comment': comment // Store the comment separately
                    };

                    fieldList.push(column);
                    columnList.push(columnName);
                }
            } else if (this.isPrimaryKey(line)) {
                let text = result[1];
                let re = /\((.*)\)/;
                let matched = text.match(re); // NOSONAR
                if (primaryKey == null) {
                    primaryKey = matched ? matched[1] : null;
                }
            }
    
            if (primaryKey != null) {
                primaryKey = primaryKey.split('(').join('').split(')').join('');
                for (let i in fieldList) {
                    if (fieldList[i]['Field'] == primaryKey) {
                        fieldList[i]['Key'] = true;
                    }
                }
            }
    
            if (rg_pk2.test(f) && rg_pk.test(f)) {
                let x = f.replace(f.match(rg_pk)[0], ''); // NOSONAR
                x = x.replace('(', '').replace(')', '');
                let pkeys = x.split(',').map(pkey => pkey.trim());
                for (let i in fieldList) {
                    if (this.inArray(pkeys, fieldList[i]['Field'])) {
                        fieldList[i]['Key'] = true;
                    }
                }
            }
        }
    
        if (primaryKey == null && primaryKeyList.length > 0) {
            primaryKey = primaryKeyList[0];
        }

        if(primaryKey != null)
        {
            fieldList = this.updatePrimaryKey(fieldList, primaryKey);
        }
    
        return { tableName: tableName, columns: fieldList, primaryKey: primaryKey };
    }

    /**
     * Updates the primary key flag for a specified field in a list of fields.
     * 
     * This function iterates over a list of field objects, compares the 'Field' property
     * of each object to the given primaryKey, and sets the 'Key' property to true 
     * for the matching field. If no match is found, the field remains unchanged.
     * 
     * @param {Array} fieldList - An array of field objects, each containing a 'Field' and 'Key' property.
     * @param {string} primaryKey - The field name to be set as the primary key.
     * @returns {Array} The updated fieldList with the 'Key' property set to true for the matched field.
     */
    updatePrimaryKey(fieldList, primaryKey)
    {
        fieldList.forEach(function(field, index){
            if(primaryKey.trim() == field.Field.trim())
            {
                fieldList[index].Key = true;
            }
        });
        return fieldList;
    }

    /**
     * Fixes and normalizes default values in SQL statements to ensure they are in the correct format.
     * This function handles various cases, including NULL values, string literals, numbers, SQL functions,
     * date literals, boolean values, and special SQL expressions such as CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
     * and CURRENT_TIMESTAMP ON INSERT CURRENT_TIMESTAMP.
     *
     * The function applies the following normalizations:
     * - 'NULL' is treated as a string without quotes.
     * - Numeric values (integers and floats) are preserved without quotes.
     * - SQL functions like `CURRENT_TIMESTAMP` or `NOW()` are normalized to uppercase.
     * - Date literals (e.g., '2025-01-01') and datetime literals (e.g., '2025-01-01 00:00:00') are preserved with single quotes.
     * - Boolean values 'TRUE' and 'FALSE' are normalized to uppercase.
     * - Special SQL expressions like `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` are normalized to uppercase.
     * - String literals are trimmed of surrounding quotes and re-quoted properly.
     *
     * @param {string} defaultValue - The input default value as a string to be fixed.
     * @param {string} dataType - The data type of the column to help with special case handling.
     * @param {number} length - The length of the column, used to determine how to handle small data types (e.g., TINYINT).
     * @returns {string|null} - A normalized default value string or null if no valid default value is provided.
     */
    fixDefaultValue(defaultValue, dataType, length)
    {
        if (defaultValue) {
            // Case 1: Handle BOOLEAN values (TRUE/FALSE)
            if(this.isBoolean(dataType, length)) {
                defaultValue = this.toBoolean(defaultValue);
            }
            // Case 2: Handle 'DEFAULT NULL'
            else if (defaultValue.toUpperCase().indexOf('NULL') != -1) {
                defaultValue = 'NULL'; // Correctly treat it as a string "NULL" without quotes
            }
            // Case 3: Handle numbers (integers or floats)
            else if (this.isNumber(defaultValue)) {
                defaultValue = "'"+defaultValue.toString()+"'"; // Numeric values are valid as-is (no quotes needed)
            }
            // Case 4: Handle SQL functions like CURRENT_TIMESTAMP or NOW()
            else if (/^(CURRENT_TIMESTAMP|NOW\(\))$/i.test(defaultValue)) {
                defaultValue = defaultValue.toUpperCase(); // Normalize SQL functions to uppercase
            }
            // Case 5: Handle date literals (e.g., '2025-01-01')
            else if (this.isDateTime(defaultValue)) {
                defaultValue = this.createDate(defaultValue); // Normalize datetime with microseconds
            }
            // Case 8: Handle boolean values (TRUE/FALSE) in any part of the string
            else if (/^TRUE/i.test(defaultValue)) {
                defaultValue = 'TRUE'; // Normalize to 'TRUE' if it starts with 'TRUE'
            } 
            else if (/^FALSE/i.test(defaultValue)) {
                defaultValue = 'FALSE'; // Normalize to 'FALSE' if it starts with 'FALSE'
            }
            // Case 9: Handle CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            else if (/^CURRENT_TIMESTAMP\s+ON\s+UPDATE\s+CURRENT_TIMESTAMP$/i.test(defaultValue)) {
                defaultValue = defaultValue.toUpperCase(); // Normalize the entire expression
            }
            // Case 10: Handle CURRENT_TIMESTAMP ON INSERT CURRENT_TIMESTAMP
            else if (/^CURRENT_TIMESTAMP\s+ON\s+INSERT\s+CURRENT_TIMESTAMP$/i.test(defaultValue)) {
                defaultValue = defaultValue.toUpperCase(); // Normalize the entire expression
            }
        } else {
            defaultValue = null; // If no default value, set it to null
        }
        return defaultValue;
    }

    /**
     * Converts a given value to a boolean string ('TRUE' or 'FALSE') based on its content.
     * This function checks if the value contains the string 'TRUE' (case-insensitive) or 
     * if it contains the character '1'. If either condition is met, it returns 'TRUE'; 
     * otherwise, it returns 'FALSE'. This is useful for normalizing boolean-like values 
     * (e.g., strings such as '1', 'TRUE', 'true', etc.) into a standardized 'TRUE'/'FALSE' format.
     *
     * @param {string} defaultValue - The input value to be converted to a boolean string.
     * @returns {string} - Returns 'TRUE' if the input contains 'TRUE' or '1', otherwise returns 'FALSE'.
     */
    toBoolean(defaultValue)
    {
        return defaultValue.toUpperCase().indexOf('TRUE') != -1 || defaultValue.indexOf('1') != -1 ? 'TRUE' : 'FALSE';
    }

    /**
     * Creates a properly quoted date string from the given input.
     * This function removes any surrounding quotes (both single and double) from the input 
     * and ensures the final value is enclosed within single quotes. If the input is null, 
     * it returns null. Additionally, it handles trimming and possible variations in 
     * quoting style.
     *
     * @param {string|null} defaultValue - The input value to be formatted as a date string.
     * @returns {string|null} - The formatted date string enclosed in single quotes, or null if the input is null.
     */
    createDate(defaultValue)
    {
        if(defaultValue == null)
        {
            return null;
        }
        defaultValue = defaultValue.trim();
        if (this.isInQuotes(defaultValue)) {
            defaultValue = defaultValue.slice(1, -1); 
        }
        else if(defaultValue.startsWith("'"))
        {
            defaultValue = defaultValue.substring(1);
        }
        else if(defaultValue.endsWith("'"))
        {
            defaultValue = defaultValue.substring(0, defaultValue.length-3);
        }

        return `'${defaultValue}'`;
    }

    /**
     * Checks if the input value is a valid date, datetime, or time format.
     * This function can detect the following formats:
     * - Date format (YYYY-MM-DD)
     * - Datetime format (YYYY-MM-DD HH:MM:SS)
     * - Datetime with microseconds (YYYY-MM-DD HH:MM:SS.SSSSSS)
     * - Time format (HH:MM:SS)
     *
     * @param {string} defaultValue - The input value to check.
     * @returns {boolean} - Returns true if the value matches one of the valid date/time formats.
     */
    isDateTime(defaultValue) {
        // Check for datetime with microseconds (e.g., '2025-01-01 12:30:45.123456')
        const dateTimeWithMicroseconds = /\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}\.\d{6}/;
        
        // Check for datetime (e.g., '2025-01-01 12:30:45')
        const dateTime = /\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/;
        
        // Check for date (e.g., '2025-01-01')
        const date = /\d{4}-\d{2}-\d{2}/;
        
        // Check for time (e.g., '01:23:45')
        const time = /\d{2}:\d{2}:\d{2}/;

        // Check if the value matches any of the patterns
        return dateTimeWithMicroseconds.test(defaultValue) || dateTime.test(defaultValue) || date.test(defaultValue) || time.test(defaultValue);
    }

    /**
     * Checks if the given data type is a boolean type or a small integer type (such as TINYINT(1)).
     * This function returns true if the data type is a boolean (e.g., BOOL) or a TINYINT with a length of 1 
     * (which is commonly used to represent boolean values in databases).
     *
     * @param {string} dataType - The data type of the column, typically from a database schema.
     * @param {number} length - The length of the column, used to help determine if it's a boolean representation.
     * @returns {boolean} - Returns true if the data type is boolean or a TINYINT(1), otherwise false.
     */
    isBoolean(dataType, length) {
        return dataType.toUpperCase().indexOf('BOOL') != -1 || (dataType.toUpperCase().indexOf('TINYINT') != -1 && length == 1);
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
     * Extracts the length of a column type if specified (e.g., VARCHAR(255)).
     * @param {string} text The attribute text containing the length (e.g., VARCHAR(255)).
     * @returns {string} The length of the column type or an empty string if no length is found.
     */
    getLength(text) {
        if (text.includes('(') && text.includes(')')) {
            let re = /\((\d+)\)/;
            let match = text.match(re); // NOSONAR
            return match ? match[1] : '';
        }
        return '';
    }

    /**
     * Checks if the given data type is valid according to the predefined type list.
     * @param {string} dataType The data type to check (e.g., 'varchar', 'int').
     * @returns {boolean} True if the data type is valid, otherwise false.
     */
    isValidType(dataType) {
        return this.typeList.includes(dataType.toUpperCase());
    }

    /**
     * Parses all CREATE TABLE statements from a SQL string and collects the information.
     * @param {string} sql The SQL string containing multiple CREATE TABLE statements.
     */
    parseAll(sql) {

        let inf = [];
        const parsedResult = this.parseSQL(sql);
        for(let i in parsedResult)
        {
            let sub = this.formatSQL(parsedResult[i].query);
            try
            {
                let info = this.parseTable(sub);
                inf.push(info);
            }
            catch(e)
            {
                // If query is not CREATE TABLE or invalid
            }
        }
        this.tableInfo = inf;
    }
    
    /**
     * Formats an SQL string to ensure consistent indentation and spacing.
     * Specifically, it ensures that:
     * - Extra spaces are removed.
     * - `CREATE TABLE` is properly formatted.
     * - `IF NOT EXISTS` (if present) is preserved and properly formatted.
     * - Parentheses are correctly placed.
     * - Columns are separated by line breaks with appropriate indentation.
     *
     * @param {string} sql - The raw SQL string to format.
     * @returns {string} - The formatted SQL string.
     */
    formatSQL(sql) {
        // Remove excess whitespace throughout the entire string
        sql = sql.replace(/\s+/g, ' ');

        // Ensure "CREATE TABLE" is consistently formatted
        sql = sql.replace(/\bCREATE\s+TABLE\s+/i, 'CREATE TABLE ');

        // Handle and preserve "IF NOT EXISTS" if it exists, ensuring consistent formatting
        sql = sql.replace(/\bIF\s+NOT\s+EXISTS\s+/i, 'IF NOT EXISTS ');

        // Ensure parentheses are positioned correctly by removing any extra spaces before the opening parenthesis
        sql = sql.replace(/\s*\(/, ' (');  // Remove spaces before opening parenthesis

        // Ensure there are no extra spaces after the closing parenthesis and move the closing parenthesis to a new line
        sql = sql.replace(/\s*\)\s*;/, "\r\n);");  // Remove spaces after closing parenthesis and ensure it moves to the next line

        // Add a new line after the first opening parenthesis to separate the columns
        sql = sql.replace(/\(\s*/, "(\n\t", sql);  // Add a new line after the first '(' to format columns

        // Ensure that columns are separated by line breaks and indented properly
        sql = sql.replace(/,\s*/g, ",\n\t");  // Add new lines and indentation after commas separating columns

        // Add a new line before "CREATE TABLE" to ensure proper formatting
        sql = sql.replace("CREATE TABLE", "\nCREATE TABLE", sql);  // Add a new line before CREATE TABLE to start fresh

        return sql;
    }

    /**
     * Parses a SQL script by splitting it into individual queries, handling comments, 
     * whitespace, and custom delimiters. It returns an array of query objects with 
     * each SQL query and its associated delimiter.
     *
     * @param {string} sql - The SQL script as a string.
     * @returns {Array} - An array of objects, where each object contains a `query` (the SQL statement) 
     *                    and `delimiter` (the delimiter used for the query).
     */
    parseSQL(sql) {
        sql = sql.replace(/\n/g, "\r\n");
        sql = sql.replace(/\r\r\n/g, "\r\n");
    
        let arr = sql.split("\r\n");
        let arr2 = [];
    
        arr.forEach((val) => {
            val = val.trim();
            if (!val.startsWith("-- ") && val !== "--" && val !== "") {
                arr2.push(val);
            }
        });
    
        arr = arr2;
        let append = 0;
        let skip = 0;
        let start = 1;
        let nquery = -1;
        let delimiter = ";";
        let queryArray = [];
        let delimiterArray = [];
    
        arr.forEach((text) => {
            if (text === "") {
                if (append === 1) {
                    queryArray[nquery] += "\r\n";
                }
            }
    
            if (append === 0) {
                if (text.trim().startsWith("--")) {
                    skip = 1;
                    nquery++;
                    start = 1;
                    append = 0;
                } else {
                    skip = 0;
                }
            }
    
            if (skip === 0) {
                if (start === 1) {
                    nquery++;
                    queryArray[nquery] = "";
                    delimiterArray[nquery] = delimiter;
                    start = 0;
                }
    
                queryArray[nquery] += text + "\r\n";
                delimiterArray[nquery] = delimiter;
                text = text.trim();
                start = text.length - delimiter.length - 1;
    
                if (text.substring(start).includes(delimiter) || text === delimiter) {
                    nquery++;
                    start = 1;
                    append = 0;
                } else {
                    start = 0;
                    append = 1;
                }
    
                delimiterArray[nquery] = delimiter;
    
                if (text.toLowerCase().startsWith("delimiter ")) {
                    text = text.trim().replace(/\s+/g, " ");
                    let arr2 = text.split(" ");
                    delimiter = arr2[1];
                    nquery++;
                    delimiterArray[nquery] = delimiter;
                    start = 1;
                    append = 0;
                }
            }
        });
    
        let result = [];
        queryArray.forEach((sql, line) => {
            let delimiter = delimiterArray[line];
            if (!sql.toLowerCase().startsWith("delimiter ")) {
                sql = sql.trim();
                sql = sql.substring(0, sql.length - delimiter.length);
                result.push({ query: sql, delimiter: delimiter });
            }
        });
    
        return result;
    }

    /**
     * Returns the parsed result containing table and column information.
     * @returns {Array} The parsed table information.
     */
    getResult() {
        return this.tableInfo;
    }
}
