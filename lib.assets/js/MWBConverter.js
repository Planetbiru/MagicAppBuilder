/**
 * Browser version of MWBConverter
 * Uses DOMParser and JSZip
 */
class MWBConverter {
    constructor() {
        this.zip = null;
        this.xmlDoc = null;
        this.sqliteBinary = null;
        this.sqliteDb = null;
        this.idMap = {};
    }

    /**
     * Load and parse the file
     * @param {File} file
     * @param {Object} options
     */
    async loadFile(file, options = { mwb: false }) {
        this.idMap = {}; // Reset map
        this.sqliteDb = null;
        let xmlContent = '';

        // 1. Try reading as ZIP (MWB file)
        if(options.mwb) {
            try {
                this.zip = await JSZip.loadAsync(file);
                
                const sqliteFile = this.zip.file("@db/data.db");
                if (sqliteFile) {
                    this.sqliteBinary = await sqliteFile.async("uint8array");
                }

                const docFile = this.zip.file("document.mwb.xml");
                if (docFile) {
                    xmlContent = await docFile.async("string");
                } else {
                    // If ZIP is valid but document.mwb.xml is missing, it might not be an MWB file
                    throw new Error("Valid ZIP file, but 'document.mwb.xml' not found inside.");
                }
            } catch (e) {
                // 2. If reading ZIP fails, try reading as plain Text (XML file)
                // JSZip will throw an error if the file is not a zip
                try {
                    xmlContent = await this.readFileAsText(file);
                    // Simple check if this is XML
                    if (!xmlContent.trim().startsWith('<')) {
                        throw new Error("Unrecognized file format. Please upload a valid .mwb or .xml file.");
                    }
                } catch (readErr) {
                    throw new Error("Failed to read file: " + readErr.message);
                }
            }
        }
        else {
            // Directly read as XML file
            xmlContent = await this.readFileAsText(file);
            // Simple check if this is XML
            if (!xmlContent.trim().startsWith('<')) {
                throw new Error("Unrecognized file format. Please upload a valid .mwb or .xml file.");
            }
        }
        // 3. Parse XML String to DOM
        const parser = new DOMParser();
        this.xmlDoc = parser.parseFromString(xmlContent, "text/xml");

        // Check for XML parsing errors
        const parseError = this.xmlDoc.querySelector("parsererror");
        if (parseError) {
            throw new Error("XML Parsing Error: " + parseError.textContent);
        }

        this.buildIdMap();
    }

    /**
     * Load and parse from binary data instead of File object
     * @param {Uint8Array|ArrayBuffer} binaryData
     * @param {Object} options
     */
    async loadBinary(binaryData, options = { mwb: false }) {
        this.idMap = {}; // Reset map
        this.sqliteDb = null;
        let xmlContent = '';

        // Normalize to Uint8Array
        let uint8;
        if (binaryData instanceof Uint8Array) {
            uint8 = binaryData;
        } else if (binaryData instanceof ArrayBuffer) {
            uint8 = new Uint8Array(binaryData);
        } else {
            throw new Error("Input must be Uint8Array or ArrayBuffer.");
        }

        if (options.mwb) {
            try {
                // 1. Try reading as ZIP (MWB)
                this.zip = await JSZip.loadAsync(uint8);

                const sqliteFile = this.zip.file("@db/data.db");
                if (sqliteFile) {
                    this.sqliteBinary = await sqliteFile.async("uint8array");
                }

                const docFile = this.zip.file("document.mwb.xml");
                if (docFile) {
                    xmlContent = await docFile.async("string");
                } else {
                    throw new Error("Valid ZIP file, but 'document.mwb.xml' not found inside.");
                }
            } catch (e) {
                // 2. If failed as ZIP, try interpreting directly as XML
                try {
                    const decoder = new TextDecoder("utf-8");
                    xmlContent = decoder.decode(uint8);

                    if (!xmlContent.trim().startsWith('<')) {
                        throw new Error("Unrecognized file format. Please upload valid MWB or XML data.");
                    }
                } catch (err) {
                    throw new Error("Failed to read binary data: " + err.message);
                }
            }
        } else {
            // Directly read as XML
            const decoder = new TextDecoder("utf-8");
            xmlContent = decoder.decode(uint8);

            if (!xmlContent.trim().startsWith('<')) {
                throw new Error("Unrecognized file format. Please upload valid XML data.");
            }
        }

        // 3. Parse XML String to DOM
        const parser = new DOMParser();
        this.xmlDoc = parser.parseFromString(xmlContent, "text/xml");



        // Check error parsing
        const parseError = this.xmlDoc.querySelector("parsererror");
        if (parseError) {
            throw new Error("XML Parsing Error: " + parseError.textContent);
        }

        this.buildIdMap();
    }

    /**
     * Load SQLite database from binary data
     */
    async loadSqlite() {
        try {
            if (typeof initSqlJs === 'undefined') {
                await this.loadScript('schema-editor/wasm/sql-wasm.js');
            }
            if (!MWBConverter.sqlEnginePromise) {
                MWBConverter.sqlEnginePromise = initSqlJs({
                    locateFile: file => `../lib.assets/wasm/sql-wasm.wasm`
                });
            }
            const SQL = await MWBConverter.sqlEnginePromise;
            this.sqliteDb = new SQL.Database(this.sqliteBinary);
        } catch (e) {
            console.error("Failed to load SQLite database:", e);
            MWBConverter.sqlEnginePromise = null;
        }
    }

    /**
     * Dynamically loads a script from a URL.
     * @param {string} url The URL of the script to load.
     * @returns {Promise} A promise that resolves when the script is loaded.
     */
    loadScript(url) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = url;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Read file content as text
     * @param {File} file
     * @returns {Promise<string>}
     */
    readFileAsText(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target.result);
            reader.onerror = (e) => reject(e.target.error);
            reader.readAsText(file);
        });
    }

    /**
     * Build a map of ID -> Table/Column Name for Foreign Key references
     */
    buildIdMap() {
        // Use querySelectorAll to find value elements with specific struct-name
        const tables = this.xmlDoc.querySelectorAll('value[struct-name="db.mysql.Table"]');

        tables.forEach(table => {
            const tableId = table.getAttribute('id');
            const tableName = this.getValue(table, 'name');

            this.idMap[tableId] = {
                type: 'table',
                name: tableName
            };

            const columnsNode = this.getNode(table, 'columns');
            if (columnsNode) {
                // Iterate children of columns node
                for (const col of columnsNode.children) {
                    const colId = col.getAttribute('id');
                    const colName = this.getValue(col, 'name');
                    this.idMap[colId] = {
                        type: 'column',
                        name: colName,
                        table: tableName
                    };
                }
            }
        });
    }

    /**
     * Get entities (tables) with columns and data
     * @returns {Array}
     */
    getEntities() {
        const entities = [];
        if (!this.xmlDoc) {
            console.error("XML Document not loaded. Make sure loadFile/loadBinary is called.");
            return [];
        }
        const tables = this.xmlDoc.querySelectorAll('value[struct-name="db.mysql.Table"]');
        const now = Date.now();

        tables.forEach((table, index) => {
            const tableName = this.getValue(table, 'name');
            if (!tableName) return;

            const tableComment = this.getValue(table, 'comment') || "";

            // Columns
            const columns = [];
            const columnsNode = this.getNode(table, 'columns');

            // Primary Key ID
            const pkIndexIdNode = this.getNode(table, 'primaryKey');
            const pkIndexId = pkIndexIdNode ? pkIndexIdNode.textContent : null;
            let pkColumnIds = [];

            // Find PK columns via indices
            if (pkIndexId) {
                const indicesNode = this.getNode(table, 'indices');
                if (indicesNode) {
                    for (const indexNode of indicesNode.children) {
                        if (indexNode.getAttribute('id') === pkIndexId) {
                            const idxColsNode = this.getNode(indexNode, 'columns');
                            if (idxColsNode) {
                                for (const idxCol of idxColsNode.children) {
                                    const refColNode = this.getNode(idxCol, 'referencedColumn');
                                    if (refColNode) {
                                        pkColumnIds.push(refColNode.textContent);
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }

            if (columnsNode) {
                for (const col of columnsNode.children) {
                    const colId = col.getAttribute('id');
                    const name = this.getValue(col, 'name');

                    // Type
                    const typeLinkNode = this.getNode(col, 'simpleType');
                    const typeLink = typeLinkNode ? typeLinkNode.textContent : '';
                    const parts = typeLink.split('.');
                    const rawType = parts[parts.length - 1];
                    let type = rawType ? rawType.toUpperCase() : 'VARCHAR';
                    type = this.fixType(type);

                    const lengthVal = this.getValue(col, 'length');
                    let lengthStr = "";
                    if (lengthVal && parseInt(lengthVal) > -1) {
                        lengthStr = lengthVal;
                    }

                    const isNotNull = this.getValue(col, 'isNotNull') == 1;
                    const autoIncrement = this.getValue(col, 'autoIncrement') == 1;

                    let defaultValue = this.getValue(col, 'defaultValue');
                    const defaultIsNull = this.getValue(col, 'defaultValueIsNull') == 1;
                    
                    if (defaultIsNull) {
                        defaultValue = "NULL";
                    } else if (defaultValue === null || defaultValue === '') {
                        defaultValue = null;
                    }

                    const comment = this.getValue(col, 'comment');

                    columns.push({
                        name: name,
                        type: type,
                        length: lengthStr,
                        nullable: !isNotNull,
                        default: defaultValue,
                        primaryKey: pkColumnIds.includes(colId),
                        autoIncrement: autoIncrement,
                        values: null,
                        description: comment || null
                    });
                }
            }

            // Data will be loaded separately
            const data = [];

            // Foreign Keys
            const foreignKeys = [];
            const fksNode = this.getNode(table, 'foreignKeys');
            if (fksNode) {
                for (const fk of fksNode.children) {
                    const deleteRule = this.getValue(fk, 'deleteRule');
                    const updateRule = this.getValue(fk, 'updateRule');
                    
                    const refTableNode = this.getNode(fk, 'referencedTable');
                    const refTableId = refTableNode ? refTableNode.textContent : null;
                    const refTableName = (refTableId && this.idMap[refTableId]) ? this.idMap[refTableId].name : null;

                    // Local columns
                    const localCols = [];
                    const fkColsNode = this.getNode(fk, 'columns');
                    if (fkColsNode) {
                        for (const link of fkColsNode.children) {
                            const colId = link.textContent;
                            if (this.idMap[colId]) localCols.push(this.idMap[colId].name);
                        }
                    }

                    let fkName = this.getValue(fk, 'name');
                    if (!fkName) {
                        if (localCols.length > 0) {
                            fkName = `fk_${tableName}_${localCols[0]}`;
                        } else if (refTableName) {
                            fkName = `fk_${tableName}_${refTableName}`;
                        }
                    }

                    // Referenced columns
                    const refCols = [];
                    const refColsNode = this.getNode(fk, 'referencedColumns');
                    if (refColsNode) {
                        for (const link of refColsNode.children) {
                            const colId = link.textContent;
                            if (this.idMap[colId]) refCols.push(this.idMap[colId].name);
                        }
                    }

                    if (localCols.length > 0) {
                        foreignKeys.push({
                            name: fkName,
                            columnName: localCols.join(','),
                            referencedTable: refTableName,
                            referencedColumn: refCols.join(','),
                            onUpdate: updateRule,
                            onDelete: deleteRule
                        });
                    }
                }
            }
            if(tableName != '' && columns.length > 0)
            {
                entities.push({
                    index: index,
                    name: tableName,
                    columns: columns,
                    data: data,
                    description: tableComment,
                    creationDate: now,
                    modificationDate: now,
                    creator: "{{userName}}",
                    modifier: "{{userName}}",
                    foreignKeys: foreignKeys
                });
            }
        });

        return entities;
    }

    /**
     * Get data for all entities
     * @returns {Promise<Object>} Object where keys are table names and values are arrays of row objects
     */
    async getEntityData() {
        await this.loadSqlite();
        const entityData = {};

        if (!this.xmlDoc) {
            return entityData;
        }

        const tables = this.xmlDoc.querySelectorAll('value[struct-name="db.mysql.Table"]');

        tables.forEach(table => {
            const tableName = this.getValue(table, 'name');
            if (!tableName) return;

            entityData[tableName] = [];
            let tableDataFound = false;

            // 1. Try SQLite
            if (this.sqliteDb) {
                const columnsNode = this.getNode(table, 'columns');
                const tableId = table.getAttribute('id');
                const cleanId = tableId.replace(/[{}]/g, '');
                let actualSqliteTableName = null;

                const checkTable = (t) => {
                    try {
                        const r = this.sqliteDb.exec(`SELECT name FROM sqlite_master WHERE type='table' AND name='${t}'`);
                        return r.length > 0;
                    } catch (e) { return false; }
                };

                if (checkTable(tableId)) actualSqliteTableName = tableId;
                else if (checkTable(cleanId)) actualSqliteTableName = cleanId;
                else if (checkTable(tableName)) actualSqliteTableName = tableName;

                if (actualSqliteTableName) {
                    try {
                        const res = this.sqliteDb.exec(`SELECT * FROM "${actualSqliteTableName}"`);
                        if (res.length > 0 && res[0].values.length > 0) {
                            const sqliteColumns = res[0].columns;
                            const rows = res[0].values;

                            // Map SQLite columns to logical names
                            const colIndexMap = {};
                            sqliteColumns.forEach((col, idx) => {
                                const cleanCol = col.replace(/[{}]/g, '');
                                colIndexMap[cleanCol] = idx;
                            });

                            // Map logical column names to indices
                            const logicalColMap = {}; // name -> index
                            if (columnsNode) {
                                for (const col of columnsNode.children) {
                                    const colId = col.getAttribute('id');
                                    const colName = this.getValue(col, 'name');
                                    const cleanColId = colId.replace(/[{}]/g, '');

                                    if (colIndexMap.hasOwnProperty(cleanColId)) {
                                        logicalColMap[colName] = colIndexMap[cleanColId];
                                    }
                                }
                            }

                            rows.forEach(row => {
                                const rowObj = {};
                                for (const [colName, idx] of Object.entries(logicalColMap)) {
                                    rowObj[colName] = row[idx];
                                }
                                entityData[tableName].push(rowObj);
                            });
                            tableDataFound = true;
                        }
                    } catch (e) {
                        console.error("Error reading SQLite data for entity " + tableName, e);
                    }
                }
            }

            // 2. Fallback to XML inserts
            if (!tableDataFound) {
                const insertsNode = this.getNode(table, 'inserts');
                if (insertsNode) {
                    for (const insert of insertsNode.children) {
                        const insertCols = [];
                        const colsNode = this.getNode(insert, 'columns');
                        if (colsNode) {
                            for (const colName of colsNode.children) {
                                insertCols.push(colName.textContent);
                            }
                        }

                        const rowsNode = this.getNode(insert, 'rows');
                        if (rowsNode && insertCols.length > 0) {
                            for (const row of rowsNode.children) {
                                const rowObj = {};
                                const valsNode = this.getNode(row, 'values');
                                if (valsNode) {
                                    let valIdx = 0;
                                    for (const val of valsNode.children) {
                                        if (valIdx < insertCols.length) {
                                            let valStr = val.textContent;
                                            if (valStr === 'NULL') valStr = null;
                                            rowObj[insertCols[valIdx]] = valStr;
                                        }
                                        valIdx++;
                                    }
                                }
                                entityData[tableName].push(rowObj);
                            }
                        }
                    }
                }
            }
        });

        return entityData;
    }

    /**
     * Generate SQL
     */
    getSql() {
        let sql = "-- Generated by MagicAppBuilder\n";
        sql += "-- Date: " + new Date().toISOString().slice(0, 19).replace('T', ' ') + "\n\n";
        sql += "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        const tables = this.xmlDoc.querySelectorAll('value[struct-name="db.mysql.Table"]');

        tables.forEach(table => {
            const tableName = this.getValue(table, 'name');
            if (!tableName) return;

            const engine = this.getValue(table, 'tableEngine') || 'InnoDB';
            const charset = this.getValue(table, 'defaultCharacterSetName') || 'utf8mb4';
            const collation = this.getValue(table, 'defaultCollationName');

            sql += `CREATE TABLE IF NOT EXISTS \`${tableName}\` (\n`;

            const lines = [];

            // 1. Columns
            const columnsNode = this.getNode(table, 'columns');
            if (columnsNode) {
                for (const col of columnsNode.children) {
                    lines.push(this.parseColumn(col));
                }
            }

            // 2. Primary Key
            const pkIndexIdNode = this.getNode(table, 'primaryKey');
            // getNode returns the element, we need its textContent (UUID)
            const pkIndexId = pkIndexIdNode ? pkIndexIdNode.textContent : null;

            const indicesNode = this.getNode(table, 'indices');
            if (indicesNode) {
                for (const index of indicesNode.children) {
                    const indexId = index.getAttribute('id');
                    const isPk = (indexId === pkIndexId);

                    const idxCols = [];
                    const idxColsNode = this.getNode(index, 'columns');
                    if (idxColsNode) {
                        for (const idxCol of idxColsNode.children) {
                            const refColNode = this.getNode(idxCol, 'referencedColumn');
                            const refColId = refColNode ? refColNode.textContent : null;

                            if (this.idMap[refColId]) {
                                let colName = this.idMap[refColId].name;
                                // Check index column length (e.g. VARCHAR(191))
                                const colLen = this.getValue(idxCol, 'columnLength');
                                if (colLen && parseInt(colLen) > 0) {
                                    colName += `(${colLen})`;
                                }
                                idxCols.push(`\`${colName}\``);
                            }
                        }
                    }

                    if (idxCols.length === 0) continue;

                    if (isPk) {
                        lines.push(`  PRIMARY KEY (${idxCols.join(', ')})`);
                    } else {
                        const indexName = this.getValue(index, 'name');
                        const isUnique = this.getValue(index, 'unique') == 1;
                        const type = isUnique ? "UNIQUE INDEX" : "INDEX";
                        lines.push(`  ${type} \`${indexName}\` (${idxCols.join(', ')})`);
                    }
                }
            }

            // 3. Foreign Keys
            const fksNode = this.getNode(table, 'foreignKeys');
            if (fksNode) {
                for (const fk of fksNode.children) {
                    const refTableNode = this.getNode(fk, 'referencedTable');
                    const refTableId = refTableNode ? refTableNode.textContent : null;

                    if (!this.idMap[refTableId]) continue;
                    const refTableName = this.idMap[refTableId].name;

                    // Local columns
                    const localCols = [];
                    const localColNames = [];
                    const fkColsNode = this.getNode(fk, 'columns');
                    if (fkColsNode) {
                        for (const link of fkColsNode.children) {
                            const colId = link.textContent;
                            if (this.idMap[colId]) {
                                localCols.push(`\`${this.idMap[colId].name}\``);
                                localColNames.push(this.idMap[colId].name);
                            }
                        }
                    }

                    const fkName = this.getValue(fk, 'name') || (localColNames.length > 0 ? `fk_${tableName}_${localColNames[0]}` : `fk_${tableName}_${refTableName}`);

                    // Referenced columns
                    const refCols = [];
                    const refColsNode = this.getNode(fk, 'referencedColumns');
                    if (refColsNode) {
                        for (const link of refColsNode.children) {
                            const colId = link.textContent;
                            if (this.idMap[colId]) refCols.push(`\`${this.idMap[colId].name}\``);
                        }
                    }

                    if (localCols.length > 0 && refCols.length > 0) {
                        const deleteRule = this.getValue(fk, 'deleteRule');
                        const updateRule = this.getValue(fk, 'updateRule');

                        let line = `  CONSTRAINT \`${fkName}\` FOREIGN KEY (${localCols.join(', ')})`;
                        line += ` REFERENCES \`${refTableName}\` (${refCols.join(', ')})`;

                        if (deleteRule && deleteRule !== 'NO ACTION') line += ` ON DELETE ${deleteRule}`;
                        if (updateRule && updateRule !== 'NO ACTION') line += ` ON UPDATE ${updateRule}`;

                        lines.push(line);
                    }
                }
            }

            sql += lines.join(",\n");
            sql += `\n) ENGINE=${engine} DEFAULT CHARSET=${charset}`;
            if (collation) {
                sql += ` COLLATE=${collation}`;
            }
            sql += ";\n\n";
        });

        return sql;
    }

    /**
     * Generate SQL Insert
     */
    async getSqlInsert() {
        await this.loadSqlite();
        let sql = "-- Generated by MagicAppBuilder\n";
        sql += "-- Date: " + new Date().toISOString().slice(0, 19).replace('T', ' ') + "\n\n";
        sql += "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        const tables = this.xmlDoc.querySelectorAll('value[struct-name="db.mysql.Table"]');

        tables.forEach(table => {
            const tableName = this.getValue(table, 'name');
            if (!tableName) return;

            // 4. Data Inserts (Initial Data)
            if (this.sqliteDb) {
                const sqliteTableName = table.getAttribute('id');

                try {
                    // Check if table exists in SQLite
                    const res = this.sqliteDb.exec(`SELECT name FROM sqlite_master WHERE type='table' AND name='${sqliteTableName}'`);
                    if (res.length > 0) {
                        const result = this.sqliteDb.exec(`SELECT * FROM "${sqliteTableName}"`);
                        if (result.length > 0 && result[0].values.length > 0) {
                            const sqliteColumns = result[0].columns;
                            const rows = result[0].values;

                            // Map SQLite column name (UUID) to index
                            const colIndexMap = {};
                            sqliteColumns.forEach((col, index) => {
                                // Normalize: remove braces if present
                                const cleanCol = col.replace(/[{}]/g, '');
                                colIndexMap[cleanCol] = index;
                            });

                            const targetCols = [];
                            const targetIndices = [];

                            // Use XML definition to determine columns to export
                            const xmlColsNode = this.getNode(table, 'columns');
                            if (xmlColsNode) {
                                for (const col of xmlColsNode.children) {
                                    const colId = col.getAttribute('id'); // {UUID}
                                    const colName = this.getValue(col, 'name');
                                    const cleanId = colId.replace(/[{}]/g, '');

                                    if (colIndexMap.hasOwnProperty(cleanId)) {
                                        targetCols.push(`\`${colName}\``);
                                        targetIndices.push(colIndexMap[cleanId]);
                                    }
                                }
                            }

                            if (targetCols.length > 0) {
                                const colString = targetCols.join(', ');

                                rows.forEach(row => {
                                    const vals = targetIndices.map(idx => {
                                        const v = row[idx];
                                        if (v === null) return 'NULL';
                                        if (typeof v === 'number') return v;
                                        return `'${String(v).replace(/'/g, "\\'")}'`;
                                    });
                                    sql += `INSERT INTO \`${tableName}\` (${colString}) VALUES (${vals.join(', ')});\n`;
                                });
                                sql += "\n";
                            }
                        }
                    }
                } catch (e) {
                    console.error(`Error extracting data for table ${tableName}:`, e);
                }
            }
        });

        sql += "SET FOREIGN_KEY_CHECKS = 1;\n\n";
        return sql;
    }

    /**
     * Get diagrams and their tables
     * @returns {Array}
     */
    getDiagrams() {
        const diagrams = [];
        if (!this.xmlDoc) {
            console.error("XML Document not loaded. Make sure loadFile/loadBinary is called.");
            return [];
        }

        const diagramNodes = this.xmlDoc.querySelectorAll('value[struct-name="workbench.physical.Diagram"]');

        diagramNodes.forEach((diagramNode, index) => {
            const name = this.getValue(diagramNode, 'name');
            const tableNames = [];

            const figuresNode = this.getNode(diagramNode, 'figures');
            if (figuresNode) {
                for (const figure of figuresNode.children) {
                    if (figure.getAttribute('struct-name') === 'workbench.physical.TableFigure') {
                        const tableLink = this.getNode(figure, 'table');
                        if (tableLink) {
                            const tableId = tableLink.textContent;
                            if (this.idMap[tableId]) {
                                tableNames.push(this.idMap[tableId].name);
                            }
                        }
                    }
                }
            }
            let id = diagramNode.getAttribute('id').replace(/[{}]/g, '');
            id = id.split('#').join('');
            id = 'diagram_' + id.split('-').join('');
            diagrams.push({
                id: id,
                name: name,
                entities: tableNames,
                sortOrder: index
            });
        });

        return diagrams;
    }

    /**
     * Parse column node to SQL definition
     * @param {Element} col
     * @returns {string}
     */
    parseColumn(col) {
        const name = this.getValue(col, 'name');

        // Determine Type
        const typeLinkNode = this.getNode(col, 'simpleType');
        const typeLink = typeLinkNode ? typeLinkNode.textContent : '';
        // Example: com.mysql.rdbms.mysql.datatype.varchar
        const parts = typeLink.split('.');
        const rawType = parts[parts.length - 1];
        let type = rawType.toUpperCase();

        // Adjust Type with length/precision
        const length = parseInt(this.getValue(col, 'length'));
        const precision = parseInt(this.getValue(col, 'precision'));
        const scale = parseInt(this.getValue(col, 'scale'));

        if (['VARCHAR', 'CHAR', 'BINARY', 'VARBINARY', 'BIT'].includes(type)) {
            if (length > -1) type += `(${length})`;
        } else if (['DECIMAL', 'FLOAT', 'DOUBLE', 'REAL'].includes(type)) {
            if (precision > -1 && scale > -1) {
                type += `(${precision}, ${scale})`;
            } else if (precision > -1) {
                type += `(${precision})`;
            }
        } else if (['TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT'].includes(type)) {
            if (length > -1) type += `(${length})`;
        } else if (type === 'TIMESTAMP_F') {
            type = 'TIMESTAMP';
        } else if (type === 'DATETIME_F') {
            type = 'DATETIME';
        } else if (type === 'TIME_F') {
            type = 'TIME';
        }

        let line = `  \`${name}\` ${type}`;

        // Flags (UNSIGNED, ZEROFILL)
        const flagsNode = this.getNode(col, 'flags');
        if (flagsNode) {
            for (const flag of flagsNode.children) {
                if (flag.textContent === 'UNSIGNED') line += " UNSIGNED";
                if (flag.textContent === 'ZEROFILL') line += " ZEROFILL";
            }
        }

        // Nullable
        const isNotNull = this.getValue(col, 'isNotNull');
        line += (isNotNull == 1) ? " NOT NULL" : " NULL";

        // Auto Increment
        if (this.getValue(col, 'autoIncrement') == 1) {
            line += " AUTO_INCREMENT";
        }

        // Default Value
        const defaultValue = this.getValue(col, 'defaultValue');
        const defaultIsNull = this.getValue(col, 'defaultValueIsNull');

        if (defaultIsNull == 1) {
            line += " DEFAULT NULL";
        } else if (defaultValue !== '' && defaultValue !== null) {
            line += ` DEFAULT ${defaultValue}`;
        }

        // Comment
        const comment = this.getValue(col, 'comment');
        if (comment) {
            line += ` COMMENT '${comment.replace(/'/g, "\\'")}'`;
        }

        return line;
    }

    fixType(type) {
        switch (type) {
            case "TIMESTAMP_F":
                type = "TIMESTAMP";
                break;
            case "DATETIME_F":
                type = "DATETIME";
                break;
            case "TIME_F":
                type = "TIME";
                break;
        }
        return type;
    }


    /**
     * Helper: Get text content from child node based on key attribute
     * @param {Element} node The parent node.
     * @param {string} key The key attribute value to search for.
     * @returns {string|null} The text content of the child node, or null if not found.
     */
    getValue(node, key) {
        const child = this.getNode(node, key);
        return child ? child.textContent : null;
    }

    /**
     * Helper: Get child node based on key attribute
     * @param {Element} node The parent node.
     * @param {string} key The key attribute value to search for.
     * @returns {Element|null} The child element, or null if not found.
     */
    getNode(node, key) {
        if (!node) return null;
        for (let i = 0; i < node.children.length; i++) {
            if (node.children[i].getAttribute('key') === key) {
                return node.children[i];
            }
        }
        return null;
    }


    /**
     * Convert JSON model to MWB (ZIP)
     * @param {Object} jsonModel
     * @returns {Promise<JSZip>}
     */
    async convertJsonToMwbOld(jsonModel) {
        const zip = new JSZip();
        const tables = jsonModel.entities;
        
        const docId = this._generateUUID();
        const logModelId = this._generateUUID();
        const physModelId = this._generateUUID();
        const catalogId = this._generateUUID();
        const schemaId = this._generateUUID();

        const tableMap = {};
        const allFKs = []; // Store all FKs for connection generation
        tables.forEach(table => {
            const colsWithIds = table.columns.map(col => ({ ...col, id: this._generateUUID() }));
            const colMap = {};
            colsWithIds.forEach(c => {
                if (c.name) colMap[c.name.trim()] = c;
            });

            let foreignKeys = table.foreignKeys || {};
            

            tableMap[table.name.trim()] = {
                id: this._generateUUID(),
                figureId: null,
                name: table.name,
                columns: colsWithIds,
                colMap: colMap,
                data: table.data,
                foreignKeys: foreignKeys
            };
        });

        let xml = '<?xml version="1.0"?>\n';
        xml += '<data grt_format="2.0" document_type="MySQL Workbench Model" version="1.4.4">\n';
        xml += `  <value type="object" struct-name="workbench.Document" id="${docId}" struct-checksum="0x0">\n`;
        
        xml += `    <value type="object" struct-name="workbench.logical.Model" id="${logModelId}" key="logicalModel">\n`;
        xml += `      <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="workbench.logical.Diagram" key="diagrams"/>\n`;
        xml += '      <value type="dict" key="customData"/>\n';
        xml += `      <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="model.Marker" key="markers"/>\n`;
        xml += '      <value type="dict" key="options"/>\n';
        xml += '      <value type="string" key="name"></value>\n';
        xml += `      <link type="object" struct-name="GrtObject" key="owner">${docId}</link>\n`;
        xml += '    </value>\n';

        xml += `    <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="workbench.OverviewPanel" key="overviewPanels"/>\n`;
        xml += `    <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="workbench.physical.Model" key="physicalModels">\n`;
        xml += `      <value type="object" struct-name="workbench.physical.Model" id="${physModelId}">\n`;
        xml += `        <value type="object" struct-name="db.mysql.Catalog" id="${catalogId}" key="catalog">\n`;
        xml += `          <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.LogFileGroup" key="logFileGroups"/>\n`;
        xml += `          <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Schema" key="schemata">\n`;
        xml += `            <value type="object" struct-name="db.mysql.Schema" id="${schemaId}">\n`;
        xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.RoutineGroup" key="routineGroups"/>\n`;
        xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Routine" key="routines"/>\n`;
        xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Sequence" key="sequences"/>\n`;
        xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.StructuredDatatype" key="structuredTypes"/>\n`;
        xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Synonym" key="synonyms"/>\n`;
        xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Table" key="tables">\n`;

        for (const tableName in tableMap) {
            const tableInfo = tableMap[tableName];
            xml += `                <value type="object" struct-name="db.mysql.Table" id="${tableInfo.id}">\n`;
            xml += '                  <value type="string" key="avgRowLength"></value>\n';
            xml += '                  <value type="int" key="checksum">0</value>\n';
            xml += `                  <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Column" key="columns">\n`;
            tableInfo.columns.forEach(col => {
                xml += `                    <value type="object" struct-name="db.mysql.Column" id="${col.id}">\n`;
                xml += `                      <value type="int" key="autoIncrement">${col.autoIncrement ? 1 : 0}</value>\n`;
                xml += '                      <value type="string" key="characterSetName"></value>\n';
                xml += `                      <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.CheckConstraint" key="checks"/>\n`;
                xml += '                      <value type="string" key="collationName"></value>\n';
                xml += '                      <value type="string" key="comment"></value>\n';
                xml += '                      <value type="string" key="datatypeExplicitParams"></value>\n';
                
                let simpleType = "com.mysql.rdbms.mysql.datatype.varchar";
                const t = (col.type || "").toLowerCase();
                if (t.includes('tinyint')) simpleType = "com.mysql.rdbms.mysql.datatype.tinyint";
                else if (t.includes('bigint')) simpleType = "com.mysql.rdbms.mysql.datatype.bigint";
                else if (t.includes('int')) simpleType = "com.mysql.rdbms.mysql.datatype.int";
                else if (t.includes('longtext')) simpleType = "com.mysql.rdbms.mysql.datatype.longtext";
                else if (t.includes('mediumtext')) simpleType = "com.mysql.rdbms.mysql.datatype.mediumtext";
                else if (t.includes('text')) simpleType = "com.mysql.rdbms.mysql.datatype.text";
                else if (t.includes('date')) simpleType = "com.mysql.rdbms.mysql.datatype.date";
                else if (t.includes('datetime')) simpleType = "com.mysql.rdbms.mysql.datatype.datetime";
                else if (t.includes('timestamp')) simpleType = "com.mysql.rdbms.mysql.datatype.timestamp";
                else if (t.includes('float')) simpleType = "com.mysql.rdbms.mysql.datatype.float";
                else if (t.includes('double')) simpleType = "com.mysql.rdbms.mysql.datatype.double";
                else if (t.includes('decimal')) simpleType = "com.mysql.rdbms.mysql.datatype.decimal";
                else if (t.includes('year')) simpleType = "com.mysql.rdbms.mysql.datatype.year";
                
                let defaultVal = "";
                let defaultIsNull = 0;
                if (col.default === null || col.default === "NULL") {
                    defaultVal = "NULL";
                    defaultIsNull = 1;
                } else if (col.default !== undefined && col.default !== null) {
                    defaultVal = col.default;
                }

                xml += `                      <value type="string" key="defaultValue">${defaultVal}</value>\n`;
                xml += `                      <value type="int" key="defaultValueIsNull">${defaultIsNull}</value>\n`;
                xml += '                      <value type="string" key="expression"></value>\n';
                xml += `                      <value _ptr_="${this._generatePtr()}" type="list" content-type="string" key="flags"/>\n`;
                xml += '                      <value type="int" key="generated">0</value>\n';
                xml += '                      <value type="string" key="generatedStorage"></value>\n';
                xml += `                      <value type="int" key="isNotNull">${col.nullable === false ? 1 : 0}</value>\n`;
                
                let len = -1;
                if (col.length && parseInt(col.length) > 0) len = parseInt(col.length);
                xml += `                      <value type="int" key="length">${len}</value>\n`;
                xml += `                      <value type="string" key="name">${col.name}</value>\n`;
                xml += `                      <value type="string" key="oldName">${col.name}</value>\n`;
                xml += `                      <link type="object" struct-name="GrtObject" key="owner">${tableInfo.id}</link>\n`;
                xml += '                      <value type="int" key="precision">-1</value>\n';
                xml += '                      <value type="int" key="scale">-1</value>\n';
                xml += `                      <link type="object" struct-name="db.SimpleDatatype" key="simpleType">${simpleType}</link>\n`;
                xml += '                    </value>\n';
            });
            xml += '                  </value>\n';
            xml += '                  <value type="string" key="comment"></value>\n';
            xml += '                  <value type="int" key="commentedOut">0</value>\n';
            xml += '                  <value type="string" key="connectionString"></value>\n';
            xml += '                  <value type="string" key="createDate">' + new Date().toISOString() + '</value>\n';
            xml += '                  <value type="dict" key="customData"/>\n';
            xml += '                  <value type="string" key="defaultCharacterSetName">utf8mb4</value>\n';
            xml += '                  <value type="string" key="defaultCollationName"></value>\n';
            xml += '                  <value type="int" key="delayKeyWrite">0</value>\n';
            xml += `                  <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.ForeignKey" key="foreignKeys">\n`;
            
            // Pre-process FKs to generate IDs and Indices
            const processedFKs = [];
            
            // Helper to find column ID
            const findColId = (map, colRef) => {
                let name = colRef;
                if (typeof colRef === 'object' && colRef !== null) {
                    name = colRef.name || colRef.fieldName || colRef.column || colRef.field;
                }
                if (!name) return null;
                
                name = String(name).trim();
                if (map[name]) return map[name].id;
                // Case insensitive fallback
                const lower = String(name).toLowerCase();
                for (const k in map) {
                    if (k.toLowerCase() === lower) return map[k].id;
                }
                return null;
            };

            if (tableInfo.foreignKeys) {
                let fkEntries = [];
                if (Array.isArray(tableInfo.foreignKeys)) {
                    fkEntries = tableInfo.foreignKeys.map(fk => ({ key: null, fk }));
                } else if (tableInfo.foreignKeys && typeof tableInfo.foreignKeys === 'object') {
                    fkEntries = Object.entries(tableInfo.foreignKeys).map(([k, v]) => ({ key: k.trim(), fk: v }));
                }

                fkEntries.forEach(({ key, fk }) => {
                    let refTable = tableMap[fk.referencedTable ? String(fk.referencedTable).trim() : ""];
                    if (!refTable && fk.referencedTable) {
                        // Case insensitive fallback for table name
                        const lowerRef = String(fk.referencedTable).trim().toLowerCase();
                        for (const tName in tableMap) {
                            if (tName.toLowerCase() === lowerRef) {
                                refTable = tableMap[tName];
                                break;
                            }
                        }
                    }
                    if (!refTable) return;
                    
                    // Resolve local columns
                    let cols = fk.columns || fk.column || fk.fields || fk.field || fk.columnName;
                    if (!cols) {
                        // Fallback 1: Use key if it matches a column in the local table
                        if (key && tableInfo.colMap[key]) {
                            cols = [key];
                        } 
                        // Fallback 2: Try finding a column named "{referencedTable}_id"
                        else {
                            const guess = `${refTable.name}_id`;
                            if (tableInfo.colMap[guess]) {
                                cols = [guess];
                            }
                        }
                    }
                    // Ensure cols is array
                    if (cols && !Array.isArray(cols)) {
                        if (typeof cols === 'string' && cols.includes(',')) {
                            cols = cols.split(',').map(c => c.trim());
                        } else {
                            cols = [cols];
                        }
                    }
                    if (!cols) cols = [];

                    // Resolve referenced columns
                    let refCols = fk.referencedColumns || fk.referencedColumn || fk.referencedFields || fk.referencedField || [];
                    if (!Array.isArray(refCols)) {
                        if (typeof refCols === 'string' && refCols.includes(',')) {
                            refCols = refCols.split(',').map(c => c.trim());
                        } else {
                            refCols = [refCols];
                        }
                    }

                    // Fallback to PK if no referenced columns specified
                    if (refCols.length === 0 || (refCols.length === 1 && !refCols[0])) {
                         const pkCols = refTable.columns.filter(c => c.primaryKey || c.pk);
                         refCols = pkCols.map(c => c.name);
                    }

                    processedFKs.push({
                        def: fk,
                        refTable: refTable,
                        id: this._generateUUID(),
                        indexId: this._generateUUID(),
                        name: fk.name || (key && !tableInfo.colMap[key] ? key : `fk_${tableInfo.name}_${refTable.name}`),
                        indexName: `fk_${tableInfo.name}_${refTable.name}_idx`,
                        cols: cols,
                        refCols: refCols
                    });

                    allFKs.push({
                        id: processedFKs[processedFKs.length - 1].id,
                        name: processedFKs[processedFKs.length - 1].name,
                        ownerName: tableInfo.name,
                        referencedName: refTable.name
                    });
                });
            }

            processedFKs.forEach(fk => {
                    const refTable = fk.refTable;
                    
                    xml += `                    <value type="object" struct-name="db.mysql.ForeignKey" id="${fk.id}">\n`;
                    xml += `                      <link type="object" struct-name="db.mysql.Table" key="referencedTable">${refTable.id}</link>\n`;
                    
                    xml += `                      <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.Column" key="columns">\n`;
                    fk.cols.forEach(c => {
                        const id = findColId(tableInfo.colMap, c);
                        if (id) {
                            xml += `                        <link type="object">${id}</link>\n`;
                        }
                    });
                    xml += `                      </value>\n`;

                    xml += '                      <value type="dict" key="customData"/>\n';
                    xml += '                      <value type="int" key="deferability">0</value>\n';
                    xml += `                      <value type="string" key="deleteRule">${fk.def.onDelete || "NO ACTION"}</value>\n`;
                    xml += `                      <link type="object" struct-name="db.Index" key="index">${fk.indexId}</link>\n`;
                    xml += '                      <value type="int" key="mandatory">1</value>\n';
                    xml += '                      <value type="int" key="many">1</value>\n';
                    xml += '                      <value type="int" key="modelOnly">0</value>\n';
                    xml += `                      <link type="object" struct-name="db.Table" key="owner">${tableInfo.id}</link>\n`;

                    xml += `                      <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.Column" key="referencedColumns">\n`;
                    fk.refCols.forEach(c => {
                        const id = findColId(refTable.colMap, c);
                        if (id) {
                            xml += `                        <link type="object">${id}</link>\n`;
                        }
                    });
                    xml += `                      </value>\n`;
                    xml += '                      <value type="int" key="referencedMandatory">1</value>\n';
                    xml += `                      <value type="string" key="updateRule">${fk.def.onUpdate || "NO ACTION"}</value>\n`;
                    xml += '                      <value type="string" key="comment"></value>\n';
                    xml += `                      <value type="string" key="name">${fk.name}</value>\n`;
                    xml += `                      <value type="string" key="oldName"></value>\n`;
                    xml += `                    </value>\n`;
            });
            xml += `                  </value>\n`;
            xml += `                  <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Index" key="indices">\n`;
            const pkCols = tableInfo.columns.filter(c => c.primaryKey);
            let indexId = null;
            if (pkCols.length > 0) {
                indexId = this._generateUUID();
                xml += `                    <value type="object" struct-name="db.mysql.Index" id="${indexId}">\n`;
                xml += '                      <value type="string" key="algorithm"></value>\n';
                xml += `                      <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.IndexColumn" key="columns">\n`;
                pkCols.forEach(pkCol => {
                    xml += `                        <value type="object" struct-name="db.mysql.IndexColumn" id="${this._generateUUID()}">\n`;
                    xml += '                          <value type="int" key="columnLength">0</value>\n';
                    xml += '                          <value type="string" key="comment"></value>\n';
                    xml += '                          <value type="int" key="descend">0</value>\n';
                    xml += '                          <value type="string" key="expression"></value>\n';
                        xml += '                          <value type="string" key="name"></value>\n';
                        xml += `                          <link type="object" struct-name="GrtObject" key="owner">${indexId}</link>\n`;
                    xml += `                          <link type="object" struct-name="db.Column" key="referencedColumn">${pkCol.id}</link>\n`;
                    xml += '                        </value>\n';
                });
                xml += '                      </value>\n';
                    xml += '                      <value type="string" key="comment"></value>\n';
                    xml += '                      <value type="int" key="commentedOut">0</value>\n';
                    xml += '                      <value type="string" key="createDate"></value>\n';
                    xml += '                      <value type="dict" key="customData"/>\n';
                    xml += '                      <value type="int" key="deferability">0</value>\n';
                xml += '                      <value type="string" key="indexKind"></value>\n';
                    xml += '                      <value type="string" key="indexType">PRIMARY</value>\n';
                    xml += '                      <value type="int" key="isPrimary">1</value>\n';
                xml += '                      <value type="int" key="keyBlockSize">0</value>\n';
                    xml += '                      <value type="string" key="lastChangeDate"></value>\n';
                xml += '                      <value type="string" key="lockOption"></value>\n';
                    xml += '                      <value type="int" key="modelOnly">0</value>\n';
                    xml += '                      <value type="string" key="name">PRIMARY</value>\n';
                    xml += '                      <value type="string" key="oldName">PRIMARY</value>\n';
                    xml += `                      <link type="object" struct-name="GrtNamedObject" key="owner">${tableInfo.id}</link>\n`;
                    xml += '                      <value type="string" key="temp_sql"></value>\n';
                    xml += '                      <value type="int" key="unique">0</value>\n';
                xml += '                      <value type="int" key="visible">1</value>\n';
                xml += '                      <value type="string" key="withParser"></value>\n';
                xml += '                    </value>\n';
            }
            
            // FK Indices
            processedFKs.forEach(fk => {
                    xml += `                    <value type="object" struct-name="db.mysql.Index" id="${fk.indexId}">\n`;
                    xml += '                      <value type="string" key="algorithm"></value>\n';
                    xml += `                      <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.IndexColumn" key="columns">\n`;
                    
                    fk.cols.forEach(c => {
                        const id = findColId(tableInfo.colMap, c);
                        if (id) {
                            const idxColId = this._generateUUID();
                            xml += `                        <value type="object" struct-name="db.mysql.IndexColumn" id="${idxColId}">\n`;
                            xml += '                          <value type="int" key="columnLength">0</value>\n';
                            xml += '                          <value type="string" key="comment"></value>\n';
                            xml += '                          <value type="int" key="descend">0</value>\n';
                            xml += '                          <value type="string" key="expression"></value>\n';
                            xml += '                          <value type="string" key="name"></value>\n';
                            xml += `                          <link type="object" struct-name="GrtObject" key="owner">${fk.indexId}</link>\n`;
                            xml += `                          <link type="object" struct-name="db.Column" key="referencedColumn">${id}</link>\n`;
                            xml += '                        </value>\n';
                        }
                    });

                    xml += '                      </value>\n';
                    xml += '                      <value type="string" key="indexKind"></value>\n';
                    xml += '                      <value type="int" key="keyBlockSize">0</value>\n';
                    xml += '                      <value type="string" key="lockOption"></value>\n';
                    xml += '                      <value type="int" key="visible">1</value>\n';
                    xml += '                      <value type="string" key="withParser"></value>\n';
                    xml += '                      <value type="string" key="comment"></value>\n';
                    xml += '                      <value type="int" key="deferability">0</value>\n';
                    xml += '                      <value type="string" key="indexType">INDEX</value>\n';
                    xml += '                      <value type="int" key="isPrimary">0</value>\n';
                    xml += `                      <value type="string" key="name">${fk.indexName}</value>\n`;
                    xml += '                      <value type="int" key="unique">0</value>\n';
                    xml += '                      <value type="int" key="commentedOut">0</value>\n';
                    xml += '                      <value type="string" key="createDate"></value>\n';
                    xml += '                      <value type="dict" key="customData"/>\n';
                    xml += '                      <value type="string" key="lastChangeDate"></value>\n';
                    xml += '                      <value type="int" key="modelOnly">0</value>\n';
                    xml += `                      <link type="object" struct-name="GrtNamedObject" key="owner">${tableInfo.id}</link>\n`;
                    xml += '                      <value type="string" key="temp_sql"></value>\n';
                    xml += `                      <value type="string" key="oldName"></value>\n`;
                    xml += '                    </value>\n';
            });
            xml += '                  </value>\n';
            xml += '                  <value type="string" key="keyBlockSize"></value>\n';
            xml += '                  <value type="string" key="maxRows"></value>\n';
            xml += '                  <value type="string" key="mergeInsert"></value>\n';
            xml += '                  <value type="string" key="mergeUnion"></value>\n';
            xml += '                  <value type="string" key="minRows"></value>\n';
            xml += '                  <value type="string" key="nextAutoInc"></value>\n';
            xml += '                  <value type="string" key="packKeys"></value>\n';
            xml += '                  <value type="int" key="partitionCount">0</value>\n';
            xml += `                  <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.PartitionDefinition" key="partitionDefinitions"/>\n`;
            xml += '                  <value type="string" key="partitionExpression"></value>\n';
            xml += '                  <value type="int" key="partitionKeyAlgorithm">0</value>\n';
            xml += '                  <value type="string" key="partitionType"></value>\n';
            xml += '                  <value type="string" key="password"></value>\n';
            if (indexId) {
                xml += `                  <link type="object" struct-name="db.mysql.Index" key="primaryKey">${indexId}</link>\n`;
            }
            xml += '                  <value type="string" key="raidChunkSize"></value>\n';
            xml += '                  <value type="string" key="raidChunks"></value>\n';
            xml += '                  <value type="string" key="raidType"></value>\n';
            xml += '                  <value type="string" key="rowFormat"></value>\n';
            xml += '                  <value type="string" key="statsAutoRecalc"></value>\n';
            xml += '                  <value type="string" key="statsPersistent"></value>\n';
            xml += '                  <value type="int" key="statsSamplePages">0</value>\n';
            xml += '                  <value type="int" key="subpartitionCount">0</value>\n';
            xml += '                  <value type="string" key="subpartitionExpression"></value>\n';
            xml += '                  <value type="int" key="subpartitionKeyAlgorithm">0</value>\n';
            xml += '                  <value type="string" key="subpartitionType"></value>\n';
            xml += '                  <value type="string" key="tableDataDir"></value>\n';
            xml += '                  <value type="string" key="tableEngine">InnoDB</value>\n';
            xml += '                  <value type="string" key="tableIndexDir"></value>\n';
            xml += '                  <value type="string" key="tableSpace"></value>\n';
            xml += `                  <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Trigger" key="triggers"/>\n`;
            xml += '                  <value type="int" key="isStub">0</value>\n';
            xml += '                  <value type="int" key="isSystem">0</value>\n';
            xml += '                  <value type="int" key="isTemporary">0</value>\n';
                xml += '                  <value type="string" key="keyBlockSize"></value>\n';
            xml += '                  <value type="string" key="lastChangeDate">' + new Date().toISOString() + '</value>\n';
                xml += '                  <value type="string" key="maxRows"></value>\n';
                xml += '                  <value type="string" key="mergeInsert"></value>\n';
                xml += '                  <value type="string" key="mergeUnion"></value>\n';
                xml += '                  <value type="string" key="minRows"></value>\n';
            xml += '                  <value type="int" key="modelOnly">0</value>\n';
            xml += `                  <value type="string" key="name">${tableInfo.name}</value>\n`;
                xml += '                  <value type="string" key="nextAutoInc"></value>\n';
                xml += `                  <value type="string" key="oldName">${tableInfo.name}</value>\n`;
            xml += `                  <link type="object" struct-name="GrtNamedObject" key="owner">${schemaId}</link>\n`;
                xml += '                  <value type="string" key="packKeys"></value>\n';
                xml += '                  <value type="int" key="partitionCount">0</value>\n';
                xml += `                  <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.PartitionDefinition" key="partitionDefinitions"/>\n`;
                xml += '                  <value type="string" key="partitionExpression"></value>\n';
                xml += '                  <value type="int" key="partitionKeyAlgorithm">0</value>\n';
                xml += '                  <value type="string" key="partitionType"></value>\n';
                xml += '                  <value type="string" key="password"></value>\n';
                if (indexId) {
                    xml += `                  <link type="object" struct-name="db.mysql.Index" key="primaryKey">${indexId}</link>\n`;
                }
                xml += '                  <value type="string" key="raidChunkSize"></value>\n';
                xml += '                  <value type="string" key="raidChunks"></value>\n';
                xml += '                  <value type="string" key="raidType"></value>\n';
                xml += '                  <value type="string" key="rowFormat"></value>\n';
                xml += '                  <value type="string" key="statsAutoRecalc"></value>\n';
                xml += '                  <value type="string" key="statsPersistent"></value>\n';
                xml += '                  <value type="int" key="statsSamplePages">0</value>\n';
                xml += '                  <value type="int" key="subpartitionCount">0</value>\n';
                xml += '                  <value type="string" key="subpartitionExpression"></value>\n';
                xml += '                  <value type="int" key="subpartitionKeyAlgorithm">0</value>\n';
                xml += '                  <value type="string" key="subpartitionType"></value>\n';
                xml += '                  <value type="string" key="tableDataDir"></value>\n';
                xml += '                  <value type="string" key="tableEngine">InnoDB</value>\n';
                xml += '                  <value type="string" key="tableIndexDir"></value>\n';
                xml += '                  <value type="string" key="tableSpace"></value>\n';
                xml += '                  <value type="string" key="temp_sql"></value>\n';
                xml += '                  <value type="string" key="temporaryScope"></value>\n';
                xml += `                  <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Trigger" key="triggers"/>\n`;
            xml += '                </value>\n';
        }
        xml += '              </value>\n';
            xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.View" key="views"/>\n`;
        xml += '              <value type="string" key="defaultCharacterSetName">utf8</value>\n';
        xml += '              <value type="string" key="defaultCollationName">utf8_general_ci</value>\n';
            xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.Event" key="events"/>\n`;
        xml += '              <value type="dict" key="customData"/>\n';
        xml += '              <value type="int" key="modelOnly">0</value>\n';
        xml += '              <value type="string" key="name">mydb</value>\n';
        xml += `              <link type="object" struct-name="GrtNamedObject" key="owner">${catalogId}</link>\n`;
        xml += '              <value type="string" key="oldName">mydb</value>\n';
        xml += '            </value>\n';
        xml += '          </value>\n';
        xml += `          <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.ServerLink" key="serverLinks"/>\n`;
        xml += `          <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mysql.Tablespace" key="tablespaces"/>\n`;
        xml += `          <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.CharacterSet" key="characterSets"/>\n`;
        xml += `          <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.UserDatatype" key="userDatatypes"/>\n`;
        xml += `          <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.User" key="users"/>\n`;
        xml += '          <value type="string" key="name">default</value>\n';
        xml += `          <link type="object" struct-name="GrtObject" key="owner">${physModelId}</link>\n`;
        xml += '        </value>\n';
        xml += '        <value type="string" key="connectionNotation">fromcolumn</value>\n';
        xml += `        <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.mgmt.Connection" key="connections"/>\n`;
        


        
        xml += `        <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="workbench.physical.Diagram" key="diagrams">\n`;

        let firstDiagramId = null;

        for (const diagDef of jsonModel.diagrams) {

            const diagramId = this._generateUUID();
            if (!firstDiagramId) firstDiagramId = diagramId;

            const rootLayerId = this._generateUUID();

            xml += `          <value type="object" struct-name="workbench.physical.Diagram" id="${diagramId}">\n`;
            xml += '            <value type="int" key="closed">0</value>\n';
            
            // Connections
            xml += `            <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="model.Connection" key="connections">\n`;
            const figuresMap = {}; // Map tableName -> figureId for this diagram
            
            // We need to generate figures first to get IDs, but XML structure requires connections first.
            // So we pre-generate figure IDs.
            for (const tableName of diagDef.entities) {
                figuresMap[tableName] = this._generateUUID();
            }

            allFKs.forEach(conn => {
                const startFig = figuresMap[conn.ownerName];
                const endFig = figuresMap[conn.referencedName];
                if (startFig && endFig) {
                    xml += `              <value type="object" struct-name="workbench.physical.Connection" id="${this._generateUUID()}">\n`;
                    xml += `                <value type="string" key="caption">${conn.name}</value>\n`;
                    xml += '                <value type="real" key="captionXOffs">0</value>\n';
                    xml += '                <value type="real" key="captionYOffs">0</value>\n';
                    xml += '                <value type="string" key="comment"></value>\n';
                    xml += '                <value type="real" key="endCaptionXOffs">0</value>\n';
                    xml += '                <value type="real" key="endCaptionYOffs">0</value>\n';
                    xml += '                <value type="string" key="extraCaption"></value>\n';
                    xml += '                <value type="real" key="extraCaptionXOffs">0</value>\n';
                    xml += '                <value type="real" key="extraCaptionYOffs">0</value>\n';
                    xml += `                <link type="object" struct-name="db.ForeignKey" key="foreignKey">${conn.id}</link>\n`;
                    xml += '                <value type="real" key="middleSegmentOffset">0</value>\n';
                    xml += '                <value type="real" key="startCaptionXOffs">0</value>\n';
                    xml += '                <value type="real" key="startCaptionYOffs">0</value>\n';
                    xml += '                <value type="int" key="drawSplit">0</value>\n';
                    xml += `                <link type="object" struct-name="model.Figure" key="endFigure">${endFig}</link>\n`;
                    xml += `                <link type="object" struct-name="model.Figure" key="startFigure">${startFig}</link>\n`;
                    xml += `                <link type="object" struct-name="model.Diagram" key="owner">${diagramId}</link>\n`;
                    xml += '                <value type="int" key="visible">1</value>\n';
                    xml += '                <value type="string" key="name"></value>\n';
                    xml += '              </value>\n';
                }
            });
            xml += `            </value>\n`;

            xml += '            <value type="string" key="description"></value>\n';
            xml += `            <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="model.Figure" key="figures">\n`;

            let x = 40, y = 40;

            for (const tableName of diagDef.entities) {

                const tableInfo = tableMap[tableName];
                if (!tableInfo) continue;

                const figureId = figuresMap[tableName]; // Use pre-generated ID

                const height = 80 + (tableInfo.columns.length * 20);

                xml += `              <value type="object" struct-name="workbench.physical.TableFigure" id="${figureId}">\n`;
                xml += '                <value type="int" key="columnsExpanded">1</value>\n';
                xml += '                <value type="int" key="foreignKeysExpanded">0</value>\n';
                xml += '                <value type="int" key="indicesExpanded">0</value>\n';
                xml += '                <value type="int" key="summarizeDisplay">-1</value>\n';
                xml += `                <link type="object" struct-name="db.Table" key="table">${tableInfo.id}</link>\n`;
                xml += '                <value type="int" key="triggersExpanded">0</value>\n';
                xml += '                <value type="string" key="color">#98BFDA</value>\n';
                xml += '                <value type="int" key="expanded">1</value>\n';
                xml += `                <value type="real" key="height">${height}</value>\n`;
                xml += `                <link type="object" struct-name="model.Layer" key="layer">${rootLayerId}</link>\n`;
                xml += `                <value type="real" key="left">${x}</value>\n`;
                xml += '                <value type="int" key="locked">0</value>\n';
                xml += '                <value type="int" key="manualSizing">0</value>\n';
                xml += `                <value type="real" key="top">${y}</value>\n`;
                xml += `                <value type="real" key="width">220</value>\n`;
                xml += `                <link type="object" struct-name="model.Diagram" key="owner">${diagramId}</link>\n`;
                xml += '                <value type="int" key="visible">1</value>\n';
                xml += `                <value type="string" key="name">${tableInfo.name}</value>\n`;
                xml += '              </value>\n';

                x += 260;
                if (x > 1600) { x = 40; y += 400; }
            }

            xml += '            </value>\n';
            xml += '            <value type="real" key="height">1380.95</value>\n';
            xml += `            <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="model.Layer" key="layers"/>\n`;
            xml += `            <value type="string" key="name">${diagDef.name}</value>\n`;
            xml += '            <value type="dict" key="options"/>\n';
            xml += `            <link type="object" struct-name="model.Model" key="owner">${physModelId}</link>\n`;
            xml += `            <value type="object" struct-name="workbench.physical.Layer" id="${rootLayerId}" key="rootLayer">\n`;
            xml += '              <value type="string" key="color"></value>\n';
            xml += '              <value type="string" key="description"></value>\n';
            xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="model.Figure" key="figures">\n`;

            for (const tableName of diagDef.entities) {

                const figureId = figuresMap[tableName];
                if (figureId) {
                    xml += `                <link type="object">${figureId}</link>\n`;
                }
            }

            xml += '              </value>\n';
            xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="model.Group" key="groups"/>\n`;
            xml += '              <value type="real" key="height">3000</value>\n';
            xml += '              <value type="real" key="left">0</value>\n';
            xml += `              <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="model.Layer" key="subLayers"/>\n`;
            xml += '              <value type="real" key="top">0</value>\n';
            xml += '              <value type="real" key="width">3000</value>\n';
            xml += `              <link type="object" struct-name="model.Diagram" key="owner">${diagramId}</link>\n`;
            xml += '              <value type="int" key="visible">1</value>\n';
            xml += '              <value type="string" key="name"></value>\n';
            xml += '            </value>\n';
            xml += `            <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="model.Object" key="selection"/>\n`;
            xml += '            <value type="int" key="updateBlocked">0</value>\n';
            xml += '            <value type="real" key="width">1972</value>\n';
            xml += '            <value type="real" key="x">0</value>\n';
            xml += '            <value type="real" key="y">0</value>\n';
            xml += '            <value type="real" key="zoom">1</value>\n';
            xml += '          </value>\n';
        }


        xml += '        </value>\n';

        
        xml += '        <value type="string" key="figureNotation">workbench/default</value>\n';
        xml += `        <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="GrtStoredNote" key="notes"/>\n`;
        xml += '        <link type="object" struct-name="db.mgmt.Rdbms" key="rdbms">com.mysql.rdbms.mysql</link>\n';
        xml += `        <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="db.Script" key="scripts"/>\n`;
        xml += '        <value type="dict" key="syncProfiles"/>\n';
        xml += `        <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="GrtObject" key="tagCategories"/>\n`;
        xml += `        <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="meta.Tag" key="tags"/>\n`;
        xml += `        <link type="object" struct-name="model.Diagram" key="currentDiagram">${firstDiagramId}</link>\n`;
        xml += '        <value type="dict" key="customData"/>\n';
        xml += `        <value _ptr_="${this._generatePtr()}" type="list" content-type="object" content-struct-name="model.Marker" key="markers"/>\n`;
        xml += '        <value type="dict" key="options"/>\n';
        xml += '        <value type="string" key="name"></value>\n';
        xml += `        <link type="object" struct-name="GrtObject" key="owner">${docId}</link>\n`;
        xml += '      </value>\n'; // End physicalModel
        xml += '    </value>\n'; // End physicalModels
        
        xml += '    <value type="dict" key="customData"/>\n';
        xml += `    <value type="object" struct-name="app.DocumentInfo" id="${this._generateUUID()}" key="info">\n`;
        xml += '      <value type="string" key="dateCreated">' + new Date().toISOString() + '</value>\n';
        xml += '      <value type="string" key="description"></value>\n';
        xml += '      <value type="string" key="version">1.0</value>\n';
        xml += '      <value type="string" key="name">Properties</value>\n';
        xml += `      <link type="object" struct-name="GrtObject" key="owner">${docId}</link>\n`;
        xml += '    </value>\n';
        
        xml += `    <value type="object" struct-name="app.PageSettings" id="${this._generateUUID()}" key="pageSettings">\n`;
        xml += '      <value type="string" key="name"></value>\n';
        xml += `      <link type="object" struct-name="GrtObject" key="owner">${docId}</link>\n`;
        xml += '    </value>\n';
        
        xml += '    <value type="string" key="name"></value>\n';
        xml += '  </value>\n'; // End Document
        xml += '</data>';

        zip.file("document.mwb.xml", xml);

        // 3. Generate SQLite
        let hasData = false;
        for (const tableName in tableMap) {
            if (tableMap[tableName].data && tableMap[tableName].data.length > 0) {
                hasData = true;
                break;
            }
        }

        if (typeof initSqlJs === 'undefined') {
            await this.loadScript('schema-editor/wasm/sql-wasm.js');
        }
        const SQL = await initSqlJs({ locateFile: file => `../lib.assets/wasm/sql-wasm.wasm` });
        const db = new SQL.Database();

        for (const tableName in tableMap) {
            const tableInfo = tableMap[tableName];
            if (tableInfo.data && tableInfo.data.length > 0) {
                // Create table with UUID name and UUID columns
                const colDefs = tableInfo.columns.map(c => `"${c.id}" TEXT`).join(", ");
                db.run(`CREATE TABLE "${tableInfo.id}" (${colDefs});`);

                // Insert data
                const insertQuery = `INSERT INTO "${tableInfo.id}" VALUES (${tableInfo.columns.map(() => '?').join(', ')})`;
                const stmt = db.prepare(insertQuery);
                tableInfo.data.forEach(row => {
                    const values = tableInfo.columns.map(c => {
                        const val = row[c.name];
                        return val === null || val === undefined ? null : String(val);
                    });
                    stmt.run(values);
                });
                stmt.free();
            }
        }

        const binary = db.export();
        zip.file("@db/data.db", binary);
        

        return zip;
    }

    /**
     * Helper: Generate Pointer
     */
    _generatePtr() {
        return '0000023C' + 'xxxxxxxx'.replace(/[x]/g, function(c) {
            return (Math.random() * 16 | 0).toString(16);
        }).toUpperCase();
    }

    /**
     * Helper: Generate UUID
     */
    _generateUUID() {
        const uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        }).toUpperCase();
        return `{${uuid}}`;
    }
}