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

        if (sql) {
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
        let rg_fld = /(\w+\s+key.*|\w+\s+bigserial|\w+\s+serial4|\w+\s+serial8|\w+\s+tinyint.*|\w+\s+bigint.*|\w+\s+longtext.*|\w+\s+mediumtext.*|\w+\s+text.*|\w+\s+nvarchar.*|\w+\s+varchar.*|\w+\s+char.*|\w+\s+real.*|\w+\s+float.*|\w+\s+integer.*|\w+\s+int.*|\w+\s+datetime.*|\w+\s+date.*|\w+\s+double.*|\w+\s+timestamp.*|\w+\s+timestamptz.*|\w+\s+boolean.*|\w+\s+bool.*|\w+\s+enum\s*\(.*\)|\w+\s+set\s*\(.*\)|\w+\s+numeric\s*\(.*\)|\w+\s+decimal\s*\(.*\)|\w+\s+int2.*|\w+\s+int4.*|\w+\s+int8.*|\w+\s+time.*|\w+\s+uuid.*|\w+\s+money.*|\w+\s+blob.*|\w+\s+bit.*|\w+\s+json.*)/gim; // NOSONAR
        let rg_fld2 = /(?<fname>\w+)\s+(?<ftype>\w+)(?<fattr>.*)/gi;
        let rg_enum = /enum\s*\(([^)]+)\)/i;
        let rg_set = /set\s*\(([^)]+)\)/i;
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
    
            // Reset regex for field parsing
            rg_fld2.lastIndex = 0;
            let fld_def = rg_fld2.exec(f);
            let dataTypeRaw = fld_def[0]; // NOSONAR
            let dataType = fld_def[2]; // NOSONAR
            let dataTypeOriginal = dataType;
            let isPk = false;
            let enumValues = null;
            let enumArray = null;
    
            if (rg_enum.test(dataTypeRaw)) {
                enumValues = rg_enum.exec(dataTypeRaw)[1];
                enumArray = enumValues.split(',').map(val => val.trim().replace(/['"]/g, ''));
            }
            if (rg_set.test(dataTypeRaw)) {
                enumValues = rg_set.exec(dataTypeRaw)[1];
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

                defaultValue = this.fixDefaultValue(defaultValue);
    
                let cmn = rg_fld_comment.exec(attr2);
                let comment = cmn && cmn[1] ? cmn[1].trim() : null; // NOSONAR

                dataType = dataType.trim();

                let length = this.getLength(attr);
    
                let columnName = fld_def.groups.fname.trim();
                if (isPk) primaryKeyList.push(columnName);
                if (!this.inArray(columnList, columnName)) {
                    fieldList.push({
                        'Field': columnName,
                        'Type': dataType,
                        'Length': length,
                        'Key': isPk,
                        'Nullable': nullable,
                        'Default': defaultValue, // Only include the default value (no COMMENT)
                        'AutoIncrement': isAi,
                        'EnumValues': enumArray,
                        'Comment': comment // Store the comment separately
                    });
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
    
        if (primaryKey == null) {
            primaryKey = primaryKeyList[0];
        }
    
        return { tableName: tableName, columns: fieldList, primaryKey: primaryKey };
    }

    /**
     * Fixes and normalizes default values in SQL statements to ensure they are in the correct format.
     * This function handles various cases, including NULL values, string literals, numbers, SQL functions, 
     * date literals, boolean values, and special SQL functions like CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP 
     * and CURRENT_TIMESTAMP ON INSERT CURRENT_TIMESTAMP.
     *
     * @param {string} defaultValue - The input default value as a string to be fixed.
     * @returns {string|null} - A normalized default value string or null if no valid default value is provided.
     */
    fixDefaultValue(defaultValue)
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
     * Extracts the length of a column type if specified (e.g., VARCHAR(255)).
     * @param {string} text The attribute text containing the length (e.g., VARCHAR(255)).
     * @returns {string} The length of the column type or an empty string if no length is found.
     */
    getLength(text) {
        // Check if the text contains parentheses and ensure there is a number inside
        if (text.includes('(') && text.includes(')')) {
            // Adjusting regex to capture the first number inside parentheses
            let re = /\((\d+)\)/;
            let match = text.match(re); // NOSONAR
            // If a match is found, return the number inside the parentheses; otherwise, return an empty string
            return match ? match[1] : '';
        }
        return ''; // Return an empty string if no parentheses are found
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
