
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
     * @param {SVGElement} svgElement - The SVG element to which the generated ERD (tables and relationships) will be appended.
     */
    constructor(svgElement) {
        this.svg = svgElement; // The SVG element to render the ERD
        this.tables = {}; // Store the SVG elements for the tables
        this.spacing = 260; // Space between tables on the canvas
        this.tableWidth = 240;
        this.maxTop = 0; // To track the maximum top position of the last row
        this.maxCol = 0; // The maximum number of columns in any table (used to wrap rows)
        this.lastMaxCol = 0; // The previous maximum column count for row wrapping
        this.mod = 0; // Modulo to help with table wrapping
        this.withLengthTypes = [
            'VARCHAR', 'CHAR',
            'VARBINARY', 'BINARY',
            'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT',
            'BIT'
        ];
        this.withValueTypes = ['ENUM', 'SET'];
        this.withRangeTypes = ['NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT'];
        this.btnSpace = 16;
        this.btnMargin = 2;
    }

    /**
     * Method to generate the ERD (Entity-Relationship Diagram).
     * This method will create all the tables and set their positions on the SVG canvas.
     * It also calculates the height of the SVG element based on the number of tables.
     * 
     * @param {Object} data - The data structure containing entities (tables), columns, and relationships. It should include:
     *   - {Array} entities - An array of entity objects where each entity represents a table with its columns and relationships.
     *   - {Array} relationships - An array of relationship objects between the entities, describing how they are related.
     * @param {number} width - The width of the SVG canvas, used to set the `width` attribute for the SVG element.
     */
    createERD(data, width) {
        this.lastMaxCol = 0;
        this.maxCol = 0;
        this.svg.innerHTML = '';
        this.data = data; // The input data structure containing the entities, columns, and relationships
        this.svg.setAttribute('width', width); // Set the width of the SVG canvas

        let xPos = 0; // Initial horizontal position for the first table
        let yPos = 0; // Initial vertical position for the first table

        // Loop through each entity (table) and create it
        this.data.entities.forEach((entity, index) => {
            const tableGroup = this.createTable(entity, index, xPos, yPos);
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
            xPos += this.spacing;
            this.mod++;

            // Wrap to the next row if we've reached the width limit of the SVG
            if (xPos > (width - this.spacing)) {
                xPos = 0;
                yPos += ((this.maxCol * 20) + 60); // Move down to the next row
                this.maxCol = 0;
                this.mod = 0;
            }
            this.maxTop = yPos;
        });

        // Adjust the height of the SVG to accommodate all tables
        let height = yPos - 20;
        if (this.mod > 0) {
            height += (this.maxCol * 20) + 60;
        }

        this.svg.setAttribute('height', height); // Set the SVG height to fit all tables

        // Create the relationships (lines) between tables
        this.createRelationships();
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

    createOffset(index)
    {
        return (index * this.btnSpace) + this.btnMargin;
    }

    /**
     * Creates an SVG representation of a table for a given entity.
     * This method generates a table with a rectangle for the table body, adds the table name at the top, and lists the columns with their respective types.
     * It also provides interactive "Edit" and "Delete" icons for the table, and buttons for moving the table up or down.
     * 
     * @param {Object} entity - The entity representing the table. It should contain:
     *   - {string} name - The name of the table.
     *   - {Array} columns - An array of column objects, where each column has a name and type.
     * @param {number} index - The index of the entity within the collection of entities. This index is used when calling the `editEntity` or `deleteEntity` methods.
     * @param {number} x - The x-coordinate for the table's position in the SVG canvas.
     * @param {number} y - The y-coordinate for the table's position in the SVG canvas.
     * 
     * @returns {SVGElement} The SVG `<g>` (group) element that represents the table, containing all the SVG elements (rectangles, text, lines, etc.).
     */
    createTable(entity, index, x, y) {
        const group = document.createElementNS("http://www.w3.org/2000/svg", "g");
        group.setAttribute("transform", `translate(${x}, ${y})`);

        // Table Rectangle
        const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        rect.setAttribute("width", this.tableWidth);
        rect.setAttribute("height", (entity.columns.length * 20) + 28);
        rect.setAttribute("fill", "transparent");
        rect.setAttribute("stroke", "#8496B1");
        rect.setAttribute("stroke-width", "0.5");
        group.appendChild(rect);

        // Header background rectangle
        const headerRect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        headerRect.setAttribute("x", 1);
        headerRect.setAttribute("y", 1);
        headerRect.setAttribute("width", this.tableWidth - 2);
        headerRect.setAttribute("height", 24);
        headerRect.setAttribute("fill", "#E0EDFF");
        group.appendChild(headerRect);

        // Table Name (header text)
        const tableName = document.createElementNS("http://www.w3.org/2000/svg", "text");
        tableName.setAttribute("x", 10);
        tableName.setAttribute("y", 17);
        tableName.setAttribute("font-size", "12");
        tableName.setAttribute("text-anchor", "left");
        tableName.setAttribute("stroke-width", "0.5");
        tableName.setAttribute("fill", "#1d3c86");
        tableName.textContent = entity.name;
        group.appendChild(tableName);

        const moveUpText = document.createElementNS("http://www.w3.org/2000/svg", "text");
        moveUpText.setAttribute("x", this.tableWidth - this.createOffset(4));
        moveUpText.setAttribute("y", 17);
        moveUpText.setAttribute("font-size", "10");
        moveUpText.textContent = "⬆️"; // Up arrow symbol
        group.appendChild(moveUpText);

        // Move Up Button (rectangle + text)
        const moveUpBtn = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        moveUpBtn.setAttribute("x", this.tableWidth - this.createOffset(4)); // Position to the left of the Delete button
        moveUpBtn.setAttribute("y", 7); // Vertically center
        moveUpBtn.setAttribute("width", 14);
        moveUpBtn.setAttribute("height", 14);
        moveUpBtn.setAttribute("fill", "transparent"); 
        moveUpBtn.setAttribute("class", "move-up-btn");
        moveUpBtn.setAttribute("data-index", index);
        group.appendChild(moveUpBtn);



        const moveDownText = document.createElementNS("http://www.w3.org/2000/svg", "text");
        moveDownText.setAttribute("x", this.tableWidth - this.createOffset(3));
        moveDownText.setAttribute("y", 17);
        moveDownText.setAttribute("font-size", "10");
        moveDownText.textContent = "⬇️"; // Down arrow symbol
        group.appendChild(moveDownText);


        // Move Down Button (rectangle + text)
        const moveDownBtn = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        moveDownBtn.setAttribute("x", this.tableWidth - this.createOffset(3)); // Position to the left of the Edit button
        moveDownBtn.setAttribute("y", 7); // Vertically center
        moveDownBtn.setAttribute("width", 14);
        moveDownBtn.setAttribute("height", 14);
        moveDownBtn.setAttribute("fill", "transparent"); 
        moveDownBtn.setAttribute("class", "move-down-btn");
        moveDownBtn.setAttribute("data-index", index);
        group.appendChild(moveDownBtn);    

        // Create Edit and Delete icons
        const editIconText = document.createElementNS("http://www.w3.org/2000/svg", "text");
        editIconText.setAttribute("x", this.tableWidth - this.createOffset(2));
        editIconText.setAttribute("y", 17);
        editIconText.setAttribute("font-size", "10");
        editIconText.textContent = "✏️"; // Edit text
        group.appendChild(editIconText);

        const editIconRect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        editIconRect.setAttribute("x", this.tableWidth - this.createOffset(2));
        editIconRect.setAttribute("y", 7);
        editIconRect.setAttribute("width", 14);
        editIconRect.setAttribute("height", 14);
        editIconRect.setAttribute("fill", "transparent");
        editIconRect.setAttribute("class", "edit-icon");
        editIconRect.setAttribute("data-index", index);
        group.appendChild(editIconRect);

        const deleteIconText = document.createElementNS("http://www.w3.org/2000/svg", "text");
        deleteIconText.setAttribute("x", this.tableWidth - this.createOffset(1));
        deleteIconText.setAttribute("y", 17);
        deleteIconText.setAttribute("font-size", "10");
        deleteIconText.textContent = "❌"; // Delete text
        group.appendChild(deleteIconText);

        const deleteIconRect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        deleteIconRect.setAttribute("x", this.tableWidth - this.createOffset(1));
        deleteIconRect.setAttribute("y", 7);
        deleteIconRect.setAttribute("width", 14);
        deleteIconRect.setAttribute("height", 14);
        deleteIconRect.setAttribute("fill", "transparent");
        deleteIconRect.setAttribute("class", "delete-icon");
        deleteIconRect.setAttribute("data-index", index);
        group.appendChild(deleteIconRect);

        // Set cursor to pointer for icons
        moveUpBtn.style.cursor = "pointer";
        moveDownBtn.style.cursor = "pointer";
        editIconRect.style.cursor = "pointer";
        deleteIconRect.style.cursor = "pointer";

        let yOffset = 40;
        let yOffsetCol = 26;

        // Table Columns with their types
        entity.columns.forEach((col, index) => {
            const columnText = document.createElementNS("http://www.w3.org/2000/svg", "text");
            columnText.setAttribute("x", 10);
            columnText.setAttribute("y", yOffset + (index * 20));
            columnText.setAttribute("font-size", "12");
            columnText.setAttribute("fill", "#3a4255");
            columnText.textContent = col.name;
            group.appendChild(columnText);

            const typeText = document.createElementNS("http://www.w3.org/2000/svg", "text");
            typeText.setAttribute("x", this.tableWidth - 10);
            typeText.setAttribute("y", yOffset + (index * 20));
            typeText.setAttribute("font-size", "10");
            typeText.setAttribute("fill", "#3a4255");

            let colType;
            if (this.withLengthTypes.includes(col.type) && col.length > 0) {
                colType = `${col.type}(${col.length})`;
            } else if (this.withRangeTypes.includes(col.type) && col.values != null) {
                colType = `${col.type}(${col.values})`;
            } else {
                colType = `${col.type}`;
            }

            typeText.textContent = colType;
            typeText.setAttribute("text-anchor", "end");
            group.appendChild(typeText);

            const borderLine = document.createElementNS("http://www.w3.org/2000/svg", "line");
            borderLine.setAttribute("x1", 0);
            borderLine.setAttribute("y1", yOffsetCol + (index * 20));
            borderLine.setAttribute("x2", this.tableWidth);
            borderLine.setAttribute("y2", yOffsetCol + (index * 20));
            borderLine.setAttribute("stroke", "#8496B1");
            borderLine.setAttribute("stroke-width", "0.3");
            group.appendChild(borderLine);
        });

        this.svg.appendChild(group); // Append the table group to the SVG
        return group;
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
        const refEntityName = col.name.replace("_id", "");
        const referenceEntity = this.getEntityByName(refEntityName);
        if(referenceEntity != null)
        {
            const refIndex = this.getColumnIndex(referenceEntity, col.name);

            let fromTable = this.tables[entity.name].table;
            let toTable = this.tables[refEntityName].table;

            let y1 = (index * 20) + this.tables[entity.name].yPos + 35;
            let y2 = (refIndex * 20) + this.tables[refEntityName].yPos + 35;

            const fromX = parseInt(fromTable.getAttribute("transform").split(",")[0].replace("translate(", ""));

            const toX = parseInt(toTable.getAttribute("transform").split(",")[0].replace("translate(", ""));

            // Draw a line between the two tables to represent the relationship
            const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
            line.setAttribute("x1", fromX + this.tableWidth);
            line.setAttribute("y1", y1);
            line.setAttribute("x2", toX);
            line.setAttribute("y2", y2);
            line.setAttribute("stroke", "#2E4C95");
            line.setAttribute("stroke-width", "0.7");
            this.svg.appendChild(line);
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
     * @param {Object} entity - The entity containing the column.
     * @param {string} columnName - The name of the column to find.
     * @returns {number} The index of the column in the entity's columns array.
     */
    getColumnIndex(entity, columnName) {
        return entity.columns.findIndex(col => col.name === columnName);
    }

    /**
     * Exports an SVG element as an SVG file.
     * 
     * This function serializes the provided SVG element into a string and triggers a download
     * of the resulting SVG content as a file.
     * 
     * @param {SVGElement} svgElement - The SVG element to export as a file.
     * @param {string} [fileName="exported-image.svg"] - The name of the exported file (default is "exported-image.svg").
     */
    exportToSVG(svgElement, fileName = "exported-image.svg") {
        // Serialize the SVG element to a string
        const svgData = new XMLSerializer().serializeToString(svgElement);

        // Embed the font-face in the SVG string (example: using Arial font)
        const svgWithFont = `
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="${width}" height="${height}">
            <defs>
                <style type="text/css">
                    <![CDATA[
                        text {
                            font-family: 'Arial', sans-serif;
                        }
                    ]]>
                </style>
            </defs>
            ${svgData}
        </svg>`;

        // Create a Blob from the SVG string
        const blob = new Blob([svgWithFont], { type: "image/svg+xml" });

        // Create a URL for the Blob
        const url = URL.createObjectURL(blob);

        // Create an <a> element to trigger the download
        const link = document.createElement("a");
        link.href = url;
        link.download = fileName;
        link.click();

        // Revoke the URL once the download is triggered
        URL.revokeObjectURL(url);
    }

    /**
     * Exports an SVG element as a PNG file.
     * 
     * This function renders the provided SVG element onto an HTML canvas and then exports
     * the canvas content as a PNG file.
     * 
     * @param {SVGElement} svgElement - The SVG element to export as a PNG file.
     * @param {string} [fileName="exported_image.png"] - The name of the exported file (default is "exported_image.png").
     */
    exportToPNG(svgElement, fileName = "exported_image.png") {
        // Get the dimensions of the SVG element
        const width = svgElement.clientWidth;
        const height = svgElement.clientHeight;

        // Create a canvas element to render the SVG content
        const canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;

        const context = canvas.getContext("2d");

        // Convert the SVG to a Data URL
        const svgData = new XMLSerializer().serializeToString(svgElement);

        // Embed the font-face in the SVG string (example: using Arial font)
        const svgWithFont = `
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="${width}" height="${height}">
            <defs>
                <style type="text/css">
                    <![CDATA[
                        text {
                            font-family: 'Arial', sans-serif;
                        }
                    ]]>
                </style>
            </defs>
            ${svgData}
        </svg>`;
        const svgUrl = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svgWithFont);

        // Create an image element from the Data URL
        const img = new Image();
        img.onload = function () {
            // Draw the image onto the canvas once it's loaded
            context.drawImage(img, 0, 0);

            // Convert the canvas content to PNG format
            const pngDataUrl = canvas.toDataURL("image/png");

            // Create an <a> element to trigger the PNG download
            const link = document.createElement("a");
            link.href = pngDataUrl;
            link.download = fileName;
            link.click();
        };

        // Handle image loading error
        img.onerror = function (err) {
            console.error('Error loading the SVG image:', err);
        };

        // Load the image from the SVG data URL
        img.src = svgUrl;
    }



    /**
     * Example method to edit an entity.
     * 
     * @param {Object} entity - The entity to be edited.
     */
    editEntity(entity) {
        console.log("Editing entity:", entity);
        // Implement your logic for editing the entity here
    }

    /**
     * Example method to delete an entity.
     * 
     * @param {Object} entity - The entity to be deleted.
     */
    deleteEntity(entity) {
        console.log("Deleting entity:", entity);
        // Implement your logic for deleting the entity here
    }

}