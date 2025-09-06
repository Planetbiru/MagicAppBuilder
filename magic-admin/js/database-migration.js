/**
 * @fileoverview Main script for a web-based data mapping and configuration editor.
 * This script manages application state, user interface interactions, data import/export,
 * and utility functions for a data migration tool.
 */

// --- State ---
// Global application state holding configuration, tables, and scripts
const state = {
    /** @type {object} - Configuration for the target database. */
    databaseTarget: { driver: '', host: '', port: '', username: '', password: '', databaseFilePath: '', databaseName: '', databseSchema: '', timeZone: '' },
    /** @type {object} - Configuration for the source database. */
    databaseSource: { driver: '', host: '', port: '', username: '', password: '', databaseFilePath: '', databaseName: '', databseSchema: '', timeZone: '' },
    /** @type {number} - The maximum number of records to process. */
    maximumRecord: 100,
    /** @type {Array<object>} - A list of table mapping configurations. */
    table: []
};

// --- Utilities ---

/**
 * Enables smooth scroll with inertia-like animation when using mouse wheel.
 * @param {HTMLElement} el - The DOM element on which to enable smooth scrolling.
 */
function enableSmoothScroll(el) {
    let scrollPos = 0;
    let isScrolling;

    el.addEventListener("wheel", e => {
        // Prevent default browser scroll behavior
        e.preventDefault();
        scrollPos += e.deltaY;
        // Cancel any pending animation frame
        cancelAnimationFrame(isScrolling);

        const start = el.scrollTop;
        const end = scrollPos;
        const duration = 400;
        const startTime = performance.now();

        /**
         * The animation loop for smooth scrolling.
         * @param {DOMHighResTimeStamp} time - The current time provided by requestAnimationFrame.
         */
        function animate(time) {
            // Calculate progress (t) from 0 to 1
            const t = Math.min(1, (time - startTime) / duration);
            // Apply easing function and update scrollTop
            el.scrollTop = start + (end - start) * easeOutCubic(t);
            // Continue animation if not finished
            if (t < 1) {
                isScrolling = requestAnimationFrame(animate);
            }
        }
        // Start the animation
        requestAnimationFrame(animate);
    }, { passive: false });

    /**
     * Easing function for a cubic-out effect.
     * @param {number} t - The progress of the animation (0 to 1).
     * @returns {number} - The eased value.
     */
    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }
}

/**
 * Displays a custom modal alert.
 * @param {string} titleKey - The key for the alert title from a template.
 * @param {string} messageKey - The key or the message text for the alert body.
 * @param {object} [vars={}] - Optional variables to replace placeholders in the message.
 */
function customAlert(titleKey, messageKey, vars = {}) {
    // Replace placeholders in the message with provided variables
    let template = dialogTemplate[messageKey] || messageKey;
    let message = template.replace(/\{(\w+)\}/g, (_, k) => vars[k] ?? '');
    let title = dialogTemplate[titleKey] || titleKey;
    
    // Update and show the modal
    $('#commonModal .modal-title').text(title);
    $('#commonModal .modal-body').text(message);
    $('#commonModal').modal('show');
};


/**
 * Sets a nested property of an object using a dot-separated path string (e.g., "a.b.c").
 * @param {object} obj - The object to modify.
 * @param {string} path - The dot-separated path string.
 * @param {*} val - The value to set.
 */
function deepSet(obj, path, val) {
    const parts = path.split('.');
    let cur = obj;
    // Traverse the object path, creating properties if they don't exist
    for (let i = 0; i < parts.length - 1; i++) {
        const k = parts[i];
        if (!(k in cur)) cur[k] = {};
        cur = cur[k];
    }
    // Set the final property value, handling number type conversion
    cur[parts[parts.length - 1]] = (typeof cur[parts[parts.length - 1]] === 'number') ? Number(val) : val;
}

/**
 * Triggers a download of a text file.
 * @param {string} filename - The name for the downloaded file.
 * @param {string} text - The content of the file.
 */
function download(filename, text) {
    const a = document.createElement('a');
    // Create a temporary URL for the Blob
    a.href = URL.createObjectURL(new Blob([text], { type: 'text/plain' }));
    a.download = filename;
    // Programmatically click the link to start the download
    a.click();
    // Clean up the temporary URL
    URL.revokeObjectURL(a.href);
}

// --- Minimal YAML Serializer ---
/**
 * Converts a JavaScript object or value to a minimal YAML string.
 * @param {*} value - The value to convert.
 * @param {number} [indent=0] - The current indentation level.
 * @returns {string} - The YAML string.
 */
function toYAML(value, indent = 0) {
    const pad = '  '.repeat(indent);
    if (value === null) return 'null';
    // Handle arrays
    if (Array.isArray(value)) {
        if (value.length === 0) return '[]';
        return value.map(v => pad + '- ' + formatYAMLValue(v, indent + 1)).join('\n')
            .replaceAll('\n' + pad + '  -', '\n' + pad + '-');
    }
    // Handle objects
    if (typeof value === 'object') {
        const keys = Object.keys(value);
        if (keys.length === 0) return '{}';
        return keys.map(k => {
            const v = value[k];
            const key = escapeKey(k);
            if (isScalar(v)) {
                return pad + key + ': ' + scalarToYAML(v);
            } else {
                return pad + key + ':\n' + toYAML(v, indent + 1);
            }
        }).join('\n');
    }
    // Handle scalar values
    return scalarToYAML(value);
}

/**
 * Formats a value for YAML output.
 * @param {*} v - The value to format.
 * @param {number} indent - The current indentation level.
 * @returns {string} - The formatted YAML string snippet.
 */
function formatYAMLValue(v, indent) {
    if (isScalar(v)) return scalarToYAML(v);
    if (Array.isArray(v)) {
        if (v.every(isScalar)) {
            return v.map((x, i) => (i === 0 ? '' : '\n' + '  '.repeat(indent)) + '- ' + scalarToYAML(x)).join('');
        }
        return '\n' + toYAML(v, indent);
    }
    return '\n' + toYAML(v, indent);
}
/**
 * Checks if a value is a scalar (null, string, number, or boolean).
 * @param {*} v - The value to check.
 * @returns {boolean} - True if the value is a scalar.
 */
function isScalar(v) {
    return v == null || ['string', 'number', 'boolean'].includes(typeof v);
}
/**
 * Determines if a string needs to be quoted in YAML.
 * @param {string} str - The string to check.
 * @returns {boolean} - True if the string requires quotes.
 */
function needsQuote(str) {
    return str === '' ||
        // Check for special characters, leading/trailing spaces, or reserved keywords
        /[:#\-?&*!|>'"%@`{}\[\],\n\r\t]/.test(str) ||
        /^\s|\s$/.test(str) ||
        /^(true|false|null|~|yes|no|on|off)$/i.test(str);
}
/**
 * Converts a scalar value to its YAML string representation.
 * @param {*} v - The scalar value.
 * @returns {string} - The YAML string.
 */
function scalarToYAML(v) {
    if (v == null) return 'null';
    if (typeof v === 'boolean') return v ? 'true' : 'false';
    if (typeof v === 'number') return String(v);
    let s = String(v);
    // Quote the string if necessary
    return needsQuote(s) ? JSON.stringify(s) : s;
}
/**
 * Escapes a key for YAML output.
 * @param {string} k - The key string.
 * @returns {string} - The escaped key string.
 */
function escapeKey(k) {
    return needsQuote(k) ? JSON.stringify(k) : k;
}

// --- Preview ---
/**
 * Refreshes the JSON and YAML previews in the UI.
 */
function refreshPreviews() {
    const json = JSON.stringify(state, null, 2);
    $('#previewJson').text(json);
    $('#previewYaml').text(toYAML(state));
}

// --- Table ---
/**
 * Adds a new table mapping row to the UI.
 * @param {object} [entry={}] - The table entry data to pre-populate the row.
 * @param {string} [entry.target] - The target table name.
 * @param {string} [entry.source] - The source table name.
 * @param {Array<string|object>} [entry.map] - Column mapping entries.
 * @param {Array<string>} [entry.preImportScript] - Pre-import scripts.
 * @param {Array<string>} [entry.postImportScript] - Post-import scripts.
 * @param {boolean} [focus=false] - Whether to set focus on the newly added row.
 * @returns {jQuery} - The jQuery object for the new row.
 */
function addTableRow(entry = { target: '', source: '', map: [], preImportScript: [], postImportScript: [] }, focus = false) {
    // Clone template and set initial values
    const node = $($('#tplTableItem').html().trim());
    const inTarget = node.find('.in-target'); inTarget.val(entry.target || '');
    const inSource = node.find('.in-source'); inSource.val(entry.source || '');
    const details = node.find('details'); const btnToggle = node.find('.btn-toggle');

    // Toggle details section on button click
    btnToggle.on('click', () => {
        details.prop('open', !details.prop('open'));
        btnToggle.text(details.prop('open') ? 'Detail ▾' : 'Detail ▸');
    });

    // Map
    const mapList = node.find('.map-list');
    /**
     * Adds a new column mapping row to the UI.
     * @param {object} pair - The column pair to add.
     * @param {boolean} [focus=false] - Whether to set focus on the new row.
     */
    function addMapRow(pair, focus = false) {
        const r = $($('#tplMapRow').html().trim());
        const t = r.find('.in-target-col'); const s = r.find('.in-source-col');
        t.val(pair?.target || ''); s.val(pair?.source || '');
        // Attach event handlers for removal and syncing
        r.find('.btn-del-map').on('click', () => { r.remove(); syncFromUI(); });
        t.on('input', syncFromUI); s.on('input', syncFromUI);
        mapList.append(r);
        if (focus) t.focus();
    }
    // Add map row on button click
    node.find('.btn-add-map').on('click', () => addMapRow({}, true));
    // Populate existing map entries
    (entry.map || []).forEach(m => {
        if (typeof m === 'string' && m.includes(':')) {
            const [left, right] = m.split(':');
            addMapRow({ target: left.trim(), source: right.trim() });
        } else if (m && typeof m === 'object') addMapRow(m);
    });

    // Pre-import script
    const preScriptList = node.find('.pre-script-list');
    /**
     * Adds a new pre-import script row.
     * @param {string} [text=''] - The script text.
     * @param {boolean} [focus=false] - Whether to set focus on the new row.
     */
    function addPreScriptRow(text = '', focus = false) {
        const r = $($('#tplScriptRow').html().trim());
        const s = r.find('.in-script'); s.val(text);
        r.find('.btn-del-script').on('click', () => { r.remove(); syncFromUI(); });
        s.on('input', syncFromUI);
        preScriptList.append(r);
        if (focus) s.focus();
    }
    node.find('.btn-add-pre-import-script').on('click', () => addPreScriptRow('', true));
    (entry.preImportScript || []).forEach(addPreScriptRow);

    // Post-import script
    const postScriptList = node.find('.post-script-list');
    /**
     * Adds a new post-import script row.
     * @param {string} [text=''] - The script text.
     * @param {boolean} [focus=false] - Whether to set focus on the new row.
     */
    function addPostScriptRow(text = '', focus = false) {
        const r = $($('#tplScriptRow').html().trim());
        const s = r.find('.in-script'); s.val(text);
        r.find('.btn-del-script').on('click', () => { r.remove(); syncFromUI(); });
        s.on('input', syncFromUI);
        postScriptList.append(r);
        if (focus) s.focus();
    }
    node.find('.btn-add-post-import-script').on('click', () => addPostScriptRow('', true));
    (entry.postImportScript || []).forEach(addPostScriptRow);

    // Control buttons
    node.find('.btn-del').on('click', () => { node.remove(); syncFromUI(); });
    node.find('.btn-up').on('click', () => { if (node.prev().length) { $('#tableList').insertBefore(node[0], node.prev()[0]); syncFromUI(); } });
    node.find('.btn-down').on('click', () => { if (node.next().length) { $('#tableList').insertBefore(node.next()[0], node[0]); syncFromUI(); } });
    inTarget.on('input', syncFromUI);
    inSource.on('input', syncFromUI);

    // Drag reorder
    node.on('dragstart', e => { 
        $('body').css('cursor','move');
        node.addClass('dragging'); 
        // Required for Firefox to start drag operation
        e.originalEvent.dataTransfer.setData('text/plain','');
    });
    node.on('drag', () => $('body').css('cursor','move'));
    node.on('dragend', () => { 
        $('body').css('cursor','');
        node.removeClass('dragging'); 
        syncFromUI();
    });

    $('#tableList').append(node);
    syncFromUI();
    if (focus) inTarget.focus();
    return node;
}

/**
 * Finds the element to place a dragged element before.
 * @param {jQuery} container - The container element.
 * @param {number} y - The Y-coordinate of the drag event.
 * @returns {HTMLElement|null} - The element to insert before, or null if appending.
 */
function getDragAfterElement(container, y) {
    // Get all non-dragging elements
    const els = container.find('.table-item:not(.dragging)').toArray();
    return els.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        // Calculate the offset from the center of the child element
        const offset = y - box.top - box.height / 2;
        // Find the element with the smallest negative offset
        if (offset < 0 && offset > closest.offset) return { offset, element: child };
        else return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// --- Sync ---
/**
 * Synchronizes the UI state back to the global `state` object.
 * This function reads all input values from the form and updates `state.table`.
 */
function syncFromUI() {
    state.table = $('#tableList .table-item').toArray().map(item => {
        const $item = $(item);
        const target = $item.find('.in-target').val().trim();
        const source = $item.find('.in-source').val().trim();
        // Map column pairs from UI to state
        const map = $item.find('.map-list .map-row').toArray().map(r => {
            const $r = $(r);
            const t = $r.find('.in-target-col').val().trim();
            const s = $r.find('.in-source-col').val().trim();
            return t || s ? `${t} : ${s}` : null;
        }).filter(Boolean);
        // Map pre-import scripts
        const preImportScript = $item.find('.pre-script-list .map-row .in-script').toArray()
            .map(s => $(s).val().trim()).filter(Boolean);
        // Map post-import scripts
        const postImportScript = $item.find('.post-script-list .map-row .in-script').toArray()
            .map(s => $(s).val().trim()).filter(Boolean);
        const entry = { target, source };
        if (map.length) entry.map = map;
        if (preImportScript.length) entry.preImportScript = preImportScript;
        if (postImportScript.length) entry.postImportScript = postImportScript;
        return entry;
    });
    refreshPreviews();
    saveDraft();
}

/**
 * Loads the application state from a data object.
 * @param {object} data - The data object to load.
 */
function loadState(data) {
    // Warn if essential properties are missing
    ['databaseTarget','databaseSource','maximumRecord','table'].forEach(k => { if (!(k in data)) console.warn('Missing property:',k); });
    // Merge loaded data into the state
    Object.assign(state.databaseTarget, data.databaseTarget || {});
    Object.assign(state.databaseSource, data.databaseSource || {});
    state.maximumRecord = Number(data.maximumRecord) || 0;
    // Normalize and load table data
    state.table = Array.isArray(data.table) ? data.table.map(normalizeEntry) : [];

    // Update UI elements from the state
    $('#configForm input').each((i,inp) => {
        const path = $(inp).data('path');
        const v = path.split('.').reduce((o,k)=>o&&o[k], state);
        if (typeof v !== 'undefined') $(inp).val(v);
    });

    $('#tableList').empty();
    state.table.forEach(addTableRow);
    refreshPreviews();
    saveDraft();
}

/**
 * Normalizes a table entry object to a consistent format.
 * @param {object} e - The table entry to normalize.
 * @returns {object} - The normalized entry.
 */
function normalizeEntry(e) {
    const out = { target: e.target || '', source: e.source || '' };
    // Convert old map format to new string format if necessary
    if (e.map) out.map = e.map.map(m => typeof m === 'string' ? m : `${m.target||''} : ${m.source||''}`);
    if (e.preImportScript) out.preImportScript = [...e.preImportScript];
    if (e.postImportScript) out.postImportScript = [...e.postImportScript];
    return out;
}

// --- Local Storage ---
// Key for local storage draft
const DRAFT_KEY = 'mapping-editor-draft-v1';
/**
 * Saves the current state to local storage as a draft.
 * @param {boolean} [force] - If true, logs a success message.
 * @returns {string|null} - The JSON string of the saved state, or null on error.
 */
function saveDraft(force) { 
    try { 
        const draft = JSON.stringify(state); 
        localStorage.setItem(DRAFT_KEY, draft); 
        if (force) console.log('draft saved'); 
        return draft;
    } catch (e) { 
        console.warn('Failed to save draft to local storage:', e); 
        return null;
    } 
}
/**
 * Loads the draft state from local storage.
 * @returns {object|null} - The parsed state object, or null if no draft exists or an error occurs.
 */
function loadDraft() { 
    try { 
        const s = localStorage.getItem(DRAFT_KEY); 
        return s ? JSON.parse(s) : null; 
    } catch (e) { 
        console.warn('Failed to load draft from local storage:', e);
        return null; 
    } 
}

// --- DOM Ready ---
jQuery(function(){
    // Config binding: Sync UI input changes to the state
    $('#configForm input').on('input', function(){
        deepSet(state, $(this).data('path'), this.type==='number'?Number($(this).val()):$(this).val());
        refreshPreviews();
        saveDraft();
    });

    // Drag reorder handler: Manage drag-and-drop for table rows
    const listEl = $('#tableList');
    listEl.on('dragover', e => {
        e.preventDefault();
        const after = getDragAfterElement(listEl, e.originalEvent.clientY);
        const dragging = $('.dragging');
        if (!dragging.length) return;
        // Reorder the elements in the DOM
        if (after == null) listEl.append(dragging);
        else $(after).before(dragging);
    });

    // Buttons
    $('#btnAddTable').on('click', () => addTableRow({}, true));
    $('#btnClearAll').on('click', () => { 
        listEl.empty(); 
        state.table=[]; 
        refreshPreviews(); 
        saveDraft(); 
    });

    // File input display: Show selected file name in the UI
    $('#fileInput').on('change', e => {
        if ($('#selectedFile').length) {
            $('#selectedFile').text(e.target.files[0].name);
        }
    });

    // Import: Handle file import (JSON or YAML)
    $('#btnImportJson').on('click', () => {
        const fileInput = document.getElementById('fileInput');
        if (!fileInput.files.length) {
            return customAlert('alert', 'info_select_file');
        }

        const file = fileInput.files[0];
        const reader = new FileReader();

        reader.onload = function(e){
            try {
                const buffer = e.target.result;
                const uint8 = new Uint8Array(buffer);

                // Detect Byte Order Mark (BOM) to determine encoding
                let encoding = 'utf-8';
                if (uint8[0]===0xFF && uint8[1]===0xFE) encoding='utf-16le';
                else if (uint8[0]===0xFE && uint8[1]===0xFF) encoding='utf-16be';
                else if (uint8[0]===0xFF && uint8[1]===0xFE && uint8[2]===0x00 && uint8[3]===0x00) encoding='utf-32le';
                else if (uint8[0]===0x00 && uint8[1]===0x00 && uint8[2]===0xFE && uint8[3]===0xFF) encoding='utf-32be';

                const decoder = new TextDecoder(encoding);
                const content = decoder.decode(uint8).trim();

                let data;
                // Attempt to parse as JSON or YAML
                if (content.startsWith("{")) {
                    try { 
                        data = JSON.parse(content); 
                        loadState(data); 
                    }
                    catch(jsonErr){ 
                        customAlert('error', 'error_parse_json', {msg: jsonErr.message}); 
                        return; 
                    }
                } else {
                    try { 
                        // jsyaml.load is an external library function
                        data = jsyaml.load(content); 
                        loadState(data); 
                    }
                    catch(yamlErr){ 
                        customAlert('error', 'error_parse_yaml', {msg: yamlErr.message}); 
                        return; 
                    }
                }
            } catch(err){ 
                customAlert('error', 'error_read_file', {msg: err.message}); 
            }
        };

        reader.onerror = function(){ 
            customAlert('error', 'error_reader', {msg: (reader.error?.message || reader.error?.name)}); 
        };

        // Read file as an ArrayBuffer to detect encoding
        reader.readAsArrayBuffer(file);
    });

    // Export: Download current state as JSON or YAML
    $('#btnDownloadJson').on('click', () => 
        download('mapping.json', JSON.stringify(state,null,2))
    );
    $('#btnDownloadYaml').on('click', () => 
        download('mapping.yml', toYAML(state))
    );

    // Local storage: Save and load drafts
    $('#btnSaveLocal').on('click', () => { 
        saveDraft(true); 
        customAlert('information', 'success_save_local'); 
    });
    $('#btnLoadLocal').on('click', () => { 
        const d = loadDraft(); 
        if(d) loadState(d); 
        else customAlert('information', 'error_no_draft'); 
    });

    // Init: Initial setup when the DOM is ready
    if ($('#selectedFile').length) {
        const fi=document.getElementById('fileInput');
        if (fi.files.length) $('#selectedFile').text(fi.files[0].name);
    }

    $('#btnAutogenerate').on('click', () => {
        let targetDriver = $('[data-path="databaseTarget.driver"]').val();
        let targetHost = $('[data-path="databaseTarget.host"]').val();
        let targetPort = $('[data-path="databaseTarget.port"]').val();
        let targetUsername = $('[data-path="databaseTarget.username"]').val();
        let targetPassword = $('[data-path="databaseTarget.password"]').val();
        let targetDatabaseFilePath = $('[data-path="databaseTarget.databaseFilePath"]').val();
        let targetDatabaseName = $('[data-path="databaseTarget.databaseName"]').val();
        let targetDatabaseSchema = $('[data-path="databaseTarget.databseSchema"]').val();
        let targetTimeZone = $('[data-path="databaseTarget.timeZone"]').val();

        let sourceDriver = $('[data-path="databaseSource.driver"]').val();
        let sourceHost = $('[data-path="databaseSource.host"]').val();
        let sourcePort = $('[data-path="databaseSource.port"]').val();
        let sourceUsername = $('[data-path="databaseSource.username"]').val();
        let sourcePassword = $('[data-path="databaseSource.password"]').val();
        let sourceDatabaseFilePath = $('[data-path="databaseSource.databaseFilePath"]').val();
        let sourceDatabaseName = $('[data-path="databaseSource.databaseName"]').val();
        let sourceDatabaseSchema = $('[data-path="databaseSource.databseSchema"]').val();
        let sourceTimeZone = $('[data-path="databaseSource.timeZone"]').val();

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {
                targetDriver: targetDriver,
                targetHost: targetHost,
                targetPort: targetPort,
                targetUsername: targetUsername,
                targetPassword: targetPassword,
                targetDatabaseFilePath: targetDatabaseFilePath,
                targetDatabaseName: targetDatabaseName,
                targetDatabaseSchema: targetDatabaseSchema,
                targetTimeZone: targetTimeZone,
                sourceDriver: sourceDriver,
                sourceHost: sourceHost,
                sourcePort: sourcePort,
                sourceUsername: sourceUsername,
                sourcePassword: sourcePassword,
                sourceDatabaseFilePath: sourceDatabaseFilePath,
                sourceDatabaseName: sourceDatabaseName,
                sourceDatabaseSchema: sourceDatabaseSchema,
                sourceTimeZone: sourceTimeZone
            },
            url: '../lib.ajax/database-migration-autogenerate.php',
            success: function (data) {
                if (data.success) {
                    loadState(data.data);
                    customAlert('information', 'success_message');
                } else {
                    customAlert('error', 'error_message', { msg: data.message });
                }
            },
            error: function (xhr, status, error) {
                customAlert('error', 'error_message', { msg: error });
            }
        });

    });

    const draft=loadDraft();
    // Load draft or sample data on page load
    if(draft) loadState(draft); else loadState(sampleData());
});