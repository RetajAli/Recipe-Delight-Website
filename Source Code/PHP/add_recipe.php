<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) die("DB error");

$message = "";
// Handle recipe submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $desc = trim($_POST['desc']);
    $category = intval($_POST['category']);
    $img = trim($_POST['image']);
    $sql = $conn->prepare("INSERT INTO recipes (name, description, category_id, image) VALUES (?, ?, ?, ?)");
    $sql->bind_param("ssis", $name, $desc, $category, $img);
    if ($sql->execute()) {
        $message = "Recipe added successfully!";
    } else {
        $message = "Error: Could not add recipe.";
    }
    $sql->close();
}
// Get categories for the dropdown
$cats = [];
$res = $conn->query("SELECT id, name FROM categories");
while ($row = $res->fetch_assoc()) $cats[] = $row;
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Recipe | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body { background: #f8f1e9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-box {
            background: #fff;
            max-width: 450px;
            margin: 40px auto;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(168,124,79,0.13);
            padding: 32px;
        }
        h2 { color: #a87c4f; margin-bottom: 22px; text-align:center;}
        label { color: #4a4a4a; font-weight:600;}
        input, select, textarea {
            width: 100%; padding: 10px; margin-bottom: 18px;
            border: 1px solid #a87c4f50; border-radius: 10px;
            font-size: 1rem; background: #faf8f3;
        }
        button {
            background: #a87c4f;
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 1.1rem;
            width: 100%;
            cursor: pointer;
            transition: background .2s;
        }
        button:hover { background: #8a6947; }
        .msg { text-align:center; margin-bottom:20px; font-weight:600; color:#2ecc71;}
        .back-link {
            display:block; text-align:center; margin-top:16px;
            color: #a87c4f; font-weight:600; text-decoration:none;
        }
        .back-link:hover { text-decoration: underline;}
.remove-btn {
    background: #e74c3c;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s;
}
.remove-btn:hover {
    background: #b03a2e;
}
		
    </style>
</head>
<body>
    <div class="main-box">
        <h2><i class="fas fa-plus"></i> Add New Recipe</h2>
        <?php if($message): ?>
            <div class="msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="name">Recipe Name</label>
            <input type="text" name="name" id="name" required>

            <label for="desc">Description</label>
            <textarea name="desc" id="desc" rows="4" required></textarea>

            <label for="category">Category</label>
            <select name="category" id="category" required>
                <?php foreach($cats as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="image">Image file name (e.g. recipe.jpg)</label>
            <input type="text" name="image" id="image" required>

            <button type="submit"><i class="fas fa-plus-circle"></i> Add Recipe</button>
        </form>
        <a class="back-link" href="admin_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
	
	<h2>All Recipes</h2>
<table border="1" cellpadding="8" cellspacing="0" style="width:100%; margin-top:20px;">
    <tr style="background:#eee;">
        <th>ID</th>
        <th>Name</th>
        <th>Category</th>
        <th>Image</th>
        <th>Action</th>
    </tr>
    <?php
    $conn = new mysqli("localhost", "root", "", "recipe_delight");
    $res = $conn->query("SELECT recipes.id, recipes.name, category.name AS catname, recipes.image FROM recipes LEFT JOIN category ON recipes.category_id=category.id");
    while ($row = $res->fetch_assoc()):
    ?>
    <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['catname']) ?></td>
        <td><img src="<?= htmlspecialchars($row['image']) ?>" style="width:50px;height:50px;object-fit:cover;" /></td>
        <td>
            <button class="remove-btn" data-id="<?= $row['id'] ?>">Remove</button>
        </td>
    </tr>
    <?php endwhile; $conn->close(); ?>
</table>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function(){
    $(".remove-btn").click(function(){
        if (confirm("Are you sure you want to remove this recipe?")) {
            var btn = $(this);
            var recipeId = btn.data('id');
            $.post('remove_recipe.php', { id: recipeId }, function(response){
                if (response.trim() === "success") {
                    btn.closest('tr').fadeOut(400, function() { $(this).remove(); });
                } else {
                    alert("Error deleting recipe.");
                }
            });
        }
    });
});
</script>

	
</body>
</html>
