<?php

/**
 * Mengambil data dari tabel berdasarkan ID tabel dan nama pelanggan.
 *
 * @param int $tableId ID tabel yang ingin dicari.
 * @param string $customerName Nama pelanggan yang ingin dicari.
 * @return array|null Data hasil query atau null jika gagal.
 */
function getTableData($tableId, $customerName) {
    // Koneksi ke database (ganti dengan konfigurasi Anda)
    $dsn = 'mysql:host=your_host;dbname=your_db;charset=utf8';
    $username = 'your_username';
    $password = 'your_password';

    try {
        // Membuat koneksi PDO
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Persiapkan query
        $sql = "SELECT * FROM table
                WHERE table_id.tableId = :tableId AND table.name = :customerName";
        $stmt = $pdo->prepare($sql);

        // Ambil parameter dari fungsi
        $reflection = new ReflectionFunction(__FUNCTION__);
        $params = $reflection->getParameters();

        // Melakukan binding otomatis
        foreach ($params as $index => $param) {
            $paramName = ':' . $param->getName();
            $value = $$paramName; // Mengambil nilai dari variabel dengan nama yang sama

            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindParam($paramName, $value, $type);
        }

        // Eksekusi query
        $stmt->execute();

        // Ambil semua hasil
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results;

    } catch (PDOException $e) {
        echo 'Query failed: ' . $e->getMessage();
        return null;
    }
}

