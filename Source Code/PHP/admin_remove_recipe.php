<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Unauthorized.");
}
if (!isset($_POST['id'])) {
    die("No recipe ID provided.");
}

$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) die("DB error");

$recipe_id = intval($_POST['id']);
$sql = $conn->prepare("DELETE FROM recipes WHERE id = ?");
$sql->bind_param("i", $recipe_id);
if ($sql->execute()) {
    echo "success";
} else {
    echo "error";
}
$sql->close();
$conn->close();
?>
