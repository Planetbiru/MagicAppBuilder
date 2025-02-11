<?php

// Fungsi utama untuk mengonversi YML ke JSON
function parseYmlToJson($ymlContent) {
    $lines = explode("\n", $ymlContent); // Pisahkan setiap baris
    $array = parseYmlLines($lines); // Proses setiap baris ke dalam struktur array
    
    // Mengonversi array PHP menjadi JSON yang diformat dengan baik (pretty print)
    return json_encode($array, JSON_PRETTY_PRINT);
}

// Fungsi rekursif untuk mengonversi YML lines ke dalam array
function parseYmlLines($lines, $indentationLevel = 0) {
    $result = [];
    $i = 0; // Posisi baris saat ini dalam array

    while ($i < count($lines)) {
        $line = trim($lines[$i]);
        $i++;

        if ($line === '') continue; // Abaikan baris kosong

        // Tentukan level indentasi untuk menentukan nesting
        $currentIndentationLevel = strlen($lines[$i-1]) - strlen(ltrim($lines[$i-1]));

        // Jika indentasi berkurang, berarti selesai dengan nested structure
        if ($currentIndentationLevel < $indentationLevel) {
            break;
        }

        // Jika ada tanda ":" maka ini adalah key
        if (strpos($line, ":") !== false) {
            list($key, $value) = explode(":", $line, 2);
            $key = trim($key, " - ");
            $value = trim($value);
            
            // Jika value kosong, berarti itu adalah nested structure
            if (empty($value)) {
                $result[$key] = parseYmlLines(array_slice($lines, $i), $currentIndentationLevel + 1);
                while (isset($lines[$i]) && strlen(trim($lines[$i])) > $currentIndentationLevel) {
                    $i++;
                }
            } else {
                // Jika value adalah array (dengan tanda "-"), proses list
                if (substr(ltrim($value), 0, 1) === "-") {
                    $result[$key] = parseYmlList($lines, $i, $currentIndentationLevel + 1);
                    while (isset($lines[$i]) && strlen(trim($lines[$i])) > $currentIndentationLevel) {
                        $i++;
                    }
                } else {
                    // Jika bukan array, langsung masukkan nilai biasa
                    $result[$key] = parseValue($value);
                }
            }
        } else {
            // Jika baris tidak mengandung ":" dan dimulai dengan "-", berarti ini adalah bagian dari list
            if (substr(ltrim($line), 0, 1) === "-") {
                $result[] = parseValue(trim(substr($line, 1)));
            }
        }
    }
    
    return $result;
}

// Fungsi untuk menangani nilai yang bisa berupa angka atau string
function parseValue($value) {
    // Jika angka, kita ubah menjadi integer atau float
    if (is_numeric($value)) {
        if (strpos($value, '.') !== false) {
            return (float) $value;
        } else {
            return (int) $value;
        }
    }
    // Jika string, kembalikan sebagai string biasa
    return $value;
}

// Fungsi untuk menangani list dalam YML (dimulai dengan "-")
// Perbaikan: Tidak menggunakan $i untuk iterasi, sehingga bisa lebih fleksibel.
function parseYmlList($lines, $startIndex, $indentationLevel) {
    $list = [];
    $i = $startIndex;

    while ($i < count($lines)) {
        $line = trim($lines[$i]);

        if ($line === '') {
            $i++;
            continue; // Abaikan baris kosong
        }

        // Tentukan level indentasi untuk menentukan nesting
        $currentIndentationLevel = strlen($lines[$i]) - strlen(ltrim($lines[$i]));

        // Jika indentasi berkurang, berarti selesai dengan list
        if ($currentIndentationLevel < $indentationLevel) {
            break;
        }

        // Jika baris dimulai dengan "-" maka ini adalah item list
        if (substr(ltrim($line), 0, 1) === "-") {
            $lineWithoutDash = trim(substr($line, 1));

            // Cek apakah item ini berisi objek (misalnya "home: 765431")
            if (strpos($lineWithoutDash, ":") !== false) {
                // Jika ada ":", maka ini adalah objek dalam list
                $list[] = parseYmlLines([$line], $indentationLevel);
            } else {
                // Jika tidak ada ":", maka ini adalah nilai biasa dalam list
                $list[] = parseValue(trim($lineWithoutDash));
            }
        }
        $i++;
    }

    return $list;
}

// Contoh penggunaan
$ymlContent = <<<YML
name: "John Doe"
age: 30
address:
  street: "1234 Main St"
  city: "Sample City"
  phone:
    - 1234567
    - 7890123
    - home: 765431
    - offile:
        room1: 999887
        room2: 266351
        room3:
          - 12345
          - 23456
  state: State
YML;

echo parseYmlToJson($ymlContent);

?>
