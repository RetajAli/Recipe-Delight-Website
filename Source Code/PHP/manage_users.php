<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) die("DB error");

$users = [];
$res = $conn->query("SELECT id, username, email, is_admin FROM users ORDER BY id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Users - Recipe Delight Admin</title>
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
.logout-btn {
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
.logout-btn:hover {
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
button.action-btn {
  padding: 6px 14px;
  border-radius: var(--radius);
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: var(--transition);
}
button.promote {
  background-color: var(--accent);
  color: white;
}
button.demote {
  background-color: var(--dark-accent);
  color: white;
}
button.promote:hover {
  background-color: var(--dark-accent);
}
button.demote:hover {
  background-color: var(--accent);
}
.status-badge {
  padding: 5px 12px;
  border-radius: 15px;
  font-weight: 600;
  color: white;
  display: inline-block;
  width: 80px;
  text-align: center;
}
.status-admin {
  background-color: var(--accent);
}
.status-user {
  background-color: #777;
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
  <h1>Manage Users</h1>
  <a href="admin_dashboard.php" class="logout-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
  <a href="logout.php?redirect=admin_login.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
</header>

<table>
  <thead>
    <tr><th>ID</th><th>Username</th><th>Email</th><th>Status</th><th>Action</th></tr>
  </thead>
<tbody>
<?php foreach ($users as $user): ?>
  <tr>
    <td><?= htmlspecialchars($user['id']) ?></td>
    <td><?= htmlspecialchars($user['username']) ?></td>
    <td><?= htmlspecialchars($user['email']) ?></td>
    <td>
      <span class="status-badge <?= $user['is_admin'] ? 'status-admin' : 'status-user' ?>">
        <?= $user['is_admin'] ? 'Admin' : 'User' ?>
      </span>
    </td>
    <td>
      <?php if ($user['id'] == $_SESSION['user_id']): ?>
        <em>Current User</em>
      <?php elseif ($user['is_admin']): ?>
        <form method="post" style="display:inline;">
          <input type="hidden" name="demote_id" value="<?= $user['id'] ?>">
          <button type="submit" class="action-btn demote">Demote</button>
        </form>
      <?php else: ?>
        <form method="post" style="display:inline;">
          <input type="hidden" name="promote_id" value="<?= $user['id'] ?>">
          <button type="submit" class="action-btn promote">Promote</button>
        </form>
      <?php endif; ?>
    </td>
  </tr>
<?php endforeach; ?>
</tbody>

</table>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function() {
  function showToast(message, type) {
    const toast = $('<div>').addClass('toast ' + type).text(message).appendTo('body');
    setTimeout(() => toast.addClass('show'), 10);
    setTimeout(() => toast.removeClass('show').delay(300).queue(() => toast.remove()), 3000);
  }

  $('button.action-btn').click(function() {
    const btn = $(this);
    const tr = btn.closest('tr');
    const userId = tr.data('userid');
    const action = btn.data('action');

    $.post('admin_user_action.php', { user_id: userId, action: action }, function(res) {
      if (res.success) {
        showToast('User ' + action + 'd successfully', 'success');
        // Update row status badge and button
        if (action === 'promote') {
          btn.removeClass('promote').addClass('demote').data('action', 'demote').text('Demote');
          tr.find('.status-badge').removeClass('status-user').addClass('status-admin').text('Admin');
        } else if (action === 'demote') {
          btn.removeClass('demote').addClass('promote').data('action', 'promote').text('Promote');
          tr.find('.status-badge').removeClass('status-admin').addClass('status-user').text('User');
        }
      } else {
        showToast('Error: ' + res.message, 'error');
      }
    }, 'json');
  });
});
</script>
</body>
</html>
