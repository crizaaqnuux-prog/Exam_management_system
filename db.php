<?php
$host = 'localhost';
$dbname = 'exam_invigilation_system'; // We will create this if it doesn't exist
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
