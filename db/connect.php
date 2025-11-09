<?php
$host = 'localhost';
$dbname = 'batcave';
$username = 'root'; // or your hosting username
$password = '';     // if using local XAMPP, usually empty

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
?>