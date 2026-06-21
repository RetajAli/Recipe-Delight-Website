<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['user_id'], $_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$user_id = intval($_POST['user_id']);
$action = $_POST['action'];

if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Cannot change own admin status']);
    exit();
}

if (!in_array($action, ['promote', 'demote'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$is_admin_val = ($action === 'promote') ? 1 : 0;

$stmt = $conn->prepare("UPDATE users SET is_admin=? WHERE id=?");
$stmt->bind_param("ii", $is_admin_val, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update']);
}
$stmt->close();
$conn->close();
