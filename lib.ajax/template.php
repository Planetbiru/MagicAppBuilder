<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template</title>
    <script type="text/javascript" src="../lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
    <script>
class TemplateConverter {
    constructor() {
        this.keys = [];
        this.keyMap = {}; // key: original label text
    }

    convertAll() {
        this.convertTextElements();
        this.convertLabels();
        this.convertOptions();
        this.convertPlaceholders();
        this.convertAttributes(['label', 'aria-label', 'data-title']);

        console.log(this.keys);      // list of keys
        console.log(this.keyMap);    // key-value pairs

        // Send to server
        this.sendBodyToServer('template.html');
        this.sendKeyMapToServer('lang.json');
    }

    addKey(key, originalText) {
        if (!this.keys.includes(key)) {
            this.keys.push(key);
        }
        if (originalText && !(key in this.keyMap)) {
            this.keyMap[key] = originalText;
        }
    }

    fixKey(key) {
        key = key.replace(/[^a-zA-Z0-9]+/g, '_');

        if (/[a-zA-Z0-9]/.test(key)) {
            key = key.replace(/^_+|_+$/g, '');
        }

        return key;
    }


    convertTextToTemplate(element) {
        element.contents().each((_, node) => {
            if (node.nodeType === 3) {
                const originalText = node.textContent.trim();
                if (originalText) {
                    let key = this.fixKey(originalText.toLowerCase().replace(/\s+/g, '_'));
                    if (key !== '_') {
                        $(node).replaceWith(`{{lang['${key}']}}`);
                        this.addKey(key, originalText);
                    }
                }
            }
        });
    }

    convertTextElements() {
        const tags = 'button, a, li, span, p, td, h1, h2, h3, h4, h5, h6, div';
        $(tags).each((_, el) => {
            this.convertTextToTemplate($(el));
        });
    }

    convertLabels() {
        $('label').each((_, label) => {
            const $label = $(label);
            $label.contents().each((_, node) => {
                if (node.nodeType === 3 && node.textContent.trim()) {
                    const originalText = node.textContent.trim();
                    let key = this.fixKey(originalText.toLowerCase().replace(/\s+/g, '_'));
                    if (key !== '_') {
                        $(node).replaceWith(`{{lang['${key}']}}`);
                        this.addKey(key, originalText);
                    }
                }
            });
        });
    }

    convertOptions() {
        $('select option').each((_, el) => {
            this.convertTextToTemplate($(el));
        });
    }

    convertPlaceholders() {
        $('input[placeholder], textarea[placeholder]').each((_, el) => {
            const $el = $(el);
            const placeholder = $el.attr('placeholder')?.trim();
            if (placeholder) {
                let key = this.fixKey(placeholder.toLowerCase().replace(/\s+/g, '_'));
                if (key !== '_') {
                    $el.attr('placeholder', `{{lang['${key}']}}`);
                    this.addKey(key, placeholder);
                }
            }
        });
    }

    convertAttributes(attributes) {
        attributes.forEach(attr => {
            $(`*[${attr}]`).each((_, el) => {
                const $el = $(el);
                const value = $el.attr(attr)?.trim();
                if (value) {
                    let key = this.fixKey(value.toLowerCase().replace(/\s+/g, '_'));
                    if (key !== '_') {
                        $el.attr(attr, `{{lang['${key}']}}`);
                        this.addKey(key, value);
                    }
                }
            });
        });
    }

    sendBodyToServer(filename = 'template.html') {
        const htmlContent = document.body.innerHTML;

        $.ajax({
            url: 'template-save.php',
            method: 'POST',
            data: {
                filename: filename,
                content: htmlContent
            },
            success: function (response) {
                console.log('HTML template saved:', response);
            },
            error: function (xhr, status, error) {
                console.error('Failed to save HTML:', error);
            }
        });
    }

    sendKeyMapToServer() {
        const jsonContent = JSON.stringify(this.keyMap, null, 4);

        $.ajax({
            url: 'template-data-save.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                content: jsonContent
            }),
            success: function (response) {
                console.log('Language JSON saved:', response);
            },
            error: function (xhr, status, error) {
                console.error('Failed to save language file:', error);
            }
        });
    }
}


// When document is ready, convert
$(document).ready(function () {
    const converter = new TemplateConverter();
    converter.convertAll();
    converter.sendBodyToServer();
    converter.sendKeyMapToServer();
});
</script>


</head>
<body>
    <?php
    include_once "body.html";
    ?>
</body>
</html>