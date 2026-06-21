<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'recipe_delight';

$conn = new mysqli($host, $user, $password, $database);

// Handle error silently (no echo here)
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
