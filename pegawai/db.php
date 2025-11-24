<?php

/**
 * Database Connection
 * 
 * Establishes a connection to the MySQL database and returns the PDO object.
 */
function getDbConnection() {
    $host = 'localhost';
    $dbname = 'majujaya_db'; // Ganti dengan nama database Anda
    $username = 'root';      // Ganti dengan username database Anda
    $password = '';          // Ganti dengan password database Anda

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit();
    }
}