let selectedElement = null;
document.addEventListener('DOMContentLoaded', function () {

    // Hide context menu when clicking elsewhere
    document.addEventListener("click", function (e) {
        const contextMenu = document.querySelector('#context-menu');
        if (!e.target.closest("#context-menu")) {
            contextMenu.style.display = "none";
        }
    });
});

/**
 * Initialize the context menu for the entity diagram SVG.
 * @param {SVGElement} svg - The SVG element containing the entity diagram.
 */
function initDiagramContextMenu(svg)
{
    const contextMenu = document.querySelector('#context-menu');
    svg.addEventListener("contextmenu", function (e) {
        const entity = e.target.closest('g.svg-entity');
        if (entity) {
            e.preventDefault();

            renderContextMenu(entity);
            selectedElement = entity;
            contextMenu.style.top = `${e.pageY}px`;
            contextMenu.style.left = `${e.pageX}px`;
            contextMenu.style.display = "block";
        } else {
            contextMenu.style.display = "none";
        }
    });
}

/**
 * Render the context menu for a given entity.
 * @param {Element} entity - The SVG group element representing the entity.
 */
function renderContextMenu(entity) {
    const contextMenu = document.querySelector('#context-menu');
    const ul = contextMenu.querySelector('ul');
    ul.innerHTML = ''; // Clear previous menu

    const tableName = entity.dataset.entity;
    const columns = entity.querySelectorAll('.diagram-column-name');

    columns.forEach((col, index) => {
        const columnName = col.textContent.trim();

        if (!columnName.startsWith(tableName) && columnName.endsWith('_id')) {

            let table = columnName.substring(0, columnName.length - 3);

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
        }
    });
}

/**
 * Select or deselect a table in the table list based on the context menu checkbox.
 * @param {Event} e - The change event from the context menu checkbox.
 */
function selectTable(e)
{
    let table = e.target.dataset.name;
    let input = document.querySelector(`.table-list input[data-name="${table}"]`);
    input.checked = e.target.checked;
    input.dispatchEvent(new Event('change', { bubbles: true }));
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