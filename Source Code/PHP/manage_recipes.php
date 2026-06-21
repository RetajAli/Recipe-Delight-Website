<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) die("DB error");

$message = "";

// Handle Remove
if (isset($_POST['remove']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    // Move to archive first
    $move = $conn->query("INSERT INTO archived_recipes SELECT * FROM recipes WHERE id=$id");
    if ($move) {
        $delete = $conn->query("DELETE FROM recipes WHERE id=$id");
        if ($delete) {
            $message = "Recipe archived and removed!";
        } else {
            $message = "Error deleting recipe.";
        }
    } else {
        $message = "Error archiving recipe.";
    }
}

// Handle Restore
if (isset($_POST['restore']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    // Move back to recipes
    $move = $conn->query("INSERT INTO recipes SELECT * FROM archived_recipes WHERE id=$id");
    if ($move) {
        $delete = $conn->query("DELETE FROM archived_recipes WHERE id=$id");
        if ($delete) {
            $message = "Recipe restored!";
        } else {
            $message = "Error removing from archive.";
        }
    } else {
        $message = "Error restoring recipe.";
    }
}

// Fetch all active recipes with category
$recipes = [];
$res = $conn->query("SELECT recipes.id, recipes.name, recipes.image, category.name AS catname FROM recipes LEFT JOIN category ON recipes.category_id=category.id");
if ($res) while ($row = $res->fetch_assoc()) $recipes[] = $row;

// Fetch all archived recipes
$archived = [];
$res2 = $conn->query("SELECT archived_recipes.id, archived_recipes.name, archived_recipes.image, category.name AS catname FROM archived_recipes LEFT JOIN category ON archived_recipes.category_id=category.id");
if ($res2) while ($row = $res2->fetch_assoc()) $archived[] = $row;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Recipes - Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
:root {
  --primary: #4a4a4a;
  --accent: #a87c4f;
  --white: #fff;
  --light: #f5f5f5;
  --radius: 13px;
  --transition: .25s;
}
body {
  font-family: 'Segoe UI', Arial, sans-serif;
  margin: 0; background: var(--light); color: var(--primary);
}
.container {
  max-width: 1000px; margin: 40px auto; background: var(--white); border-radius: var(--radius); box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  padding: 36px 28px;
}
h1 {
  color: var(--accent); margin-bottom: 2rem; text-align: center;
}
.message {
  padding: 14px 20px; margin-bottom: 28px;
  border-radius: 10px; background: #efe6da; color: #805834; border-left: 6px solid var(--accent); font-weight: 600;
}
.recipe-list, .archive-list {
  width: 100%; border-collapse: collapse; margin-bottom: 36px;
}
.recipe-list th, .archive-list th {
  background: var(--accent); color: var(--white); font-weight: 600;
  padding: 14px 12px; border: none;
}
.recipe-list td, .archive-list td {
  background: #fcf8f3; padding: 12px; text-align: center; border-bottom: 1px solid #ece3d7;
}
.recipe-img {
  width: 95px; height: 60px; object-fit: cover; border-radius: 10px; border: 2px solid #e8d6bf;
  background: #f5e9db;
}
.cat-badge {
  background: var(--accent); color: var(--white); padding: 5px 14px; border-radius: 14px; font-size: .95rem;
}
.btn-remove, .btn-restore {
  background: #e0c3a0; color: #805834; padding: 8px 18px; border: none; border-radius: 18px; font-weight: 600;
  cursor: pointer; transition: var(--transition); font-size: .97rem;
  display: flex; align-items: center; gap: 6px; justify-content: center;
}
.btn-remove:hover {
  background: #e74c3c; color: #fff;
}
.btn-restore {
  background: #7f9957; color: #fff;
}
.btn-restore:hover {
  background: #3f6827;
}
.section-title {
  margin-top: 1.5rem; margin-bottom: 0.7rem; color: var(--accent);
  font-size: 1.25rem; font-weight: 700; border-left: 5px solid var(--accent); padding-left: 12px;
}
@media (max-width: 700px) {
  .container { padding: 10px 2px; }
  .recipe-list th, .archive-list th, .recipe-list td, .archive-list td { font-size: 0.95rem; padding: 8px 3px; }
}
</style>
</head>
<body>
<div class="container">
  <h1><i class="fas fa-bread-slice"></i> Manage Recipes</h1>
  <?php if($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

  <div class="section-title"><i class="fas fa-list"></i> Active Recipes</div>
  <table class="recipe-list">
    <tr>
      <th>Image</th>
      <th>Name</th>
      <th>Category</th>
      <th>Action</th>
    </tr>
    <?php foreach ($recipes as $rec): ?>
    <tr>
      <td><img class="recipe-img" src="<?= htmlspecialchars($rec['image']) ?>" alt="<?= htmlspecialchars($rec['name']) ?>"></td>
      <td><?= htmlspecialchars($rec['name']) ?></td>
      <td><span class="cat-badge"><?= htmlspecialchars($rec['catname'] ?? "No Category") ?></span></td>
      <td>
        <form method="post" style="display:inline;">
          <input type="hidden" name="id" value="<?= $rec['id'] ?>">
          <button class="btn-remove" name="remove" type="submit"><i class="fas fa-archive"></i> Archive</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($recipes)): ?>
    <tr><td colspan="4" style="color:#b89c6a;">No active recipes found.</td></tr>
    <?php endif; ?>
  </table>

  <div class="section-title"><i class="fas fa-archive"></i> Archived Recipes</div>
  <table class="archive-list">
    <tr>
      <th>Image</th>
      <th>Name</th>
      <th>Category</th>
      <th>Action</th>
    </tr>
    <?php foreach ($archived as $rec): ?>
    <tr>
      <td><img class="recipe-img" src="<?= htmlspecialchars($rec['image']) ?>" alt="<?= htmlspecialchars($rec['name']) ?>"></td>
      <td><?= htmlspecialchars($rec['name']) ?></td>
      <td><span class="cat-badge"><?= htmlspecialchars($rec['catname'] ?? "No Category") ?></span></td>
      <td>
        <form method="post" style="display:inline;">
          <input type="hidden" name="id" value="<?= $rec['id'] ?>">
          <button class="btn-restore" name="restore" type="submit"><i class="fas fa-redo"></i> Restore</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($archived)): ?>
    <tr><td colspan="4" style="color:#b89c6a;">No archived recipes found.</td></tr>
    <?php endif; ?>
  </table>
</div>
</body>
</html>
