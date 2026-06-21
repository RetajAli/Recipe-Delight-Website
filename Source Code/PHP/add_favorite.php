<?php
session_start();

// Stop if user is not logged in
if (!isset($_SESSION["user_id"])) {
    die("Not logged in.");
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) {
    die("Database error.");
}

// Stop if no recipe name was sent check if its saved in db
if (!isset($_POST['recipe_name'])) {
    die("No recipe specified.");
}

$user_id = $_SESSION["user_id"];

// Clean the input to make it safe (protect db)
$recipe_name = $conn->real_escape_string($_POST['recipe_name']);

// Check if recipe is already in favorites (send real values)
$check = $conn->prepare("SELECT id FROM favorites WHERE user_id=? AND recipe_name=?");
$check->bind_param("is", $user_id, $recipe_name);
$check->execute();
$check->store_result();
// already saved by user
if ($check->num_rows > 0) {
    echo "Already added";
    exit;
}
$check->close();

// Add new favorite
$stmt = $conn->prepare("INSERT INTO favorites (user_id, recipe_name) VALUES (?, ?)");
$stmt->bind_param("is", $user_id, $recipe_name);

if ($stmt->execute()) {
    echo "Added";
} else {
    echo "Error adding recipe.";
}

$stmt->close();
$conn->close();
?>
