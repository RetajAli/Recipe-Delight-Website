<?php
session_start();
// byb3t lel browser json not html (json way to send and receive data)
//share info between website and server
header('Content-Type: application/json');
// el user 3amel log in wla la
if (!isset($_SESSION["user_id"])) 
{
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}
// hayshof el recipe name de kant mab3ota wla la
if (!isset($_POST["recipe_name"]))
	{
    echo json_encode(["success" => false, "message" => "Missing recipe name"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database error"]);
    exit();
}
// get the id and recipe name of logged in user
$user_id = $_SESSION["user_id"];
$recipe_name = $conn->real_escape_string($_POST["recipe_name"]);

$stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_name = ?");
// send real values
$stmt->bind_param("is", $user_id, $recipe_name);
// was it successful? did it remove something?
if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error removing recipe"]);
}
$stmt->close();
$conn->close();
?>
