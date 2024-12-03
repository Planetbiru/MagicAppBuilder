<?php
$host = 'localhost';
$port = '5432'; // Default PostgreSQL port
$dbname = 'sipro';
$user = 'postgres';
$password = 'Cebong2017';

try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=sipro", $user, $password);
    echo "Connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}