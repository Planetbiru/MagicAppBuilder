/**
 * Class to parse SQL CREATE TABLE statements and extract information about tables and columns.
 * It handles various SQL types and constraints such as primary keys, data types, not null, default values, and more.
 */
class TableParser {
    
    /**
     * Constructor initializes the type list and parses the given SQL if provided.
     * @param {string} [sql] Optional SQL string to parse upon initialization.
     */
    constructor() {

        /**
         * Helper function to check if an element exists in an array.
         * @param {Array} haystack The array to search in.
         * @param {string} needle The element to search for.
         * @returns {boolean} Returns true if the element exists in the array, otherwise false.
         */
        this.inArray = function (haystack, needle) {
            for (let i in haystack) {
                if (haystack[i] == needle) {
                    return true;
                }
            }
            return false;
        };

        /**
         * Parses a CREATE TABLE SQL statement and extracts table and column information.
         * @param {string} sql The SQL string representing a CREATE TABLE statement.
         * @returns {Object} An object containing table name and columns, along with primary key information.
         */
        this.parseTable = function (sql) {
            let arr = sql.split(";");
            sql = arr[0];

            // The regex for each component:
            let rg_tb = /(create\s+table\s+if\s+not\s+exists|create\s+table)\s(?<tb>.*)\s\(/gim;
            let rg_fld = /(\w+\s+key.*|\w+\s+bigserial|\w+\s+serial4|\w+\s+tinyint.*|\w+\s+bigint.*|\w+\s+longtext.*|\w+\s+mediumtext.*|\w+\s+smalltext.*|\w+\s+text.*|\w+\s+nvarchar.*|\w+\s+varchar.*|\w+\s+char.*|\w+\s+real.*|\w+\s+float.*|\w+\s+integer.*|\w+\s+int.*|\w+\s+datetime.*|\w+\s+date.*|\w+\s+double.*|\w+\s+bigserial.*|\w+\s+serial.*|\w+\s+timestamp.*|\w+\s+timestamptz.*|\w+\s+boolean.*|\w+\s+bool.*|\w+\s+enum\s*\(.*\))/gim;

            let rg_fld2 = /(?<fname>\w+)\s+(?<ftype>\w+)(?<fattr>.*)/gi;
            let rg_enum = /enum\s*\(([^)]+)\)/i; // Regex untuk menangkap isi enum
            let rg_not_null = /not\s+null/i;
            let rg_pk = /primary\s+key/i;
            let rg_fld_def = /default\s+(.+)/i;
            let rg_pk2 = /(PRIMARY|UNIQUE) KEY[a-zA-Z_0-9\s]+\(([a-zA-Z_0-9,\s]+)\)/gi;

            // Look for table name
            let result = rg_tb.exec(sql);
            let tableName = result.groups.tb;

            let fld_list = [];
            let primaryKey = null;
            let columnList = [];
            let pk = null;
            let pkLine = "";

            while ((result = rg_fld.exec(sql)) != null) {
                let f = result[0];

                // Reset
                rg_fld2.lastIndex = 0;
                let fld_def = rg_fld2.exec(f);
                let dataType = fld_def[2];
                let is_pk = false;

                // If it's an ENUM type, convert it to VARCHAR or equivalent based on the database
                if (rg_enum.test(dataType)) {
                    let enumValues = rg_enum.exec(dataType)[1]; // Extract values inside ENUM parentheses
                    let enumArray = enumValues.split(',').map(val => val.trim().replace(/['"]/g, '')); // Remove quotes
                    let maxLength = Math.max(...enumArray.map(val => val.length)); // Find the max length
                    let length = maxLength + 2; // Add 2 characters as per requirement

                    // Use target database type (example: 'VARCHAR' or 'NVARCHAR')
                    let targetType = "VARCHAR"; // You can dynamically change this to 'NVARCHAR' or 'CHARACTER VARYING' based on the DB target
                    dataType = `${targetType}(${length})`; // Convert ENUM to VARCHAR with length
                }

                if (this.isValidType(dataType.toString())) {
                    // Remove the field definition terminator.
                    let attr = fld_def.groups.fattr.replace(',', '').trim();

                    // Look for NOT NULL.
                    let nullable = !rg_not_null.test(attr);

                    // Remove NOT NULL.
                    let attr2 = attr.replace(rg_not_null, '');

                    // Look for PRIMARY KEY
                    is_pk = rg_pk.test(attr2);

                    // Look for DEFAULT
                    let def = rg_fld_def.exec(attr2);

                    let comment = null;
                    if (def && def.length > 0) {
                        def = def[1].trim();
                        if (def.toLowerCase().indexOf('comment') != -1) {
                            comment = def.substring(def.indexOf('comment'));
                        }
                    } else {
                        def = null;
                    }

                    let length = this.getLength(attr);

                    // Append to the arr only if not already present in the columnList
                    let columnName = fld_def.groups.fname.trim();
                    if (!this.inArray(columnList, columnName)) {
                        fld_list.push({
                            'Field': columnName,
                            'Type': dataType.trim(),
                            'Length': length,
                            'Key': is_pk,
                            'Nullable': nullable,
                            'Default': def
                        });
                        columnList.push(columnName); // Mark this column as processed
                    }
                } else if (result[1].toLowerCase().indexOf('primary') != -1 && result[1].toLowerCase().indexOf('key') != -1) {
                    let text = result[1];
                    let re = /\((.*)\)/;
                    let matched = text.match(re);
                    if (primaryKey == null) {
                        primaryKey = matched != null && typeof matched[1] != 'undefined' ? matched[1] : null;
                    }
                }

                if (primaryKey != null) {
                    primaryKey = primaryKey.split('(').join('').split(')').join('');
                    for (let i in fld_list) {
                        if (fld_list[i]['Field'] == primaryKey) {
                            fld_list[i]['Key'] = true;
                        }
                    }
                }

                if (rg_pk2.test(f) && rg_pk.test(f)) {
                    let x = f.replace(f.match(rg_pk)[0], '');
                    x = x.replace('(', '').replace(')', '');
                    let pkeys = x.split(',');
                    for (let i in pkeys) {
                        pkeys[i] = pkeys[i].trim();
                    }
                    for (let i in fld_list) {
                        if (this.inArray(pkeys, fld_list[i]['Field'])) {
                            fld_list[i]['Key'] = true;
                        }
                    }
                }
            }
            return { tableName: tableName, columns: fld_list, primaryKey: primaryKey };
        };

        /**
         * Extracts the length of a column type if specified (e.g., VARCHAR(255)).
         * @param {string} text The attribute text containing the length (e.g., VARCHAR(255)).
         * @returns {string} The length of the column type or an empty string if no length is found.
         */
        this.getLength = function (text) {
            if (text.indexOf('(') != -1 && text.indexOf(')') != -1) {
                let re = /\((.*)\)/;
                let match = text.match(re);
                return match ? match[1] : ''; // Check if match exists
            }
            return '';
        };

        /**
         * Checks if the given data type is valid according to the predefined type list.
         * @param {string} dataType The data type to check (e.g., 'varchar', 'int').
         * @returns {boolean} True if the data type is valid, otherwise false.
         */
        this.isValidType = function (dataType) {
            return this.typeList.includes(dataType.toLowerCase());
        };

        /**
         * Returns the parsed result containing table and column information.
         * @returns {Array} The parsed table information.
         */
        this.getResult = function () {
            return this.tableInfo;
        };

        /**
         * Initializes the type list for valid SQL column types.
         */
        this.init = function () {
            let typeList = 'timestamptz,timestamp,serial4,bigserial,int2,int4,int8,tinyint,smallint,mediumint,bigint,tinytext,smalltext,mediumtext,longtext,longtext,text,nvarchar,varchar,enum,char,real,float,integer,int,datetime,date,double,boolean,bool';
            this.typeList = typeList.split(',');
        };

        /**
         * Parses all CREATE TABLE statements from a SQL string and collects the information.
         * @param {string} sql The SQL string containing multiple CREATE TABLE statements.
         */
        this.parseAll = function (sql) {
            let inf = [];
            let result;
            let rg_tb = /(create\s+table\s+if\s+not\s+exists|create\s+table)\s(?<tb>.*)\s\(/gi;
            while ((result = rg_tb.exec(sql)) != null) {
                let sub = sql.substring(result.index);
                let info = this.parseTable(sub);
                inf.push(info);
            }
            this.tableInfo = inf;
        };

        this.tableInfo = [];
        this.init();

        if (typeof sql != 'undefined') {
            this.parseAll(sql);
        }
    }
}
