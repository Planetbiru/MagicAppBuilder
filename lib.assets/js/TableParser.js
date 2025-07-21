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
        this.data = {};
        this.init();
        if (sql != null) {
            this.parseAll(sql);
        }
    }

    /**
     * Initializes the type list for valid SQL column types.
     */
    init() {
        const typeList = 'TIMESTAMPTZ,TIMESTAMP,SERIAL4,BIGSERIAL,INT2,INT4,INT8,TINYINT,BIGINT,LONGTEXT,MEDIUMTEXT,TEXT,NVARCHAR,VARCHAR,ENUM,SET,NUMERIC,DECIMAL,CHAR,REAL,FLOAT,INTEGER,INT,DATETIME2,DATETIME,DATE,DOUBLE,BOOLEAN,BOOL,TIME,UUID,MONEY,BLOB,BIT,JSON';
        this.typeList = typeList.split(',');
    }

    /**
     * Parses a single SQL INSERT query string.
     * Supports column names quoted with backticks (` `), square brackets (`[ ]`), or double quotes (`" "`).
     * Handles both single-row and multi-row INSERT statements.
     * Accurately extracts complex values, including HTML, CSS, JavaScript, and properly escaped
     * single quotes (`''`) and double quotes (`""`). This method provides a robust
     * mechanism for parsing all value types and ensuring all rows are extracted.
     *
     * @param {string} query The SQL INSERT query string to parse.
     * @returns {object|null} An object with `tableName` (string), `columns` (string[]), and `rows` (Array<Array<string|number|boolean|null>>).
     * Returns `null` if the query format is invalid or cannot be parsed.
     */
    parseInsertQuery(query) {
        // Trim leading/trailing whitespace from the query.
        const trimmedQuery = query.trim();
        // Regex to extract table name, column list (optional), and the VALUES section.
        // `is` flag enables dotall mode (s) and case-insensitivity (i).
        const insertRegex = /INSERT INTO\s+(`[^`]+`|\[[^\]]+\]|"[^"]+"|[\w.]+)\s*(?:\(([^)]*?)\))?\s*VALUES\s*(.*)/is; // NOSONAR
        const match = trimmedQuery.match(insertRegex); // NOSONAR

        // If the query does not match the INSERT format, return null.
        if (!match) {
            return null;
        }

        // Extract and clean the table name, removing any surrounding quotes/brackets.
        const tableName = match[1].replace(/^[`\["]|[`\]"]$/g, ''); // NOSONAR
        // Extract and clean column names, if provided.
        // It handles different quoting styles for column names.
        const columns = match[2] ? match[2].match(/(`[^`]+`|\[[^\]]+\]|"[^"]+"|[^,]+)/g).map(c => c.trim().replace(/^[`\["]|[`\]"]$/g, '')) : []; // NOSONAR // If no columns are explicitly listed, return an empty array.

        // Get the raw string section containing all row values.
        const valueSection = match[3];
        // Extract individual row strings from the value section, handling nested parentheses and quoted values.
        const rowStrings = this.extractRowStrings(valueSection);
        const rawRows = rowStrings.map(row => this.parseRow(row));
        // Parse each row string into an array of typed values.
        const rows = rawRows.map(values => {
            const obj = {};
            for (let i = 0; i < columns.length; i++) {
                obj[columns[i]] = values[i];
            }
            return obj;
        });

        // Return the parsed query structure.
        return { tableName, columns, rows };
    }

    /**
     * Extracts individual row content strings from the overall VALUES section.
     * This method is designed to correctly separate rows, even when values contain
     * nested parentheses or commas within quoted strings.
     *
     * @param {string} valueSection The part of the query after `VALUES`.
     * @returns {string[]} An array of strings, where each string represents the content of one row (without the outer parentheses).
     */
    extractRowStrings(valueSection) // NOSONAR 
    {
        const rows = [];
        let buffer = ''; // Accumulates characters for the current row.
        let inSingle = false; // Flag to track if currently inside a single-quoted string.
        let inDouble = false; // Flag to track if currently inside a double-quoted string.
        let escape = false; // Flag to track if the next character is escaped.
        let depth = 0; // Tracks the nesting depth of parentheses, crucial for distinguishing row boundaries.

        // Iterate through each character of the valueSection.
        for (let i = 0; i < valueSection.length; i++) {
            const char = valueSection[i];
            const next = valueSection[i + 1]; // Lookahead to check for escaped quotes.

            // Handle escaped characters (e.g., `\` followed by any char).
            if (escape) {
                buffer += char;
                escape = false; // Reset escape flag.
            } else if (char === '\\') {
                // If a backslash is encountered, the next char is escaped.
                buffer += char;
                escape = true;
            } else if (char === "'" && !inDouble) {
                // Handle single quotes, only if not inside a double-quoted string.
                buffer += char;
                if (inSingle && next === "'") {
                    // Handle SQL-style escaped single quote (two single quotes).
                    buffer += next;
                    i++; // NOSONAR // Skip the next character.
                } else {
                    // Toggle inSingle state.
                    inSingle = !inSingle;
                }
            } else if (char === '"' && !inSingle) {
                // Handle double quotes, only if not inside a single-quoted string.
                buffer += char;
                if (inDouble && next === '"') {
                    // Handle SQL-style escaped double quote (two double quotes, less common but supported).
                    buffer += next;
                    i++; // NOSONAR // Skip the next character.
                } else {
                    // Toggle inDouble state.
                    inDouble = !inDouble;
                }
            } else if (char === '(' && !inSingle && !inDouble) {
                // Handle opening parenthesis for a row.
                // Only consider it a new row start if at depth 0.
                if (depth === 0) buffer = ''; // Clear buffer for a new row.
                buffer += char;
                depth++; // Increase depth.
            } else if (char === ')' && !inSingle && !inDouble) {
                // Handle closing parenthesis for a row.
                buffer += char;
                depth--; // Decrease depth.
                // If depth returns to 0, it means a full row block `(...)` has been parsed.
                if (depth === 0) {
                    // Ensure the buffer starts and ends with parentheses, then extract content.
                    if (buffer.startsWith('(') && buffer.endsWith(')')) {
                        rows.push(buffer.substring(1, buffer.length - 1)); // Remove outer parentheses.
                    } else {
                        rows.push(buffer); // Fallback, though this case should ideally not be hit with correct logic.
                    }
                    buffer = ''; // Reset buffer for the next row.
                }
            } else {
                // Append other characters to the current buffer.
                buffer += char;
            }
        }

        return rows;
    }

    /**
     * A simple trim function, ensuring consistency if a custom trim behavior is needed.
     * @param {string} str The string to trim.
     * @returns {string} The trimmed string.
     */
    customTrim(str) {
        return str.trim();
    }

    /**
     * Parses a single row content string into an array of JavaScript values (strings, numbers, booleans, null).
     * This method handles commas as value delimiters and correctly parses different SQL literal types.
     *
     * @param {string} rowContent The string content of a single row (e.g., "1, 'Alice', NULL").
     * @returns {(string|number|boolean|null)[]} An array of parsed values for the row.
     */
    parseRow(rowContent) // NOSONAR 
    {
        const values = [];
        let current = ''; // Accumulates characters for the current value.
        let inSingle = false; // Flag: inside single-quoted string.
        let inDouble = false; // Flag: inside double-quoted string.
        let escape = false; // Flag: next character is escaped.

        /**
         * Helper function to process and push the accumulated `current` value into the `values` array.
         * Handles type conversion (null, boolean, number) and string unescaping.
         */
        const pushValue = () => {
            // Do not trim `current` entirely to preserve whitespace within the string (e.g., if it's '  ').
            const raw = current;
            const trimmed = this.customTrim(raw); // Use customTrim for type detection and initial processing.
            let parsed;

            // Determine the actual type and parse the value.
            if (!trimmed) {
                // Handle empty string or string containing only whitespace.
                parsed = '';
            } else if (/^null$/i.test(trimmed)) {
                // Parse NULL literal.
                parsed = null;
            } else if (/^true$/i.test(trimmed)) {
                // Parse TRUE boolean literal.
                parsed = true;
            } else if (/^false$/i.test(trimmed)) {
                // Parse FALSE boolean literal.
                parsed = false;
            } else if (trimmed.startsWith("'") && trimmed.endsWith("'")) {
                // Handle SQL-style single-quoted string.
                parsed = trimmed.slice(1, -1) // Remove outer quotes.
                    .replace(/''/g, "'") // Unescape doubled single quotes (`''` -> `'`).
                    .replace(/\\(["'\\])/g, '$1'); // Unescape `\"`, `\'`, `\\` inside single quotes (common in some dialects).
            } else if (trimmed.startsWith('"') && trimmed.endsWith('"')) {
                parsed = trimmed.slice(1, -1)
                    .replace(/''/g, "'")            // handle '' as single quote (even in double quotes!)
                    .replace(/\\\\/g, '\\')         // unescape backslash
                    .replace(/\\"/g, '"');          // unescape double quote
            } else if (!isNaN(trimmed) && trimmed !== '') { // Check if it's a valid number.
                // Parse numeric literal.
                parsed = Number(trimmed);
            } else {
                // Default to string for unquoted values.
                parsed = trimmed;
            }

            values.push(parsed); // Add the parsed value to the array.
            current = ''; // Reset `current` for the next value.
        };

        // Iterate through each character in the row content to parse individual values.
        for (let i = 0; i < rowContent.length; i++) {
            const char = rowContent[i];
            const next = rowContent[i + 1]; // Lookahead for escaped/doubled quotes.

            // Handle escape sequences (`\`).
            if (escape) {
                current += char;
                escape = false;
            } else if (char === '\\') {
                current += char;
                escape = true;
            } else if (char === "'" && !inDouble) {
                // Handle single quotes, respecting double quotes.
                current += char;
                if (inSingle && next === "'") {
                    current += next; 
                    i++; // NOSONAR // Consume the second single quote for escape.
                } else {
                    inSingle = !inSingle; // Toggle single quote state.
                }
            } else if (char === '"' && !inSingle) {
                // Handle double quotes, respecting single quotes.
                current += char;
                if (inDouble && next === '"') {
                    current += next; 
                    i++; // NOSONAR // Consume the second double quote for escape.
                } else {
                    inDouble = !inDouble; // Toggle double quote state.
                }
            } else if (char === ',' && !inSingle && !inDouble) {
                // If a comma is encountered outside of any quoted string, it's a value delimiter.
                pushValue(); // Process the accumulated value.
            } else {
                // Append other characters to the current value buffer.
                current += char;
            }
        }

        pushValue(); // Push the last value after the loop finishes.
        return values;
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
        sql = sql.replace(/(\bnvarchar\s*)\(\s*max\s*\)/gi, 'TEXT');
        sql = sql.replace(/(\bvarchar\s*)\(\s*max\s*\)/gi, 'TEXT');

        let rg_tb = /(create\s+table\s+if\s+not\s+exists|create\s+table)\s(?<tb>.*)\s\(/gim;
        let rg_fld = /(\w+\s+key.*|\w+\s+bigserial|\w+\s+serial4|\w+\s+serial8|\w+\s+tinyint.*|\w+\s+bigint.*|\w+\s+longtext.*|\w+\s+mediumtext.*|\w+\s+text.*|\w+\s+nvarchar.*|\w+\s+varchar.*|\w+\s+char.*|\w+\s+real.*|\w+\s+float.*|\w+\s+integer.*|\w+\s+int.*|\w+\s+datetime2.*|\w+\s+datetime.*|\w+\s+date.*|\w+\s+double.*|\w+\s+timestamp.*|\w+\s+timestamptz.*|\w+\s+boolean.*|\w+\s+bool.*|\w+\s+enum\s*\(([^)]+)\)|\w+\s+set\s*\(([^)]+)\)|\w+\s+numeric\s*\(([^)]+)\)|\w+\s+decimal\s*\(([^)]+)\)|\w+\s+float\s*\(([^)]+)\)|\w+\s+int2.*|\w+\s+int4.*|\w+\s+int8.*|\w+\s+time.*|\w+\s+uuid.*|\w+\s+money.*|\w+\s+blob.*|\w+\s+bit.*|\w+\s+json.*)/gim; // NOSONAR
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
                    if(isPk)
                    {
                        nullable = false;
                    }
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
                // If parsing fails, log the error and continue.
            }   
        }
        this.tableInfo = inf;
    }

    /**
     * Parses INSERT INTO statements from a SQL string and extracts row data into a structured object.
     * 
     * The extracted data is stored in the `this.data` property, where each key is the table name,
     * and the value is an array of row objects inserted into that table.
     * 
     * @param {string} sql - The SQL string containing one or more INSERT INTO statements.
     */
    parseData(sql) {
        this.data = {};
        const parsedResult = this.parseSQL(sql);
        for (let i in parsedResult) {
            let data = this.parseInsertQuery(parsedResult[i].query);
            if (
                data != null &&
                data.tableName != null &&
                data.tableName !== '' &&
                typeof data.rows !== 'undefined' &&
                Array.isArray(data.rows) &&
                data.rows.length > 0
            ) {
                if (typeof this.data[data.tableName] === 'undefined') {
                    this.data[data.tableName] = [];
                }
                // Merge the extracted rows into the corresponding table's array
                this.data[data.tableName].push(...data.rows);
            }
        }
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
