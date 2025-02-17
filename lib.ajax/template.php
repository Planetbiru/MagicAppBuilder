<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template</title>
    <script type="text/javascript" src="../lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
    <script>
        $(document).ready(function() {
            function convertToTemplate() {
                // Fungsi untuk mengubah teks dalam elemen menjadi template
                function convertTextToTemplate(element) {
                    var originalText = element.text().trim();  // Mendapatkan teks dari elemen
                    if (originalText) {
                        // Mengganti teks dengan format template {{lang['key']}}
                        var key = originalText.toLowerCase().replace(/\s+/g, '_');  // Membuat key berdasarkan teks
                        element.text(`{{lang['${key}']}}`);
                    }
                }

                // Mengubah teks dalam button, a, li, span, p, dan elemen lainnya
                $('button, a, li, span, p, td, h1, h2, h3, h4, h5, h6').each(function() {
                    convertTextToTemplate($(this));  // Mengubah teks dalam elemen
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
                        $(this).attr('placeholder', `{{lang['${key}']}}`);
                    }
                });
                
                $('*[label]').each(function() {
                    var label = $(this).attr('label').trim();
                    if (label) {
                        // Mengganti label dengan format template {{lang['key']}}
                        var key = label.toLowerCase().replace(/\s+/g, '_');  // Membuat key berdasarkan label
                        $(this).attr('label', `{{lang['${key}']}}`);
                    }
                });
                
                $('*[aria-label]').each(function() {
                    var label = $(this).attr('aria-label').trim();
                    if (label) {
                        // Mengganti label dengan format template {{lang['key']}}
                        var key = label.toLowerCase().replace(/\s+/g, '_');  // Membuat key berdasarkan label
                        $(this).attr('aria-label', `{{lang['${key}']}}`);
                    }
                });
            }

            // Menjalankan fungsi convertToTemplate untuk mengubah HTML menjadi template
            convertToTemplate();
        });

    </script>
</head>
<body>
    <?php
    include "body.html";
    ?>
</body>
</html>