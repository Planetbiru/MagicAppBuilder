class SqliteConverter {
    constructor() {
        this.dbToSqlite = {
            // MySQL and PostgreSQL types to SQLite mapping
            "int": "INTEGER",
            "tinyint": "INTEGER",  // MySQL treats tinyint as integer
            "smallint": "INTEGER",
            "mediumint": "INTEGER",
            "bigint": "INTEGER",
            "float": "REAL",
            "double": "REAL",
            "decimal": "REAL",  // SQLite doesn't have DECIMAL, treated as REAL
            "nvarchar": "NVARCHAR",
            "varchar": "NVARCHAR",
            "character varying": "NVARCHAR",
            "char": "TEXT",
            "text": "TEXT",
            "tinytext": "TEXT",
            "mediumtext": "TEXT",
            "longtext": "TEXT",
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
            "INTEGER": "INT",
            "REAL": "FLOAT",
            "TEXT": "TEXT",
            "NVARCHAR": "VARCHAR",
            "VARCHAR": "VARCHAR",
            "CHARACTER VARYING": "VARCHAR",
            "BOOLEAN": "TINYINT(1)",
            "DATETIME": "DATETIME",
            "DATE": "DATE",
            "TIMESTAMPTZ": "TIMESTAMP",
            "TIMESTAMP WITH TIME ZONE": "TIMESTAMP",
            "TIMESTAMP WITHOUT TIME ZONE": "DATETIME",
            "TIMESTAMP": "TIMESTAMPTZ",
            "JSON": "JSON"
        };

        this.dbToPostgreSQL = {
            // SQLite types to PostgreSQL mapping
            "INTEGER": "INTEGER",
            "REAL": "REAL",
            "TEXT": "TEXT",
            "NVARCHAR": "CHARACTER VARYING",
            "VARCHAR": "CHARACTER VARYING",
            "BOOLEAN": "BOOLEAN",
            "DATETIME": "TIMESTAMP",
            "DATE": "DATE",
            "TIMESTAMPTZ": "TIMESTAMP WITH TIME ZONE",
            "TIMESTAMP": "TIMESTAMP WITH TIME ZONE",
            "DATETIME": "TIMESTAMP WITHOUT TIME ZONE",
            "JSON": "JSONB"
        };
    }

    replaceAll(str, search, replacement) {
        const regex = new RegExp(search, 'gi'); // 'i' for case-insensitive, 'g' for global
        return str.replace(regex, replacement);
    }

    translate(value, targetType) {
        value = this.replaceAll(value, '`', '');
        value = this.replaceAll(value, ' timestamp with time zone', ' timestamptz');
        value = this.replaceAll(value, ' timestamp without time zone', ' timestamp');
        value = this.replaceAll(value, ' character varying', ' varchar');
        value = this.replaceAll(value, ' COLLATE pg_catalog."default"', '');
        let tableParser = new TableParser();
        tableParser.parseAll(value);
        let tables = tableParser.getResult();
        let lines = [];
        for (let i in tables) {
            let table = this.convertQuery(tables[i], targetType);
            lines.push(table);
            lines.push('');
        }
        let resultTable = lines.join('\r\n');
        return resultTable;
    }

    convertQuery(table, targetType) {
        if (targetType === 'sqlite') {
            return this.toSqliteOut(table, targetType);
        } else if (targetType === 'mysql' || targetType === 'mariadb') {
            return this.toMySQLOut(table, targetType);
        } else if (targetType === 'pgsql' || targetType === 'postgresql') {
            return this.toPostgreSQLOut(table, targetType);
        }
    }

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

    toMySQLOut(table, targetType) {
        let mysqlTable = {};
        mysqlTable.tableName = table.tableName;
        mysqlTable.primaryKey = table.primaryKey;
        mysqlTable.columns = [];
        for (let i in table.columns) {
            let column = Object.assign({}, table.columns[i]);
            column.Field = "`"+column.Field+"`"
            column.Type = this.toMySQLType(column.Type, column.Length);
            mysqlTable.columns.push(column);
        }
        return this.toMySQLTable(mysqlTable, targetType);
    }

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

    toSqliteTable(sqliteTable, targetType) {
        return this.toTable(sqliteTable, targetType);
    }

    toMySQLTable(mysqlTable, targetType) {
        return this.toTable(mysqlTable, targetType);
    }

    toPostgreSQLTable(pgTable, targetType) {
        return this.toTable(pgTable, targetType);
    }

    toTable(table, targetType) {
        let tableName = table.tableName;
        if (tableName.indexOf('.') !== -1) {
            tableName = tableName.split('.')[1];
        }
        let lines = [];
        if(targetType === 'mysql' || targetType === 'mariadb')
        {
            tableName = '`' + tableName + '`';
        }
        else if(targetType === 'pgsql' || targetType === 'postgresql')
        {
            tableName = '"' + tableName + '"';
        }
        lines.push('CREATE TABLE ' + tableName);
        lines.push('(');
        let linesCol = [];
        for (let i in table.columns) {
            let primaryKey = table.columns[i].Field === table.primaryKey;
            let colDef = '\t' + table.columns[i].Field + ' ' + table.columns[i].Type;
            if (primaryKey) {
                colDef += ' PRIMARY KEY';
                table.columns[i].Nullable = false;
            }
            if (table.columns[i].Nullable) {
                colDef += ' NULL';
            } else {
                colDef += ' NOT NULL';
            }
            let defaultValue = table.columns[i].Default;
            if (defaultValue !== '' && defaultValue !== null) {
                defaultValue = this.replaceAll(defaultValue, '::character varying', '');
                defaultValue = this.fixDefaultValue(defaultValue, targetType);
                if (defaultValue !== '' && defaultValue !== null) {
                    colDef += ' DEFAULT ' + defaultValue;
                }
            }
            linesCol.push(colDef);
        }
        lines.push(linesCol.join(',\r\n'));
        lines.push(');');
        return lines.join('\r\n');
    }
    
    fixDefaultValue(defaultValue, targetType) {
        if(targetType === 'sqlite')
        {
            if(defaultValue.toLowerCase().indexOf('now(') !== -1)
            {
                defaultValue = '';
            }
        }
        return defaultValue;
    }

    toSqliteType(type, length) {
        console.log(type, length)
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
        if(type.toUpperCase().indexOf('ENUM') != -1)
        {
            const {resultArray, maxLength} = this.parseEnumValue(length);
            sqliteType = 'NVARCHAR(' + (maxLength+2) + ')';
        }
        else if ((sqliteType === 'NVARCHAR' || sqliteType === 'INT') && length > 0) {
            sqliteType = sqliteType + '(' + length + ')';
        }
        return sqliteType;
    }

    toMySQLType(type, length) {
        let mysqlType = 'TEXT';
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
        if(type.toUpperCase().indexOf('ENUM') != -1)
        {
            const {resultArray, maxLength} = this.parseEnumValue(length);
            mysqlType = 'enum(\'' + (resultArray.join('\',\'')) + '\')';
        }
        if (mysqlType === 'VARCHAR' && length > 0) {
            mysqlType = mysqlType + '(' + length + ')';
        }
        return mysqlType;
    }

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
        if(type.toUpperCase().indexOf('ENUM') != -1)
        {
            const {resultArray, maxLength} = this.parseEnumValue(length);
            pgType = 'CHARACTER VARYING(' + (maxLength+2) + ')';
        }
        else if (pgType === 'CHARACTER VARYING' && length > 0) {
            pgType = pgType + '(' + length + ')';
        }
        return pgType;
    }

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
}