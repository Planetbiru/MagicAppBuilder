<?php
function getDirTree($dir) {
    // Scan folder dan ambil file serta subdirektori
    $files = scandir($dir);
    $result = [];

    // Loop untuk menampilkan sub-direktori dan file
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                // Buat array untuk direktori
                $result[] = [
                    'type' => 'dir',
                    'name' => $file,
                    'path' => $path,
                    'subdirs' => getDirTree($path) // Rekursif untuk sub-direktori
                ];
            } else if (is_file($path)) {
                // Buat array untuk file
                $result[] = [
                    'type' => 'file',
                    'name' => $file,
                    'path' => $path
                ];
            }
        }
    }

    return json_encode($result); // Mengembalikan data dalam format JSON
}

if (isset($_GET['dir'])) {
    header('Content-type: application/json');
    $dir = $_GET['dir'];
    echo getDirTree($dir);
}
?>
