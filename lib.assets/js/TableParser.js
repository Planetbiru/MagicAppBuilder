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
        const typeList = 'timestamptz,timestamp,serial4,bigserial,int2,int4,int8,tinyint,bigint,text,nvarchar,varchar,enum,set,numeric,decimal,char,real,float,integer,int,datetime,date,double,boolean,bool';
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
     * @param {string} field The field definition.
     * @returns {boolean} True if the field is auto-incremented, otherwise false.
     */
    isAutoIncrement(field) {
        const f = field.toUpperCase().replace(/\s+/g, ' ').trim();
        return f.includes('AUTO_INCREMENT');
    }

    /**
     * Parses a CREATE TABLE SQL statement and extracts table and column information.
     * @param {string} sql The SQL string representing a CREATE TABLE statement.
     * @returns {Object} An object containing table name and columns, along with primary key information.
     */
    parseTable(sql) {
        let arr = sql.split(";");
        sql = arr[0];

        let rg_tb = /(create\s+table\s+if\s+not\s+exists|create\s+table)\s(?<tb>.*)\s\(/gim;
        let rg_fld = /(\w+\s+key.*|\w+\s+bigserial|\w+\s+serial4|\w+\s+tinyint.*|\w+\s+bigint.*|\w+\s+text.*|\w+\s+nvarchar.*|\w+\s+varchar.*|\w+\s+char.*|\w+\s+real.*|\w+\s+float.*|\w+\s+integer.*|\w+\s+int.*|\w+\s+datetime.*|\w+\s+date.*|\w+\s+double.*|\w+\s+bigserial.*|\w+\s+serial.*|\w+\s+timestamp.*|\w+\s+timestamptz.*|\w+\s+boolean.*|\w+\s+bool.*|\w+\s+enum\s*\(.*\)|\w+\s+enum\s*\(.*\)|\w+\s+set\s*\(.*\)|\w+\s+decimal\s*\(.*\)|\w+\s+numeric\s*\(.*\))/gim;
        let rg_fld2 = /(?<fname>\w+)\s+(?<ftype>\w+)(?<fattr>.*)/gi;
        let rg_enum = /enum\s*\(([^)]+)\)/i;
        let rg_not_null = /not\s+null/i;
        let rg_pk = /primary\s+key/i;
        let rg_fld_def = /default\s+(.+)/i;
        let rg_pk2 = /(PRIMARY|UNIQUE) KEY[a-zA-Z_0-9\s]+\(([a-zA-Z_0-9,\s]+)\)/gi;

        let result = rg_tb.exec(sql);
        let tableName = result.groups.tb;

        let fld_list = [];
        let primaryKey = null;
        let columnList = [];
        let pk = null;
        let pkLine = "";
        let primaryKeyList = [];

        while ((result = rg_fld.exec(sql)) != null) {
            let f = result[0];
            let line = f;

            // Reset regex for field parsing
            rg_fld2.lastIndex = 0;
            let fld_def = rg_fld2.exec(f);
            let dataType = fld_def[2];
            let is_pk = false;

            // Handle ENUM type and convert to VARCHAR
            if (rg_enum.test(dataType)) {
                let enumValues = rg_enum.exec(dataType)[1];
                let enumArray = enumValues.split(',').map(val => val.trim().replace(/['"]/g, ''));
                let maxLength = Math.max(...enumArray.map(val => val.length));
                let length = maxLength + 2;
                let targetType = "VARCHAR";
                dataType = `${targetType}(${length})`;
            }

            if (this.isValidType(dataType.toString())) {
                let attr = fld_def.groups.fattr.replace(',', '').trim();
                let nullable = !rg_not_null.test(attr);
                let attr2 = attr.replace(rg_not_null, '');

                is_pk = rg_pk.test(attr2) || this.isPrimaryKey(line);
                let is_ai = this.isAutoIncrement(line);

                let def = rg_fld_def.exec(attr2);
                let comment = null;
                if (def && def.length > 0) {
                    def = def[1].trim();
                    if (def.toLowerCase().includes('comment')) {
                        comment = def.substring(def.indexOf('comment'));
                    }
                } else {
                    def = null;
                }

                let length = this.getLength(attr);

                let columnName = fld_def.groups.fname.trim();
                if (is_pk) primaryKeyList.push(columnName);
                if (!this.inArray(columnList, columnName)) {
                    fld_list.push({
                        'Field': columnName,
                        'Type': dataType.trim(),
                        'Length': length,
                        'Key': is_pk,
                        'Nullable': nullable,
                        'Default': def,
                        'AutoIncrement': is_ai
                    });
                    columnList.push(columnName);
                }
            } else if (result[1].toLowerCase().includes('primary') && result[1].toLowerCase().includes('key')) {
                let text = result[1];
                let re = /\((.*)\)/;
                let matched = text.match(re);
                if (primaryKey == null) {
                    primaryKey = matched ? matched[1] : null;
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
                let pkeys = x.split(',').map(pkey => pkey.trim());
                for (let i in fld_list) {
                    if (this.inArray(pkeys, fld_list[i]['Field'])) {
                        fld_list[i]['Key'] = true;
                    }
                }
            }
        }

        if (primaryKey == null) {
            primaryKey = primaryKeyList[0];
        }

        return { tableName: tableName, columns: fld_list, primaryKey: primaryKey };
    }

    /**
     * Extracts the length of a column type if specified (e.g., VARCHAR(255)).
     * @param {string} text The attribute text containing the length (e.g., VARCHAR(255)).
     * @returns {string} The length of the column type or an empty string if no length is found.
     */
    getLength(text) {
        if (text.includes('(') && text.includes(')')) {
            let re = /\((.*)\)/;
            let match = text.match(re);
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
        return this.typeList.includes(dataType.toLowerCase());
    }

    /**
     * Parses all CREATE TABLE statements from a SQL string and collects the information.
     * @param {string} sql The SQL string containing multiple CREATE TABLE statements.
     */
    parseAll(sql) {
        let inf = [];
        let result;
        let rg_tb = /(create\s+table\s+if\s+not\s+exists|create\s+table)\s(?<tb>.*)\s\(/gi;
        while ((result = rg_tb.exec(sql)) != null) {
            let sub = sql.substring(result.index);
            let info = this.parseTable(sub);
            inf.push(info);
        }
        this.tableInfo = inf;
    }

    /**
     * Returns the parsed result containing table and column information.
     * @returns {Array} The parsed table information.
     */
    getResult() {
        return this.tableInfo;
    }
}
