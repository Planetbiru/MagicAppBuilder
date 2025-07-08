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
     * Creates an instance of the Column class.
     * 
     * @param {string} name - The name of the column.
     * @param {string} [type="VARCHAR"] - The data type of the column (e.g., "VARCHAR", "INT", "ENUM", etc.).
     * @param {string} [length=""] - The length of the column for types like VARCHAR (optional).
     * @param {boolean} [nullable=false] - Whether the column can be NULL (default is false).
     * @param {string} [defaultValue=""] - The default value for the column (optional).
     * @param {boolean} [primaryKey=false] - Whether the column is a primary key (default is false).
     * @param {boolean} [autoIncrement=false] - Whether the column auto-increments (default is false).
     * @param {string} [values=""] - The valid values for ENUM or SET types, or the range of values for types like DECIMAL, NUMERIC, FLOAT, and DOUBLE (optional, comma-separated).
     */
    constructor(name, type = "VARCHAR", length = "", nullable = false, defaultValue = "", primaryKey = false, autoIncrement = false, values = "") //NOSONAR
    {
        this.name = name;
        this.type = type;
        this.length = length;
        this.nullable = nullable;
        this.default = defaultValue;
        this.primaryKey = primaryKey;
        this.autoIncrement = autoIncrement;
        this.values = values;
    }

    /**
     * Converts the column definition into a valid SQL column definition string.
     * 
     * This method generates the SQL column definition based on the column's properties such as:
     * - data type (e.g., VARCHAR, INT, ENUM, etc.)
     * - nullable status
     * - primary key status
     * - auto-increment behavior
     * - default value
     * - valid values for ENUM/SET types or range values for DECIMAL, NUMERIC, FLOAT, and DOUBLE.
     * 
     * @returns {string} The SQL column definition.
     */
    toSQL() // NOSONAR
    {
        let withValueTypes = ['ENUM', 'SET'];
        let withRangeTypes = ['NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT'];
        let numericTypes = ['BIGINT', 'INT', 'MEDIUMINT', 'SMALLINT', 'TINYINT', 'NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT'];
        let withLengthTypes = [
            'VARCHAR', 'CHAR', 
            'VARBINARY', 'BINARY',
            'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT',
            'BIT'
        ];

        let columnDef = "";
        
        if (this.hasValue(withValueTypes)) 
        {
            // Handle ENUM and SET types
            let enumList = this.values.split(',').map(val => `'${val.trim()}'`).join(', ');
            columnDef = `${this.name} ${this.type}(${enumList})`;
        }     
        else if (this.hasRange(withRangeTypes)) 
        {
            // Handle range types like NUMERIC, DECIMAL, DOUBLE, FLOAT
            let rangeList = this.values.split(',').map(val => val.trim());

            // Filter only integer values, remove non-integer values
            rangeList = rangeList.filter(val => Number.isInteger(parseFloat(val)));

            // Join the valid integer values back into a range string
            let rangeString = rangeList.join(', ');

            // Set the column definition
            if (rangeList.length < 2) {
                // If no valid integer values are present, don't set a range
                columnDef = `${this.name} ${this.type}`;
            } else {
                // If there are valid integer values, include the range in the column definition
                columnDef = `${this.name} ${this.type}(${rangeString})`;
            }
        } 
        else if (this.hasLength(withLengthTypes)) 
        {
            // Handle types that support length, like VARCHAR, CHAR, etc.
            columnDef = `${this.name} ${this.type}(${this.length})`;
        }
        else
        {
            columnDef = `${this.name} ${this.type}`;
        }
        
        if (!this.primaryKey) {
            // Nullable
            columnDef += this.nullable ? " NULL" : " NOT NULL";
        } else {
            columnDef += " NOT NULL PRIMARY KEY";
        }
        // Auto increment logic
        if (this.autoIncrement) {
            columnDef += " AUTO_INCREMENT";
        }
        // Default value logic
        if (this.hasDefault()) {
            if(this.isTypeBoolean(this.type, this.length)) {
                columnDef += ` DEFAULT ${this.toBoolean(this.default)}`; // No quotes for boolean values
            } else if(this.isTypeNumeric(this.type, numericTypes)) {
                columnDef += ` DEFAULT ${this.toNumeric(this.default)}`; // No quotes for boolean values
            } else if (numericTypes.includes(this.type) && !isNaN(this.default)) {
                columnDef += ` DEFAULT ${this.default}`; // No quotes for numeric values
            } else {
                columnDef += ` DEFAULT ${this.fixDefaultColumnValue(this.default)}`; // Default is a string, so use quotes
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
     * @returns {string|null} - A normalized default value string or null if no valid default value is provided.
     */
    fixDefaultColumnValue(defaultValue)
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
     * Converts a given value to a boolean-like string representation.
     * 
     * This function evaluates the input value and converts it to either 'TRUE' or 'FALSE'.
     * It interprets values such as:
     * - Any string containing 'false' (case-insensitive) will return 'FALSE'.
     * - Any non-zero integer or string 'true' (case-insensitive) will return 'TRUE'.
     * - Any other value will return 'FALSE'.
     *
     * @param {string} value - The value to be converted to a boolean-like string.
     * @returns {string} 'TRUE' or 'FALSE' based on the value.
     */
    toBoolean(value) {
        if (value.toLowerCase().indexOf('false') !== -1) {
            return 'FALSE';
        }
        return (parseInt(value) !== 0 || value.toLowerCase() === 'true') ? 'TRUE' : 'FALSE';
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
     * @returns {string} The SQL statement for creating the entity (table).
     */
    toSQL() {
        let sql = `CREATE TABLE IF NOT EXISTS ${this.name} (\r\n`;
        this.columns.forEach(col => {
            sql += `\t${col.toSQL()},\r\n`;
        });
        sql = sql.slice(0, -3); // Remove trailing comma
        sql += "\r\n);\r\n\r\n";
        return sql;
    }

    /**
     * Generates a single SQL INSERT statement with multiple rows.
     *
     * @returns {string} SQL INSERT statement.
     */
    toSQLInsert() {
        if (!this.data || this.data.length === 0) return '';

        const columnNames = this.columns.map(col => col.name);
        const valuesList = this.data.map(row => {
            return '(' + columnNames.map(name => this.formatValue(row[name])).join(', ') + ')';
        });

        return `INSERT INTO ${this.name} (${columnNames.join(', ')}) VALUES\n${valuesList.join(',\n')};`+'\r\n';
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
     * Formats a value for SQL insertion, handling strings, nulls, and escaping.
     * 
     * @param {*} value - The value to format.
     * @returns {string} SQL-safe representation of the value.
     */
    formatValue(value) {
        if (value === null || value === undefined) {
            return 'NULL';
        } else if (typeof value === 'number') {
            return value.toString();
        } else if (typeof value === 'boolean') {
            return value ? '1' : '0';
        } else {
            // Escape single quotes by doubling them
            const escaped = String(value).replace(/'/g, "''");
            return `'${escaped}'`;
        }
    }
}

function Diagram(name, sortOrder, originalEntities)
{
    /**
     * Array of entity names included in this diagram.
     * @type {string[]}
     */
    this.entitieNames = [];
    /**
     * Name of the diagram.
     * @type {string}
     */
    this.name = name;
    /**
     * Sort order of the diagram.
     * @type {number}
     */
    this.sortOrder = sortOrder;
    /**
     * Original entities available for the diagram.
     * @type {Entity[]}
     */
    this.originalEntities = originalEntities;
    /**
     * Whether this diagram is active.
     * @type {boolean}
     */
    this.active = false;
    /**
     * Creates the ERD for this diagram.
     * @param {number} updatedWidth - The width for rendering.
     * @param {boolean} drawRelationship - Whether to draw relationships.
     */
    this.createERD = function(updatedWidth, drawRelationship)
    {
        this.entityRenderer.createERD(this.getData(), updatedWidth, drawRelationship);
    }
    /**
     * Gets the entities included in this diagram.
     * @returns {Entity[]} The entities in the diagram.
     */
    this.getData = function()
    {
        let entities = [];
        for(let entity of this.originalEntities)
        {
            if(this.entitieNames.includes(entity.name))
            {
                entities.push(entity);
            }
        }
        return entities;
    }
}

/**
 * Class to manage the creation, editing, and deletion of database entities (tables),
 * as well as generating SQL statements for the entities.
 * 
 * The EntityEditor class allows users to create new database tables (entities),
 * add or remove columns, modify column properties, and export the generated SQL 
 * statements for creating the tables in MySQL.
 */
class EntityEditor {

    /**
     * Creates an instance of the EntityEditor class.
     * 
     * @param {string} selector - The CSS selector that identifies the target element for the entity editor.
     * @param {Object} [options={}] - Optional configuration settings for the entity editor.
     */
    constructor(selector, options = {}) {
        this.setting = {
            defaultDataType: 'VARCHAR',
            defaultDataLength: '48',
            primaryKeyDataType: 'VARCHAR',
            primaryKeyDataLength: '40'
        };
        this.keyWords = 'absolute,action,add,after,aggregate,alias,all,allocate,alter,analyse,analyze,and,any,are,array,as,asc,assertion,at,authorization,avg,before,begin,between,binary,bit,bit_length,blob,boolean,both,breadth,by,call,cascade,cascaded,case,cast,catalog,char,character,character_length,char_length,check,class,clob,close,coalesce,collate,collation,column,commit,completion,connect,connection,constraint,constraints,constructor,continue,convert,corresponding,count,create,cross,cube,current,current_date,current_path,current_role,current_time,current_timestamp,current_user,cursor,cycle,data,date,day,deallocate,dec,decimal,declare,default,deferrable,deferred,delete,depth,deref,desc,describe,descriptor,destroy,destructor,deterministic,diagnostics,dictionary,disconnect,distinct,do,domain,double,drop,dynamic,each,else,end,end-exec,equals,escape,every,except,exception,exec,execute,exists,external,extract,false,fetch,first,float,for,foreign,found,free,from,full,function,general,get,global,go,goto,grant,group,grouping,having,host,hour,identity,ignore,immediate,in,indicator,initialize,initially,inner,inout,input,insensitive,insert,int,integer,intersect,interval,into,is,isolation,iterate,join,key,language,large,last,lateral,leading,left,less,level,like,limit,local,localtime,localtimestamp,locator,lower,map,match,max,min,minute,modifies,modify,month,names,national,natural,nchar,nclob,new,next,no,none,not,null,nullif,numeric,object,octet_length,of,off,offset,old,on,only,open,operation,option,or,order,ordinality,out,outer,output,overlaps,pad,parameter,parameters,partial,path,placing,position,postfix,precision,prefix,preorder,prepare,preserve,primary,prior,privileges,procedure,public,read,reads,real,recursive,ref,references,referencing,relative,restrict,result,return,returns,revoke,right,role,rollback,rollup,routine,row,rows,savepoint,schema,scope,scroll,search,second,section,select,sequence,session,session_user,set,sets,size,smallint,some,space,specific,specifictype,sql,sqlcode,sqlerror,sqlexception,sqlstate,sqlwarning,start,state,statement,static,structure,substring,sum,system_user,table,temporary,terminate,than,then,time,timestamp,timezone_hour,timezone_minute,to,trailing,transaction,translate,translation,treat,trigger,trim,true,under,union,unique,unknown,unnest,update,upper,usage,user,using,value,values,varchar,variable,varying,view,when,whenever,where,with,without,work,write,year,zone'.split(',');


        // Copy properties from options to setting object
        Object.assign(this.setting, options);

        this.selector = selector;
        this.entities = [];
        this.diagrams = [];
        this.currentEntityIndex = -1;
        this.mysqlDataTypes = [
            'BIGINT', 'INT', 'MEDIUMINT', 'SMALLINT', 'TINYINT',
            'NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT', 
            'BIT',
            'DATE', 'TIME', 'DATETIME', 'TIMESTAMP', 'YEAR',
            'LONGTEXT', 'MEDIUMTEXT', 'TEXT', 'TINYTEXT', 'VARCHAR', 'CHAR',
            'ENUM', 'SET', 
            'LONGBLOB', 'MEDIUMBLOB', 'BLOB', 'TINYBLOB',
            'UUID', 
            'VARBINARY', 'BINARY',
            'POLYGON', 'LINESTRING', 'POINT', 'GEOMETRY',
            'JSON',
        ];
        this.withLengthTypes = [
            'VARCHAR', 'CHAR', 
            'VARBINARY', 'BINARY',
            'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT',
            'BIT'
        ];
        this.withValueTypes = ['ENUM', 'SET'];
        this.withRangeTypes = ['NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT'];
        this.defaultLength = {
            'BIGINT'    : 20,
            'INT'       : 11,
            'MEDIUMINT' : 8,
            'SMALLINT'  : 5,
            'TINYINT'   : 4
        };
        this.autonumberTypes = [
            'BIGINT', 'INT', 'MEDIUMINT', 'SMALLINT', 'TINYINT'
        ];
        this.addDomListeners();

        this.callbackLoadEntity = this.setting.callbackLoadEntity;
        this.callbackSaveEntity = this.setting.callbackSaveEntity;
        this.callbackSaveDiagram = this.setting.callbackSaveDiagram;
        this.callbackLoadTemplate = this.setting.callbackLoadTemplate;
        this.callbackSaveTemplate = this.setting.callbackSaveTemplate;
        this.callbackSaveConfig = this.setting.callbackSaveConfig;
        this.defaultDataType = this.setting.defaultDataType + '';
        this.defaultDataLength = this.setting.defaultDataLength + '';
        this.primaryKeyDataType = this.setting.primaryKeyDataType + '';
        this.primaryKeyDataLength = this.setting.primaryKeyDataLength + '';

        if(typeof this.callbackLoadEntity == 'function')
        {
            this.callbackLoadEntity();
        }
        if(typeof this.callbackLoadTemplate == 'function')
        {
            this.callbackLoadTemplate();
        }
        this.template = {columns: []};
        this.clearBeforeImport = true;
        this.dragSrcRow = null;
        this.tbody = null;
        this.operation = 'create';
        this.currentEntityData = [];
    }

    /**
     * Searches for an entity by name.
     * 
     * @param {string} name - The name of the entity to find.
     * @returns {Object|null} - Returns the entity if found, otherwise returns null.
     */
    getEntityByName(name) {
        for (let entity of this.entities) {
            if (name === entity.name) {
                return entity;
            }
        }
        return null;
    }

    /**
     * Adds event listeners to checkboxes for selecting and deselecting entities.
     */
    addDomListeners() {
        let _this = this;
        document.querySelector(".check-all-entity-structure").addEventListener('change', (event) => {
            let checked = event.target.checked;
            let allEntities = event.target.closest('table').querySelector('tbody').querySelectorAll(".selected-entity-structure");
            
            if(allEntities)
            {
                allEntities.forEach((entity, index) => {
                    entity.checked = checked;
                })
            }
            
            this.exportToSQL();
        });
        document.querySelector(".check-all-entity-data").addEventListener('change', (event) => {
            let checked = event.target.checked;
            let allEntities = event.target.closest('table').querySelector('tbody').querySelectorAll(".selected-entity-data");
            
            if(allEntities)
            {
                allEntities.forEach((entity, index) => {
                    entity.checked = checked;
                })
            }
            
            this.exportToSQL();
        });
        
        document.querySelector(this.selector+" .right-panel .table-list-for-export").addEventListener('change', (event) => {
            if (event.target.classList.contains('selected-entity-structure') || event.target.classList.contains('selected-entity-data')) {
                this.exportToSQL();
            }
        });
        
        document.addEventListener('change', function (event) {
            
            if (event.target.classList.contains('column-primary-key')) {
                const isChecked = event.target.checked;
                const tr = event.target.closest('tr');
                if(isChecked)
                {  
                    tr.querySelector('.column-type').value = _this.primaryKeyDataType;
                    _this.updateColumnLengthInput(tr.querySelector('.column-type'));
                    tr.querySelector('.column-length').value = _this.primaryKeyDataLength;
                    tr.querySelector('.column-nullable').checked = false;
                    tr.querySelector('.column-nullable').disabled = true;
                    if(tr.querySelector('.column-primary-key').checked)
                    {
                        if(_this.autonumberTypes.includes(tr.querySelector('.column-type').value))
                        {
                            tr.querySelector('.column-autoIncrement').disabled = false;
                        }
                        else
                        {
                            tr.querySelector('.column-autoIncrement').disabled = true;
                            tr.querySelector('.column-autoIncrement').checked = false;
                        }
                    }
                }
                else
                {
                    tr.querySelector('.column-nullable').disabled = false;
                }
            }
            
        });

        document.querySelector(this.selector+" .import-file-json").addEventListener("change", function () {
            const file = this.files[0]; // Get the selected file
            if (file) {
                editor.importJSON(file, function(entities){
                    let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                    let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                    let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                    let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                    sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities); 
        
                }); // Import the file if it's selected
    
                
            } else {
                console.log("Please select a JSON file first.");
            }
        });

        document.querySelector(this.selector+" .import-file-sql").addEventListener("change", function () {
            const file = this.files[0]; // Get the selected file
            if (file) {
                editor.importSQLFile(file, function(entities){
                    let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                    let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                    let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                    let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                    sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities); 
        
                }); // Import the file if it's selected
            } else {
                console.log("Please select a JSON file first.");
            }
        });

        document.querySelector(this.selector+" .import-file-sheet").addEventListener("change", function () {
            const file = this.files[0]; // Get the selected file
            if (file) {
                editor.importSheetFile(file); 
            } else {
                console.log("Please select a JSON file first.");
            }
        });
        
        document.querySelector(this.selector).addEventListener('keypress', function(event){
            if(event.key == 'Enter') 
            {
                if((event.target.closest('.entity-container .entity-name') || event.target.closest('.entity-container .column-name')))
                {
                    _this.addColumnIfValid(event.target);
                }
                else if(event.target.closest('.template-container .column-name'))
                {
                    _this.addColumnTemplateIfValid(event.target);
                }
            }
        });
        
        this.initIconEvent();
    }

    /**
     * Checks if the value of the input element is a reserved keyword.
     * If the value is reserved, shows an alert and prevents further input.
     * If the value is not reserved, proceeds to add a column.
     *
     * @param {HTMLElement} element - The input element whose value is to be checked.
     */
    addColumnIfValid(element) {
        // Convert the input value to lowercase for case-insensitive comparison
        const value = element.value.toLowerCase(); 

        // Check if the value is included in the reserved keywords list (case-insensitive)
        if (this.keyWords.some(keyword => keyword.toLowerCase() === value)) {  
            // Show an alert if the word is reserved
            this.showAlertDialog(`The word "${element.value}" is reserved and cannot be used. Please choose another one.`, `Reserved Word`, `Close`, function(){
                // Select the input text for the user to correct it
                element.select();
            });
        } else {
            // Proceed to add a column if the value is not a reserved keyword
            this.addColumn(true);
        }
    }

    /**
     * Checks if the value of the input element is a reserved keyword.
     * If the value is reserved, shows an alert and prevents further input.
     * If the value is not reserved, proceeds to add a column.
     *
     * @param {HTMLElement} element - The input element whose value is to be checked.
     */
    addColumnTemplateIfValid(element) {
        // Convert the input value to lowercase for case-insensitive comparison
        const value = element.value.toLowerCase(); 

        // Check if the value is included in the reserved keywords list (case-insensitive)
        if (this.keyWords.some(keyword => keyword.toLowerCase() === value)) {  
            // Show an alert if the word is reserved
            this.showAlertDialog(`The word "${element.value}" is reserved and cannot be used. Please choose another one.`, `Reserved Word`, `Close`, function(){
                // Select the input text for the user to correct it
                element.select();
            });
        } else {
            // Proceed to add a column if the value is not a reserved keyword
            this.addColumnTemplate(true);
        }
    }

    
    /**
     * Initializes the event listeners for click events on various icons within the SVG.
     * The event listener checks for specific icon classes within the SVG (e.g., move up, move down, edit, delete)
     * and triggers the corresponding methods based on the target element that was clicked.
     * 
     * Event Listeners:
     * - `move-down-icon`: Calls `moveEntityUp()` with the index of the clicked entity.
     * - `move-up-icon`: Calls `moveEntityDown()` with the index of the clicked entity.
     * - `edit-icon`: Calls `editEntity()` with the index of the clicked entity.
     * - `delete-icon`: Calls `deleteEntity()` with the index of the clicked entity.
     */
    initIconEvent()
    {
        let _this = this;
        entityRenderer.svg.addEventListener('click', function(e) {
            if (e.target.closest('.erd-svg .view-data-icon')) {
                _this.viewData(parseInt(e.target.dataset.index))
            }
            if (e.target.closest('.erd-svg .move-down-icon')) {
                _this.moveEntityUp(parseInt(e.target.dataset.index))
            }
            if (e.target.closest('.erd-svg .move-up-icon')) {
                _this.moveEntityDown(parseInt(e.target.dataset.index))
            }
            if (e.target.closest('.erd-svg .edit-icon')) {
                _this.editEntity(parseInt(e.target.dataset.index))
            }
            if (e.target.closest('.erd-svg .delete-icon')) {
                _this.deleteEntity(parseInt(e.target.dataset.index))
            }
        });
    }

    /**
     * Shows the entity editor with the columns of an existing entity or prepares
     * a new entity for editing.
     * 
     * @param {number} entityIndex - The index of the entity to be edited. If not provided, a new entity is created.
     */
    showEditor(entityIndex = -1) {
        if (entityIndex >= 0) {
            this.operation = 'update';
            this.currentEntityIndex = entityIndex;
            const entity = this.entities[entityIndex];
            document.querySelector(this.selector+" .entity-name").value = entity.name;
            document.querySelector(this.selector+" .entity-columns-table-body").innerHTML = '';
            entity.columns.forEach(col => this.addColumnToTable(col));
        } else {
            this.operation = 'create';
            this.currentEntityIndex = -1;
            let newTableName = this.getNewTableName();
            document.querySelector(this.selector+" .entity-name").value = newTableName;
            document.querySelector(this.selector+" .entity-columns-table-body").innerHTML = '';
        }
        document.querySelector(this.selector+" .button-container").style.display = "none";
        document.querySelector(this.selector+" .entity-container").style.display = "block";
        document.querySelector(this.selector+" .template-container").style.display = "none";
        document.querySelector(this.selector+" .editor-form").style.display = "block";
        if(entityIndex == -1)
        {
            document.querySelector(this.selector+" .entity-name").select();
        }
    }

    /**
     * Generates a unique new table name that does not conflict with existing entities.
     *
     * The method starts with "new_table" and appends a numeric suffix if necessary
     * to ensure uniqueness against existing entity names (case-insensitive).
     *
     * @returns {string} A unique table name like "new_table", "new_table_1", "new_table_2", etc.
     */
    getNewTableName() {
        let index = 0;
        let newTableName = 'new_table';

        const existingNames = this.entities.map(e => e.name.toLowerCase());

        while (existingNames.includes(newTableName.toLowerCase())) {
            index++;
            newTableName = `new_table_${index}`;
        }

        return newTableName;
    }


    /**
     * Imports column definitions from a spreadsheet or external source
     * and initializes a new entity for table creation.
     *
     * This method performs the following:
     * - Sets the application state to "create" mode.
     * - Resets the current entity selection.
     * - Uses the provided table name or generates a new one if not given.
     * - Clears existing columns from the UI.
     * - Adds new columns from the given array.
     * - Updates the UI to display the entity editor form.
     *
     * @param {Array<Object>} columns - An array of column definitions to be added.
     *   Each object should represent a column with necessary attributes (e.g., name, type).
     * @param {string} [tableName] - (Optional) A custom table name to use. If omitted, a new name is auto-generated.
     */
    importFromSheet(columns, tableName) {
        this.operation = 'create';
        this.currentEntityIndex = -1;

        tableName = tableName || this.getNewTableName();
        document.querySelector(this.selector + " .entity-name").value = tableName;
        document.querySelector(this.selector + " .entity-columns-table-body").innerHTML = '';

        columns.forEach(column => {
            this.addColumnToTable(column);
        });

        document.querySelector(this.selector + " .button-container").style.display = "none";
        document.querySelector(this.selector + " .entity-container").style.display = "block";
        document.querySelector(this.selector + " .template-container").style.display = "none";
        document.querySelector(this.selector + " .editor-form").style.display = "block";
        document.querySelector(this.selector + " .entity-name").select();
    }

    /**
     * Adds a column to the columns table for editing.
     * 
     * @param {Column} column - The column to add.
     * @param {boolean} [focus=false] - Whether to focus on the new column's name input.
     */
    addColumnToTable(column, focus = false) {
        const tableBody = document.querySelector(this.selector+" .entity-columns-table-body");
        const row = document.createElement("tr");
        let columnLength = column.length == null ? '' : column.length.toString().replace(/\D/g,'');
        let columnDefault = column.default == null ? '' : column.default;
        let typeSimple = column.type.split('(')[0].trim();
        let nullable = '';
        if(column.primaryKey)
        {
            nullable = 'disabled';
        }
        else if(column.nullable)
        {
            nullable = 'checked';
        }
        
        row.innerHTML = `
            <td class="drag-handle"></td>
            <td class="column-action">
                <button onclick="editor.removeColumn(this)" class="icon-delete"></button>
                <button onclick="editor.moveUp(this)" class="icon-move-up"></button>
                <button onclick="editor.moveDown(this)" class="icon-move-down"></button>    
            </td>
            <td><input type="text" class="column-name" value="${column.name}" placeholder="Column Name"></td>
            <td>
                <select class="column-type" onchange="editor.updateColumnLengthInput(this)">
                    ${this.mysqlDataTypes.map(typeOption => `<option value="${typeOption}" ${typeOption === typeSimple ? 'selected' : ''}>${typeOption}</option>`).join('')}
                </select>
            </td>
            <td><input type="text" class="column-length" value="${columnLength}" placeholder="Length" style="display: ${this.withLengthTypes.includes(typeSimple) ? 'inline-block' : 'none'};"></td>
            <td><input type="text" class="column-enum" value="${column.values}" placeholder="Values (comma separated)" style="display: ${this.withValueTypes.includes(typeSimple) || this.withRangeTypes.includes(typeSimple) ? 'inline' : 'none'};"></td>
            <td><input type="text" class="column-default" value="${columnDefault}" placeholder="Default Value"></td>
            <td class="column-nl"><input type="checkbox" class="column-nullable" ${nullable}></td>
            <td class="column-pk"><input type="checkbox" class="column-primary-key" ${column.primaryKey ? 'checked' : ''}></td>
            <td class="column-ai"><input type="checkbox" class="column-autoIncrement" ${column.autoIncrement ? 'checked' : ''} ${column.autoIncrement ? '' : 'disabled'}></td>
        `;
        tableBody.appendChild(row);
        this.initDraggableRow(row);
        if(focus)
        {
            row.querySelector('.column-name').select();
        }
    }

    /**
     * Initializes drag-and-drop functionality for a given table row.
     *
     * This function enables a `<tr>` element to be draggable and defines how it behaves
     * during the drag-and-drop lifecycle, including visual feedback and reordering logic.
     *
     * The function ensures:
     * - The row is marked draggable.
     * - Appropriate classes are added/removed during drag events.
     * - Drop location is determined and the row is repositioned accordingly.
     * - Row numbering is updated after reorder.
     *
     * Assumptions:
     * - A global variable `dragSrcRow` holds the row being dragged.
     * - A global variable `tbody` refers to the `<tbody>` element containing the rows.
     * - A function `updateRowNumbers()` is defined to renumber rows after drag-drop.
     *
     * @param {HTMLElement} row - The table row element (<tr>) to make draggable.
     */
    initDraggableRow(row) {
        let _this = this;

        // Determine tbody only once
        if (typeof _this.tbody === 'undefined' || _this.tbody === null) {
            _this.tbody = row.closest('tbody');
        }

        // Prevent re-initialization
        if (row.dataset.hasDragEvent === 'true') return;
        row.dataset.hasDragEvent = 'true';

        // Set draggable attribute based on the clicked element
        row.addEventListener("mousedown", function (e) {
            const tag = e.target.tagName;
            const isInteractive = ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag);
            row.setAttribute("draggable", isInteractive ? "false" : "true");
        });

        // When dragging starts
        row.addEventListener("dragstart", function (e) {
            _this.dragSrcRow = this;
            this.classList.add("dragging");
            e.dataTransfer.effectAllowed = "move";
        });

        // When dragging ends
        row.addEventListener("dragend", function () {
            this.classList.remove("dragging");
        });

        // When a dragged item is moved over another row
        row.addEventListener("dragover", function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = "move";
            this.classList.add("over");
        });

        // When the pointer leaves the row without dropping
        row.addEventListener("dragleave", function () {
            this.classList.remove("over");
        });

        // When the dragged row is dropped on another row
        row.addEventListener("drop", function (e) {
            e.preventDefault();

            if (_this.dragSrcRow !== this) {
                const rows = Array.from(_this.tbody.children);
                const draggedIndex = rows.indexOf(_this.dragSrcRow);
                const targetIndex = rows.indexOf(this);

                if (draggedIndex < targetIndex) {
                    _this.tbody.insertBefore(_this.dragSrcRow, this.nextSibling);
                } else {
                    _this.tbody.insertBefore(_this.dragSrcRow, this);
                }

                // (Optional) call updateRowNumbers if it exists
                if (typeof _this.updateRowNumbers === 'function') {
                    _this.updateRowNumbers();
                }
            }

            this.classList.remove("over");
        });
    }

    /**
     * Adds a new column to the entity currently being edited.
     * If the entity name (table name) is detected as a duplicate, a confirmation dialog will prompt
     * the user to rename it to avoid conflicts.
     *
     * @param {boolean} [focus=false] - Whether to automatically focus on the newly added column's name input field.
     */
    addColumn(focus = false) {
        const selector = this.selector + " .entity-container .entity-name";
        const entityNameInput = document.querySelector(selector);
        const entityName = entityNameInput.value;

        // Check if an entity with the same name already exists.
        if (this.operation == 'create' && this.isEntityExists(entityName)) {
            this.showConfirmationDialog(
                `Entity '${entityName}' already exists.`, // Using single quotes for entity name for clarity
                'Duplicate Entity Detected', // More descriptive title
                'Rename',
                'Close',
                (isOk) => {
                    if(isOk)
                    {
                        // If user chooses 'Rename', append '_new' and select the text for easy editing.
                        entityNameInput.value = `${entityName}_new`;
                        entityNameInput.select();
                    }
                }
            );
            return; // Stop function execution if entity name is a duplicate.
        }

        // Determine the new column's name based on existing columns.
        const columnCount = document.querySelectorAll(this.selector + " .entity-container .column-name").length;
        const countSuffix = columnCount === 0 ? '' : columnCount + 1; // Use count + 1 for subsequent columns
        
        // If it's the first column, name it `${entityName}_id`, otherwise `${entityName}_colX`.
        const columnName = columnCount === 0 ? `${entityName}_id` : `${entityName}_col${countSuffix}`;
        
        // Create a new Column instance with default data type and length.
        const column = new Column(columnName, this.defaultDataType, this.defaultDataLength);
        column.nullable = true; // Set the new column as nullable by default.

        // Add the column to the table in the UI.
        this.addColumnToTable(column, focus);
        
        // Scroll to the bottom of the table container to show the newly added column.
        const element = document.querySelector(this.selector + ' .entity-container .table-container');
        element.scrollTop = element.scrollHeight;
    }

    /**
     * Checks if an entity with the given name already exists in the current list of entities.
     *
     * @param {string} entityName - The name of the entity to check for existence.
     * @returns {boolean} - True if an entity with the name exists, false otherwise.
     */
    isEntityExists(entityName) {
        for (const entity of this.entities) { 
            if (entity.name === entityName) { 
                return true;
            }
        }
        return false;
    }

    /**
     * Saves the currently edited entity, either creating a new one or updating an existing one.
     *
     * This method is the primary handler for saving an entity.
     * 1. It first checks for duplicate entity names and prompts the user to rename if found.
     * 2. If the name is unique or has been resolved, it calls `doSaveEntity()` to persist the data.
     * 3. After saving, it checks for an active diagram tab (`.tabs-link-container li.diagram-tab.active`).
     * 4. If an active diagram tab exists, it re-selects it by calling `selectDiagram()`, which ensures
     * the associated diagram is re-rendered to reflect any updated entity data.
     *
     * This function guarantees that both the underlying data and its visual representation in the diagram remain synchronized.
     */
    saveEntity() {
        const selector = this.selector + " .entity-container .entity-name";
        const entityNameInput = document.querySelector(selector);
        const entityName = entityNameInput.value;

        // Check if an entity with the same name already exists.
        if (this.operation == 'create' && this.isEntityExists(entityName)) {
            this.showConfirmationDialog(
                `Entity '${entityName}' already exists.`, // Using single quotes for entity name for clarity
                'Duplicate Entity Detected', // More descriptive title
                'Rename',
                'Close',
                (isOk) => {
                    if(isOk)
                    {
                        // If user chooses 'Rename', append '_new' and select the text for easy editing.
                        entityNameInput.value = `${entityName}_new`;
                        entityNameInput.select();
                    }
                }
            );
            return; // Stop function execution if entity name is a duplicate.
        }
        
        // Proceed to save the entity if no duplicate name issue or if resolved.
        this.doSaveEntity();
        
        // Check for an active diagram tab and re-select it to refresh the diagram.
        const activeDiagram = document.querySelector('.tabs-link-container li.diagram-tab.active');
        if (activeDiagram) {
            this.selectDiagram(activeDiagram);
        }
    }

    /**
     * Saves the current entity, either updating an existing one or creating a new one.
     */
    doSaveEntity() {
        const entityName = document.querySelector(this.selector+" .entity-name").value;
        const columns = [];
        const columnNames = document.querySelectorAll(this.selector+" #table-entity-editor .column-name");
        const columnTypes = document.querySelectorAll(this.selector+" #table-entity-editor .column-type");
        const columnNullables = document.querySelectorAll(this.selector+" #table-entity-editor .column-nullable");
        const columnDefaults = document.querySelectorAll(this.selector+" #table-entity-editor .column-default");
        const columnPrimaryKeys = document.querySelectorAll(this.selector+" #table-entity-editor .column-primary-key");
        const columnAutoIncrements = document.querySelectorAll(this.selector+" #table-entity-editor .column-autoIncrement");
        const columnLengths = document.querySelectorAll(this.selector+" #table-entity-editor .column-length");
        const columnEnums = document.querySelectorAll(this.selector+" #table-entity-editor .column-enum");

        for (let i = 0; i < columnNames.length; i++) {
            let column = new Column(
                columnNames[i].value,
                columnTypes[i].value,
                columnLengths[i].value || null,
                columnNullables[i].checked,
                columnDefaults[i].value || null,
                columnPrimaryKeys[i].checked,
                columnAutoIncrements[i].checked,
                columnEnums[i].value || null,
            );
            columns.push(column);
        }

        if (this.currentEntityIndex >= 0) {
            // Update existing entity
            this.entities[this.currentEntityIndex].name = entityName;
            this.entities[this.currentEntityIndex].index = this.currentEntityIndex;
            this.entities[this.currentEntityIndex].columns = columns;
        } else {
            // Add a new entity
            const newEntity = new Entity(entityName, this.entities.length);
            columns.forEach(col => newEntity.addColumn(col));
            newEntity.setData(this.snakeize(this.currentEntityData, columns));
            this.entities.push(newEntity);
        }
        this.renderEntities();
        this.updateDiagram();
        this.cancelEdit();
        this.exportToSQL();
        if(typeof this.callbackSaveEntity == 'function')
        {
            this.callbackSaveEntity(this.entities);
        }
    }

    /**
     * Updates the diagram view for all diagrams in the container.
     */
    updateDiagram()
    {
        let _this = this;
        let diagramContainer = document.querySelector('.diagram-container');
        diagramContainer.querySelectorAll('.diagram-entity').forEach((diagram, index) => {
            let id = diagram.getAttribute('id');
            let updatedWidth = diagram.closest('.left-panel').offsetWidth;
            let dataEntities = diagram.dataset.entities || '';
            let entities = dataEntities.split(',');
            let data = [];
            entities.forEach((entityName) => {
                let entity = _this.getEntityByName(entityName);
                if(entity != null)
                {
                    data.push(entity);
                }
            });
            diagramRenderer[id].createERD({entities: data}, updatedWidth - 240, document.querySelector('#draw-relationship').checked);
            let svg = diagram.querySelector('svg');
            _this.removeDiagramEventListener(svg);
            _this.addDiagramEventListener(svg);
        });
        
    }
    
    /**
     * Save diagram to server
     */
    saveDiagram()
    {
        let selection = this.getCheckedEntities();
        let diagrams = [];
        let sortOrder = 0;
        Object.entries(selection).forEach(([id, entities]) => {
            let name = document.querySelector(`.tabs-link-container [data-id="${id}"] input`).value;
            diagrams.push({id: id, name: name, sortOrder: sortOrder, entities: entities});
            sortOrder++;
        });
        if(typeof this.callbackSaveDiagram == 'function')
        {
            this.callbackSaveDiagram(diagrams);
        }
    }

    /**
     * Displays the template editor, hides the entity editor, and shows the appropriate containers.
     */
    showEditorTemplate() {
        this.currentEntityIndex = -2;
        document.querySelector(this.selector + " .template-columns-table-body").innerHTML = '';
        this.template.columns.forEach(col => this.addColumnToTemplate(col));
        document.querySelector(this.selector + " .button-container").style.display = "none";
        document.querySelector(this.selector + " .entity-container").style.display = "none";
        document.querySelector(this.selector + " .template-container").style.display = "block";
        document.querySelector(this.selector + " .editor-form").style.display = "block";
    }

    /**
     * Adds a new column template to the editor with default values.
     * 
     * @param {boolean} focus - Whether to focus on the newly added column input field.
     */
    addColumnTemplate(focus) {
        let column = {
            "name": "",
            "type": "VARCHAR",
            "length": "50",
            "nullable": true,
            "default": null,
            "values": ""
        }
        this.addColumnToTemplate(column, focus);
        const element = document.querySelector(this.selector + ' .template-container .table-container');
        element.scrollTop = element.scrollHeight;
    }

    /**
     * Adds a new column to the template editor.
     * 
     * @param {Object} column - The column data to add (name, type, length, nullable, default, values).
     * @param {boolean} focus - Whether to focus on the column input field after adding it.
     */
    addColumnToTemplate(column, focus) {
        const tableBody = document.querySelector(this.selector + " .template-columns-table-body");
        const row = document.createElement("tr");
        let columnLength = column.length == null ? '' : column.length.replace(/\D/g, '');
        let columnDefault = column.default == null ? '' : column.default;
        let typeSimple = column.type.split('(')[0].trim();
        row.innerHTML = `
            <td class="drag-handle"></td>
            <td class="column-action">
                <button onclick="editor.removeColumn(this)" class="icon-delete"></button>
                <button onclick="editor.moveUp(this)" class="icon-move-up"></button>
                <button onclick="editor.moveDown(this)" class="icon-move-down"></button>    
            </td>
            <td><input type="text" class="column-name" value="${column.name}" placeholder="Column Name"></td>
            <td>
                <select class="column-type" onchange="editor.updateColumnLengthInput(this)">
                    ${this.mysqlDataTypes.map(typeOption => `<option value="${typeOption}" ${typeOption === typeSimple ? 'selected' : ''}>${typeOption}</option>`).join('')}
                </select>
            </td>
            <td><input type="text" class="column-length" value="${columnLength}" placeholder="Length" style="display: ${this.withLengthTypes.includes(typeSimple) ? 'inline' : 'none'};"></td>
            <td><input type="text" class="column-enum" value="${column.values}" placeholder="Values (comma separated)" style="display: ${this.withValueTypes.includes(typeSimple) || this.withRangeTypes.includes(typeSimple) ? 'inline' : 'none'};"></td>
            <td><input type="text" class="column-default" value="${columnDefault}" placeholder="Default Value"></td>
            <td class="column-nl"><input type="checkbox" class="column-nullable" ${column.nullable ? 'checked' : ''}></td>
        `;
        tableBody.appendChild(row);
        this.initDraggableRow(row);
        if (focus) {
            row.querySelector('.column-name').select();
        }
    }

    /**
     * Saves the column template by collecting the values from the form and updating the template.
     */
    saveTemplate() {
        const columns = [];
        const columnNames = document.querySelectorAll(this.selector + " .table-template-editor .column-name");
        const columnTypes = document.querySelectorAll(this.selector + " .table-template-editor .column-type");
        const columnNullables = document.querySelectorAll(this.selector + " .table-template-editor .column-nullable");
        const columnDefaults = document.querySelectorAll(this.selector + " .table-template-editor .column-default");
        const columnLengths = document.querySelectorAll(this.selector + " .table-template-editor .column-length");
        const columnEnums = document.querySelectorAll(this.selector + " .table-template-editor .column-enum");

        for (let i = 0; i < columnNames.length; i++) {
            let column = new Column(
                columnNames[i].value,
                columnTypes[i].value,
                columnLengths[i].value || null,
                columnNullables[i].checked,
                columnDefaults[i].value || null,
                columnEnums[i].value || null,
            );
            columns.push(column);
        }
        this.template.columns = columns;
        document.querySelector(this.selector + " .entity-container").style.display = "block";
        document.querySelector(this.selector + " .template-container").style.display = "none";
        if(typeof this.callbackSaveTemplate == 'function')
        {
            this.callbackSaveTemplate(this.template)
        }
    }

    /**
     * Cancels the template editing process and reverts to the entity editor view.
     */
    cancelEditTemplate() {
        document.querySelector(this.selector + " .entity-container").style.display = "block";
        document.querySelector(this.selector + " .template-container").style.display = "none";
    }

    /**
     * Adds columns from the template to the table, ensuring that only columns not already present are added.
     */
    addColumnFromTemplate() {
        const selector = this.selector + " .entity-container .entity-name";
        const entityNameInput = document.querySelector(selector);
        const entityName = entityNameInput.value;

        // Check if an entity with the same name already exists.
        if (this.operation == 'create' && this.isEntityExists(entityName)) {
            this.showConfirmationDialog(
                `Entity '${entityName}' already exists.`, // Using single quotes for entity name for clarity
                'Duplicate Entity Detected', // More descriptive title
                'Rename',
                'Close',
                (isOk) => {
                    if(isOk)
                    {
                        // If user chooses 'Rename', append '_new' and select the text for easy editing.
                        entityNameInput.value = `${entityName}_new`;
                        entityNameInput.select();
                    }
                }
            );
            return; // Stop function execution if entity name is a duplicate.
        }
        const existingColumnNames = [];
        const columnNames = document.querySelectorAll(this.selector + " #table-entity-editor .column-name");
        for (const columnName of columnNames) {
            existingColumnNames.push(columnName.value);
        }
        this.template.columns.forEach(column => {
            if (!existingColumnNames.includes(column.name)) {
                this.addColumnToTable(column, focus);
            }
        });
        const element = document.querySelector(this.selector+' .entity-container .table-container');
        element.scrollTop = element.scrollHeight;
    }

    /**
     * Removes the selected column from the entity.
     * 
     * @param {HTMLElement} button - The button that was clicked to remove the column.
     */
    removeColumn(button) {
        const row = button.closest("tr");
        row.remove();
    }

    /**
     * Moves a column up in the columns table.
     * 
     * @param {HTMLElement} button - The button that was clicked to move the column up.
     */
    moveUp(button) {
        const row = button.closest("tr");
        const tableBody = row.closest('tbody');
        const previousRow = row.previousElementSibling;
        if (previousRow) {
            tableBody.insertBefore(row, previousRow);
        }
    }

    /**
     * Moves a column down in the columns table.
     * 
     * @param {HTMLElement} button - The button that was clicked to move the column down.
     */
    moveDown(button) {
        const row = button.closest("tr");
        const tableBody = row.closest('tbody');
        const nextRow = row.nextElementSibling;
        if (nextRow) {
            tableBody.insertBefore(nextRow, row);
        }
    }

    /**
     * Converts a JSON array to an array of Entity objects.
     * 
     * @param {Array} jsonData - The JSON array containing entities and their columns.
     * @returns {Array} - An array of Entity objects.
     */
    createEntitiesFromJSON(jsonData) {
        const entities = [];

        // Iterate over each entity in the JSON data
        jsonData.entities.forEach(entityData => {
            // Create a new Entity instance
            const entity = new Entity(entityData.name, entityData.index);
            
            // Iterate over each column in the entity's columns array
            entityData.columns.forEach(columnData => {
                // Create a new Column instance
                const column = new Column(
                    columnData.name,
                    columnData.type,
                    columnData.length,
                    columnData.nullable,
                    columnData.default,
                    columnData.primaryKey,
                    columnData.autoIncrement,
                    columnData.values !== "null" ? columnData.values : "",
                );
                
                // Add the column to the entity
                entity.addColumn(column);
                
            });
            entity.setData(entityData.data);

            // Add the entity to the entities array
            entities.push(entity);
        });

        return {entities:entities, diagrams:jsonData.diagrams};
    }

    /**
     * Converts a JSON array to an array of Entity objects.
     * 
     * @param {Array} jsonData - The JSON array containing entities and their columns.
     * @returns {Array} - An array of Entity objects.
     */
    createTemplateFromJSON(jsonData) {
        const columns = [];
        // Iterate over each column in the entity's columns array
        jsonData.columns.forEach(columnData => {
            // Create a new Column instance
            const column = new Column(
                columnData.name,
                columnData.type,
                columnData.length,
                columnData.nullable,
                columnData.default,
                columnData.values !== "null" ? columnData.values : "",
            );
            columns.push(column);
        });
        return {columns: columns};
    }

    /**
     * Creates an array of Entity instances from the given SQL table data.
     * 
     * This method takes in an array of tables, each containing information about table columns, and converts that 
     * data into Entity and Column objects. It then returns an array of the created entities.
     *
     * @param {Array} tables - An array of tables (each table being an object) with column data to convert into entities.
     * Each table should contain a `tableName` and a `columns` array where each column object contains metadata about the column (e.g., Field, Type, Length, Nullable, etc.).
     * 
     * @returns {Array} entities - An array of Entity objects, each containing Column objects based on the provided table data.
     */
    createEntitiesFromSQL(tables) {
        const entities = [];

        // Iterate over each entity in the JSON data
        tables.forEach((table, index) => {
            // Create a new Entity instance
            const entity = new Entity(table.tableName, index);
            
            // Iterate over each column in the entity's columns array
            table.columns.forEach(columnData => {
                // Create a new Column instance
                const column = new Column(
                    columnData.Field,
                    columnData.Type.toUpperCase(),
                    columnData.Length,
                    columnData.Nullable,
                    columnData.Default,
                    columnData.Key,
                    columnData.AutoIncrement,
                    (columnData.EnumValues != null && typeof columnData.EnumValues == 'object') ? columnData.EnumValues.join(', ') : null,
                );
                
                // Add the column to the entity
                entity.addColumn(column);
            });

            // Add the entity to the entities array
            entities.push(entity);
        });

        return entities;
    };

    /**
     * Gets the checked (selected) entities for each diagram.
     * 
     * @returns {Object} An object where each key is a diagram ID and the value is an array of selected entity names.
     */
    getCheckedEntities() {
        let diagramEntities = {};
        let diagrams = document.querySelectorAll('.diagram-entity.tab-content');
        diagrams.forEach((diagram) => {
            let id = diagram.getAttribute('id');
            let entities = diagram.dataset.entities;
            diagramEntities[id] = entities ? entities.split(',') : [];
        });
        return diagramEntities;
    };

    /**
     * Sets the checked (selected) entities for each diagram.
     * 
     * @param {Object} diagramEntities - An object where each key is a diagram ID and the value is an array of entity names to set as checked.
     */
    setCheckedEntities(diagramEntities) {
        let diagrams = document.querySelectorAll('.diagram-entity.tab-content');
        diagrams.forEach((diagram) => {
            let id = diagram.getAttribute('id');
            let entities = diagramEntities[id];
            if (entities) {
                diagram.setAttribute('data-entities', entities.join(','));
            }
        });
    };

    /**
     * Restores checked entities for the currently active diagram tab.
     * Calls selectDiagram() for the active diagram tab to update the UI.
     */
    restoreCheckedEntitiesFromCurrentDiagram()
    {
        let li = document.querySelector('.tabs-link-container .diagram-tab.active');
        this.selectDiagram(li);
    }
    
    /**
     * Restores checked (selected) entities in the UI for the currently active diagram.
     * Updates the checkboxes in the table list to match the entities of the active diagram.
     */
    restoreCheckedEntities()
    {
        let diagram = document.querySelector('.diagram-entity.tab-content.active');
        if(diagram)
        {
            let entities = diagram.dataset.entities;
            let checked = entities ? entities.split(',') : [];
            document.querySelectorAll('.left-panel .table-list [type="checkbox"]').forEach((input) => {
                input.checked = checked.includes(input.dataset.name);
                input.disabled = false;
            });
        }
    }

    /**
     * Renders the entities to the DOM.
     * This method updates the UI by rendering a list of entities as checkboxes,
     * allowing users to select or deselect entities. It also ensures that previously
     * selected entities are checked when the list is re-rendered.
     * 
     * The method also updates the width of the ERD (Entity-Relationship Diagram) based on 
     * the available space in the container and re-renders the ERD with the updated width.
     * 
     * @returns {void}
     */
    renderEntities() {
        // Get the container element for the entities
        const container = document.querySelector(this.selector+" .entities-container");

        // Create an array to hold the names of selected entities
        const selectedEntity = [];

        // Get all selected entity checkboxes (those that are checked)
        const selectedEntities = document.querySelectorAll(this.selector+" .right-panel .selected-entity-structure:checked");

        // If there are selected checkboxes, add their data-name to the selectedEntity array
        
        if (selectedEntities) {
            selectedEntities.forEach(checkbox => {
                selectedEntity.push(checkbox.dataset.name);
            });
        }

        // Get the list element where the entities will be rendered
        const tabelListForExport = document.querySelector(this.selector+" .table-list-for-export");
        const tabelListMain = document.querySelector(this.selector+" .left-panel .table-list");
        let drawRelationship = document.querySelector(this.selector+" .draw-relationship").checked;


        // Clear any existing content in the table list
        tabelListMain.innerHTML = '';
        tabelListForExport.innerHTML = '';

        // Iterate over the entities and create a checkbox for each entity
        this.entities.forEach((entity, index) => {
            // Create a new list item for each entity
            let entityCbForExport = document.createElement('tr');
            entityCbForExport.innerHTML = `
            <td>
                <label><input type="checkbox" class="selected-entity-structure" data-name="${entity.name}" value="${index}" /> S</label>
            </td>
            <td>
                <label><input type="checkbox" class="selected-entity-data" data-name="${entity.name}" value="${index}" /> D</label>
            </td>
            <td>
            ${entity.name}
            </td>
            `;
            
            let entityCbMain = document.createElement('li');
            

            entityCbMain.innerHTML = `<input type="checkbox" class="selected-entity" data-name="${entity.name}" value="${index}" 
            /><a class="edit-table" href="javascript:">✏️</a><a class="delete-table" href="javascript:">❌</a> ${entity.name}`
            
            // Append the created list item to the table list   

            entityCbMain.setAttribute('data-index', index);
            entityCbMain.setAttribute('title', entity.name);
            tabelListMain.appendChild(entityCbMain);

            entityCbMain.querySelector('a.edit-table').addEventListener('click', function(e){
                editor.editEntity(parseInt(e.target.parentNode.dataset.index))
            });
            entityCbMain.querySelector('a.delete-table').addEventListener('click', function(e){
                editor.deleteEntity(parseInt(e.target.parentNode.dataset.index))
            });


            tabelListForExport.appendChild(entityCbForExport);
        });
        
        let count = this.entities.length;
        let countStr = count > 0 ? `(${count})` : ``;
        document.querySelector(this.selector + " .entity-count").textContent = countStr;

        // Ensure that previously selected entities are checked
        
        selectedEntity.forEach(value => {
            // Find the checkbox corresponding to the selected entity name
            let cb = document.querySelector(`.right-panel input[data-name="${value}"]`);
            if (cb) {
                // Check the checkbox if found
                cb.checked = true;
            }
        });
        

        // Calculate the updated width of the SVG container
        let updatedWidth = container.closest('.left-panel').offsetWidth;

        // If the width is 0 (meaning it's not set), fallback to the left panel width
        if (updatedWidth == 0) {
            updatedWidth = resizablePanels.getLeftPanelWidth();
        }
        
        updatedWidth = updatedWidth - 200;

        // Re-render the ERD with the updated width (subtracting 40 for padding/margin)
        entityRenderer.createERD(editor.getData(), updatedWidth - 40, drawRelationship);
    }
    
    /**
     * Refreshes the entities by re-rendering and restoring checked entities.
     * This method temporarily stores the checked entities, re-renders the entities, 
     * updates the diagram, and then restores the checked entities.
     */
    refreshEntities() {
        let _this = this;
        setTimeout(function () {
            let checkedEntities = _this.getCheckedEntities();
            _this.renderEntities();
            _this.updateDiagram();
            _this.setCheckedEntities(checkedEntities);
            _this.restoreCheckedEntities();
        }, true);
    }

    /**
     * Prepares the diagram by loading saved entities and diagrams from the server.
     * If diagrams are available, they are added to the UI.
     */
    prepareDiagram() {
        let _this = this;
        if (this.diagrams.length > 0) {
            this.diagrams.forEach((diagram) => {
                let ul = document.querySelector('.tabs-link-container .diagram-list.tabs');
                _this.addDiagram(ul, diagram.name, diagram.id, diagram.entities, true);
            });
        }
    }

    /**
     * Selects a diagram and updates the UI accordingly.
     * 
     * This method highlights the selected diagram tab, deactivates all other tabs, 
     * and displays the corresponding diagram in the container while hiding others.
     * Additionally, it updates the entity checkboxes based on the selected diagram's entities.
     * 
     * @param {HTMLElement} li - The selected list item (diagram tab) element.
     */
    selectDiagram(li) {
        let diagramContainer = document.querySelector('.diagram-container');
        let entities = [];
        if(li)
        {
            // Remove active class from all diagram tabs
            li.closest('ul').querySelectorAll('li.diagram-tab').forEach((tab) => {
                tab.classList.remove('active');
            });

            // Remove active class from the "All Entities" tab
            li.closest('ul').querySelector('li.all-entities').classList.remove('active');

            // Remove active class from all diagram containers
            diagramContainer.querySelectorAll('div.diagram').forEach((tab) => {
                tab.classList.remove('active');
            });

            // Activate the selected tab
            li.classList.add('active');

            // Get the selected diagram ID and activate the corresponding diagram
            let selector = li.dataset.id;
            let diagram = diagramContainer.querySelector('#' + selector);
            diagram.classList.add('active');

            // Retrieve associated entities for the selected diagram
            let dataEntity = diagram.dataset.entities || '';
            entities = dataEntity.split(',');
        }

        // Update entity checkboxes based on selected diagram's entities
        document.querySelector('.entity-editor .table-list').querySelectorAll('li').forEach((li2) => {
            let input = li2.querySelector('input[type="checkbox"]');
            let value = input.dataset.name;

            if (entities.length > 0 && value !== '') {
                input.checked = entities.includes(value);
            }
            
            input.disabled = false;
        });

        // Update the diagram
        this.updateDiagram();
    }

    /**
     * Adds a new diagram tab and its corresponding diagram content.
     * 
     * @param {HTMLElement} ul - The parent <ul> element to append the new diagram tab.
     * @param {string} diagramName - The name of the diagram.
     * @param {string} id - Unique identifier for the diagram.
     * @param {Array} entities - List of entities associated with the diagram.
     * @param {boolean} [finish=false] - Whether the diagram is in edit mode.
     */
    addDiagram(ul, diagramName, id, entities, finish)
    {
        let _this = this;
        finish = finish || false;
        let template = `
        <input type="text" value="${diagramName}">
        <a href="#tab1" class="tab-link select-diagram">${diagramName}</a> 
        <a 
            href="javascript:" class="update-diagram"><span class="icon-ok"></span></a><a 
            href="javascript:" class="edit-diagram"><span class="icon-edit"></span></a><a 
            href="javascript:" class="delete-diagram"><span class="icon-delete"></span></a>
        `;


        let newTab = document.createElement("li");
        newTab.innerHTML = template;
        newTab.setAttribute('data-edit-mode', finish ? 'false':'true');
        newTab.querySelector('a.tab-link').setAttribute('href', '#'+diagramName);
        newTab.setAttribute('data-id', id);
        newTab.classList.add('diagram-tab');
        
        let lastChild = ul.lastElementChild;
        ul.insertBefore(newTab, lastChild);
        newTab.querySelector('input').select();

        newTab.querySelector('input').addEventListener('keypress', function(e){
            if(e.key == 'Enter') 
            {
                let value = e.target.value;
                let label = newTab.querySelector('.tab-link');
                label.innerText = value;
                newTab.setAttribute('data-edit-mode', 'false');
                _this.updateDiagram();
                _this.saveDiagram();
            }
        });

        ul.querySelectorAll('li.diagram-tab').forEach((tab, index) => {
            tab.classList.remove('active');
        });
        newTab.classList.add('active');

        let diagramContainer = document.querySelector('.diagram-container');

        diagramContainer.querySelectorAll('.diagram').forEach((tab, index) => {
            tab.classList.remove('active');
        });

        let diagram = document.createElement('div');
        diagram.setAttribute('id', id);
        diagram.classList.add('diagram');
        diagram.classList.add('diagram-entity');
        diagram.classList.add('tab-content');
        diagram.classList.add('active');
        diagram.setAttribute('data-entities', entities.join(','));
        let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute('width', 4);
        svg.setAttribute('height', 4);
        svg.classList.add('erd-svg');

        initDiagramContextMenu(svg);

        diagram.appendChild(svg);
        diagramContainer.appendChild(diagram);
        diagramRenderer[id] = new EntityRenderer(`.diagram-container #${id} .erd-svg`);
        
        ul.querySelectorAll('li.diagram-tab').forEach((li, index) => {
            li.setAttribute('data-index', index);
        });
        this.selectDiagram(newTab);
        this.updateDiagram();
        
        newTab.querySelector('.select-diagram').addEventListener('click', function(e){
            e.preventDefault();
            let li = e.target.closest('li');
            _this.selectDiagram(li);
        });

        newTab.querySelector('.update-diagram').addEventListener('click', function(e){
            e.preventDefault();
            let li = e.target.closest('li');
            let input = li.querySelector('input');
            let value = input.value;
            let label = li.querySelector('.tab-link');
            label.innerText = value;
            li.setAttribute('data-edit-mode', 'false');
            _this.updateDiagram();
            _this.saveDiagram();
            _this.restoreCheckedEntitiesFromCurrentDiagram();
        });

        newTab.querySelector('.edit-diagram').addEventListener('click', function(e){
            e.preventDefault();
            let li = e.target.closest('li');
            let label = li.querySelector('.tab-link');
            let value = label.innerText;
            let input = li.querySelector('input');
            input.value = value;
            li.setAttribute('data-edit-mode', 'true');
            input.select();
            _this.updateDiagram();
            _this.restoreCheckedEntitiesFromCurrentDiagram();
        });

        newTab.querySelector('.delete-diagram').addEventListener('click', function(e){
            e.preventDefault();
            let diagramName = e.target.closest('li').querySelector('input[type="text"]').value;

            _this.showConfirmationDialog(`<p>Are you sure you want to delete the diagram &quot;${diagramName}&quot;?</p>`, 'Delete Confirmation', 'Yes', 'No', function(isConfirmed) {
                if (isConfirmed) {
                    let li = e.target.closest('li');
                    let selector = '#'+li.dataset.id;
                    let ul = li.closest('ul');
                    li.parentNode.removeChild(li);
                    let diagram = diagramContainer.querySelector(selector);
                    diagram.parentNode.removeChild(diagram);
                    ul.querySelectorAll('li.diagram-tab').forEach((li, index) => {
                        li.setAttribute('data-index', index);
                    });
                    _this.updateDiagram();
                    _this.saveDiagram();
                } 
            });
            
        });
        
        if(tabDragger === null)
        {
            tabDragger = new TabDragger(ul, function(){
                let diagrams = _this.getDiagrams();
                _this.callbackSaveDiagram(diagrams);
            });
            tabDragger.initAll();
        }
        
        tabDragger.makeDraggable(newTab);
        
        let move = -10 - newTab.offsetWidth;
        updateMarginLeft(move)
    }

    /**
     * Adds a click event listener to an SVG diagram.
     * 
     * @param {SVGElement} svg - The SVG element to add the event listener to.
     */
    addDiagramEventListener(svg) {
        let _this = this;
    
        // Simpan referensi fungsi dalam elemen
        if (!svg._clickHandler) {
            svg._clickHandler = function(e) {
                _this.editEventListener(e);
            };
        }
    
        svg.addEventListener('click', svg._clickHandler);
    }

    /**
     * Generates a unique name for a new diagram, based on existing diagram names.
     *
     * If the base name (e.g., "New Diagram") already exists, it appends a number
     * (e.g., "New Diagram 1", "New Diagram 2", and so on) until a unique name is found.
     *
     * @returns {string} The unique name for the new diagram.
     */
    getNewDiagramName() {
        // Define the base name for new diagrams.
        let baseName = "New Diagram";
        // Retrieve the list of existing diagrams from the current context.
        let existingDiagrams = this.getDiagrams();
        // Initialize the new name with the base name.
        let newName = baseName;
        // Initialize a counter for numeric suffixes.
        let counter = 0;
        // Flag to control the loop, initially true to start checking.
        let nameExists = true;

        // Loop until a unique name is found.
        while (nameExists) {
            // Assume the current newName is unique at the start of each iteration.
            nameExists = false;

            // Iterate through all existing diagrams to check for name collisions.
            for (const diagram of existingDiagrams) {
                // If a diagram with the current newName already exists:
                if (diagram.name === newName) {
                    nameExists = true; // Set flag to true to continue the outer loop.
                    counter++; // Increment the counter for the next suffix.
                    // Construct the new name with the updated counter.
                    newName = `${baseName} ${counter}`;
                    // Break from the inner loop as a collision is found; the outer loop will re-check the newName.
                    break;
                }
            }
        }
        // Return the unique name that was found.
        return newName;
    }

    /**
     * Collects and returns a list of diagrams with their metadata.
     *
     * This function retrieves all diagram tabs from the DOM and constructs an array
     * of diagram objects. Each object includes:
     * - `id`: The unique identifier of the diagram tab (from `data-id` attribute).
     * - `name`: The name of the diagram (from the input field inside the tab).
     * - `sortOrder`: The index order of the diagram in the tab list.
     * - `entities`: An array of entity IDs associated with the diagram (from the container's data).
     *
     * Assumptions:
     * - Diagrams are listed as `<li class="diagram-tab">` inside `.diagram-list.tabs`.
     * - Each diagram has a corresponding `.diagram-container > div` with a matching ID and `data-entities` attribute.
     *
     * @returns {Array<Object>} Array of diagram metadata objects.
     */
    getDiagrams()
    {
        let diagrams = [];
        document.querySelector('.diagram-list.tabs').querySelectorAll('li.diagram-tab').forEach((tab, index) => {
            diagrams.push({
                id: tab.dataset.id,
                name: tab.querySelector('input').value,
                sortOrder: index,
                entities: document.querySelector('.diagram-container').querySelector(`#${tab.dataset.id}`).dataset.entities.split(',')
            })
        });
        return diagrams;
    }
    
    /**
     * Removes a click event listener from an SVG diagram.
     * 
     * @param {SVGElement} svg - The SVG element to remove the event listener from.
     */
    removeDiagramEventListener(svg) {
        if (svg._clickHandler) {
            svg.removeEventListener('click', svg._clickHandler);
            delete svg._clickHandler; // Hapus referensi setelah dilepas
        }
    }

    /**
     * Clears all diagrams from the diagram list and diagram container.
     * 
     * This method removes all diagram tabs and their corresponding content from the DOM,
     * and sets the "all-entities" tab as active.
     */
    clearDiagrams()
    {
        document.querySelector('.diagram-list.tabs .all-entities').classList.add('active');

        let diagramTab = document.querySelectorAll('.diagram-tab');
        if(diagramTab)
        {
            diagramTab.forEach((tab) => {
                tab.parentNode.removeChild(tab);
            });
        }
        //
        let diagramPages = document.querySelectorAll('.diagram-entity.tab-content');
        if(diagramPages)
        {
            diagramPages.forEach((page) => {
                page.parentNode.removeChild(page);
            });
        }
        document.querySelector('.diagram-container #all-entities').classList.add('active');
    }

    /**
     * Clears all entities from the diagram container.
     * 
     * This method removes all SVG elements from the "all-entities" diagram,
     * effectively clearing the diagram of all entities.
     */
    clearEntities()
    {
        let allDiagramContainer = document.querySelector('.diagram-container .all-entities');
        if(allDiagramContainer)
        {
            let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            allDiagramContainer.appendChild(svg);
        }
    }

    /**
     * Handles click events inside a diagram SVG element.
     * 
     * @param {Event} e - The event object.
     */
    editEventListener(e)
    {
        let _this = this;
        if (e.target.closest('.erd-svg .view-data-icon')) {
            let index = parseInt(e.target.dataset.index);
            _this.viewData(index);
        }
        if (e.target.closest('.erd-svg .move-down-icon')) {
            let haystack = e.target.closest('.diagram-entity').dataset.entities;
            let needle = e.target.closest('.svg-entity').dataset.entity;
            let newEntities = _this.arrayElementOperation(haystack, needle, 1);
            e.target.closest('.diagram-entity').setAttribute('data-entities', newEntities);
            _this.updateDiagram();
            _this.saveDiagram();
        }
        if (e.target.closest('.erd-svg .move-up-icon')) {
            let haystack = e.target.closest('.diagram-entity').dataset.entities;
            let needle = e.target.closest('.svg-entity').dataset.entity;
            let newEntities = _this.arrayElementOperation(haystack, needle, -1);
            e.target.closest('.diagram-entity').setAttribute('data-entities', newEntities);
            _this.updateDiagram();
            _this.saveDiagram();
        }
        if (e.target.closest('.erd-svg .edit-icon')) {
            let index = parseInt(e.target.dataset.index);
            _this.editEntity(index);
        }
        if (e.target.closest('.erd-svg .delete-icon')) {
            let haystack = e.target.closest('.diagram-entity').dataset.entities;
            let needle = e.target.closest('.svg-entity').dataset.entity;
            let newEntities = _this.removeUniqueElements(haystack.split(','), needle).join(',');
            document.querySelector(`.selected-entity[data-name="${needle}"]`).checked = false;
            e.target.closest('.diagram-entity').setAttribute('data-entities', newEntities);
            _this.updateDiagram();
            _this.saveDiagram();
        }
    }

    /**
     * Removes a specific target element from the array if it appears only once.
     * 
     * @param {Array} arr - The array to filter.
     * @param {string} target - The element to remove if it is unique.
     * @returns {Array} - A new array with the target removed if it was unique.
     */
    removeUniqueElements(arr, target) {
        return arr.filter(item => !(item === target && arr.indexOf(item) === arr.lastIndexOf(item)));
    }

    /**
     * Moves an element up or down within an array.
     * 
     * @param {string} haystack - Comma-separated string of elements.
     * @param {string} needle - The element to move.
     * @param {number} operation - 1 to move down, -1 to move up.
     * @returns {string} - The updated comma-separated string.
     */
    arrayElementOperation(haystack, needle, operation) {
        let array = haystack.split(',');
        let index = array.indexOf(needle);
        
        // If the element is not found or already at the left/right boundary, return the original string
        if (index === -1 || (operation === -1 && index === 0) || (operation === 1 && index === array.length - 1)) {
            return haystack;
        }
        
        // Determine the new index
        let newIndex = index + operation;
        
        // Swap the element with the element at the new position
        [array[index], array[newIndex]] = [array[newIndex], array[index]];
        
        return array.join(',');
    }
    
    viewData(index)
    {
        this.currentEntityIndex = index;
        this.showEntityDataDialog(this.entities[index], 'Entity Data', 'Close');
    }

    /**
     * Moves an entity up in the list of entities.
     * This method swaps the selected entity with the one before it in the array.
     * 
     * @param {number} index - The index of the entity to move up.
     */
    moveEntityUp(index) {
        this.cancelEdit();
        // Ensure the index is valid and it's not the last element
        if (index < this.entities.length - 1) {
            // Swap the entity at 'index' with the one after it (at 'index + 1')
            const temp = this.entities[index];
            this.entities[index] = this.entities[index + 1];
            this.entities[index + 1] = temp;
            this.updateEntityIndex();
            // Re-render the entities after the change
            this.renderEntities();
            this.restoreCheckedEntitiesFromCurrentDiagram();
            this.exportToSQL();
            
            if(typeof this.callbackSaveEntity == 'function')
            {
                this.callbackSaveEntity(this.entities);
            }
        }
    }

    /**
     * Moves an entity down in the list of entities.
     * This method swaps the selected entity with the one after it in the array.
     * 
     * @param {number} index - The index of the entity to move down.
     */
    moveEntityDown(index) {
        this.cancelEdit();
        // Ensure the index is valid and it's not the first element
        if (index > 0) {
            // Swap the entity at 'index' with the one before it (at 'index - 1')
            const temp = this.entities[index];
            this.entities[index] = this.entities[index - 1];
            this.entities[index - 1] = temp;
            this.updateEntityIndex();
            // Re-render the entities after the change
            this.renderEntities();
            this.restoreCheckedEntitiesFromCurrentDiagram();
            this.exportToSQL();
            
            if(typeof this.callbackSaveEntity == 'function')
            {
                this.callbackSaveEntity(this.entities);
            }
        }
    }

    /**
     * Sorts the entities alphabetically based on the 'name' property.
     * This method sorts the entities in ascending order (A-Z) and then calls 
     * `renderEntities()` to re-render the sorted list of entities in the UI.
     */
    sortEntities() {
        // Sort the entities array alphabetically by the 'name' property
        this.entities.sort((a, b) => {
            if (a.name > b.name) {
                return 1;  // a comes after b in alphabetical order
            }
            if (a.name < b.name) {
                return -1;  // a comes before b in alphabetical order
            }
            return 0;  // a and b are equal, no change
        });
        this.updateEntityIndex();
        // Re-render the sorted list of entities
        this.renderEntities();
        this.restoreCheckedEntitiesFromCurrentDiagram();
        this.exportToSQL();
        if(typeof this.callbackSaveEntity == 'function')
        {
            this.callbackSaveEntity(this.entities);
        }
    }

    /**
     * Sorts entities by grouping them into 'Custom' and 'System' categories.
     * 'Custom' entities (those not in a predefined system list) are placed first,
     * followed by 'System' entities (a predefined list).
     * Both groups are then sorted alphabetically by their 'name' property.
     */
    sortAndGroupEntities() {
        const systemEntities = [
            'admin',
            'admin_level',
            'admin_profile',
            'admin_role',
            'menu_cache',
            'menu_group_translation',
            'menu_translation',
            'message',
            'message_folder',
            'module',
            'module_group',
            'notification',
            'user_activity',
            'user_password_history',
        ];

        // Separate entities into system and custom groups
        let customGroup = [];
        let systemGroup = [];

        this.entities.forEach(entity => {
            if (systemEntities.includes(entity.name)) {
                systemGroup.push(entity);
            } else {
                customGroup.push(entity);
            }
        });

        // Sort both groups alphabetically
        customGroup.sort((a, b) => a.name.localeCompare(b.name));
        systemGroup.sort((a, b) => a.name.localeCompare(b.name));

        // Combine the sorted groups: custom entities first, then system entities
        this.entities = [...customGroup, ...systemGroup];
        this.updateEntityIndex();

        // Re-render the sorted list of entities in the UI
        this.renderEntities();
        this.restoreCheckedEntitiesFromCurrentDiagram();
        this.exportToSQL();
        if(typeof this.callbackSaveEntity === 'function') {
            this.callbackSaveEntity(this.entities);
        }
    }

    /**
     * Updates the 'index' property for each entity in the `this.entities` array.
     * This method iterates through the `entities` array and assigns each entity's
     * `index` property based on its current position in the array. This is useful
     * for maintaining a consistent order or reference for entities, especially
     * after sorting or reordering operations.
     */
    updateEntityIndex() {
         this.entities.forEach((entity, index) => {
            entity.index = index;
         });
    }

    /**
     * Edits the specified entity based on its index in the entities array.
     * 
     * @param {number} index - The index of the entity to edit.
     */
    editEntity(index) {
        this.currentEntityIndex = index;
        this.showEditor(index);
    }

    /**
     * Deletes the specified entity based on its index in the entities array.
     * 
     * @param {number} index - The index of the entity to delete.
     */
    deleteEntity(index) {
        let _this = this;
        let entityName = _this.entities[index].name;
        _this.showConfirmationDialog(`<p>Are you sure you want to delete the entity &quot;${entityName}&quot;?</p>`, 'Delete Confirmation', 'Yes', 'No', function(isConfirmed) {
            if (isConfirmed) {
                _this.entities.splice(index, 1);
                _this.renderEntities();
                _this.restoreCheckedEntitiesFromCurrentDiagram();
                _this.exportToSQL();
                if(typeof _this.callbackSaveEntity == 'function')
                {
                    _this.callbackSaveEntity(_this.entities);
                }
            } 
        });
    }

    /**
     * Cancels the entity editing process and hides the editor form.
     */
    cancelEdit() {
        document.querySelector(this.selector+" .editor-form").style.display = "none";
        document.querySelector(this.selector+" .button-container").style.display = "block";
    }

    /**
     * Updates the length and enum fields based on the selected column type.
     * 
     * @param {HTMLElement} selectElement - The select element for the column type.
     */
    updateColumnLengthInput(selectElement) {
        const tr = selectElement.closest("tr");
        const columnType = selectElement.value;
        const lengthInput = tr.querySelector(".column-length");
        const enumInput = tr.querySelector(".column-enum");

        // Show length input for specific types
        if (this.withLengthTypes.includes(columnType)) 
        {
            lengthInput.style.display = "inline";
        } 
        else 
        {
            lengthInput.style.display = "none";
        }

        // Show enum input for ENUM type
        if (this.withValueTypes.includes(columnType) || this.withRangeTypes.includes(columnType)) 
        {
            enumInput.style.display = "inline";
        } 
        else 
        {
            enumInput.style.display = "none";
        }
        if(typeof this.defaultLength[columnType] != 'undefined')
        {
            let defaultLength = this.defaultLength[columnType];
            lengthInput.value = defaultLength;
        }

        if(tr.querySelector('.column-primary-key').checked)
        {
            if(this.autonumberTypes.includes(tr.querySelector('.column-type').value))
            {
                tr.querySelector('.column-autoIncrement').disabled = false;
            }
            else
            {
                tr.querySelector('.column-autoIncrement').disabled = true;
                tr.querySelector('.column-autoIncrement').checked = false;
            }
        }
    }

    /**
     * Exports the selected entities as a MySQL SQL statement for creating the tables.
     */
    exportToSQL() {
        let sql = [];       
        
        const selectedEntities = document.querySelectorAll(this.selector+" .right-panel .selected-entity-structure:checked");  
        selectedEntities.forEach((checkbox, index) => {
            const entityIndex = parseInt(checkbox.value); 
            const entity = this.entities[entityIndex]; 
            if (entity) {
                sql.push(entity.toSQL());
            }
        });
        
        const selectedEntitiesData = document.querySelectorAll(this.selector+" .right-panel .selected-entity-data:checked");  
        selectedEntitiesData.forEach((checkbox, index) => {
            const entityIndex = parseInt(checkbox.value); 
            const entity = this.entities[entityIndex]; 
            if (entity) {
                let query = entity.toSQLInsert();
                if(query != '')
                {
                    sql.push(query);
                }
            }
        });
        
        document.querySelector(this.selector+" .query-generated").value = sql.join("\r\n");
    }
    
    /**
     * Generates a base filename based on the provided data object.
     * The priority for naming is:
     * 1. databaseName-databaseSchema (if both exist)
     * 2. databaseName (if only databaseName exists)
     * 3. applicationId (as a fallback if databaseName is not present)
     *
     * @param {Object} data - The data object containing naming information (e.g., databaseName, databaseSchema, applicationId).
     * @returns {string} The generated base filename string.
     */
    generateFileName(data) {
        if (data.databaseName && data.databaseSchema) {
            return `${data.databaseName}-${data.databaseSchema}`;
        } else if (data.databaseName) {
            return `${data.databaseName}`;
        } else {
            return `${data.applicationId}`;
        }
    }

    /**
     * Exports the given data object as a JSON file.
     * 
     * This function converts the provided JavaScript object into a JSON string, creates a Blob object from
     * the string, and then triggers a download of the Blob as a `.json` file using a temporary anchor element.
     * The filename will include a datetime suffix to avoid overwriting files, and the last underscore
     * will be removed from the datetime part of the filename.
     * 
     * @param {Object} data - The JavaScript object to be exported as JSON.
     */
    exportJSON(data) {
        // Get the base filename from the data object
        const fileName = this.generateFileName(data);

        // Get current date and time in the format YYYY-MM-DD_HH-MM-SS
        const now = new Date();
        let dateTimeSuffix = now.toISOString().replace(/[-T:\.Z]/g, '_');  // NOSONAR

        // Remove the last underscore if present
        if (dateTimeSuffix.endsWith('_')) {
            dateTimeSuffix = dateTimeSuffix.slice(0, -1);  // Remove the last underscore
        }

        // Create the filename with the datetime suffix
        const finalFileName = `${fileName}_${dateTimeSuffix}.json`;

        // Convert the object to a JSON string
        const jsonData = JSON.stringify(data); // Indented JSON for readability

        // Create a Blob object from the JSON string
        const blob = new Blob([jsonData], { type: "application/json" });

        // Create a URL for the Blob
        const url = URL.createObjectURL(blob);

        // Create a temporary anchor element
        const a = document.createElement("a");
        a.href = url;
        a.download = finalFileName; // Set the filename to include the datetime suffix
        document.body.appendChild(a);
        a.click(); // Trigger the download by clicking the anchor
        document.body.removeChild(a); // Clean up by removing the anchor
        URL.revokeObjectURL(url); // Release the object URL
    }

    /**
     * Downloads a SQL file containing database information.
     * 
     * This function collects metadata about the database from the document, constructs a data object, 
     * and generates a `.sql` file for download. The filename will include a datetime suffix to avoid 
     * overwriting files.
     * 
     * @returns {void} - This function does not return a value.
     */
    downloadSQL() {
        let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
        let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
        let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
        let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
        
        const data = {
            applicationId: applicationId,
            databaseType: databaseType,
            databaseName: databaseName,
            databaseSchema: databaseSchema,
        };

        // Get the base filename from the data object
        const fileName = this.generateFileName(data);

        // Get current date and time in the format YYYY-MM-DD_HH-MM-SS
        const now = new Date();
        let dateTimeSuffix = now.toISOString().replace(/[-T:\.Z]/g, '_');  // NOSONAR

        // Remove the last underscore if present
        if (dateTimeSuffix.endsWith('_')) {
            dateTimeSuffix = dateTimeSuffix.slice(0, -1);  // Remove the last underscore
        }

        // Create the filename with the datetime suffix
        const finalFileName = `${fileName}_${dateTimeSuffix}.sql`;

        // Convert the object to a JSON string

        // Create a Blob object from the JSON string
        const blob = new Blob([document.querySelector(this.selector+' .query-generated').value], { type: "text/plain" });

        // Create a URL for the Blob
        const url = URL.createObjectURL(blob);

        // Create a temporary anchor element
        const a = document.createElement("a");
        a.href = url;
        a.download = finalFileName; // Set the filename to include the datetime suffix
        document.body.appendChild(a);
        a.click(); // Trigger the download by clicking the anchor
        document.body.removeChild(a); // Clean up by removing the anchor
        URL.revokeObjectURL(url); // Release the object URL
    }

    /**
     * Imports JSON data from a file and processes it.
     * 
     * This function accepts a file object, reads its contents as text using a FileReader, then parses the JSON
     * content and updates the editor's entities with the parsed data. It also triggers a re-render of the entities.
     * After the import, a callback function is invoked with the imported entities, if provided.
     * 
     * @param {File} file - The file object containing the JSON data to be imported.
     * @param {Function} [callback] - Optional callback function to be executed after the entities are updated. 
     *                                The callback will receive the updated entities as its argument.
     */
    importJSON(file, callback) {
        let _this = this;
        const reader = new FileReader(); // Create a FileReader instance
        reader.onload = function (e) {
            const contents = e.target.result; // Get the content of the file
            try {
                let raw = JSON.parse(contents);  // Parse the JSON content
                let data = editor.createEntitiesFromJSON(raw); // Create entities from the parsed JSON
                _this.clearEntities(); // Clear the existing entities
                _this.clearDiagrams(); // Clear the existing diagrams
                _this.entities = data.entities; // Insert the received data into editor.entities
                _this.diagrams = data.diagrams || []; // Insert the received data into editor.diagrams
                _this.prepareDiagram(); // Prepare the diagram by loading saved entities and diagrams
                _this.updateDiagram(); // Update the diagram with the imported entities
                _this.renderEntities(); // Update the view with the fetched entities
                if (typeof callback === 'function') {
                    callback(_this.entities); // Execute callback with the updated entities
                }
            } catch (err) {
                console.log("Error parsing JSON: " + err.message); // Handle JSON parsing errors
            }
        };
        reader.readAsText(file); // Read the file as text
    }

    /**
     * Imports an SQL file and processes its content.
     * 
     * This function accepts an SQL file, reads its contents as text using a FileReader, then parses it 
     * using a `TableParser` and updates the editor's entities with the parsed data. After the import, 
     * a callback function is invoked with the updated entities, if provided.
     * 
     * @param {File} file - The SQL file object to be imported.
     * @param {Function} [callback] - Optional callback function to be executed after the entities are updated. 
     *                                The callback will receive the updated entities as its argument.
     * @returns {void} - This function does not return a value.
     */
    importSQLFile(file, callback) {
        let _this = this;
        const reader = new FileReader(); // Create a FileReader instance
        reader.onload = function (e) {
            let contents = e.target.result; // Get the content of the file
            try {
                let translator = new SQLConverter();
                contents = translator.translate(contents, 'mysql').split('`').join('');
                let parser = new TableParser(contents);


                let importedEntities = editor.createEntitiesFromSQL(parser.tableInfo); // Insert the received data into editor.entities  
                if(_this.clearBeforeImport)
                {
                    _this.entities = importedEntities;    
                    _this.clearEntities(); // Clear the existing entities
                    _this.clearDiagrams(); // Clear the existing diagrams
                    _this.renderEntities(); // Update the view with the fetched entities
                }
                else
                {
                    let existing = [];
                    _this.entities.forEach((entity) => {
                        existing.push(entity.name);
                    });
                    importedEntities.forEach((entity) => {
                        if(!existing.includes(entity.name))
                        {
                            entity.index = _this.entities.length;
                            _this.entities.push(entity);
                        }
                    });    
                    _this.renderEntities(); // Update the view with the fetched entities
                }
                
                if (typeof callback === 'function') {
                    callback(_this.entities); // Execute callback with the updated entities
                }

                _this.restoreCheckedEntitiesFromCurrentDiagram(); // Restore checked entities from the current diagram
                
            } catch (err) {
                console.log("Error parsing JSON: " + err.message); // Handle JSON parsing errors
            }
        };
        reader.readAsText(file); // Read the file as text
    }
    
    /**
     * Converts a string (e.g., file or sheet name) into a valid entity/table name.
     *
     * This function ensures the result is compatible with database naming conventions by:
     * - Removing file extensions (e.g., `.csv`, `.xlsx`).
     * - Replacing non-alphanumeric characters with underscores.
     * - Converting the entire string to lowercase.
     * - Trimming leading and trailing underscores.
     *
     * @param {string} str - The original name (e.g., file name or sheet name).
     * @returns {string} A sanitized and valid table name in lowercase with underscores.
     */
    toValidTableName(str) {
        return str
            .replace(/\.[^/.]+$/, '') // NOSONAR
            .replace(/[^a-zA-Z0-9]+/g, '_') // NOSONAR
            .toLowerCase()
            .replace(/^_+|_+$/g, ''); // NOSONAR
    }

    /**
     * Imports and parses a spreadsheet file (CSV, XLSX, or XLS),
     * then generates and loads column definitions into the entity editor.
     *
     * Supports both text-based CSV and binary spreadsheet formats.
     * Uses FileReader and XLSX/PapaParse libraries to extract data.
     *
     * @param {File} file - The uploaded file to be imported.
     */
    importSheetFile(file) {
        const _this = this;
        const ext = file.name.split('.').pop().toLowerCase();
        const reader = new FileReader();

        reader.onload = function (e) {
            const contents = e.target.result;

            if (ext === 'csv') {
                const parsed = Papa.parse(contents, { header: true });
                const headers = parsed.meta.fields;
                const rows = parsed.data;

                const entityName = _this.toValidTableName(file.name);
                const columns = _this.generateCreateTable(headers, rows);
                _this.importFromSheet(columns, entityName);
                _this.currentEntityData = rows;
            } else if (ext === 'xlsx' || ext === 'xls') {
                const uint8Array = new Uint8Array(contents);
                const workbook = XLSX.read(uint8Array, { type: "array" });

                const selectSheetAndImport = (sheetIndex) => {
                    const sheetName = workbook.SheetNames[sheetIndex];
                    const json = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], { defval: "" });
                    _this.currentEntityData = json;
                    if (json.length > 0) {
                        const headers = Object.keys(json[0]);
                        const entityName = _this.toValidTableName(file.name)+'_'+_this.toValidTableName(sheetName);
                        const columns = _this.generateCreateTable(headers, json);
                        _this.importFromSheet(columns, entityName);
                    }
                };

                if (workbook.SheetNames.length > 1) {
                    let message = `
                        <table class="two-side-table">
                            <tbody>
                                <tr>
                                    <td>Sheet to Import</td>
                                    <td>
                                        <select id="sheet-index" class="form-control">
                                            ${workbook.SheetNames.map((name, index) => 
                                                `<option value="${index}">${index + 1}. ${name}</option>`
                                            ).join('')}
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    `;
                    _this.showConfirmationDialog(message, 'Select Sheet', 'OK', 'Cancel', function(isOk){
                        if (isOk) {
                            let sheetIndex = parseInt(document.querySelector('#sheet-index').value);
                            selectSheetAndImport(sheetIndex);
                        }
                    });
                } else {
                    selectSheetAndImport(0);
                }
            } else {
                alert("Unsupported file format: " + ext);
            }
        };

        if (ext === 'csv') {
            reader.readAsText(file); // for CSV
        } else {
            reader.readAsArrayBuffer(file); // for Excel
        }
    }

    /**
     * Converts all keys in each row object to `snake_case`, but only includes keys that match column definitions.
     *
     * This function ensures that only columns defined in the `columns` array will be included in the result,
     * and their keys will be converted to snake_case using the `cleanColumnName()` helper.
     *
     * @param {Array<Object>} rows - Array of data row objects with camelCase or PascalCase keys.
     * @param {Array<Object>} columns - Array of column metadata, each with a `name` property.
     * @returns {Array<Object>} A new array of objects with keys converted to snake_case.
     */
    snakeize(rows, columns) {
        const columnNames = columns.map(col => col.name);
        const result = [];

        if (Array.isArray(rows)) {
            for (const row of rows) {
                const snakeRow = {};
                for (const key of Object.keys(row)) {
                    const snakeKey = this.cleanColumnName(key);
                    if (columnNames.includes(snakeKey)) {
                        snakeRow[snakeKey] = row[key];
                    }
                }
                result.push(snakeRow);
            }
        }

        return result;
    }

    /**
     * Infers the most appropriate SQL data type from a sample array of values.
     *
     * The function tests whether values appear to be:
     * - Integers (BIGINT)
     * - Floats (FLOAT)
     * - Booleans (BOOLEAN)
     * - Timestamps/Dates (TIMESTAMP)
     * - Otherwise defaults to TEXT.
     *
     * Only the first 10 values are evaluated for performance.
     *
     * @param {Array<any>} values - An array of sample values to analyze.
     * @returns {string} The inferred SQL data type as a string.
     */
    guessType(values) // NOSONAR
    {
        let isInt = true, isFloat = true, isBool = true, isDate = true;

        for (let val of values.slice(0, 10)) {
            // Safely normalize to string
            if (val === undefined || val === null) {
                continue;
            } else if (typeof val !== "string") {
                val = String(val).trim(); // NOSONAR
            } else {
                val = val.trim(); // NOSONAR
            }

            if (val === "") continue;

            if (!/^[-+]?\d+$/.test(val)) isInt = false;
            if (!/^[-+]?\d+(\.\d+)?$/.test(val)) isFloat = false;
            if (!/^(true|false|yes|no|1|0)$/i.test(val)) isBool = false;
            if (isNaN(Date.parse(val))) isDate = false;
        }

        if (isFloat) return "FLOAT";
        if (isInt) return "BIGINT";
        if (isBool) return "BOOLEAN";
        if (isDate) return "TIMESTAMP";
        return "TEXT";
    }


    /**
     * Generates an array of column definitions based on headers and data rows.
     *
     * This function:
     * - Cleans and normalizes column names (spaces and special characters removed).
     * - Uses sample data from each column to infer the appropriate SQL data type.
     * - Creates a `Column` instance for each header with inferred properties.
     *
     * @param {string[]} headers - The list of column headers (field names) from the data source.
     * @param {Array<Object>} rows - The data rows used to sample values for type inference.
     * @returns {Column[]} An array of `Column` objects representing the inferred table schema.
     */
    generateCreateTable(headers, rows) {
        let _this = this;

        const cols = headers.map(header => {
            const cleanName = _this.cleanColumnName(header)

            const values = rows.map(row => row[header]);
            const type = _this.guessType(values);

            return new Column(cleanName, type, null, true, null, false, false, null);
        });

        return cols;
    }
    
    cleanColumnName(header)
    {
        return header
            .replace(/\s+/g, "_")
            .replace(/[^\w]/g, "")
            .toLowerCase();
    }


    /**
     * Triggers the import action for JSON entities by simulating a click
     * on the file input element associated with JSON import.
     * 
     * This method sets `clearBeforeImport` to `true`, which indicates that
     * any existing data should be cleared before importing new data. It then 
     * locates the DOM element using the `selector` property combined with 
     * the `.import-file-json` class, and simulates a click to prompt file selection.
     */
    uploadEntities() {
        this.clearBeforeImport = true;
        document.querySelector(this.selector + " .import-file-json").click();
    }

    /**
     * Triggers the import action for SQL data by simulating a click
     * on the file input element associated with SQL import.
     * 
     * This method sets `clearBeforeImport` to `true`, meaning existing data
     * will be cleared before new data is imported. It locates the file input
     * element using the `selector` property and triggers a click event.
     */
    importSQL() {
        this.clearBeforeImport = true;
        document.querySelector(this.selector + " .import-file-sql").click();
    }

    importSheet() {
        this.clearBeforeImport = true;
        document.querySelector(this.selector + " .import-file-sheet").click();
    }

    /**
     * Appends data from an SQL file without clearing existing data,
     * by simulating a click on the file input element associated with SQL import.
     * 
     * This method sets `clearBeforeImport` to `false`, allowing the imported
     * SQL data to be appended to the current dataset. It uses the `selector` 
     * property to locate the appropriate DOM element and triggers a click.
     */
    appendFromSQL() {
        this.clearBeforeImport = false;
        document.querySelector(this.selector + " .import-file-sql").click();
    }

    /**
     * Gathers metadata from the HTML document and exports the entities data as a JSON file.
     * The function retrieves application-specific details such as `application-id`, 
     * `database-name`, `database-schema`, and `database-type` from the meta tags in the document.
     * Then, it constructs a data object containing these values and the current list of entities from
     * the editor, and passes it to the `exportJSON` method to export the data as a JSON file.
     * 
     * @returns {void} - This function does not return a value. 
     */
    downloadEntities() {
        let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
        let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
        let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
        let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
        
        const data = {
            applicationId: applicationId,
            databaseType: databaseType,
            databaseName: databaseName,
            databaseSchema: databaseSchema,
            entities: this.entities,  // Converting the entities array into a JSON string
            diagrams: this.getDiagrams() // Converting the diagrams array into a JSON string
        };
        
        this.exportJSON(data); // Export the sample object to a JSON file
    }

    /**
     * Retrieves the data containing the entities.
     *
     * This method returns an object with a property `entities`, which contains
     * the current entities stored in the class.
     *
     * @returns {Object} An object containing the `entities` property.
     * @property {Array} entities - The array of entities in the current instance.
     */
    getData() {
        return {entities: this.entities};
    }

    /**
     * Displays an alert dialog with an OK button.
     * The dialog will show a message and a title, and execute a callback when the OK button is clicked.
     *
     * @param {string} message - The message to display in the body of the dialog.
     * @param {string} title - The title to display in the header of the dialog.
     * @param {string} captionOk - The label to display on the OK button.
     * @param {Function} callback - The callback function to be called when the OK button is clicked.
     * 
     * @returns {void} - This function does not return a value.
     */
    showAlertDialog(message, title, captionOk, callback)
    {
        // Get modal and buttons
        const modal = document.querySelector('#asyncAlert');
        let okBtn = modal.querySelector('.alert-ok');
        okBtn = this.removeAllEventListeners(okBtn);

        modal.querySelector('.modal-header h3').innerHTML = title;
        modal.querySelector('.modal-body').innerHTML = message;
        okBtn.innerHTML = captionOk;

        // Show the modal
        modal.style.display = 'block';

        // Define the event listener for OK button
        function handleOkClick() {
            modal.style.display = 'none';
            if(typeof callback == 'function')
            {
                callback();
            }
        }

        // Add event listeners for OK and Cancel buttons
        okBtn.addEventListener('click', handleOkClick);
        okBtn.focus();
    }

    /**
     * Displays a confirmation dialog with OK and Cancel buttons.
     * Executes the provided callback with `true` if OK is clicked, or `false` if Cancel is clicked.
     * 
     * @param {string} message - The message to display in the dialog.
     * @param {string} title - The title of the dialog.
     * @param {string} captionOk - The label for the OK button.
     * @param {string} captionCancel - The label for the Cancel button.
     * @param {Function} callback - The callback function to be called with the result (`true` or `false`).
     * 
     * @returns {void} - This function does not return a value.
     */
    showConfirmationDialog(message, title, captionOk, captionCancel, callback) {
        // Get modal and buttons
        const modal = document.querySelector('#asyncConfirm');
        
        let okBtn = modal.querySelector('.confirm-ok');
        let cancelBtn = modal.querySelector('.confirm-cancel');
        okBtn = this.removeAllEventListeners(okBtn);
        cancelBtn = this.removeAllEventListeners(cancelBtn);

        modal.querySelector('.modal-header h3').innerHTML = title;
        modal.querySelector('.modal-body').innerHTML = message;
        okBtn.innerHTML = captionOk;
        cancelBtn.innerHTML = captionCancel;

        // Show the modal
        modal.style.display = 'block';

        // Define the event listener for OK button
        function handleOkClick() {
            modal.style.display = 'none';
            callback(true);  // Execute callback with 'true' if OK is clicked
        }

        // Define the event listener for Cancel button
        function handleCancelClick() {
            modal.style.display = 'none';
            callback(false);  // Execute callback with 'false' if Cancel is clicked
        }

        // Add event listeners for OK and Cancel buttons
        okBtn.addEventListener('click', handleOkClick);
        cancelBtn.addEventListener('click', handleCancelClick);
    }

    /**
     * Removes all event listeners from the given element by replacing it with a cloned copy.
     * The new element will be an exact copy of the original element, including its children and attributes,
     * but without any event listeners attached.
     *
     * @param {HTMLElement} element - The DOM element from which event listeners will be removed.
     * 
     * @returns {HTMLElement} - The cloned element that is a replacement for the original element, without event listeners attached.
     */
    removeAllEventListeners(element) {
        const newElement = element.cloneNode(true);  // clone the element with all children and attributes
        element.parentNode.replaceChild(newElement, element);  // replace the old element with the new one
        return newElement;  // return the cloned element
    }

    /**
     * Creates a dropdown (select) option with MySQL data types and binds it to the given name.
     * 
     * @param {string} name - The name for the select input field.
     * @param {string} selectorLength - The selector for the length input field.
     * 
     * @returns {string} - The HTML string for the dropdown (select) element.
     */
    createDataTypeOption(name, selectorLength)
    {
        let html = '';
        html += `<select name="${name}" onchange="editor.setDefaultLength(this, '${selectorLength}')">\r\n`;
        this.mysqlDataTypes.forEach((type, index) => {
            html += `<option value="${type}">${type}</option>\r\n`;
        });
        html += `</select>`;
        return html;
    }
    
    /**
     * Sets the default length for the selected data type in the given element.
     * 
     * @param {HTMLElement} element - The select input element for the data type.
     * @param {string} selectorLength - The selector for the length input field.
     * 
     * @returns {void} - This function does not return a value.
     */
    setDefaultLength(element, selectorLength)
    {
        let type = element.value;
        if(typeof this.defaultLength[type] != 'undefined')
        {
            document.querySelector(selectorLength).value = this.defaultLength[type];
        }
    }

    /**
     * Displays the preference settings dialog and handles saving the user's preferences.
     * 
     * @returns {void} - This function does not return a value.
     */
    preference()
    {
        let _this = this;
        _this.showSettingDialog(`
            <table class="two-side-table">
                <tbody>
                    <tr>
                        <td>Primary Key Type</td>
                        <td>${_this.createDataTypeOption('primary_key_type', '#primary_key_length')}</td>
                    </tr>
                    <tr>
                        <td>Primary Key Length</td>
                        <td><input type="text" name="primary_key_length" id="primary_key_length" value=""></td>
                    </tr>
                    <tr>
                        <td>Column Type</td>
                        <td>${_this.createDataTypeOption('column_type', '#column_length')}</td>
                    </tr>
                    <tr>
                        <td>Column Length</td>
                        <td><input type="text" name="column_length" id="column_length" value=""></td>
                    </tr>
                </tbody>
            </table>
            `, 'Preferences', 'OK', 'Cancel', function(isConfirmed) {
            if (isConfirmed) {
                _this.primaryKeyDataType = document.querySelector('[name="primary_key_type"]').value;
                _this.primaryKeyDataLength = document.querySelector('[name="primary_key_length"]').value;
                _this.defaultDataType = document.querySelector('[name="column_type"]').value;
                _this.defaultDataLength = document.querySelector('[name="column_length"]').value;    
                if(typeof _this.callbackSaveConfig == 'function')
                {
                    _this.callbackSaveConfig({
                        primaryKeyDataType: _this.primaryKeyDataType,
                        primaryKeyDataLength: _this.primaryKeyDataLength,
                        defaultDataType: _this.defaultDataType,
                        defaultDataLength: _this.defaultDataLength,
                    });
                }
            } 
        });

        document.querySelector('[name="primary_key_type"]').value = _this.primaryKeyDataType;
        document.querySelector('[name="primary_key_length"]').value = _this.primaryKeyDataLength;
        document.querySelector('[name="column_type"]').value = _this.defaultDataType;
        document.querySelector('[name="column_length"]').value = _this.defaultDataLength;

    }

    /**
     * Displays a settings dialog for configuring various preferences.
     * 
     * @param {string} message - The content (HTML) to be displayed inside the modal.
     * @param {string} title - The title of the dialog.
     * @param {string} captionOk - The label for the OK button.
     * @param {string} captionCancel - The label for the Cancel button.
     * @param {Function} callback - The callback function to be called with the result (`true` or `false`).
     * 
     * @returns {void} - This function does not return a value.
     */
    showSettingDialog(message, title, captionOk, captionCancel, callback) {
        // Get modal and buttons
        const modal = document.querySelector('#settingModal');
        const okBtn = modal.querySelector('.confirm-ok');
        const cancelBtn = modal.querySelector('.confirm-cancel');

        modal.querySelector('.modal-header h3').innerHTML = title;
        modal.querySelector('.modal-body').innerHTML = message;
        okBtn.innerHTML = captionOk;
        cancelBtn.innerHTML = captionCancel;

        // Show the modal
        modal.style.display = 'block';

        // Remove existing event listeners to prevent duplicates
        okBtn.removeEventListener('click', handleOkConfig);
        cancelBtn.removeEventListener('click', handleCancelConfig);

        // Define the event listener for OK button
        function handleOkConfig() {
            modal.style.display = 'none';
            callback(true);  // Execute callback with 'true' if OK is clicked
        }

        // Define the event listener for Cancel button
        function handleCancelConfig() {
            modal.style.display = 'none';
            callback(false);  // Execute callback with 'false' if Cancel is clicked
        }

        // Add event listeners for OK and Cancel buttons
        okBtn.addEventListener('click', handleOkConfig);
        cancelBtn.addEventListener('click', handleCancelConfig);
    }
    
    /**
     * Displays a dialog showing editable tabular data for a given entity.
     *
     * @param {Object} entity - The entity metadata containing column definitions and data.
     * @param {string} title - The title to be displayed on the modal dialog.
     *
     * @returns {void}
     */
    showEntityDataDialog(entity, title) {
        const modal = document.querySelector('#entityDataEditorModal');
        const modalHeader = modal.querySelector('.modal-header h3');
        const modalBody = modal.querySelector('.modal-body');
        const data = entity.data || [];

        modalHeader.innerHTML = title || 'Entity Data';
        modalBody.innerHTML = ''; // Clear previous content

        // Create scrollable wrapper
        const wrapper = document.createElement('div');
        wrapper.style.overflow = 'auto';
        wrapper.style.maxHeight = '400px';

        // Create table
        const table = document.createElement('table');
        table.className = 'data-preview-table';
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';

        // Create thead
        const thead = document.createElement('thead');
        const headRow = document.createElement('tr');

        const emptyTh = document.createElement('th'); // For delete button column
        emptyTh.classList.add('td-remover');
        headRow.appendChild(emptyTh);

        entity.columns.forEach(col => {
            const th = document.createElement('th');
            th.textContent = col.name;
            th.style.padding = '6px';
            th.style.background = '#f8f8f8';
            headRow.appendChild(th);
        });

        thead.appendChild(headRow);
        table.appendChild(thead);

        // Create tbody
        const tbody = document.createElement('tbody');

        data.forEach((row, rowIndex) => {
            const tr = document.createElement('tr');

            // Delete button column
            const deleteTd = document.createElement('td');
            deleteTd.classList.add('td-remover');
            const deleteLink = document.createElement('a');
            deleteLink.className = 'delete-row';
            deleteLink.href = 'javascript:';
            deleteLink.textContent = '❌';
            
            deleteTd.appendChild(deleteLink);
            tr.appendChild(deleteTd);

            entity.columns.forEach((col, colIndex) => {
                const td = this.createEntityDataCell(rowIndex, colIndex, col, row[col.name] ?? '');
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
            
            deleteLink.addEventListener('click', function(e){
               e.preventDefault();
               tbody.removeChild(tr);
            });
        });

        table.appendChild(tbody);
        wrapper.appendChild(table);
        modalBody.appendChild(wrapper);

        // Show modal
        modal.style.display = 'block';
    }
    
    /**
     * Creates a <td> element containing an editable input for entity data.
     *
     * @param {number} rowIndex - The row index in the table.
     * @param {number} colIndex - The column index in the table.
     * @param {Object} col - Column definition object with at least `name` and `type` properties.
     * @param {string} [value=""] - Optional value to prefill the input field.
     * @returns {HTMLTableCellElement} The created <td> element.
     */
    createEntityDataCell(rowIndex, colIndex, col, value = "") {
        const td = document.createElement('td');
        const input = document.createElement('input');

        input.type = 'text';
        input.classList.add('entity-data-cell');
        input.name = `cell-${rowIndex}-${colIndex}`;
        input.dataset.row = rowIndex;
        input.dataset.col = col.name;
        input.dataset.type = col.type;
        input.value = value ?? '';
        input.style.width = '100%';
        input.style.boxSizing = 'border-box';

        td.appendChild(input);
        return td;
    }


    /**
     * Adds a new empty row to the editable entity data table.
     * 
     * @returns {void}
     */
    addData() {
        const entity = this.entities[this.currentEntityIndex];
        const modal = document.querySelector('#entityDataEditorModal');
        const tableBody = modal.querySelector('.data-preview-table tbody');

        if (!tableBody) return;

        const rowIndex = tableBody.rows.length;
        const row = document.createElement('tr');

        // Kolom untuk tombol delete
        const deleteTd = document.createElement('td');
        deleteTd.classList.add('td-remover');
        const deleteLink = document.createElement('a');
        deleteLink.className = 'delete-row';
        deleteLink.href = 'javascript:';
        deleteLink.textContent = '❌';
        deleteTd.appendChild(deleteLink);
        row.appendChild(deleteTd);

        // Tambahkan input sel berdasarkan kolom entity
        entity.columns.forEach((col, colIndex) => {
            const td = this.createEntityDataCell(rowIndex, colIndex, col, '');
            row.appendChild(td);
            deleteLink.addEventListener('click', function(e){
               e.preventDefault();
               tbody.removeChild(tr);
            });
        });

        tableBody.appendChild(row);
    }

    
    /**
     * Saves the current editable data from the entity data table
     * into the corresponding entity's data structure.
     * 
     * @returns {void}
     */
    saveData() {
        const entity = this.entities[this.currentEntityIndex];
        const modal = document.querySelector('#entityDataEditorModal');
        const inputs = modal.querySelectorAll('.data-preview-table input');

        const rowDataMap = {};

        // Kelompokkan input berdasarkan baris
        inputs.forEach(input => {
            const row = input.dataset.row;
            const col = input.dataset.col;

            if (!rowDataMap[row]) {
                rowDataMap[row] = {};
            }

            rowDataMap[row][col] = input.value;
        });

        // Konversi ke array
        const newData = Object.keys(rowDataMap)
            .sort((a, b) => parseInt(a) - parseInt(b))
            .map(rowKey => rowDataMap[rowKey]);

        // Update entity data
        entity.data = newData;
        let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
        let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
        let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
        let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
        sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, this.entities); 
        modal.style.display = 'none';
    }



}


// Global instance variable for TabDragger, initialized to null.
// This allows external access to the TabDragger instance once it's created.
let tabDragger = null;