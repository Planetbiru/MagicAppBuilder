/**
 * Class representing an Entity-Relationship Diagram (ERD) generator.
 * This class generates a visual representation of database tables and their relationships
 * using SVG elements, based on the provided data structure.
 * 
 * The data structure includes entities (tables), columns, and foreign key relationships.
 * The class positions tables on an SVG canvas, draws them, and connects them with lines to show relationships.
 */
class EntityRenderer {

    /**
     * Creates an instance of the ERDGenerator, initializing properties for rendering an Entity-Relationship Diagram (ERD).
     * 
     * @param {string} selector - The SVG element selector to which the generated ERD (tables and relationships) will be appended.
     */
    constructor(selector) {
        this.selector = selector;
        this.svg = document.querySelector(this.selector); // The SVG element to render the ERD
        this.tables = {}; // Store the SVG elements for the tables

        this.xPadding = 5;
        this.yPadding = 5;

        this.betweenX  = 20;
        this.betweenY = 20;
        this.tableWidth = 250; // Table width
        this.maxTop = 0; // To track the maximum top position of the last row
        this.maxCol = 0; // The maximum number of columns in any table (used to wrap rows)
        this.lastMaxCol = 0; // The previous maximum column count for row wrapping
        this.withLengthTypes = [
            'VARCHAR', 'CHAR',
            'VARBINARY', 'BINARY',
            'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT',
            'BIT'
        ];
        this.withValueTypes = ['ENUM', 'SET'];
        this.withRangeTypes = ['NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT'];
        this.entityStrokeWidth = "0.5";
        this.stroke = "#8496B1";
        this.columnTextColor = "#3a4255";
        this.columnHeight = 20;
        this.columnTypeFontSize = 9;
        this.columnFontSize = 11
        this.headerBackgroundColor = "#d8e8ff";
        this.buttonSpace = 16;
        this.buttonMargin = 6;
        this.buttonWidth = 14;
        this.buttonHeight = 14;
        this.buttonFontSize = 10;
        this.tableFontSize = 12;
        this.relationStrokeWidth = 0.7;
    }

    /**
     * Method to generate the Entity-Relationship Diagram (ERD).
     * This method creates tables on the SVG canvas, sets their positions, 
     * and calculates the SVG canvas's height based on the number of tables. 
     * It also supports drawing relationships between tables if specified.
     *
     * @param {Object} data - The data structure containing entities (tables), columns, and relationships. It should include:
     *   - {Array} entities - An array of entity objects where each entity represents a table with its columns and relationships.
     *   - {Array} relationships - An array of relationship objects between the entities, describing how they are related.
     *
     * @param {number} width - The width of the SVG canvas, used to set the `width` attribute for the SVG element.
     * 
     * @param {boolean} drawRelationship - A flag to indicate whether to draw relationships between tables. 
     *   If `true`, the method will create relationship lines between the tables after placing them.
     */
    createERD(data, width, drawRelationship) {
        this.svg = document.querySelector(this.selector); // The SVG element to render the ERD
        this.lastMaxCol = 0;
        this.maxCol = 0;
        this.svg.innerHTML = '';
        this.data = data; // The input data structure containing the entities, columns, and relationships
        this.svg.setAttribute('width', width); // Set the width of the SVG canvas
        let xOffset = this.xPadding;
        let yOffset = this.yPadding;
        let xPos = xOffset; // Initial horizontal position for the first table
        let yPos = yOffset; // Initial vertical position for the first table
        let maxMod = 0;
        let mod = 0; // Modulo to help with table wrapping

        // Loop through each entity (table) and create it
        this.data.entities.forEach(entity => {
            const tableGroup = this.createTable(entity, entity.index, xPos, yPos);
            this.tables[entity.name] = { 
                table: tableGroup, 
                xPos: xPos, 
                yPos: yPos 
            };

            // Update the maximum column count for wrapping the tables in rows
            if (this.maxCol < entity.columns.length) {
                this.maxCol = entity.columns.length;
            }
            this.lastMaxCol = this.maxCol;

            // Update the x position for the next table
            xPos += (this.betweenX + this.tableWidth);
            mod++;
            if(mod > maxMod)
            {
                maxMod = mod;
            }

            // Wrap to the next row if we've reached the width limit of the SVG
            if (xPos > (width - this.tableWidth - 1)) {
                xPos = xOffset;
                yPos += ((this.maxCol * this.columnHeight) + this.betweenY + 30); // Move down to the next row
                this.maxCol = 0;
                mod = 0;
            }
            this.maxTop = yPos;
        });

        // Adjust the height of the SVG to accommodate all tables
        let height = yPos;
        if (mod > 0) {
            height += (this.maxCol * this.columnHeight) + 30;
        }
        else
        {
            height = height - this.betweenY;
        }

        let finalWidth = (2 * this.xPadding) + (maxMod * (this.betweenX + this.tableWidth)) - (this.betweenX) + 2;
        let finalHeight = (2 * this.yPadding) + height - 2;

        this.svg.setAttribute('height', finalHeight);
        this.svg.setAttribute('width', finalWidth);

        // Create the relationships (lines) between tables
        if(drawRelationship)
        {
            this.createRelationships();
        }
        this.svg = document.querySelector(this.selector); // The SVG element to render the ERD
    }

    /**
     * Method to create relationships between tables based on foreign key columns.
     * It will look for columns that end with "_id" and create lines between the relevant tables.
     */
    createRelationships() {
        this.data.entities.forEach(entity => {
            entity.columns.forEach((col, index) => {
                // Check if the column is a foreign key (ends with "_id")
                if (col.name.endsWith("_id") && !col.primaryKey) {
                    const refEntityName = col.name.replace("_id", "");
                    // Check if the referenced entity exists in the tables list
                    if (entity.name != refEntityName && this.tables[refEntityName]) {
                        this.createRelationship(entity, col, index);
                    }
                }
            });
        });
    }

    /**
     * Calculates the offset position for a button based on its index.
     * The offset is determined by the index, multiplied by the space between buttons, 
     * and adjusted by the button margin. This is useful for positioning buttons or 
     * UI elements in a sequence.
     * 
     * @param {number} index - The index of the button or element to calculate the offset for.
     * @returns {number} The calculated offset value, used for positioning.
     */
    createOffset(index)
    {
        return (index * this.buttonSpace) + this.buttonMargin;
    }

    /**
     * Creates an SVG representation of a database table entity for visual modeling.
     *
     * This method generates a visual block representing a table, including:
     * - The table body and header rendered as <rect> elements.
     * - The table name rendered with <foreignObject> for proper overflow handling (ellipsis on overflow).
     * - A list of columns, each rendered with their name and type.
     * - Visual distinction for primary key columns.
     * - Action buttons (⬅️, ➡️, ✏️, ❌) as text+rect elements for moving, editing, and deleting the table.
     *
     * Each generated table group (`<g>`) can be positioned freely on the SVG canvas via the `x` and `y` parameters.
     * This method uses helper functions (`createSvgRect`, `createSvgForeignText`, `getFormattedType`) to modularize rendering logic.
     *
     * @param {Object} entity - The entity object representing the table schema.
     * @param {string} entity.name - The name of the entity/table to display.
     * @param {Array<Object>} entity.columns - List of column definitions for the table.
     * @param {string} entity.columns[].name - The name of the column.
     * @param {string} entity.columns[].type - The data type of the column (e.g., "TEXT", "INTEGER").
     * @param {number} [entity.columns[].length] - Optional column length (for types like VARCHAR).
     * @param {string} [entity.columns[].values] - Optional range or enum values.
     * @param {boolean} [entity.columns[].primaryKey=false] - Whether the column is a primary key.
     *
     * @param {number} index - The index of the entity in the overall entity list (used for action buttons).
     * @param {number} x - The X-coordinate to place the table on the SVG canvas.
     * @param {number} y - The Y-coordinate to place the table on the SVG canvas.
     *
     * @returns {SVGGElement} An SVG `<g>` group element containing the rendered table and its interactive parts.
     *
     * @see createSvgRect
     * @see createSvgForeignText
     * @see getFormattedType
     */
    createTable(entity, index, x, y) {
        let yOffset = 40;
        let yOffsetCol = 26;

        const group = document.createElementNS("http://www.w3.org/2000/svg", "g");
        group.setAttribute('data-entity', entity.name);
        group.classList.add('svg-entity');
        group.setAttribute("transform", `translate(${x}, ${y})`);

        const tableHeight = (entity.columns.length * this.columnHeight) + 26;

        const rect = this.createSvgRect(0, 0, this.tableWidth, tableHeight, "#ffffff", this.stroke, this.entityStrokeWidth);
        group.appendChild(rect);

        const headerRect = this.createSvgRect(1, 1, this.tableWidth - 2, 24, this.headerBackgroundColor);
        group.appendChild(headerRect);

        const titleGroup = this.createSvgForeignText(10, 5, this.tableWidth - 95, 18, entity.name);
        group.appendChild(titleGroup);

        const controlButtons = [
            { icon: "📄", offset: 5, className: "view-data-icon" },
            { icon: "⬅️", offset: 4, className: "move-up-icon" },
            { icon: "➡️", offset: 3, className: "move-down-icon" },
            { icon: "✏️", offset: 2, className: "edit-icon" },
            { icon: "❌", offset: 1, className: "delete-icon" }
        ];

        controlButtons.forEach(({ icon, offset, className }) => {
            const xOffset = this.tableWidth - this.createOffset(offset);

            const iconText = document.createElementNS("http://www.w3.org/2000/svg", "text");
            iconText.setAttribute("x", xOffset);
            iconText.setAttribute("y", 17);
            iconText.setAttribute("font-size", this.buttonFontSize);
            iconText.textContent = icon;
            group.appendChild(iconText);

            const iconRect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
            iconRect.setAttribute("x", xOffset);
            iconRect.setAttribute("y", 7);
            iconRect.setAttribute("width", this.buttonWidth);
            iconRect.setAttribute("height", this.buttonHeight);
            iconRect.setAttribute("fill", "transparent");
            iconRect.setAttribute("class", className);
            iconRect.setAttribute("data-index", index);
            iconRect.style.cursor = "pointer";
            group.appendChild(iconRect);
        });

        entity.columns.forEach((col, i) => {
            const yPosition = yOffset + (i * this.columnHeight);
            const yLine = yOffsetCol + (i * this.columnHeight);

            if (col.primaryKey) {
                const pkRect = this.createSvgRect(1, yLine + 1, this.tableWidth - 2, this.columnHeight - 2, "#f4f8ff");
                group.appendChild(pkRect);
            }

            const columnText = document.createElementNS("http://www.w3.org/2000/svg", "text");
            columnText.setAttribute("x", 10);
            columnText.setAttribute("y", yPosition);
            columnText.setAttribute("font-size", this.columnFontSize);
            columnText.setAttribute("fill", this.columnTextColor);
            columnText.textContent = col.name;
            columnText.classList.add('diagram-column-name');
            group.appendChild(columnText);

            const typeText = document.createElementNS("http://www.w3.org/2000/svg", "text");
            typeText.setAttribute("x", this.tableWidth - 10);
            typeText.setAttribute("y", yPosition);
            typeText.setAttribute("font-size", this.columnTypeFontSize);
            typeText.setAttribute("fill", this.columnTextColor);
            typeText.setAttribute("text-anchor", "end");

            const colType = this.getFormattedType(col);
            typeText.textContent = colType;
            group.appendChild(typeText);

            const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
            line.setAttribute("x1", 0);
            line.setAttribute("y1", yLine);
            line.setAttribute("x2", this.tableWidth);
            line.setAttribute("y2", yLine);
            line.setAttribute("stroke", this.stroke);
            line.setAttribute("stroke-width", this.entityStrokeWidth);
            group.appendChild(line);
        });

        this.svg.appendChild(group);
        return group;
    }

    /**
     * Creates an SVG `<rect>` element with the specified position, size, and styling.
     *
     * This helper method simplifies the creation of rectangular SVG shapes by encapsulating
     * attribute assignment for position, size, fill color, stroke color, and stroke width.
     *
     * Commonly used for drawing table containers, headers, and cell highlights in SVG-based diagrams.
     *
     * @param {number} x - The x-coordinate of the rectangle's top-left corner.
     * @param {number} y - The y-coordinate of the rectangle's top-left corner.
     * @param {number} width - The width of the rectangle.
     * @param {number} height - The height of the rectangle.
     * @param {string} [fill="transparent"] - The fill color of the rectangle (any valid CSS/SVG color).
     * @param {string} [stroke="none"] - The stroke (border) color of the rectangle.
     * @param {number} [strokeWidth=0] - The width of the stroke in pixels.
     *
     * @returns {SVGRectElement} An SVG `<rect>` element ready to be appended to an SVG group or canvas.
     */
    createSvgRect(x, y, width, height, fill = "transparent", stroke = "none", strokeWidth = 0) {
        const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        rect.setAttribute("x", x);
        rect.setAttribute("y", y);
        rect.setAttribute("width", width);
        rect.setAttribute("height", height);
        rect.setAttribute("fill", fill);
        rect.setAttribute("stroke", stroke);
        rect.setAttribute("stroke-width", strokeWidth);
        return rect;
    }

    /**
     * Creates an SVG `<foreignObject>` element containing a styled HTML `<div>` to render text
     * with ellipsis overflow behavior (similar to CSS `text-overflow: ellipsis`).
     *
     * This helper is typically used to display table names or labels within a constrained width,
     * ensuring that long text does not overflow or disrupt the layout of surrounding SVG elements.
     *
     * The HTML `<div>` inside the `<foreignObject>` uses the following styles:
     * - Font: 12px sans-serif
     * - Color: #1d3c86
     * - No wrapping (`white-space: nowrap`)
     * - Hidden overflow with ellipsis (`overflow: hidden; text-overflow: ellipsis`)
     *
     * @param {number} x - The x-coordinate of the `<foreignObject>` container in the SVG.
     * @param {number} y - The y-coordinate of the `<foreignObject>` container in the SVG.
     * @param {number} width - The width of the container area (used for overflow constraint).
     * @param {number} height - The height of the container area.
     * @param {string} text - The plain text content to be rendered inside the div.
     *
     * @returns {SVGForeignObjectElement} An SVG `<foreignObject>` element containing a styled div.
     */
    createSvgForeignText(x, y, width, height, text) {
        const foreign = document.createElementNS("http://www.w3.org/2000/svg", "foreignObject");
        foreign.setAttribute("x", x);
        foreign.setAttribute("y", y);
        foreign.setAttribute("width", width);
        foreign.setAttribute("height", height);

        const div = document.createElement("div");
        div.setAttribute("xmlns", "http://www.w3.org/1999/xhtml");
        div.style.cssText = `font-size: 12px; font-family: sans-serif; color: #1d3c86; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; height: ${height}px;`;
        div.textContent = text;
        foreign.appendChild(div);

        return foreign;
    }

    /**
     * Formats the data type of a column into a string representation suitable for display.
     *
     * This method checks if the column's type supports length (e.g., VARCHAR, CHAR)
     * or range/values (e.g., ENUM, SET) and appends the appropriate length or values
     * in parentheses. If neither applies, it simply returns the type as-is.
     *
     * Examples:
     * - { type: "VARCHAR", length: 100 } → "VARCHAR(100)"
     * - { type: "ENUM", values: "'A','B','C'" } → "ENUM('A','B','C')"
     * - { type: "TEXT" } → "TEXT"
     *
     * @param {Object} col - The column definition object.
     * @param {string} col.type - The base data type of the column.
     * @param {number} [col.length] - The optional length (used with types like VARCHAR).
     * @param {string|Array} [col.values] - The optional values (used with types like ENUM).
     *
     * @returns {string} A formatted string representing the column type.
     */
    getFormattedType(col) {
        if (this.withLengthTypes.includes(col.type) && col.length > 0) {
            return `${col.type}(${col.length})`;
        } else if (this.withRangeTypes.includes(col.type) && col.values != null) {
            return `${col.type}(${col.values})`;
        }
        return `${col.type}`;
    }

    /**
     * Method to create a relationship line between two tables.
     * The line connects the foreign key in one table to the corresponding primary key in the referenced table.
     * 
     * @param {Object} entity - The entity representing the table with the foreign key.
     * @param {Object} col - The column representing the foreign key.
     * @param {number} index - The index of the foreign key column in the entity's columns.
     */
    createRelationship(entity, col, index) {
        // Determine the name of the referenced table by removing '_id' from the foreign key column name
        let refEntityName = col.name.replace("_id", "");
        
        // Get the referenced entity using the reference entity's name
        let referenceEntity = this.getEntityByName(refEntityName);
        
        // If the reference entity exists
        if (referenceEntity != null) {
            // Get the index of the column in the referenced entity (primary key)
            let refIndex = this.getColumnIndex(referenceEntity, col.name);

            // Get the 'from' and 'to' tables based on the entities
            let fromTable = this.tables[entity.name].table;
            let toTable = this.tables[refEntityName].table;

            // Calculate the y-coordinates for the foreign key and primary key
            let y1 = (index * this.columnHeight) + this.tables[entity.name].yPos + 36; // Foreign key y-position
            let x1 = parseInt(fromTable.getAttribute("transform").split(",")[0].replace("translate(", "")); // Foreign key x-position

            // Calculate the x and y positions for the primary key column in the referenced table
            let x4 = parseInt(toTable.getAttribute("transform").split(",")[0].replace("translate(", ""));
            let y4 = (refIndex * this.columnHeight) + this.tables[refEntityName].yPos + 36; // Primary key y-position

            // Set the y-coordinates for the line to be drawn (horizontal alignment)
            let y2 = y1;
            let y3 = y4;

            // Define the x-coordinates for the relationship line based on the positions of the tables
            let x2;
            let x3;
            
            // Adjust positions

            if (x1 == x4) {
                x1 = x1 + 4;  // Slightly adjust the position of the first table
                x4 = x4 + 4;  // Adjust the position of the second table
                x2 = x1 - 8;  // Set intermediate x-coordinate for the path
                x3 = x4 - 8;  // Set intermediate x-coordinate for the path
            }
            else if (x1 <= x4) {
                x1 += this.tableWidth; // Move the first table further to the right
                x1 = x1 - 4;  // Slightly adjust the position of the first table
                x4 = x4 + 4;  // Adjust the position of the second table
                x2 = x1 + 8;  // Set intermediate x-coordinate for the path
                x3 = x4 - 8;  // Set intermediate x-coordinate for the path
            }
            else {
                x4 += this.tableWidth; // Move the second table further to the right
                x1 = x1 + 4;  // Slightly adjust the position of the first table
                x4 = x4 - 4;  // Adjust the position of the second table
                x2 = x1 - 8;  // Set intermediate x-coordinate for the path
                x3 = x4 + 8;  // Set intermediate x-coordinate for the path
            }

            // Create circles at the start and end of the path (representing the foreign key and primary key)
            let circle1 = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            let circle2 = document.createElementNS("http://www.w3.org/2000/svg", "circle");

            // Set attributes for the first circle (foreign key)
            circle1.setAttribute("cx", x1);
            circle1.setAttribute("cy", y1);
            circle1.setAttribute("r", 3); // Radius of the circle
            circle1.setAttribute("fill", "#2A56BD"); // Color of the circle

            // Set attributes for the second circle (primary key)
            circle2.setAttribute("cx", x4);
            circle2.setAttribute("cy", y4);
            circle2.setAttribute("r", 3); // Radius of the circle
            circle2.setAttribute("fill", "#CC0088"); // Color of the circle

            // Create an SVG path element to represent the relationship line
            let path = document.createElementNS("http://www.w3.org/2000/svg", "path");

            // Define the path data for the relationship (connects the two tables with a curved line)
            let pathData = `M ${x1} ${y1} L ${x2} ${y2} L ${x3} ${y3} L ${x4} ${y4}`;
            path.setAttribute("d", pathData);
            path.setAttribute("stroke", "#2A56BD"); // Color of the line
            path.setAttribute("stroke-width", this.relationStrokeWidth); // Thickness of the line
            path.setAttribute("fill", "transparent"); // Ensures the path is not filled with color

            // Append the path and circles to the SVG element (to be drawn on the screen)
            this.svg.appendChild(path);
            this.svg.appendChild(circle1);
            this.svg.appendChild(circle2);
        }
    }

    /**
     * Helper method to get an entity by its name.
     * 
     * @param {string} entityName - The name of the entity to retrieve.
     * @returns {Object} The entity object that matches the given name.
     */
    getEntityByName(entityName) {
        return this.data.entities.find(entity => entity.name === entityName);
    }

    /**
     * Helper method to get the index of a column by its name in an entity.
     * 
     * This function searches through the columns of the provided entity and returns
     * the index of the column with the specified name.
     * If the column is not found, it returns -1.
     * 
     * @param {Object} entity - The entity containing the columns array.
     * @param {string} columnName - The name of the column to find in the entity.
     * @returns {number} The index of the column in the entity's columns array, or -1 if not found.
     */
    getColumnIndex(entity, columnName) {
        return entity.columns.findIndex(col => col.name === columnName);
    }

    /**
     * Exports the current SVG content to a file and triggers a download.
     * 
     * This method calls the `exportToSVG` function, passing the current SVG data 
     * for export. It handles the process of downloading the SVG file.
     */
    downloadSVG() {
        let fileName = this.getFileName() + '.svg';
        this.exportToSVG(this.svg, fileName);
    }

    /**
     * Exports the current SVG content to a PNG file and triggers a download.
     * 
     * This method calls the `exportToPNG` function, passing the current SVG data 
     * for export. It handles the process of downloading the PNG file.
     */
    downloadPNG() {
        let fileName = this.getFileName() + '.png';
        this.exportToPNG(this.svg, fileName);
    }

    downloadMD() {
        let fileName = this.getFileName() + '.md';
        this.exportToMD(fileName);
    }

    /**
     * Retrieves the filename of the currently active diagram or the database name.
     *
     * This method checks the active tab in the diagram list to determine the filename.
     * If an input field is present in the active tab, it retrieves the value from the input field.
     * If the "all-entities" tab is active, it retrieves the database name from the meta tag.
     *
     * @return {string} The filename of the active diagram or the database name.
     */
    getFileName()
    {
        let ul = document.querySelector('.diagram-list.tabs');
        let input = ul.querySelector('.diagram-tab.active input[type="text"]');
        let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');;
        let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');;
        let fileName = '';
        let name = '';
        if(databaseName != '')
        {
            name = databaseName;
        }
        else
        {
            name = applicationId;
        }
        if(input != null)
        {
            fileName = name + ' - ' + input.value;
        }
        else
        {
            fileName = name;
        }
        return fileName;
    }

    /**
     * Generates an SVG string with embedded styles.
     *
     * @param {SVGElement} svgElement - The SVG element to serialize.
     * @returns {string} - The serialized SVG string with embedded styles.
     */
    generateSVGString(svgElement) {
        const width = svgElement.clientWidth;
        const height = svgElement.clientHeight + 2;
        const svgData = new XMLSerializer().serializeToString(svgElement);

        return `
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="${width}" height="${height}">
            <defs>
                <style type="text/css">
                    <![CDATA[
                        text {
                            font-family: 'Arial', sans-serif;
                        }
                        .move-up-icon, .move-down-icon, .edit-icon, .delete-icon {
                            display: none;
                            visibility: hidden;
                            opacity: 0;
                            pointer-events: none;
                        }
                    ]]>
                </style>
            </defs>
            ${svgData}
        </svg>`;
    }

    /**
     * Exports an SVG element as an SVG file.
     *
     * @param {SVGElement} svgElement - The SVG element to export as a file.
     * @param {string} [fileName="exported-image.svg"] - The name of the exported file.
     */
    exportToSVG(svgElement, fileName = "exported-image.svg") {
        const svgWithFont = this.generateSVGString(svgElement);
        const blob = new Blob([svgWithFont], { type: "image/svg+xml" });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement("a");
        link.href = url;
        link.download = fileName;
        link.click();

        URL.revokeObjectURL(url);
    }

    /**
     * Exports an SVG element as a PNG file.
     *
     * @param {SVGElement} svgElement - The SVG element to export as a PNG file.
     * @param {string} [fileName="exported-image.png"] - The name of the exported file.
     */
    exportToPNG(svgElement, fileName = "exported-image.png") {
        const width = svgElement.clientWidth;
        const height = svgElement.clientHeight;
        const canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;
        const context = canvas.getContext("2d");

        const svgWithFont = this.generateSVGString(svgElement);
        const svgUrl = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svgWithFont);
        
        const img = new Image();
        img.onload = function () {
            context.drawImage(img, 0, 0);
            const pngDataUrl = canvas.toDataURL("image/png");
            
            const link = document.createElement("a");
            link.href = pngDataUrl;
            link.download = fileName;
            link.click();
        };

        img.onerror = function (err) {
            console.error('Error loading the SVG image:', err);
        };

        img.src = svgUrl;
    }

    exportToMD(fileName = "exported-image.md") {
        let _this = this;
        let mdContent = `# Entity-Relationship Diagram\n\n`;
        mdContent += `This diagram represents the entities and their relationships in the database.\n\n`;
        mdContent += `## Entities\n\n`;

        this.data.entities.forEach(entity => {
            mdContent += `### ${entity.name}\n\n`;

            if(entity.description)
            {
                mdContent += `**Description:** ${entity.description}\n\n`;
            }

            if(entity.creationDate)
            {
                mdContent += `**Created on:** ${_this.formatDate(entity.creationDate)}\n`;
            }
            if(entity.creator && entity.creator !== "" && entity.creator !== "{{userName}}")
            {
                mdContent += `**Created by:** ${entity.creator}\n`;
            }
            if(entity.modificationDate)
            {
                mdContent += `**Updated on:** ${_this.formatDate(entity.modificationDate)}\n`;
            }
            if(entity.modifier && entity.modifier !== "" && entity.modifier !== "{{userName}}")
            {
                mdContent += `**Updated by:** ${entity.modifier}\n`;
            }

            mdContent += `\n`;

            // Header labels
            const nameHeader = "Column Name";
            const typeHeader = "Type";
            const nullableHeader = "Null";
            const pkHeader = "Key";
            const aiHeader = "AI";

            // Determine max width for padding
            let colNameWidth = Math.max(...entity.columns.map(col => col.name.length), nameHeader.length);
            let typeWidth = Math.max(...entity.columns.map(col => this.getFormattedType(col).length), typeHeader.length);
            const nullableWidth = Math.max(nullableHeader.length, 4);
            const pkWidth = Math.max(pkHeader.length, 3);
            const aiWidth = Math.max(aiHeader.length, 3);

            if (colNameWidth < 30) colNameWidth = 30;
            if (typeWidth < 13) typeWidth = 13;

            // Header row
            mdContent += `| ${nameHeader.padEnd(colNameWidth)} | ${typeHeader.padEnd(typeWidth)} | ${nullableHeader.padEnd(nullableWidth)} | ${pkHeader.padEnd(pkWidth)} | ${aiHeader.padEnd(aiWidth)} |\n`;

            // Divider row
            mdContent += `| ${'-'.repeat(colNameWidth)} | ${'-'.repeat(typeWidth)} | ${'-'.repeat(nullableWidth)} | ${'-'.repeat(pkWidth)} | ${'-'.repeat(aiWidth)} |\n`;

            // Data rows
            entity.columns.forEach(col => {
                const colType = this.getFormattedType(col);
                const nullable = col.nullable ? "YES" : "NO";
                const primaryKey = col.primaryKey ? "YES" : "NO";
                const autoIncrement = col.autoIncrement ? "YES" : "NO";

                mdContent += `| ${col.name.padEnd(colNameWidth)} | ${colType.padEnd(typeWidth)} | ${nullable.padEnd(nullableWidth)} | ${primaryKey.padEnd(pkWidth)} | ${autoIncrement.padEnd(aiWidth)} |\n`;
            });

            mdContent += `\n`;
        });

        mdContent += `\n`;

        const blob = new Blob([mdContent], { type: "text/markdown" });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = fileName;
        link.click();
        URL.revokeObjectURL(url);
    }

    formatDate(timestamp) {
        const date = new Date(timestamp);
        const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        return date.toLocaleDateString('en-US', options).replace(',', '');
    }

    /**
     * Returns the SVG element currently used for rendering.
     * @returns {SVGElement} The SVG element.
     */
    getSVGElement() {
        return this.svg;
    }

}