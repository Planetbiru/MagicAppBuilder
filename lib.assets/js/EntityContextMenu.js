// Global variable to track which entity was right-clicked
let selectedElement = null;

document.addEventListener('DOMContentLoaded', function () {
    /**
     * Hide context menu when clicking anywhere outside it.
     */
    document.addEventListener("click", function (e) {
        const contextMenu = document.querySelector('#context-menu');
        if (!e.target.closest("#context-menu")) {
            contextMenu.style.display = "none";
        }
    });
});

/**
 * Programmatically hides the context menu.
 */
function hideContextMenu() {
    const contextMenu = document.querySelector('#context-menu');
    contextMenu.style.display = "none";
}

/**
 * Initialize context menu behavior for an SVG element containing entity diagrams.
 * @param {SVGElement} svg - The SVG container to bind the right-click context menu to.
 */
function initDiagramContextMenu(svg) {
    const contextMenu = document.querySelector('#context-menu');

    svg.addEventListener("contextmenu", function (e) {
        const entity = e.target.closest('g.svg-entity');
        selectedElement = entity;

        if (entity) {
            e.preventDefault(); // Prevent the default browser context menu

            // Position context menu near the mouse click
            contextMenu.style.top = `${e.pageY}px`;
            contextMenu.style.left = `${e.pageX}px`;
            contextMenu.style.display = "block";

            const referenceMenu = document.querySelector('#menu-make-reference');
            const submenu = document.querySelector('#reference-submenu');

            // Remove all submenu items except the first one (usually "Check all")
            let checkAll = null;
            submenu.querySelectorAll('li').forEach((li) => {
                const input = li.querySelector('input');
                if (!input || input.id !== 'id1') {
                    li.remove();
                }
                else
                {
                    checkAll = input;
                }
            });

            // Add submenu items dynamically based on entity's relations
            const result = renderReferenceSubmenu(entity, submenu);
            const count = result.count;

            if(checkAll != null)
            {
                checkAll.checked = result.count == result.checked;
            }

            // Show or hide the "Add Reference" menu based on available relationships
            referenceMenu.style.display = count > 0 ? 'block' : 'none';

            // Automatically position submenu to the left or right based on screen space
            const viewportWidth = window.innerWidth;
            const shouldOpenLeft = e.pageX > (viewportWidth / 2);
            submenu.style.left = shouldOpenLeft ? '-240px' : '97%';
            submenu.style.right = shouldOpenLeft ? '97%' : 'auto';

        } else {
            contextMenu.style.display = "none";
        }
    });
}


/**
 * Populate the reference submenu with available foreign key relations.
 * @param {Element} entity - The SVG group element representing the entity.
 * @param {HTMLElement} submenu - The submenu DOM element to populate.
 * @returns {{count: number, checked: number}} Number of submenu items rendered and number of items initially checked.
 */
function renderReferenceSubmenu(entity, submenu) {
    const tableName = entity.dataset.entity;
    const columns = entity.querySelectorAll('.diagram-column-name');
    let count = 0;
    let checked = 0;

    columns.forEach((col, index) => {
        const columnName = col.textContent.trim();
        const refTable = columnName.substring(0, columnName.length - 3);

        if (refTable !== tableName && columnName.endsWith('_id')) {
            const testInput = document.querySelector(`.table-list input[data-name="${refTable}"]`);
            if (testInput) {
                const li = document.createElement('li');

                const input = document.createElement('input');
                const inputId = `reference-checkbox-${index}`;
                input.type = 'checkbox';
                input.dataset.name = refTable;
                input.id = inputId;

                const isChecked = isSelectedTable(refTable);
                input.checked = isChecked;
                if (isChecked) checked++;

                input.addEventListener('change', selectTable);

                const label = document.createElement('label');
                label.setAttribute('for', inputId);
                label.textContent = ` ${columnName}`; // Spasi sebelum teks agar rapi

                li.appendChild(input);
                li.appendChild(label);
                submenu.appendChild(li);
                count++;
            }
        }
    });

    return { count, checked };
}



/**
 * Render the context menu for a given entity.
 * @param {Element} entity - The SVG group element representing the entity.
 * @returns {number} The number of relation menu items rendered.
 */
function renderContextMenu(entity) {
    const contextMenu = document.querySelector('#context-menu');
    const ul = contextMenu.querySelector('ul');

    const tableName = entity.dataset.entity;
    const columns = entity.querySelectorAll('.diagram-column-name');
    let count = 0;

    columns.forEach((col, index) => {
        const columnName = col.textContent.trim();
        let table = columnName.substring(0, columnName.length - 3);
        if (table != tableName && columnName.endsWith('_id')) {
            let testInput = document.querySelector(`.table-list input[data-name="${table}"]`);
            if(testInput != null)
            {
                const li = document.createElement('li');
                li.dataset.type = 'relation';

                const label = document.createElement('label');
                const input = document.createElement('input');

                const inputId = `relation-${index}`;
                input.type = 'checkbox';
                input.id = inputId;
                input.dataset.name = table;
                input.addEventListener('change', function(e2){
                    selectTable(e2);
                });

                input.checked = isSelectedTable(table);

                label.setAttribute('for', inputId);
                label.appendChild(input);

                const relationText = ` ${columnName}`;
                label.appendChild(document.createTextNode(relationText));

                li.appendChild(label);
                ul.appendChild(li);
                count++;
            }
        }
    });
    return count;
}

/**
 * Select or deselect a table in the table list based on the context menu checkbox.
 * @param {Event} e - The change event from the context menu checkbox.
 * @returns {void}
 */
function selectTable(e)
{
    let table = e.target.dataset.name;
    let input = document.querySelector(`.table-list input[data-name="${table}"]`);
    if(input != null)
    {
        input.checked = e.target.checked;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

/**
 * Check if a table is currently selected in the table list.
 * @param {string} table - The table name to check.
 * @returns {boolean} True if the table is selected, false otherwise.
 */
function isSelectedTable(table)
{
    let input = document.querySelector(`.table-list input[data-name="${table}"]`);
    if(input)
    {
        return input.checked;
    }
    return false;
}