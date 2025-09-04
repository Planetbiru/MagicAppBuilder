
let currentData = null; // To hold the currently selected data
let selectedRowIndex = null; // To keep track of the selected row index
let columnInfo = []; // To hold column information for the currently selected table
let db;
let currentSqliteTableName = null; // To hold the name of the currently selected table
let curretDatabaseName = null;
let exportType = '';
/**
 * Load an SQLite database file from a given URL and initialize the UI.
 *
 * Workflow:
 *  1. Read the database URL from the input field (#sqliteDatabaseUrl).
 *  2. Validate the URL; if empty, show an alert and stop execution.
 *  3. Fetch the database file from the server as an ArrayBuffer.
 *  4. Initialize SQL.js using the WebAssembly binary (sql-wasm.wasm).
 *  5. Load the database into memory (`db` instance).
 *  6. Query `sqlite_master` to get the list of tables.
 *  7. Populate the sidebar (#sqlite-table-list) with:
 *     - A structure link (`.sqlite-table-structure`) → shows table schema.
 *     - A content link (`.sqlite-table-content`) → shows table data.
 *  8. Enable the "Export Database to SQL" button.
 *
 * Event bindings:
 *  - Clicking on a table content link → calls `displayTableData(db, tableName)`.
 *  - Clicking on a table structure link → calls `displayTableStructure(db, tableName)`.
 *  - Both trigger `hilightTable(e)` to update UI state.
 *
 * Error handling:
 *  - Alerts the user if the URL is invalid or if the fetch/initialization fails.
 *
 * @function loadDatabaseFromUrl
 * @param sqliteDatabaseUrl SQLite file URL
 * @returns {void} This function does not return a value.
 *
 * @requires initSqlJs - SQL.js initializer function.
 * @requires displayTableData - Function to display data for a given table.
 * @requires displayTableStructure - Function to display schema for a given table.
 * @requires hilightTable - Function to highlight the selected table in the UI.
 */
function loadDatabaseFromUrl(sqliteDatabaseUrl)
{
  // Fetch the database from the server
  fetch(sqliteDatabaseUrl)
      .then(response => {
          if (!response.ok) {
              throw new Error("Failed to load database. Please check the URL.");
          }
          return response.arrayBuffer();  // Convert the response to ArrayBuffer
      })
      .then(arrayBuffer => {
          const uint8Array = new Uint8Array(arrayBuffer);

          // Initialize SQL.js and load the database
          initSqlJs({ locateFile: file => `lib.assets/wasm/sql-wasm.wasm` }).then(SQL => {
              db = new SQL.Database(uint8Array);  // Create a new database instance

              // Get the names of all tables in the database
              let res1 = db.exec("SELECT name FROM sqlite_master WHERE type='table';");

              let tableList = document.querySelector('#sqlite-table-sidebar #sqlite-table-list'); // Get sidebar element
              tableList.innerHTML = ''; // Clear previous table names

              if(res1?.[0]?.values?.length)
              {
                res1[0].values.forEach(row => {
                  let tableListItem = document.createElement('li');
                  let tableName = row[0]; // Extract table name
                  let tableContentLink = document.createElement('a'); // Create a link for the table
                  tableContentLink.href = '#';
                  tableContentLink.innerText = tableName; // Set link text to table name
                  tableContentLink.classList.add('sqlite-table-content');
                  tableContentLink.addEventListener('click', function (e) { //NOSONAR
                      e.preventDefault(); // Prevent default link behavior
                      displayTableData(db, tableName); // Display table data on click
                      hilightTable(e);
                  });
                  let tableStructureLink = document.createElement('a'); // Create a link for the table
                  tableStructureLink.href = '#';
                  tableStructureLink.innerText = '☰'; // Symbol link for structure
                  tableStructureLink.classList.add('sqlite-table-structure');
                  tableStructureLink.addEventListener('click', function (e) { //NOSONAR
                      e.preventDefault(); // Prevent default link behavior
                      displayTableStructure(db, tableName); // Display schema on click
                      hilightTable(e);
                  });

                  tableListItem.appendChild(tableStructureLink);
                  tableListItem.appendChild(document.createTextNode(' '));
                  tableListItem.appendChild(tableContentLink);
                  tableList.appendChild(tableListItem); // Add link to sidebar
                });

                document.getElementById('sqliteDownloadAllSqlButton').disabled = false; // Enable download button for all tables
              }
              else
              {
                db = null;
              }
          });
      })
      .catch(error => {
          alert(`Error loading database from server: ${error.message}`);
      });
}


/**
 * Display the structure of a given table (columns and their metadata).
 *
 * @param {object} db - The SQLite database instance.
 * @param {string} tableName - The name of the table to inspect.
 */
function displayTableStructure(db, tableName) {
    currentSqliteTableName = tableName; // Save the active table name
    let res = db.exec(`PRAGMA table_info(${tableName});`); // Retrieve column metadata
    let output = document.getElementById('sqlite-output'); // Get output area
    output.innerHTML = ''; // Clear previous content
    exportType = 'structure';
    document.getElementById('sqliteDownloadSqlButton').disabled = false; // Enable export button

    if (res.length > 0) {
        // Build an HTML table containing column details
        let tableString = `<h3>Table Structure: ${tableName}</h3>
                           <table class="sqlite-table-data">
                               <thead>
                                   <tr>
                                       <th>Column Name</th>
                                       <th>Data Type</th>
                                       <th>Not Null</th>
                                       <th>Default</th>
                                       <th>Primary Key</th>
                                   </tr>
                               </thead>
                               <tbody>`;

        // Iterate through column definitions and append rows
        res[0].values.forEach(column => {
            tableString += '<tr>';
            tableString += `<td>${column[1]}</td>`; // Column name
            tableString += `<td>${column[2]}</td>`; // Data type
            tableString += `<td>${column[3] === 1 ? 'Yes' : 'No'}</td>`; // Not Null constraint
            tableString += `<td>${column[4] === null ? '' : column[4]}</td>`; // Default value
            tableString += `<td>${column[5] === 1 ? 'Yes' : 'No'}</td>`; // Primary Key
            tableString += '</tr>';
        });

        tableString += '</tbody></table>'; // Close table
        output.innerHTML = tableString; // Render the result
    } else {
        output.innerHTML = "No structure found."; // Display message if no structure
    }
}

/**
 * Display data from the given table.
 *
 * @param {object} db - The SQLite database instance.
 * @param {string} tableName - The name of the table to fetch data from.
 */
function displayTableData(db, tableName) {
    currentSqliteTableName = tableName; // Save the active table name
    let res = db.exec("SELECT * FROM " + tableName); // Query all table rows
    let output = document.getElementById('sqlite-output');
    output.innerHTML = '';
    exportType = 'structure+data';
    document.getElementById('sqliteDownloadSqlButton').disabled = false; // Enable export button

    // Fetch column metadata
    columnInfo = db.exec(`PRAGMA table_info(${tableName});`)[0].values;

    if (res.length > 0) {
        output.innerHTML = `<h3>Table Content: ${tableName}</h3>` + createTable(res[0]); // Render table
        currentData = res[0]; // Save for editing
    } else {
        output.innerHTML = `<h3>Table Content: ${tableName}</h3><p>No data found.</p>`;
    }
}

/**
 * Convert database query results into an HTML table.
 *
 * @param {object} data - Query result object with `columns` and `values`.
 * @returns {string} HTML representation of the table.
 */
function createTable(data) {
    let tableString = '<table class="sqlite-table-data"><thead><tr>';

    // Add column headers
    data.columns.forEach(column => {
        tableString += `<th>${column}</th>`;
    });
    tableString += '</tr></thead><tbody>';

    // Add data rows
    data.values.forEach(row => {
        tableString += '<tr>';
        row.forEach(cell => {
            tableString += `<td>${cell !== null ? cell : ''}</td>`; // Handle NULLs
        });
        tableString += '</tr>';
    });

    tableString += '</tbody></table>';
    return tableString;
}


/**
 * Retrieve the data type of a specific column.
 *
 * @param {string} columnName - Column name.
 * @returns {string} Data type (or "UNKNOWN" if not found).
 */
function getDataType(columnName) {
    const column = columnInfo.find(col => col[1] === columnName);
    return column ? column[2] : 'UNKNOWN';
}

/**
 * Highlight the currently selected table in the sidebar.
 *
 * @param {Event} e - Click event triggered on the table list item.
 */
function hilightTable(e) {
    let li = e.target.closest('li');
    const listItems = li.closest('ul').querySelectorAll('li');

    // Remove highlight from all <li> elements
    listItems.forEach(item => {
        item.classList.remove('highlight');
    });

    // Highlight the clicked <li>
    li.classList.add('highlight');
}

const SQLITE_EXPORT_BATCH_SIZE = 50; // configurable batch size

/**
 * Exports the entire SQLite database (all user-defined tables) into a single SQL file.
 * Supports batching of INSERT statements (default 50 rows per batch).
 */
function sqliteDownloadAllSql() {
  const res = db.exec("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
  if (res.length === 0 || res[0].values.length === 0) {
      return;
  }

  const tableNames = res[0].values.map(row => row[0]);
  let sqlContent = "-- SQL Export for All Tables\r\n\r\n";

  tableNames.forEach(tableName => {
      // --- Struktur tabel ---
      const tableStructureRes = db.exec(`PRAGMA table_info(${tableName});`);
      let columnTypes = {};
      if (tableStructureRes.length > 0) {
          const columns = tableStructureRes[0].values.map(col => {
              const columnName = col[1];
              const dataType = col[2];
              columnTypes[columnName] = dataType.toUpperCase();
              const isNotNull = col[3] === 1 ? "NOT NULL" : "";
              const defaultValue = col[4] ? `DEFAULT ${col[4]}` : "";
              const primaryKey = col[5] === 1 ? "PRIMARY KEY" : "";
              return `${columnName} ${dataType} ${isNotNull} ${defaultValue} ${primaryKey}`.trim();
          }).join(",\r\n  ");

          sqlContent += `-- Table: ${tableName}\r\n`;
          sqlContent += `CREATE TABLE ${tableName} (\r\n  ${columns}\r\n);\r\n\r\n`;
      }

      // --- Data tabel ---
      const tableDataRes = db.exec(`SELECT * FROM ${tableName};`);
      if (tableDataRes.length > 0) {
          const columns = tableDataRes[0].columns;
          const rows = tableDataRes[0].values;

          for (let i = 0; i < rows.length; i += SQLITE_EXPORT_BATCH_SIZE) {
              const batch = rows.slice(i, i + SQLITE_EXPORT_BATCH_SIZE);

              const valuesList = batch.map(row => {
                  const values = row.map((value, idx) => {
                      if (value === null) return "NULL";

                      const colName = columns[idx];
                      const type = columnTypes[colName] || "";

                      if (/(INT|REAL|NUM|DECIMAL|DOUBLE|FLOAT|BOOL)/.test(type)) {
                          return value;
                      }
                      return `'${value.toString().replace(/'/g, "''")}'`;
                  });
                  return `(${values.join(", ")})`;
              });

              sqlContent += `INSERT INTO ${tableName} (${columns.join(", ")}) VALUES\r\n${valuesList.join(",\r\n")}\r\n;\r\n`;
          }

          sqlContent += "\r\n";
      }
  });

  const blob = new Blob([sqlContent], { type: "text/sql" });
  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = `${curretDatabaseName}.sql`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);

  URL.revokeObjectURL(url);
}

/**
 * Exports the schema and data of the currently selected SQLite table into an SQL file.
 * Supports batching of INSERT statements (default 50 rows per batch).
 */
function sqliteDownloadSql() {
  const res = db.exec("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
  if (res.length === 0 || res[0].values.length === 0) {
      return;
  }

  let tableName = currentSqliteTableName;
  if (!tableName) {
      return;
  }
  let sqlContent = `-- SQL Export for Table ${tableName}\r\n\r\n`;

  const tableStructureRes = db.exec(`PRAGMA table_info(${tableName});`);
  let columnTypes = {};

  if(exportType.indexOf('structure') != -1)
  {
    if (tableStructureRes.length > 0) {
        const columns = tableStructureRes[0].values.map(col => {
            const columnName = col[1];
            const dataType = col[2];
            columnTypes[columnName] = dataType.toUpperCase();
            const isNotNull = col[3] === 1 ? "NOT NULL" : "";
            const defaultValue = col[4] ? `DEFAULT ${col[4]}` : "";
            const primaryKey = col[5] === 1 ? "PRIMARY KEY" : "";
            return `${columnName} ${dataType} ${isNotNull} ${defaultValue} ${primaryKey}`.trim();
        }).join(",\r\n  ");

        sqlContent += `-- Table: ${tableName}\r\n`;
        sqlContent += `CREATE TABLE ${tableName} (\r\n  ${columns}\r\n);\r\n\r\n`;
    }
  }
  if(exportType.indexOf('data') != -1)
  {
    const tableDataRes = db.exec(`SELECT * FROM ${tableName};`);
    if (tableDataRes.length > 0) {
        const columns = tableDataRes[0].columns;
        const rows = tableDataRes[0].values;

        for (let i = 0; i < rows.length; i += SQLITE_EXPORT_BATCH_SIZE) {
            const batch = rows.slice(i, i + SQLITE_EXPORT_BATCH_SIZE);

            const valuesList = batch.map(row => {
                const values = row.map((value, idx) => {
                    if (value === null) return "NULL";

                    const colName = columns[idx];
                    const colType = columnTypes[colName] || "";

                    if (/(INT|REAL|NUM|DECIMAL|DOUBLE|FLOAT|BOOL)/.test(colType)) {
                        return value;
                    }
                    return `'${value.toString().replace(/'/g, "''")}'`;
                });
                return `(${values.join(", ")})`;
            });

            sqlContent += `INSERT INTO ${tableName} (${columns.join(", ")}) VALUES\r\n${valuesList.join(",\r\n")}\r\n;\r\n`;
        }

        sqlContent += "\r\n";
    }
  }

  const blob = new Blob([sqlContent], { type: "text/sql" });
  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = `${tableName}.sql`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);

  URL.revokeObjectURL(url);
}


/**
 * Create the HTML structure for the SQLite database resource interface.
 *
 * The layout consists of three main sections:
 *  - Sidebar (sqlite-table-sidebar): Displays the list of tables.
 *  - Main (sqlite-table-main): Provides input controls (upload file, load from server)
 *    and an output area for displaying table data.
 *
 * Structure:
 *  <div id="sqlite-app-container">
 *    <aside id="sqlite-table-sidebar">...</aside>
 *    <main id="sqlite-table-main">...</main>
 *  </div>
 *
 * @function createDatabaseResource
 * @returns {string} HTML string representing the database resource UI layout.
 */
function createDatabaseResource() {
  return `
    <div id="sqlite-app-container">
      <aside id="sqlite-table-sidebar" class="sqlite-sidebar">
        <div class="sqlite-header-section">Tables</div>
        <div id="sqlite-table-container">
          <ul id="sqlite-table-list"></ul>
        </div>
      </aside>

      <main id="sqlite-table-main" class="sqlite-main">
        <div class="sqlite-input-area sqlite-header-section">
          <button class="btn btn-primary" id="sqliteDownloadSqlButton" onclick="sqliteDownloadSql()" disabled>Export Table to SQL</button>
          <button class="btn btn-primary" id="sqliteDownloadAllSqlButton" onclick="sqliteDownloadAllSql()" disabled>Export Database to SQL</button>
          <span class="sql-file-source btn btn-secondary"><span class="sqlite-file-path"></span></span>
        </div>
        <div id="sqlite-output"></div>
      </main>
    </div>
  `;
}
