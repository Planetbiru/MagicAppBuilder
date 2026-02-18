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
        this.index = index; // Internal index
        this.name = name;
        this.columns = [];
        this.foreignKeys = []; // Foreign keys
        this.indexes = []; // Insexes
        this.data = [];
        this.description = ''; // Description of the entity
        this.creationDate = null; // Timestamp of creation
        this.modificationDate = null; // Timestamp of last modification
        this.creator = null; // User who created the entity
        this.modifier = null; // User who last modified the entity
    }

    /**
     * Creates an Entity instance from a plain object.
     *
     * @param {Object} entity - A plain object representing the entity.
     * @returns {Entity} An instance of the Entity class.
     */
    static valueOf(entity) {
        const newEntity = new Entity(entity.name, entity.index);
        if (entity.columns) {
            entity.columns.forEach(col => {
                newEntity.addColumn(Column.valueOf(col));
            });
        }
        if (entity.data) {
            newEntity.setData(entity.data);
        }
        if (entity.description) {
            newEntity.description = entity.description;
        }
        if (entity.creationDate) {
            newEntity.creationDate = entity.creationDate;
        }
        if (entity.modificationDate) {
            newEntity.modificationDate = entity.modificationDate;
        }
        if (entity.creator) {
            newEntity.creator = entity.creator;
        }
        if (entity.modifier) {
            newEntity.modifier = entity.modifier;
        }
        newEntity.foreignKeys = entity.foreignKeys;
        newEntity.indexes = entity.indexes || [];
        return newEntity;
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
     * Appends new data (rows) to the entity, preventing primary key collisions.
     * * This method adds new data to the entity's existing `this.data` array.
     * It first checks for primary key conflicts to ensure that no duplicate
     * primary keys are inserted. If a row's primary key(s) already exist
     * in the current data, that row is ignored and not added.
     * * @param {Array<Object>} newData - An array of data objects to be appended.
     * @returns {number} The number of rows successfully appended.
     */
    appendData(newData) {
        if (!newData || newData.length === 0) {
            return 0;
        }
        if(!this.data || Array.isArray(this.data))
        {
            this.data = [];
        }

        const primaryKeyColumns = this.getPrimaryKeyColumns();

        // If there are no primary keys, all data is appended.
        if (primaryKeyColumns.length === 0) {
            this.data.push(...newData);
            return newData.length;
        }

        const existingKeys = new Set();
        this.data.forEach(row => {
            const key = primaryKeyColumns.map(pk => row[pk]).join('__');
            existingKeys.add(key);
        });

        let appendedCount = 0;
        newData.forEach(newRow => {
            const newKey = primaryKeyColumns.map(pk => newRow[pk]).join('__');

            if (!existingKeys.has(newKey)) {
                this.data.push(newRow);
                existingKeys.add(newKey);
                appendedCount++;
            }
        });

        return appendedCount;
    }

    /**
     * Get entity data
     * @returns {Array<Object>} newData - An array of data objects
     */
    getData()
    {
        return this.data;
    }

    /**
     * Counts the number of columns marked as primary keys.
     *
     * @returns {number} The number of primary key columns.
     */
    countPrimaryKey() {
        return this.columns.filter(col => col.primaryKey).length;
    }

    /**
     * Returns an array of column names that are marked as primary keys.
     *
     * @returns {string[]} Array of primary key column names.
     */
    getPrimaryKeyColumns() {
        return this.columns
            .filter(col => col.primaryKey)
            .map(col => col.name);
    }

    /**
     * Returns a comma-separated string of primary key column names,
     * formatted according to the SQL dialect.
     *
     * - For MySQL: backticks are added (e.g., `id`, `user_id`)
     * - For other dialects: plain comma-separated list (e.g., id, user_id)
     *
     * @param {string} dialect - SQL dialect (e.g., "mysql", "postgresql", etc.)
     * @returns {string} The formatted primary key columns as a string.
     */
    getPrimaryKeyColumnsAsString(dialect = "mysql") {
        const keys = this.getPrimaryKeyColumns();
        return dialect === "mysql"
            ? keys.map(k => `\`${k}\``).join(', ')
            : keys.join(', ');
    }

    getForeignKeys() {
        return this.foreignKeys;
    }

    /**
     * Converts the entity (table definition with its columns) into a valid SQL `CREATE TABLE` statement.
     *
     * This method generates a complete `CREATE TABLE` statement based on:
     * - SQL dialect (e.g., MySQL, PostgreSQL, SQLite, SQL Server)
     * - Column definitions and types
     * - Primary key placement (inline or separate based on number of keys)
     * - Handling of auto-increment fields, default values, nullability, etc.
     *
     * If the table has a composite primary key (more than one column),
     * the primary key constraint is placed separately at the end of the column list.
     *
     * @param {string} dialect - Target SQL dialect: "mysql", "postgresql", "sqlite", or "sqlserver".
     * @param {boolean} withForeignKey - Whether to include foreign key constraints in the generated SQL.
     * @param {boolean} createIndex - Whether to create index into the generated SQL.
     * @returns {string} The generated SQL `CREATE TABLE` statement.
     */
    toSQL(dialect = "mysql", withForeignKey = false, createIndex = false) {

        const separatePrimaryKey = this.countPrimaryKey() > 1;
        const cols = [];
        const indexStatements = [];

        let sql = '';

        const supportsCreateIfNotExists =
            dialect === 'mysql' ||
            dialect === 'postgresql' ||
            dialect === 'sqlite';

        const ifNotExists = supportsCreateIfNotExists ? 'IF NOT EXISTS ' : '';

        sql += `-- TABLE ${this.name} BEGIN\r\n\r\n`;

        sql += `CREATE TABLE ${ifNotExists}${this.name} (\r\n`;

        this.columns.forEach(col => {
            cols.push(`\t${col.toSQL(dialect, separatePrimaryKey)}`);
        });

        if (separatePrimaryKey) {
            cols.push(`\tPRIMARY KEY(${this.getPrimaryKeyColumnsAsString(dialect)})`);
        }

        const processedIndexColumns = new Set();

        if (createIndex && this.indexes && Array.isArray(this.indexes)) {
            this.indexes.forEach(index => {
                if (index && index.columns && index.columns.length > 0) {
                    const indexSignature = index.columns.slice().sort().join(',');
                    if (processedIndexColumns.has(indexSignature)) {
                        return;
                    }
                    processedIndexColumns.add(indexSignature);

                    const indexName = index.name || `idx_${this.name}_${index.columns.join('_')}`;
                    const unique = index.unique ? 'UNIQUE ' : '';
        
                    if (dialect === 'mysql') {
                        const indexCols = index.columns.map(c => `\`${c}\``).join(', ');
                        // Add index inside CREATE TABLE for MySQL
                        cols.push(`\t${unique}INDEX \`${indexName}\` (${indexCols})`);
                    } else {
                        const indexCols = index.columns.map(c => `${c}`).join(', ');
                        const qIndexName = `${indexName}`;
                        const qTableName = `${this.name}`;

                        // Generate separate CREATE INDEX for other dialects
                        const ifNotExists = (dialect === 'sqlite' || dialect === 'postgresql') ? 'IF NOT EXISTS ' : '';
                        indexStatements.push(`CREATE ${unique}INDEX ${ifNotExists}${qIndexName} ON ${qTableName} (${indexCols});`);
                    }
                }
            });
        }

        if (withForeignKey && this.foreignKeys) {
            const fks = Array.isArray(this.foreignKeys) ? this.foreignKeys : Object.values(this.foreignKeys);

            fks.forEach(fk => {
                const fkStr = this.createForeignKey(fk, dialect);
                if (fkStr) {
                    cols.push(`\t${fkStr}`);
                }

                const fkColumns = fk.columnName.split(',').map(c => c.trim());
                const fkIndexSignature = fkColumns.slice().sort().join(',');
                if (!processedIndexColumns.has(fkIndexSignature)) {
                    const idxStr = this.createIndexStandalone(fk, dialect);
                    if (idxStr) {
                        indexStatements.push(idxStr);
                        processedIndexColumns.add(fkIndexSignature);
                    }
                }
            });
        }

        sql += cols.join(",\r\n");
        sql += "\r\n);\r\n\r\n";

        // Tambahkan index setelah CREATE TABLE
        if (indexStatements.length) {
            sql += indexStatements.join("\r\n") + "\r\n\r\n";
        }

        sql += `-- TABLE ${this.name} END\r\n\r\n`;

        return sql;
    }

    /**
     * Generates standalone SQL `CREATE INDEX` statements for the current entity.
     *
     * This method creates index definitions based on the configured indexes
     * associated with the entity. It supports multiple SQL dialects and
     * automatically adjusts syntax differences such as `IF NOT EXISTS`
     * support where applicable.
     *
     * Behavior:
     * - Generates standard `CREATE INDEX` or `CREATE UNIQUE INDEX` statements.
     * - Automatically generates index names using the convention:
     *   `idx_<table>_<column1>_<column2>` if no explicit name is provided.
     * - Adds `IF NOT EXISTS` for PostgreSQL and SQLite.
     * - Ensures compatibility with MySQL and SQL Server
     *   (without `IF NOT EXISTS`, as not supported).
     *
     * Note:
     * This method generates standalone index statements and does not embed
     * indexes inside the `CREATE TABLE` statement. It is intended to be used
     * after table creation.
     *
     * @param {string} dialect - Target SQL dialect:
     *                           "mysql", "postgresql", "sqlite", or "sqlserver".
     * @param {boolean} createIndex - Whether to generate index statements.
     *                                If false, an empty array is returned.
     * @returns {string[]} An array of SQL `CREATE INDEX` statements.
     */
    createIndexStatements(dialect = "mysql", createIndex = true) {

        const indexStatements = [];

        if (!createIndex || !this.indexes || !Array.isArray(this.indexes)) {
            return indexStatements;
        }

        const supportsIfNotExists =
            dialect === 'postgresql' ||
            dialect === 'sqlite';

        const indexIfNotExists = supportsIfNotExists ? 'IF NOT EXISTS ' : '';

        this.indexes.forEach(index => {

            if (!index || !index.columns || index.columns.length === 0) {
                return;
            }

            const indexName =
                index.name || `idx_${this.name}_${index.columns.join('_')}`;

            const unique = index.unique ? 'UNIQUE ' : '';
            const indexCols = index.columns.join(', ');

            indexStatements.push(
                `CREATE ${unique}INDEX ${indexIfNotExists}${indexName} ON ${this.name} (${indexCols});`
            );
        });

        return indexStatements;
    }


    /**
     * Creates a foreign key constraint for the entity.
     * @param {*} fk - An object representing the foreign key definition, containing:
     * {
     *   name: fkName,
     *   columnName: columnName,
     *   referencedTable: selectedReferencedTable,
     *   referencedColumn: selectedReferencedColumn,
     *   onUpdate: onUpdateAction,
     *   onDelete: onDeleteAction
     * }
     * @param {string} dialect - Target SQL dialect: "mysql", "postgresql", "sqlite", or "sqlserver".
     * @returns {string} The generated SQL statement for the foreign key constraint.
     */
    createForeignKey(fk, dialect = "mysql") {
        if (!fk || !fk.columnName || !fk.referencedTable || !fk.referencedColumn) {
            return '';
        }

        const constraintName = fk.name
            ? `CONSTRAINT ${fk.name} `
            : '';

        const normalizeAction = (action) => {
            if (!action) return '';
            const allowed = ["NO ACTION", "RESTRICT", "CASCADE", "SET NULL", "SET DEFAULT"];
            const upper = action.toUpperCase();
            return allowed.includes(upper) ? upper : '';
        };

        let onUpdate = '';
        let onDelete = '';

        if (dialect === "postgresql") {
            const updateAction = normalizeAction(fk.onUpdate);
            const deleteAction = normalizeAction(fk.onDelete);

            if (updateAction) onUpdate = ` ON UPDATE ${updateAction}`;
            if (deleteAction) onDelete = ` ON DELETE ${deleteAction}`;

            // PostgreSQL best practice (optional but recommended)
            const deferrable = fk.deferrable
                ? ` DEFERRABLE INITIALLY DEFERRED`
                : '';

            return `${constraintName}FOREIGN KEY (${fk.columnName}) REFERENCES ${fk.referencedTable}(${fk.referencedColumn})${onUpdate}${onDelete}${deferrable}`;
        }

        // SQLite handling
        if (dialect === "sqlite") {
            const allowed = ["CASCADE", "SET NULL", "SET DEFAULT", "NO ACTION"];
            if (fk.onUpdate && allowed.includes(fk.onUpdate.toUpperCase())) {
                onUpdate = ` ON UPDATE ${fk.onUpdate.toUpperCase()}`;
            }
            if (fk.onDelete && allowed.includes(fk.onDelete.toUpperCase())) {
                onDelete = ` ON DELETE ${fk.onDelete.toUpperCase()}`;
            }

            return `${constraintName}FOREIGN KEY (${fk.columnName}) REFERENCES ${fk.referencedTable}(${fk.referencedColumn})${onUpdate}${onDelete}`;
        }

        // Default (MySQL & SQL Server)
        if (fk.onUpdate) onUpdate = ` ON UPDATE ${fk.onUpdate.toUpperCase()}`;
        if (fk.onDelete) onDelete = ` ON DELETE ${fk.onDelete.toUpperCase()}`;

        return `${constraintName}FOREIGN KEY (${fk.columnName}) REFERENCES ${fk.referencedTable}(${fk.referencedColumn})${onUpdate}${onDelete}`;
    }

    /**
     * Generates inline index definition for CREATE TABLE statement.
     * 
     * Only MySQL supports inline INDEX inside CREATE TABLE.
     * Other dialects (PostgreSQL, SQLite, SQL Server) require
     * standalone CREATE INDEX statements.
     *
     * @param {Object} fk - Foreign key definition object.
     * @param {string} fk.name - Optional index or foreign key name.
     * @param {string} fk.columnName - Column name to be indexed.
     * @param {string} [dialect="mysql"] - SQL dialect (mysql, postgresql, sqlite, sqlserver).
     * @returns {string} Inline index SQL fragment or empty string if not supported.
     */
    createIndex(fk, dialect = "mysql") {
        if (!fk || !fk.columnName) return '';

        let indexName = fk.name;
        if (!indexName) {
            indexName = `idx_${this.name}_${fk.columnName}`;
        }
        else if(indexName.startsWith("fk_"))
        {
            indexName = 'idx_' + indexName.substring(3);
        }
        else if(fk.columnName)
        {
            indexName = 'idx_' + this.name + '_' + fk.columnName;
        }

        switch (dialect) {

            case "mysql":
                // MySQL mendukung inline INDEX
                return `INDEX ${indexName} (${fk.columnName})`;

            case "postgresql":
                // PostgreSQL TIDAK mendukung INDEX di dalam CREATE TABLE
                return '';

            case "sqlite":
                // SQLite juga tidak mendukung inline index
                return '';

            case "sqlserver":
                // SQL Server tidak mendukung inline index biasa
                return '';

            default:
                return '';
        }
    }

    /**
     * Generates standalone CREATE INDEX statement.
     * 
     * Used for dialects that do not support inline INDEX
     * inside CREATE TABLE (PostgreSQL, SQLite, SQL Server).
     * MySQL also supports standalone index creation.
     *
     * PostgreSQL and SQLite use IF NOT EXISTS to prevent errors
     * when the index already exists.
     *
     * @param {Object} fk - Foreign key definition object.
     * @param {string} fk.name - Optional index or foreign key name.
     * @param {string} fk.columnName - Column name to be indexed.
     * @param {string} [dialect="mysql"] - SQL dialect (mysql, postgresql, sqlite, sqlserver).
     * @returns {string} Complete CREATE INDEX SQL statement or empty string.
     */
    createIndexStandalone(fk, dialect = "mysql") {
        if (!fk || !fk.columnName) return '';

        let indexName = fk.name;
        if (!indexName) {
            indexName = `idx_${this.name}_${fk.columnName}`;
        }
        else if(indexName.startsWith("fk_"))
        {
            indexName = 'idx_' + indexName.substring(3);
        }
        else if(fk.columnName)
        {
            indexName = 'idx_' + this.name + '_' + fk.columnName;
        }

        switch (dialect) {

            case "mysql":
                return `CREATE INDEX ${indexName} ON ${this.name} (${fk.columnName});`;

            case "postgresql":
                return `CREATE INDEX IF NOT EXISTS ${indexName} ON ${this.name} (${fk.columnName});`;

            case "sqlite":
                return `CREATE INDEX IF NOT EXISTS ${indexName} ON ${this.name} (${fk.columnName});`;

            case "sqlserver":
                return `CREATE INDEX ${indexName} ON ${this.name} (${fk.columnName});`;

            default:
                return '';
        }
    }


    /**
     * Generates one or more SQL INSERT statements, each containing up to `maxRow` rows.
     *
     * This method splits the data into chunks of up to `maxRow` rows and generates
     * separate INSERT statements for each chunk. It also handles proper value formatting
     * based on the target SQL dialect and column definitions.
     *
     * @param {string} dialect - Target SQL dialect. Supported values: "mysql", "postgresql", "sqlite", "sqlserver".
     * @param {number} maxRow - Maximum number of rows per INSERT statement (default is 100).
     * @returns {string} The generated SQL INSERT statements as a single string.
     */
    toSQLInsert(dialect = "mysql", maxRow = 100) {
        if (!this.data || this.data.length === 0) return '';

        const columnNames = this.columns.map(col => col.name);
        const chunks = [];

        for (let i = 0; i < this.data.length; i += maxRow) {
            const slice = this.data.slice(i, i + maxRow);
            const valuesList = slice.map(row => {
                return '(' + columnNames.map(name => {
                    const column = this.columns.find(col => col.name === name);
                    const nullable = column ? column.nullable : false;
                    return this.formatValue(row[name], column, dialect, nullable);
                }).join(', ') + ')';
            });

            const insertStatement = `INSERT INTO ${this.name} (${columnNames.join(', ')}) VALUES\n${valuesList.join(',\n')};\r\n`;
            chunks.push(insertStatement);
        }

        return chunks.join('\n');
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
        // Fix: Here we need to access the column from `this.columns`
        // to get the correct `nullable` and `type` properties.
        const valuesPart = columnNames.map(name => {
            const column = this.columns.find(col => col.name === name);
            const nullable = column ? column.nullable : true;
            return this.formatValue(row[name], column, 'mysql', nullable); // Default dialect mysql
        }).join(', ');
        return `INSERT INTO ${this.name} (${columnsPart}) VALUES (${valuesPart});`;
    }

    /**
     * Formats a value for SQL insertion based on column type and SQL dialect.
     *
     * If the value is null-like (null, undefined, empty string, or "null" literal for non-text types):
     * - If `nullable` is true → returns `'null'`
     * - If `nullable` is false:
     *   - Returns column.default if available
     *   - Otherwise, returns type-specific default (0 for number, '' for text, etc.)
     *
     * @param {*} value - The value to format.
     * @param {Column} column - The Column object associated with the value.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", or "sqlserver".
     * @param {boolean} [nullable=true] - If false, null-like values will be replaced with defaults.
     * @returns {string} SQL-safe representation of the value.
     */
    formatValue(value, column, dialect = 'mysql', nullable = true) {
        const type = column.type.toLowerCase();
        const length = column.length;

        const isText = column.isTypeText(type);
        const isInteger = column.isTypeInteger(type);
        const isFloat = column.isTypeFloat(type);
        const isBoolean = column.isTypeBoolean(type, length);



        const isNullLike = (
            value === null ||
            value === undefined ||
            (typeof value === 'string' && value.trim() === '') ||
            (!isText && String(value).toLowerCase() === 'null')
        );

        if (isNullLike) {
            if (nullable) {
                return 'null';
            }

            // Use defaultValue if defined
            if (column.default !== undefined && column.default !== null) {
                return this.formatValue(column.default, column, dialect, true);
            }

            // Type-specific fallback
            if (isBoolean) {
                return this.formatBoolean(value, dialect, false, column.default); // default false
            } else if (isInteger || isFloat) {
                return '0';
            } else {
                return "''";
            }
        }

        // Format non-null value
        if (isBoolean) {
            return this.formatBoolean(value, dialect, nullable, column.default);
        } else if (isInteger || isFloat) {
            return value.toString();
        } else {
            return this.quoteString(value, dialect);
        }
    }


    /**
     * Converts a boolean-like value into dialect-specific representation.
     *
     * @param {*} value - Input value, possibly boolean, number, or string.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sqlserver".
     * @param {boolean} [nullable=true] - If false, null-like values will be replaced with default false.
     * @param {boolean|string|number|null} [defaultValue=null] - Optional default value to use when value is null-like.
     * @returns {string} The formatted boolean value: '1', '0', 'true', 'false', or 'null'.
     */
    formatBoolean(value, dialect = 'mysql', nullable = true, defaultValue = null) {
        const isNullLike =
            value === null ||
            value === undefined ||
            (typeof value === 'string' && value.trim() === '') ||
            (typeof value === 'string' && value.trim().toLowerCase() === 'null');

        // Handle null-like input
        if (isNullLike) {
            if (nullable) {
                return 'null';
            }

            // Use defaultValue if provided
            if (defaultValue !== null && defaultValue !== undefined) {
                return this.formatBoolean(defaultValue, dialect, false);
            }

            // Fallback default false
            switch (dialect.toLowerCase()) {
                case 'postgresql':
                    return 'false';
                case 'sqlite':
                case 'sqlserver':
                case 'mysql':
                default:
                    return '0';
            }
        }

        // Determine truthiness
        const val = String(value).toLowerCase().trim();
        const isTrue = val === 'true' || val === '1' || val === 'yes' || val === 'on';

        switch (dialect.toLowerCase()) {
            case 'postgresql':
                return isTrue ? 'true' : 'false';
            case 'sqlite':
            case 'sqlserver':
            case 'mysql':
            default:
                return isTrue ? '1' : '0';
        }
    }


    /**
     * Escapes and quotes a string value for SQL.
     *
     * @param {string} value - The string value to escape and quote.
     * @param {string} dialect - SQL dialect: "mysql", "postgresql", "sqlite", "sqlserver".
     * @returns {string} - Escaped and quoted string.
     */
    quoteString(value, dialect = 'mysql') {
        let str = String(value);

        // Escape single quotes
        str = str.replace(/'/g, "''");

        switch (dialect.toLowerCase()) {
            case 'mysql':
                // Escape backslashes for MySQL
                str = str.replace(/\\/g, '\\\\');
                break;

            case 'postgresql':
                // Escape backslashes and use E'' syntax for PostgreSQL
                str = str.replace(/\\/g, '\\\\');
                return `E'${str}'`;

            case 'sqlite':
                // Backslashes are literal in SQLite, no need to escape
                break;

            case 'sqlserver':
                // Backslashes are literal in SQL Server, no need to escape
                break;

            default:
                // Default to MySQL-style escaping
                str = str.replace(/\\/g, '\\\\');
                break;
        }

        return `'${str}'`;
    }

}
