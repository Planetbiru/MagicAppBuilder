let modified = true;
let cmEditorModule = null;
let cmEditorFile = null;
let cmEditorSQL = null;
let cmEditorSQLExecute = null;
let cmEditorValidator = null; // New editor variable
let transEd1 = null;
let transEd2 = null;
let transEd3 = null;
let transEd4 = null;
let transEd5 = null;
let transEd6 = null;
let currentTab = "";
let lastLine1 = -1;
let lastLine2 = -1;
let focused = {};
let modeInput = null;
let fileManagerEditor = null;

/**
 * Clears the content of the module editor.
 *
 * This function resets the content of the `cmEditorModule` editor by setting
 * its value to an empty string and refreshing it to ensure updates are visible.
 */
function clearModuleFile() {
    cmEditorModule.getDoc().setValue('');
    setTimeout(function () {
        cmEditorModule.refresh();
    }, 1);
}

/**
 * Clears the content of the entity file editor.
 *
 * This function resets the content of the `cmEditorFile` editor by setting
 * its value to an empty string and refreshing it.
 */
function clearEntityFile() {
    cmEditorFile.getDoc().setValue('');
    setTimeout(function () {
        cmEditorFile.refresh();
    }, 1);
}

/**
 * Sets the content of the entity file editor.
 *
 * @param {string} content - The new content to be set in the editor.
 */
function setEntityFile(content) {
    cmEditorFile.getDoc().setValue(content);
    setTimeout(function () {
        cmEditorFile.refresh();
    }, 1);
}

/**
 * Clears the content of the validator editor.
 *
 * This function resets the content of the `cmEditorValidator` editor by setting
 * its value to an empty string and refreshing it.
 */
function clearValidatorFile() {
    cmEditorValidator.getDoc().setValue('');
    setTimeout(function () {
        cmEditorValidator.refresh();
    }, 1);
}

/**
 * Sets the content of the validator editor.
 *
 * @param {string} content - The new content to be set in the editor.
 */
function setValidatorFile(content) {
    cmEditorValidator.getDoc().setValue(content);
    setTimeout(function () {
        cmEditorValidator.refresh();
    }, 1);
}

/**
 * Clears the content of the SQL editor.
 *
 * This function resets the content of the `cmEditorSQL` editor by setting
 * its value to an empty string and refreshing it.
 */
function clearEditorSQL() {
    cmEditorSQL.getDoc().setValue('');
    setTimeout(function () {
        cmEditorSQL.refresh();
    }, 1);
}

/**
 * Clears the content of the SQL execute editor.
 *
 * This function resets the content of the `cmEditorSQLExecute` editor by setting
 * its value to an empty string and refreshing it.
 */
function clearEditorSQLExecute() {
    cmEditorSQLExecute.getDoc().setValue('');
    setTimeout(function () {
        cmEditorSQLExecute.refresh();
    }, 1);
}

/**
 * Clears the content of the first translation editor.
 */
function clearTtransEd1() {
    transEd1.getDoc().setValue('');
    setTimeout(function () {
        transEd1.refresh();
    }, 1);
}

/**
 * Clears the content of the second translation editor.
 */
function clearTtransEd2() {
    transEd2.getDoc().setValue('');
    setTimeout(function () {
        transEd2.refresh();
    }, 1);
}

/**
 * Clears the content of the third translation editor.
 */
function clearTtransEd3() {
    transEd3.getDoc().setValue('');
    setTimeout(function () {
        transEd3.refresh();
    }, 1);
}

/**
 * Clears the content of the fourth translation editor.
 */
function clearTtransEd4() {
    transEd4.getDoc().setValue('');
    setTimeout(function () {
        transEd4.refresh();
    }, 1);
}

function clearTtransEd5() {
    transEd5.getDoc().setValue('');
    setTimeout(function () {
        transEd5.refresh();
    }, 1);
}

function clearTtransEd6() {
    transEd6.getDoc().setValue('');
    setTimeout(function () {
        transEd6.refresh();
    }, 1);
}

/**
 * Formats the content of all editors using auto-formatting.
 *
 * This function calculates the total number of lines in each editor and applies
 * automatic formatting to improve code readability.
 */
function format() {
    let totalLinesModule = cmEditorModule.lineCount();
    cmEditorModule.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesModule });

    let totalLinesFile = cmEditorFile.lineCount();
    cmEditorFile.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesFile });

    let totalLinesValidator = cmEditorValidator.lineCount();
    cmEditorValidator.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesValidator });

    let totalLinesSql = cmEditorSQL.lineCount();
    cmEditorSQL.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesSql });

    let totalLinesSqlExecute = cmEditorSQLExecute.lineCount();
    cmEditorSQLExecute.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesSqlExecute });

    let totalLinesEntityTran1 = transEd1.lineCount();
    let totalLinesEntityTran2 = transEd2.lineCount();

    transEd1.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran1 });
    transEd2.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran2 });

    let totalLinesEntityTran3 = transEd3.lineCount();
    let totalLinesEntityTran4 = transEd4.lineCount();

    transEd3.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran3 });
    transEd4.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran4 });

    let totalLinesAppTran5 = transEd5.lineCount();
    let totalLinesAppTran6 = transEd6.lineCount();

    transEd5.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesAppTran5 });
    transEd6.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesAppTran6 });
}

/**
 * Returns whether the given element is hidden (display:none or visibility:hidden).
 * @param {HTMLElement} el - The HTML element to check.
 * @returns {boolean} True if the element is hidden, false otherwise.
 */
function isHidden(el) {
    return el.display == 'none' ||
        el.visibility == 'hidden';
}

let initEditor = function () {

    $('#modal-query-executor').on('shown.bs.modal', function () {
        cmEditorSQLExecute.refresh();
        cmEditorSQLExecute.focus();
        $('.button-execute-query')[0].disabled = false;
    });
    $('#maintab').on("shown.bs.tab", function (e) {
        let currId = $(e.target).attr("id");
        currentTab = currId;
        if (currId == 'entity-file-tab') {
            cmEditorFile.focus();
            cmEditorFile.refresh();
        }
        if (currId == 'validator-file-tab') { // New tab handler for validator editor
            cmEditorValidator.focus();
            cmEditorValidator.refresh();
        }
        if (currId == 'entity-query-tab') {
            cmEditorSQL.focus();
            cmEditorSQL.refresh();
        }

        if (currId == 'translate-entity-tab') {
            transEd1.refresh();
            transEd2.refresh();
        }

        if (currId == 'translate-application-tab') {
            transEd3.refresh();
            transEd4.refresh();
        }

        if (currId == 'module-file-tab') {
            cmEditorModule.refresh();
        }

        if (currId == 'file-manager-tab') {
            fileManagerEditor.refresh();
        }

        if (currId == 'translate-app-tab') {
            transEd5.refresh();
            transEd6.refresh();
        }
    });

    $(document).on('keydown', function (e) {
        if ((e.which == '115' || e.which == '83') && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            if (currentTab == 'module-file-tab') {
                saveModule();
            }
            if (currentTab == 'entity-file-tab') {
                saveEntity();
            }
            if (currentTab == 'validator-file-tab') { // Save handler for validator editor
                saveValidator(); // Assuming you'll have a saveValidator function
            }
            if (currentTab == 'entity-query-tab') {
                saveQuery();
            }
            return false;
        } else {
            return true;
        }
    });
    CodeMirror.modeURL = "lib.assets/cm/mode/%N/%N.js";

    cmEditorModule = CodeMirror.fromTextArea(
        document.querySelector(".module-file"),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
            extraKeys: {
                "Ctrl-S": function (instance) {
                    saveModule();
                }
            }
        }
    );
    cmEditorFile = CodeMirror.fromTextArea(
        document.querySelector(".entity-file"),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
        }
    );

    // New CodeMirror instance for cmEditorValidator
    cmEditorValidator = CodeMirror.fromTextArea(
        document.querySelector(".validator-file"), // Assuming a class 'validator-file' in your HTML
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
        }
    );

    cmEditorSQL = CodeMirror.fromTextArea(
        document.querySelector(".entity-query"),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
        }
    );

    cmEditorSQLExecute = CodeMirror.fromTextArea(
        document.querySelector("#query_to_execute"),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
        }
    );

    transEd1 = CodeMirror.fromTextArea(
        document.querySelector('.entity-translate-original'),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
            readOnly: true
        }
    );

    transEd2 = CodeMirror.fromTextArea(
        document.querySelector('.entity-translate-target'),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
        }
    );

    transEd1.on('focus', function () {
        focused['transEd1'] = true;
        hilightLine1();
    });
    transEd2.on('focus', function () {
        focused['transEd2'] = true;
        hilightLine2();
    });
    transEd1.on('cursorActivity', function () {
        if (typeof focused['transEd1'] != 'undefined') {
            hilightLine1();
        }
    });

    transEd2.on('cursorActivity', function () {
        if (typeof focused['transEd2'] != 'undefined') {
            hilightLine2();
        }
    });

    transEd3 = CodeMirror.fromTextArea(
        document.querySelector('.module-translate-original'),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
            readOnly: true
        }
    );

    transEd4 = CodeMirror.fromTextArea(
        document.querySelector('.module-translate-target'),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
        }
    );

    transEd5 = CodeMirror.fromTextArea(
        document.querySelector('.app-translate-original'),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
            readOnly: true
        }
    );

    transEd6 = CodeMirror.fromTextArea(
        document.querySelector('.app-translate-target'),
        {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
        }
    );

    transEd3.on('focus', function () {
        focused['transEd3'] = true;
        hilightLine3();
    });
    transEd4.on('focus', function () {
        focused['transEd4'] = true;
        hilightLine4();
    });
    transEd3.on('cursorActivity', function () {
        if (typeof focused['transEd3'] != 'undefined') {
            hilightLine3();
        }
    });

    transEd4.on('cursorActivity', function () {
        if (typeof focused['transEd4'] != 'undefined') {
            hilightLine4();
        }
    });

    transEd5.on('focus', function () {
        focused['transEd5'] = true;
        hilightLine5();
    });
    transEd6.on('focus', function () {
        focused['transEd6'] = true;
        hilightLine6();
    });
    transEd5.on('cursorActivity', function () {
        if (typeof focused['transEd5'] != 'undefined') {
            hilightLine5();
        }
    });

    transEd6.on('cursorActivity', function () {
        if (typeof focused['transEd6'] != 'undefined') {
            hilightLine6();
        }
    });


    // Initial mode setting for editors
    // These blocks set the mode for the *initial state* of the editor.
    // Dynamic mode changes (e.g., in file manager) will be handled by setEditorModeByFilename.

    // Module Editor
    let infoModule = CodeMirror.findModeByExtension("php");
    if (infoModule) {
        cmEditorModule.setOption("mode", infoModule.mime);
        CodeMirror.autoLoadMode(cmEditorModule, infoModule.mode);
        setTimeout(function () {
            cmEditorModule.refresh();
        }, 1);
    }

    // Entity Editor
    let infoFile = CodeMirror.findModeByExtension("php");
    if (infoFile) {
        cmEditorFile.setOption("mode", infoFile.mime);
        CodeMirror.autoLoadMode(cmEditorFile, infoFile.mode);
        setTimeout(function () {
            cmEditorFile.refresh();
        }, 1);
    }

    // Validator Editor (NEW)
    let infoValidator = CodeMirror.findModeByExtension("php"); // Assuming validators are PHP files
    if (infoValidator) {
        cmEditorValidator.setOption("mode", infoValidator.mime);
        CodeMirror.autoLoadMode(cmEditorValidator, infoValidator.mode);
        setTimeout(function () {
            cmEditorValidator.refresh();
        }, 1);
    }

    // SQL Editors
    let infoSQL = CodeMirror.findModeByExtension("sql");
    if (infoSQL) {
        cmEditorSQL.setOption("mode", infoSQL.mime);
        CodeMirror.autoLoadMode(cmEditorSQL, infoSQL.mode);
        setTimeout(function () {
            cmEditorSQL.refresh();
        }, 1);

        cmEditorSQLExecute.setOption("mode", infoSQL.mime);
        CodeMirror.autoLoadMode(cmEditorSQLExecute, infoSQL.mode);
        setTimeout(function () {
            cmEditorSQLExecute.refresh();
        }, 1);
    }

    // Update scroll
    syncScroll(transEd1, transEd2);
    syncScroll(transEd3, transEd4);
    syncScroll(transEd5, transEd6);

}; // End of initEditor function

/**
 * Synchronizes the scroll position between two CodeMirror editor instances.
 * When one editor is scrolled, the other editor will scroll to the same position.
 * This helps in scenarios like parallel translation or diff viewing.
 *
 * @param {CodeMirror.Editor} editor1 - The first CodeMirror editor instance.
 * @param {CodeMirror.Editor} editor2 - The second CodeMirror editor instance.
 */
function syncScroll(editor1, editor2) {
    // Get the scroller element for each editor
    let scroller1 = editor1.getScrollerElement();
    let scroller2 = editor2.getScrollerElement();

    // Variable to prevent infinite loops (when editors scroll each other)
    let syncingScroll = false;

    // Listener for editor1
    scroller1.addEventListener('scroll', function() {
        if (!syncingScroll) {
            syncingScroll = true;
            // Get scroll position of editor1
            let scrollInfo1 = editor1.getScrollInfo();
            // Set scroll position of editor2
            editor2.scrollTo(scrollInfo1.left, scrollInfo1.top);
            // Reset flag after scroll completes
            setTimeout(function() {
                syncingScroll = false;
            }, 10); // Small delay to ensure the event completes
        }
    });

    // Listener for editor2
    scroller2.addEventListener('scroll', function() {
        if (!syncingScroll) {
            syncingScroll = true;
            // Get scroll position of editor2
            let scrollInfo2 = editor2.getScrollInfo();
            // Set scroll position of editor1
            editor1.scrollTo(scrollInfo2.left, scrollInfo2.top);
            // Reset flag after scroll completes
            setTimeout(function() {
                syncingScroll = false;
            }, 10); // Small delay
        }
    });
}

/**
 * Highlights the current line in the first translation editor.
 */
function hilightLine1() {
    let cursor = transEd1.getCursor();
    let lineNumber = cursor.line;

    transEd1.removeLineClass(lastLine1, 'background', 'highlight-line');
    transEd2.removeLineClass(lastLine1, 'background', 'highlight-line');

    transEd2.addLineClass(lineNumber, 'background', 'highlight-line');
    transEd1.addLineClass(lineNumber, 'background', 'highlight-line');

    lastLine1 = lineNumber;

    let translationStatus = entityTranslationData[lineNumber].propertyName;
    $('.entity-translation-status').text(translationStatus);
}

/**
 * Highlights the current line in the second translation editor.
 */
function hilightLine2() {
    let cursor = transEd2.getCursor();
    let lineNumber = cursor.line;

    transEd1.removeLineClass(lastLine1, 'background', 'highlight-line');
    transEd2.removeLineClass(lastLine1, 'background', 'highlight-line');

    transEd2.addLineClass(lineNumber, 'background', 'highlight-line');
    transEd1.addLineClass(lineNumber, 'background', 'highlight-line');

    lastLine1 = lineNumber;

    let translationStatus = entityTranslationData[lineNumber].propertyName;
    $('.entity-translation-status').text(translationStatus);
}

/**
 * Highlights the current line in the third translation editor.
 */
function hilightLine3() {
    let cursor = transEd3.getCursor();
    let lineNumber = cursor.line;

    transEd3.removeLineClass(lastLine1, 'background', 'highlight-line');
    transEd4.removeLineClass(lastLine1, 'background', 'highlight-line');

    transEd4.addLineClass(lineNumber, 'background', 'highlight-line');
    transEd3.addLineClass(lineNumber, 'background', 'highlight-line');

    lastLine1 = lineNumber;
    let translationStatus = moduleTranslationData && moduleTranslationData[lineNumber] ? moduleTranslationData[lineNumber].propertyName : undefined; // NOSONAR
    if (translationStatus) {
        $('.module-translation-status').text(translationStatus);
    }

}

/**
 * Highlights the current line in the fourth translation editor.
 */
function hilightLine4() {
    let cursor = transEd4.getCursor();
    let lineNumber = cursor.line;

    transEd3.removeLineClass(lastLine1, 'background', 'highlight-line');
    transEd4.removeLineClass(lastLine1, 'background', 'highlight-line');

    transEd4.addLineClass(lineNumber, 'background', 'highlight-line');
    transEd3.addLineClass(lineNumber, 'background', 'highlight-line');

    lastLine1 = lineNumber;
    let translationStatus = moduleTranslationData && moduleTranslationData[lineNumber] ? moduleTranslationData[lineNumber].propertyName : undefined; // NOSONAR
    if (translationStatus) {
        $('.module-translation-status').text(translationStatus);
    }
}

function hilightLine5() {
    let cursor = transEd5.getCursor();
    let lineNumber = cursor.line;

    transEd5.removeLineClass(lastLine2, 'background', 'highlight-line');
    transEd6.removeLineClass(lastLine2, 'background', 'highlight-line');

    transEd5.addLineClass(lineNumber, 'background', 'highlight-line');
    transEd6.addLineClass(lineNumber, 'background', 'highlight-line');

    lastLine2 = lineNumber;

    let translationStatus = appTranslationData && appTranslationData[lineNumber] ? appTranslationData[lineNumber].propertyName : undefined; // NOSONAR
    if (translationStatus) {
        $('.app-translation-status').text(translationStatus);
    }
}

function hilightLine6() {
    let cursor = transEd6.getCursor();
    let lineNumber = cursor.line;

    transEd5.removeLineClass(lastLine2, 'background', 'highlight-line');
    transEd6.removeLineClass(lastLine2, 'background', 'highlight-line');

    transEd5.addLineClass(lineNumber, 'background', 'highlight-line');
    transEd6.addLineClass(lineNumber, 'background', 'highlight-line');

    lastLine2 = lineNumber;

    let translationStatus = appTranslationData && appTranslationData[lineNumber] ? appTranslationData[lineNumber].propertyName : undefined; // NOSONAR
    if (translationStatus) {
        $('.app-translation-status').text(translationStatus);
    }
}

/**
 * Initializes the CodeMirror editor for the file manager.
 *
 * This function sets up the CodeMirror editor with various configurations such as line numbers,
 * line wrapping, bracket matching, and indentation settings. It also adjusts the editor's size
 * based on the window's size, ensuring the editor fits within the available space in the UI.
 *
 * - CodeMirror's mode URL is configured to load the correct syntax mode files.
 * - The editor is initialized with the content of the textarea with ID 'code'.
 * - A resize event listener is added to dynamically adjust the editor's size when the window is resized.
 */
function initCodeMirror2() {
    modeInput = document.getElementById('filename');
    CodeMirror.modeURL = "lib.assets/cm/mode/%N/%N.js"; // Path to CodeMirror mode files
    fileManagerEditor = CodeMirror.fromTextArea(document.getElementById("code"),
        {
            lineNumbers: true,           // Show line numbers in the editor
            lineWrapping: true,          // Enable line wrapping to prevent horizontal scrolling
            matchBrackets: true,         // Highlight matching brackets
            indentUnit: 4,               // Set the indentation unit to 4 spaces
            indentWithTabs: true         // Use tabs for indentation
        });

    // Adjust editor size when window is resized
    window.addEventListener('resize', function (e) {
        let w = document.querySelector('#file-content').offsetWidth - 16;  // Adjust width
        let h = document.innerHeight - 160;  // Adjust height based on window height
        fileManagerEditor.setSize(w, h);  // Apply the new size to the editor
    });

    // Initial editor size adjustment
    let w = document.querySelector('#file-content').offsetWidth - 16;
    let h = document.innerHeight - 160;
    fileManagerEditor.setSize(w, h);
}

/**
 * Focuses the CodeMirror editor on a specific line, moves the cursor to its beginning,
 * and ensures the line is visible within the editor's viewport.
 *
 * @param {CodeMirror.Editor} editor - The CodeMirror editor instance.
 * @param {number} lineNumber - The 0-indexed line number to focus on.
 * (e.g., 0 for the first line, 1 for the second, and so on).
 * @returns {void}
 */
function focusOnLine(editor, lineNumber) {
    // Check if the editor object is valid and its DOM element is still connected.
    // The .getWrapperElement() method should return the main div of the CodeMirror instance.
    if (!editor || !editor.getWrapperElement() || !editor.getWrapperElement().isConnected) // NOSONAR
    {
        console.error("CodeMirror editor instance is invalid or not connected to the DOM. Cannot focus on line.");
        // Consider re-initializing the editor here if this is a recoverable state,
        // e.g., initializeValidatorEditor();
        return;
    }

    // Ensure the line number is within the current document's bounds.
    if (lineNumber < 0 || lineNumber >= editor.lineCount()) {
        return;
    }

    try {
        editor.setCursor(lineNumber, 0); // Move the cursor to the beginning of the specified line.
        editor.scrollIntoView({line: lineNumber, ch: 0}, 20); // Scroll the view to make the line visible with a 20px margin.
        editor.focus(); // Give keyboard focus to the editor.
        editor.addLineClass(lineNumber, 'background', 'highlight-line');
    } catch (e) {
        console.log(e.getMessage());
    }
}