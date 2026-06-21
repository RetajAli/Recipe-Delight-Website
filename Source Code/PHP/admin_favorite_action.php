<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['fav_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing favorite ID']);
    exit();
}

$fav_id = intval($_POST['fav_id']);

$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM favorites WHERE id=?");
$stmt->bind_param("i", $fav_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete']);
}
$stmt->close();
$conn->close();
