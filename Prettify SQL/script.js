const data = {"applicationId":"","databaseType":"mysql","databaseName":"sipro","databaseSchema":"public","entities":[{"name":"song","columns":[{"name":"song_id","type":"VARCHAR","length":"40","nullable":false,"default":null,"primaryKey":true,"autoIncrement":true,"values":"null"},{"name":"name","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":"null"},{"name":"title","type":"VARCHAR","length":"250","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":"null"},{"name":"producer_id","type":"VARCHAR","length":"40","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":"null"},{"name":"artist_id","type":"VARCHAR","length":"20","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":"null"},{"name":"active","type":"TINYINT","length":"1","nullable":false,"default":"1","primaryKey":false,"autoIncrement":false,"values":"null"},{"name":"song_col7","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col8","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col9","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col10","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col11","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col12","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col13","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col14","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col15","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col16","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"song_col17","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null}]},{"name":"producer","columns":[{"name":"producer_id","type":"VARCHAR","length":"40","nullable":false,"default":null,"primaryKey":true,"autoIncrement":false,"values":null},{"name":"name","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"active","type":"TINYINT","length":"1","nullable":false,"default":"1","primaryKey":false,"autoIncrement":false,"values":null}]},{"name":"artist","columns":[{"name":"artist_id","type":"VARCHAR","length":"40","nullable":false,"default":null,"primaryKey":true,"autoIncrement":false,"values":"null"},{"name":"name","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":"null"},{"name":"agency_id","type":"VARCHAR","length":"40","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"active","type":"TINYINT","length":"1","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":"null"}]},{"name":"agency","columns":[{"name":"agency_id","type":"VARCHAR","length":"40","nullable":false,"default":null,"primaryKey":true,"autoIncrement":false,"values":null},{"name":"name","type":"VARCHAR","length":"50","nullable":false,"default":null,"primaryKey":false,"autoIncrement":false,"values":null},{"name":"active","type":"TINYINT","length":"1","nullable":false,"default":"1","primaryKey":false,"autoIncrement":false,"values":null}]}]};



// SVG Setup
const svg = document.getElementById("erd-svg");

// Function to generate tables and relationships
function createERD() {
    let xPos = 0; // Initial X position for tables
    let yPos = 0; // Initial Y position for tables
    const spacing = 250; // Space between tables

    const tables = {}; // To store table elements for relationships

    let maxTop = 0;
    let maxCol = 0;
    let lastMaxCol = 0;

    // Create entities as tables
    data.entities.forEach(entity => {
        const tableGroup = createTable(entity, xPos, yPos);
        tables[entity.name] = {table: tableGroup, xPos: xPos, yPos: yPos};

        if(maxCol < entity.columns.length)
        {
            maxCol = entity.columns.length;
        }
        lastMaxCol = maxCol;

        // Update position for next table
        xPos += spacing;

        // Wrap to next row after 3 tables (adjust this number as needed)
        if (xPos > svg.clientWidth - spacing) {
            xPos = 0;
            yPos += ((maxCol * 20) + 60);
            maxCol = 0;
        }
        maxTop = yPos;
    });

    console.log(maxTop, maxCol)
    let height = yPos + (lastMaxCol * 20) + 70;

    // Create relationships based on foreign keys
    data.entities.forEach(entity => {
        entity.columns.forEach((col, index) => {
            if (col.name.endsWith("_id") && !col.primaryKey) {
                const refEntityName = col.name.replace("_id", "");

                if (tables[refEntityName]) {
                    createRelationship(tables, data.entities, entity, col, index);
                }
            }
        });
    });

    svg.setAttribute('height', height);
}

function getReferenceEntity(entities, refEntityName)
{
    let result = {};
    entities.forEach(entity => {
        if(entity.name == refEntityName)
        {
            result = entity;
        }
    });
    return result;
}


function getColumnIndex(entity, col)
{
    let result = 0;
    entity.columns.forEach((column, index) => {
        if(column.name == col)
        {
            result = index;
        }
    });
    return result;
}

// Create table as SVG group element
function createTable(entity, x, y) {
    const group = document.createElementNS("http://www.w3.org/2000/svg", "g");
    group.setAttribute("transform", `translate(${x}, ${y})`);

    // Table Rectangle
    const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    rect.setAttribute("width", 150);
    rect.setAttribute("height", (entity.columns.length * 20) + 40);
    rect.setAttribute("fill", "#e0e0e0");
    rect.setAttribute("stroke", "#555555");
    rect.setAttribute("stroke-width", "0.5");
    group.appendChild(rect);

    // Header background rectangle (with color)
    const headerRect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    headerRect.setAttribute("width", 150);
    headerRect.setAttribute("height", 30);
    headerRect.setAttribute("fill", "#4CAF50");  // Green background for header
    headerRect.setAttribute("stroke", "#3e8e41");  // Darker green border for header
    headerRect.setAttribute("stroke-width", "1");
    group.appendChild(headerRect);

    // Table Name (header text)
    const tableName = document.createElementNS("http://www.w3.org/2000/svg", "text");
    tableName.setAttribute("x", 75);
    tableName.setAttribute("y", 20);
    tableName.setAttribute("font-size", "14");
    tableName.setAttribute("text-anchor", "middle");
    tableName.setAttribute("stroke-width", "0.5");
    tableName.setAttribute("fill", "#ffffff");  // White text color for header
    tableName.textContent = entity.name;
    group.appendChild(tableName);

    // Border bottom for header
    const bottomBorder = document.createElementNS("http://www.w3.org/2000/svg", "line");
    bottomBorder.setAttribute("x1", 0);
    bottomBorder.setAttribute("y1", 30);  // Just below the header
    bottomBorder.setAttribute("x2", 150);
    bottomBorder.setAttribute("y2", 30);  // Just below the header
    bottomBorder.setAttribute("stroke", "#3e8e41");  // Dark green border color
    bottomBorder.setAttribute("stroke-width", "1");
    group.appendChild(bottomBorder);

    // Table Columns
    entity.columns.forEach((col, index) => {
        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
        text.setAttribute("x", 10);
        text.setAttribute("y", 50 + index * 20);
        text.setAttribute("font-size", "12");
        text.setAttribute("stroke-width", "0.5");
        text.textContent = col.name;
        group.appendChild(text);
    });

    svg.appendChild(group);
    return group;
}

// Create relationship between tables
function createRelationship(tables, entities, entity, col, index) {

    const refEntityName = col.name.replace("_id", "");

    let referenceEntity = getReferenceEntity(entities, refEntityName);
    let index2 = getColumnIndex(referenceEntity, col.name);
    console.log(index2)
    let fromTable = tables[entity.name].table;
    let toTable = tables[refEntityName].table;

    let y1 = (index * 20) + tables[entity.name].yPos + 40;
    let y2 = (index2 * 20) + tables[refEntityName].yPos + 40;

    const fromX = parseInt(fromTable.getAttribute("transform").split(",")[0].replace("translate(", ""));
    const fromY = parseInt(fromTable.getAttribute("transform").split(",")[1]);

    const toX = parseInt(toTable.getAttribute("transform").split(",")[0].replace("translate(", ""));
    const toY = parseInt(toTable.getAttribute("transform").split(",")[1]);

    // Draw a line between tables to represent the relationship
    const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
    line.setAttribute("x1", fromX + 150);
    line.setAttribute("y1", y1); // Adjusting position for better alignment
    line.setAttribute("x2", toX);
    line.setAttribute("y2", y2); // Adjusting position for better alignment
    line.setAttribute("stroke", "#555555");
    line.setAttribute("stroke-width", "0.7");
    svg.appendChild(line);
}

// Initialize the ERD creation
createERD();
