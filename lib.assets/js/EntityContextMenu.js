// Global variable to track which entity was right-clicked
let selectedElement = null;

document.addEventListener('DOMContentLoaded', function () {
    /**
     * Hide all context menus when clicking anywhere outside them.
     */
    document.addEventListener("click", function (e) {
        document.querySelectorAll('.context-menu').forEach(menu => {
            if (!e.target.closest(".context-menu")) {
                menu.style.display = "none";
            }
        });
    });
});

/**
 * Programmatically hides all context menus.
 */
function hideContextMenu() {
    document.querySelectorAll('.context-menu').forEach(menu => {
        menu.style.display = "none";
    });
}


/**
 * Initializes the context menu for entities within a diagram SVG.
 *
 * This function sets up a right-click (`contextmenu`) event listener on the provided `svg` element.
 * When a user right-clicks on an entity (`<g>` element with class `svg-entity`):
 *   - Updates the global `selectedElement` to the clicked entity.
 *   - Displays the custom context menu (`#context-menu`) near the mouse cursor.
 *   - Dynamically updates the "Reference" submenu (`#reference-submenu`) based on the entity's relationships.
 *   - Removes all existing submenu items except the first ("Check all") and adds new items via `renderReferenceSubmenu`.
 *   - Checks/unchecks the "Check all" item depending on the current selection.
 *   - Shows or hides the "Add Reference" menu (`#menu-make-reference`) depending on available relationships.
 *   - Positions the submenu automatically to the left or right based on available screen space.
 *
 * If the right-click does not occur on an entity, the context menu is hidden.
 *
 * @param {SVGSVGElement} svg - The SVG element containing diagram entities.
 *
 * @returns {void}
 *
 * @example
 * // Initialize diagram context menu for an SVG with id 'diagram-svg'
 * const svg = document.getElementById('diagram-svg');
 * initDiagramContextMenu(svg);
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
            submenu.style.left = shouldOpenLeft ? '-240px' : '96%';
            submenu.style.right = shouldOpenLeft ? '96%' : 'auto';

        } else {
            contextMenu.style.display = "none";
        }
    });
}

/**
 * Initializes the context menu for all entities within a given SVG element.
 *
 * This function sets up a right-click (`contextmenu`) event listener on the provided `svg` element.
 * When a user right-clicks on an entity (`<g>` element with class `svg-entity`):
 *   - The `selectedElement` is updated to the clicked entity.
 *   - The custom context menu (`#context-menu-all-entities`) is displayed near the mouse cursor.
 *
 * If the right-click does not occur on an entity, the context menu is hidden.
 *
 * @param {SVGSVGElement} svg - The SVG element containing all entity groups.
 *
 * @returns {void}
 *
 * @example
 * // Initialize context menu for an SVG with id 'diagram-svg'
 * const svg = document.getElementById('diagram-svg');
 * initAllEntitiesContextMenu(svg);
 */
function initAllEntitiesContextMenu(svg) {
    const contextMenu = document.querySelector('#context-menu-all-entities');

    svg.addEventListener("contextmenu", function (e) {
        const entity = e.target.closest('g.svg-entity');
        selectedElement = entity;

        if (entity) {
            e.preventDefault(); // Prevent the default browser context menu

            // Position context menu near the mouse click
            contextMenu.style.top = `${e.pageY}px`;
            contextMenu.style.left = `${e.pageX}px`;
            contextMenu.style.display = "block";
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
    const columns = entity.querySelectorAll('.svg-column-name');
    let count = 0;
    let checked = 0;

    columns.forEach((col, index) => {
        const columnName = col.textContent.trim();
        const refTable = columnName.substring(0, columnName.length - 3);

        if (refTable !== tableName && columnName.endsWith('_id')) {
            const testInput = document.querySelector(`.table-list input[data-name="${refTable}"]`);
            if (testInput) {
                const li = document.createElement('li');

                const label = document.createElement('label');
                label.textContent = ` ${columnName}`; // teks label

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.dataset.name = refTable;

                const isChecked = isSelectedTable(refTable);
                input.checked = isChecked;
                if (isChecked) checked++;

                input.addEventListener('change', selectTable);

                label.insertBefore(input, label.firstChild);

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
    const columns = entity.querySelectorAll('.svg-column-name');
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