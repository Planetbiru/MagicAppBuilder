<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template</title>
    <script type="text/javascript" src="../lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
    <script>
    let keys = [];
    function addKey(key)
    {
        if(!keys.includes(key))
        {
            keys.push(key);
        }   
    }
    
    function fixKey(key) {
        return key.replace(/[^a-zA-Z0-9]+/g, '_');  // Ganti semua karakter non-alphanumeric dan underscore dengan '_'
    }

    $(document).ready(function() {
        function convertToTemplate() {
            // Fungsi untuk mengubah teks dalam elemen menjadi template
            function convertTextToTemplate(element) {
                let html = element.html().trim();
                var originalText = element.text().trim();  // Mendapatkan teks dari elemen

                if (html.indexOf('<') === -1 && originalText) {
                    // Mengganti teks dengan format template {{lang['key']}}
                    var key = originalText.toLowerCase().replace(/\s+/g, '_');  // Membuat key berdasarkan teks
                    key = fixKey(key);
                    if(key != '_')
                    {
                        element.text(`{{lang['${key}']}}`);
                        addKey(key);
                    }
                }
            }

            // Mengubah teks dalam button, a, li, span, p, td, h1, h2, h3, h4, h5, h6
            $('button, a, li, span, p, td, h1, h2, h3, h4, h5, h6').each(function() {
                // Hanya ubah teks label jika tidak ada input di dalamnya 
                convertTextToTemplate($(this));  // Mengubah teks dalam elemen
            });
            
            // Mengubah teks dalam label, hanya teks setelah input yang diubah
            $('label').each(function() {
                var labelContent = $(this).contents();
                var textNode = null;
                
                // Iterasi isi label untuk menemukan teks setelah input
                labelContent.each(function() {
                    // Memeriksa apakah ini adalah teks node (bukan input)
                    if (this.nodeType === 3 && this.textContent.trim()) {
                        textNode = $(this);
                    }
                });

                // Jika ada teks node, ganti teks dengan template
                if (textNode) {
                    var originalText = textNode.text().trim();
                    if (originalText) {
                        var key = originalText.toLowerCase().replace(/\s+/g, '_');  // Membuat key berdasarkan teks
                        key = fixKey(key);
                        if(key != '_')
                        {
                            textNode.replaceWith(`{{lang['${key}']}}`);  // Ganti teks dengan template
                            addKey(key);
                        }
                        
                    }
                }
            });

            // Mengubah teks dalam option di dalam select
            $('select option').each(function() {
                convertTextToTemplate($(this));  // Mengubah teks pada option
            });

            // Mengubah placeholder pada input atau textarea
            $('input[placeholder], textarea[placeholder]').each(function() {
                var placeholderText = $(this).attr('placeholder').trim();
                if (placeholderText) {
                    // Mengganti placeholder dengan format template {{lang['key']}}
                    var key = placeholderText.toLowerCase().replace(/\s+/g, '_');  // Membuat key berdasarkan placeholder
                    key = fixKey(key);
                    if(key != '_')
                    {
                        $(this).attr('placeholder', `{{lang['${key}']}}`);
                        addKey(key);
                    }
                }
            });
            
            // Mengganti label dan aria-label di elemen lainnya
            $('*[label]').each(function() {
                var label = $(this).attr('label').trim();
                if (label) {
                    // Mengganti label dengan format template {{lang['key']}}
                    var key = label.toLowerCase().replace(/\s+/g, '_');  // Membuat key berdasarkan label
                    key = fixKey(key);
                    if(key != '_')
                    {
                        $(this).attr('label', `{{lang['${key}']}}`);
                        addKey(key);
                    }
                }
            });
            
            $('*[aria-label]').each(function() {
                var label = $(this).attr('aria-label').trim();
                if (label) {
                    // Mengganti label dengan format template {{lang['key']}}
                    var key = label.toLowerCase().replace(/\s+/g, '_');  // Membuat key berdasarkan label
                    key = fixKey(key);
                    if(key != '_')
                    {
                        $(this).attr('aria-label', `{{lang['${key}']}}`);
                        addKey(key);
                    }
                }
            });
        }

        // Menjalankan fungsi convertToTemplate untuk mengubah HTML menjadi template
        convertToTemplate();
        console.log(keys);
    });

</script>

</head>
<body>
    <?php
    include "body.html";
    ?>
</body>
</html>