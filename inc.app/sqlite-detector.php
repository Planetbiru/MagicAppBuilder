<?php
try {
    $pdo = new PDO('sqlite::memory:');
} catch (PDOException $e) {
    require_once dirname(__DIR__) . "/sqlite.php";
    exit();
}
