<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) die("DB error");

// Site Analytics
$user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$fav_count = $conn->query("SELECT COUNT(*) as count FROM favorites")->fetch_assoc()['count'];
$recent_users = [];
$res = $conn->query("SELECT username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
if ($res) while ($row = $res->fetch_assoc()) $recent_users[] = $row;

$recent_favs = [];
$res = $conn->query(
    "SELECT f.recipe_name, u.username, f.date_added
     FROM favorites f JOIN users u ON f.user_id = u.id
     ORDER BY f.date_added DESC LIMIT 5"
);
if ($res) while ($row = $res->fetch_assoc()) $recent_favs[] = $row;

// Recipe statistics by category for chart
$categories = [];
$cat_counts = [];
$res = $conn->query("SELECT category, COUNT(*) as count FROM recipes GROUP BY category");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row['category'];
        $cat_counts[] = $row['count'];
    }
}

// For User Search
$search_results = [];
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $query = '%' . $conn->real_escape_string($_GET['search']) . '%';
    $sql = "SELECT username, email, created_at FROM users WHERE username LIKE ? OR email LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $search_results[] = $row;
    $stmt->close();
}
// Get Top Favorite Recipe
$top_fav = null;
$top_fav_count = 0;
$res = $conn->query("
  SELECT recipe_name, COUNT(*) as total
  FROM favorites
  GROUP BY recipe_name
  ORDER BY total DESC
  LIMIT 1
");
if ($res && $row = $res->fetch_assoc()) {
    $top_fav = $row['recipe_name'];
    $top_fav_count = $row['total'];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Dashboard - Recipe Delight</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
  --primary: #4a4a4a;
  --accent: #a87c4f;
  --light-accent: #f8f1e9;
  --dark-accent: #8a6947;
  --white: #ffffff;
  --light-gray: #f9f6f2;
  --shadow: 0 6px 32px rgba(62,39,35,0.11);
  --radius: 16px;
  --transition: all 0.3s ease;
}
body {
  background-color: var(--light-gray);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  margin: 0; padding: 0;
}
.dashboard-container {
  max-width: 1250px; margin: 40px auto 32px auto; background: #fffdf7;
  border-radius: var(--radius); box-shadow: var(--shadow);
  padding: 40px 40px 36px 40px;
}
header {
  display: flex; justify-content: space-between; align-items: center;
  border-bottom: 2px solid #f8e6d3; padding-bottom: 18px; margin-bottom: 26px;
}
header h1 {
  margin: 0; font-weight: 700; font-size: 2.1rem; color: var(--accent);
  display: flex; align-items: center; gap: 10px;
}
.logout-btn {
  background-color: rgba(255,255,255,0.2);
  padding: 10px 24px; border-radius: var(--radius);
  color: var(--accent); text-decoration: none; font-weight: 600;
  border: 2px solid var(--accent);
  transition: var(--transition); display: flex; align-items: center; gap: 0.7rem;
}
.logout-btn:hover { background-color: var(--accent); color: white; }
.stats-row {
  display: flex; gap: 32px; justify-content: flex-start; margin-bottom: 30px;
}
.stat-card {
  background: #fcf8f3; border-radius: 14px; box-shadow: 0 2px 10px #bc8f6333;
  padding: 30px 40px; display: flex; align-items: center; gap: 18px; flex: 1;
}
.stat-card i {
  font-size: 2.6rem; color: #bc8f63; background: #fff4e4;
  border-radius: 50%; padding: 12px;
}
.stat-card h3 {
  margin: 0; font-size: 2.1rem; color: var(--dark-accent);
}
.stat-card p {
  margin: 0; font-weight: 600; color: var(--primary); font-size: 1.08rem;
}
.quick-actions {
  display: flex; gap: 16px; margin-bottom: 34px; flex-wrap: wrap; margin-top: 16px;
}
.quick-btn {
  background: var(--accent); color: #fff; padding: 14px 26px;
  border-radius: 22px; font-weight: bold; text-decoration: none;
  box-shadow: 0 2px 8px #bda27e55; transition: background 0.2s;
  display: flex; align-items: center; gap: 9px; border: none;
  font-size: 1.05rem;
}
.quick-btn:hover { background: #815d3b; color: #fff; }
.main-content {
  display: grid; grid-template-columns: 1fr 1fr; gap: 36px; margin-top: 20px;
}
.card {
  background: #fcf8f3; border-radius: 14px; box-shadow: 0 2px 10px #bc8f6333;
  padding: 30px 32px 26px 32px; margin-bottom: 0;
}
.card h2 {
  margin-top: 0; color: var(--accent); margin-bottom: 16px; font-size: 1.22rem;
  display: flex; align-items: center; gap: 10px;
}
.recent-list { list-style: none; padding-left: 0; margin-bottom: 0; }
.recent-list li {
  margin-bottom: 9px; font-size: 1rem; color: #65503a; display: flex; gap: 8px;
}
.recent-list li i { color: #a87c4f; }
.user-search-form {
  display: flex; gap: 12px; margin-bottom: 13px; align-items: center;
}
.user-search-form input[type="text"] {
  padding: 9px 12px; border: 1.5px solid #c1b49c; border-radius: 8px; font-size: 1rem;
  background: #fff;
}
.user-search-form button {
  background: var(--accent); color: #fff; border: none; border-radius: 8px;
  padding: 9px 18px; font-weight: 600; cursor: pointer;
  transition: background 0.2s;
}
.user-search-form button:hover { background: #815d3b; }
.user-search-results {
  margin: 0; padding: 0; list-style: none; font-size: 0.99rem;
}
.user-search-results li {
  margin-bottom: 7px; padding: 7px 0; border-bottom: 1px solid #e8dccb;
  color: #684a2c;
}
.chart-section {
  background: #fff; border-radius: 10px; padding: 26px; margin-top: 18px;
  box-shadow: 0 2px 8px #bda27e55;
}
@media (max-width: 950px) {
  .main-content { grid-template-columns: 1fr; gap: 22px; }
  .dashboard-container { padding: 16px 4vw 20px 4vw; }
}
/* Top Favorite Box */
.top-favorite-box {
  display: flex;
  align-items: center;
  justify-content: start;
  gap: 28px;
  background: linear-gradient(120deg, #e8d7c1 60%, #bc8f63 100%);
  border-radius: 20px;
  box-shadow: 0 4px 14px rgba(62,39,35,0.08);
  padding: 28px 38px;
  margin: 40px auto 0 auto;
  max-width: 520px;
  border: 2.5px solid #a87c4f;
  transition: box-shadow 0.3s;
}

.trophy-icon {
  background: #bc8f63;
  color: #fff;
  border-radius: 50%;
  width: 70px; height: 70px;
  display: flex; align-items: center; justify-content: center;
  font-size: 2.7rem;
  box-shadow: 0 3px 14px #bc8f6344;
}

.top-favorite-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.top-label {
  color: #a87c4f;
  font-size: 1.2rem;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 8px;
}

.top-recipe {
  color: #3e2723;
  font-size: 1.4rem;
  font-weight: bold;
  margin-bottom: 6px;
}

.top-fav-count {
  color: #4a4a4a;
  font-size: 1.01rem;
  font-weight: 600;
  letter-spacing: .3px;
}

.top-recipe.no-fav {
  color: #888;
  font-weight: 400;
}

</style>
</head>
<body>
<div class="dashboard-container">
  <header>
    <h1><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
    <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Log Out</a>
  </header>

  <!-- Site Analytics/Stats -->
  <section class="stats-row">
    <div class="stat-card">
      <i class="fas fa-users"></i>
      <div>
        <h3><?= $user_count ?></h3>
        <p>Total Users</p>
      </div>
    </div>
    <div class="stat-card">
      <i class="fas fa-heart"></i>
      <div>
        <h3><?= $fav_count ?></h3>
        <p>Total Favorites</p>
      </div>
    </div>
  </section>

  <!-- Top Favorite Recipe Section -->
  <div class="top-favorite-box">
    <div class="trophy-icon">
      <i class="fas fa-crown"></i>
    </div>
    <div class="top-favorite-content">
      <div class="top-label">Top Favorite Recipe</div>
      <?php if($top_fav): ?>
        <div class="top-recipe"><?= htmlspecialchars($top_fav) ?></div>
        <div class="top-fav-count"><?= $top_fav_count ?> users saved</div>
      <?php else: ?>
        <div class="top-recipe no-fav">No favorites yet</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick Actions -->
  <section class="quick-actions">
    <a href="manage_users.php" class="quick-btn"><i class="fas fa-users-cog"></i> Manage Users</a>
    <a href="manage_favorites.php" class="quick-btn"><i class="fas fa-heart"></i> Manage Favorites</a>
    <a href="Menu.php" class="quick-btn"><i class="fas fa-utensils"></i> Visit Site</a>
  </section>

  <!-- Main Dashboard Content -->
  <div class="main-content">
    <!-- Recent Activity -->
    <div class="card">
      <h2><i class="fas fa-clock"></i> Recent Activity</h2>
      <strong>New Users</strong>
      <ul class="recent-list">
        <?php foreach ($recent_users as $u): ?>
          <li><i class="fas fa-user"></i>
            <?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['email']) ?>)
            <span style="color:#adadad; font-size:0.92em; margin-left:7px;"><?= htmlspecialchars($u['created_at']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <br>
      <strong>Latest Favorites</strong>
      <ul class="recent-list">
        <?php foreach ($recent_favs as $f): ?>
          <li><i class="fas fa-heart"></i>
            <?= htmlspecialchars($f['recipe_name']) ?> by <?= htmlspecialchars($f['username']) ?>
            <span style="color:#adadad; font-size:0.92em; margin-left:7px;"><?= htmlspecialchars($f['date_added']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- User Search -->
    <div class="card">
      <h2><i class="fas fa-search"></i> User Search</h2>
      <form class="user-search-form" method="GET" action="admin_dashboard.php">
        <input type="text" name="search" placeholder="Username or Email..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit"><i class="fas fa-search"></i> Search</button>
      </form>
      <?php if (isset($_GET['search'])): ?>
        <ul class="user-search-results">
          <?php if (count($search_results)): ?>
            <?php foreach ($search_results as $row): ?>
              <li><b><?= htmlspecialchars($row['username']) ?></b> — <?= htmlspecialchars($row['email']) ?>
                <span style="color:#adadad; font-size:0.92em; margin-left:7px;"><?= htmlspecialchars($row['created_at']) ?></span>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>No users found for your search.</li>
          <?php endif; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recipe Statistics with Chart.js -->
  <div class="chart-section">
    <h2 style="color:#a87c4f; margin-bottom:18px;"><i class="fas fa-chart-pie"></i> Recipe Category Statistics</h2>
    <canvas id="recipeChart" height="110"></canvas>
  </div>
</div>
<script>
const ctx = document.getElementById('recipeChart').getContext('2d');
const recipeChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($categories) ?>,
        datasets: [{
            data: <?= json_encode($cat_counts) ?>,
            backgroundColor: [
                '#bc8f63','#a87c4f','#f8f1e9','#8a6947','#f5d7b8','#d6b38b','#f6eee3'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
</body>
</html>
