<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) die("DB error");

$favorites = [];
$sql = "SELECT f.id, f.recipe_name, u.username, u.email FROM favorites f JOIN users u ON f.user_id = u.id ORDER BY f.id DESC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $favorites[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Favorites - Recipe Delight Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
:root {
  --primary: #4a4a4a;
  --accent: #a87c4f;
  --light-accent: #f8f1e9;
  --dark-accent: #8a6947;
  --white: #ffffff;
  --light-gray: #f5f5f5;
  --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  --radius: 12px;
  --transition: all 0.3s ease;
}
body {
  background-color: var(--light-gray);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  margin: 0;
  padding: 1rem 2rem;
  color: var(--primary);
}
header {
  background: linear-gradient(135deg, var(--accent), var(--dark-accent));
  color: white;
  padding: 1rem 2rem;
  border-radius: var(--radius);
  margin-bottom: 2rem;
  box-shadow: var(--shadow);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
header h1 {
  margin: 0;
  font-weight: 600;
}
.logout-btn, .back-btn {
  background-color: rgba(255,255,255,0.2);
  padding: 8px 16px;
  border-radius: var(--radius);
  color: white;
  text-decoration: none;
  font-weight: 600;
  transition: var(--transition);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.logout-btn:hover, .back-btn:hover {
  background-color: rgba(255,255,255,0.35);
}
table {
  width: 100%;
  border-collapse: collapse;
  background: var(--white);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow);
}
th, td {
  padding: 12px 15px;
  border-bottom: 1px solid #ddd;
  text-align: left;
}
th {
  background-color: var(--accent);
  color: white;
}
button.remove-fav-btn {
  background-color: var(--dark-accent);
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: var(--radius);
  cursor: pointer;
  font-weight: 600;
  transition: var(--transition);
}
button.remove-fav-btn:hover {
  background-color: var(--accent);
}
.toast {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  border-radius: 4px;
  color: white;
  font-weight: 500;
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: 1000;
}
.toast.show {
  opacity: 1;
}
.toast.success {
  background-color: #2ecc71;
}
.toast.error {
  background-color: #e74c3c;
}
</style>
</head>
<body>
<header>
  <h1>Manage Favorites</h1>
  <a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
  <a href="logout.php?redirect=admin_login.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
</header>

<table>
  <thead>
    <tr><th>ID</th><th>Recipe Name</th><th>User</th><th>User Email</th><th>Action</th></tr>
  </thead>
  <tbody>
    <?php foreach ($favorites as $fav): ?>
      <tr data-favid="<?= $fav['id'] ?>">
        <td><?= $fav['id'] ?></td>
        <td><?= htmlspecialchars($fav['recipe_name']) ?></td>
        <td><?= htmlspecialchars($fav['username']) ?></td>
        <td><?= htmlspecialchars($fav['email']) ?></td>
        <td><button class="remove-fav-btn">Remove</button></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function(){
  function showToast(msg, type) {
    const toast = $('<div>').addClass('toast ' + type).text(msg).appendTo('body');
    setTimeout(() => toast.addClass('show'), 10);
    setTimeout(() => toast.removeClass('show').delay(300).queue(() => toast.remove()), 3000);
  }

  $('.remove-fav-btn').click(function(){
    const btn = $(this);
    const tr = btn.closest('tr');
    const favId = tr.data('favid');

    $.post('admin_favorite_action.php', {fav_id: favId}, function(res){
      if(res.success) {
        showToast('Favorite removed', 'success');
        tr.fadeOut(300, function(){ $(this).remove(); });
      } else {
        showToast('Error: ' + res.message, 'error');
      }
    }, 'json');
  });
});
</script>
</body>
</html>
