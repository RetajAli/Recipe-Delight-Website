<?php
session_start();
$is_admin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

if (!isset($_SESSION["user_id"])) {
    header("Location: Index.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Connect to database
$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) {
    die("Database connection error.");
}

// Get favorites for this user
$favorites = [];
$fav_query = $conn->prepare("SELECT recipe_name FROM favorites WHERE user_id=?");
$fav_query->bind_param("i", $user_id);
$fav_query->execute();
$result = $fav_query->get_result();
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row['recipe_name'];
}
$fav_query->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Recipe Menu | Recipe Delight</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --beige: #fffdf7;
      --accent: #bc8f63;
      --primary: #3e2723;
      --light: #ffffff;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
	
	
	
.cookie-msg {
    background: #f3e6d0;
    color: #8a5a23;
    padding: 16px;
    border-radius: 12px;
    text-align: center;
    font-weight: 600;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(188,143,99,0.09);
    max-width: 440px;
    margin-left: auto;
    margin-right: auto;
    font-size: 1.1rem;
}


    body {
      background-color: var(--beige);
      color: var(--primary);
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
    }
    header {
      background: linear-gradient(rgba(62,39,35,0.8), rgba(188,143,99,0.8)), url('Fage_May23_Sour_16oz_Corn_Fritters_Recipe_Headers_1200x500.jpg') no-repeat center center/cover;
      color: white;
      text-align: center;
      padding: 80px 20px 40px 20px;
      position: relative;
      box-shadow: var(--shadow);
    }
    header h1 {
      font-size: 3rem;
      margin-bottom: 10px;
    }
    .account-link {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 22px;
      background: var(--accent);
      color: #fff;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: var(--shadow);
      transition: background 0.3s;
    }
    .account-link:hover {
      background: #a87c4f;
    }
    nav {
      background: var(--light);
      text-align: center;
      padding: 12px 0;
      box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    }
    nav a {
      color: var(--primary);
      text-decoration: none;
      margin: 0 15px;
      font-weight: 600;
      padding: 8px 18px;
      border-radius: 22px;
      transition: background 0.3s;
    }
    nav a:hover {
      background: var(--accent);
      color: #fff;
    }
    .container {
      max-width: 1100px;
      margin: 40px auto 0 auto;
      padding: 0 20px;
    }
    .menu-section {
      margin-bottom: 44px;
      background: var(--light);
      border-radius: 18px;
      padding: 28px 22px 18px 22px;
      box-shadow: var(--shadow);
      transition: box-shadow 0.3s;
    }
    .menu-section h2 {
      color: var(--accent);
      font-size: 1.7rem;
      margin-bottom: 26px;
    }
    .menu-section ul {
      list-style: none;
      padding: 0;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 32px;
    }
    .menu-section li {
      background: #fcf8f3;
      border-radius: 14px;
      box-shadow: 0 2px 12px rgba(188,143,99,0.06);
      padding-bottom: 12px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: box-shadow 0.3s;
    }
    .menu-section img {
      width: 100%;
      max-height: 180px;
      object-fit: cover;
    }
    .recipe-content {
      padding: 20px 12px 8px 12px;
      text-align: center;
      width: 100%;
    }
    .recipe-content a {
      color: var(--primary);
      font-size: 1.2rem;
      font-weight: bold;
      text-decoration: none;
      display: block;
      margin-bottom: 12px;
      transition: color 0.3s;
    }
    .recipe-content a:hover {
      color: var(--accent);
    }
    .favorite-btn {
      background-color: var(--accent);
      color: white;
      border: none;
      border-radius: 20px;
      padding: 10px 24px;
      font-size: 1rem;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s;
      margin-top: 8px;
      outline: none;
    }
 .favorite-btn.added,
.favorite-btn:disabled {
  background-color: var(--accent) !important;
  color: #fff;
  cursor: not-allowed;
}

    @media (max-width: 900px) {
      .menu-section ul {
        grid-template-columns: 1fr;
      }
      .menu-section li {
        max-width: 400px;
        margin: 0 auto;
      }
    }
  </style>
</head>
<body>



  <header>
    <h1>Our Handpicked Recipes</h1>
    <a href="account.php" class="account-link">
      <i class="fas fa-user-circle"></i> My Profile
    </a>
	
	<pre>
	
	<?php
if (isset($_COOKIE['username'])) {
    echo "<div class='cookie-msg'>Welcome back, " . htmlspecialchars($_COOKIE['username']) . "!</div>";
}
?>
</pre>
  </header>
  <nav>
  <?php if ($is_admin): ?>
  <a href="admin_dashboard.php" style="
      position:fixed;top:25px;left:25px;
      background:#a87c4f;color:#fff;
      padding:12px 25px;border-radius:11px;
      text-decoration:none;font-weight:600;
      box-shadow:0 2px 8px #a87c4f22;z-index:99;">
    <i class="fas fa-user-shield"></i> Back to Admin Dashboard
  </a>
<?php endif; ?>

    <a href="Index.php">Home</a>
    <a href="AboutPage.html">About</a>
  </nav>
  <div style="max-width:1100px; margin: 20px auto; padding: 0 20px; text-align:center;">
  <input type="text" id="recipeSearch" placeholder="Search recipes..." 
         style="width: 100%; max-width: 400px; padding: 12px 20px; font-size: 1rem; border-radius: 25px; border: 1px solid #ccc;"/>
</div>

  <div class="container">
    <!-- Breakfast -->
    <div class="menu-section">
      <h2>Breakfast</h2>
      <ul>
        <li>
          <img src="vegetarian-denver-omelette-8.jpg" alt="Vegetable Omelette">
          <div class="recipe-content">
            <a href="Omelette.html">Vegetable Omelette</a>
            <button
              class="favorite-btn<?= in_array('Vegetable Omelette', $favorites) ? ' added' : '' ?>"
              data-name="Vegetable Omelette"
              <?= in_array('Vegetable Omelette', $favorites) ? 'disabled' : '' ?>>
              <?= in_array('Vegetable Omelette', $favorites) ? 'Added' : 'Add to Favorite' ?>
            </button>
          </div>
        </li>
        <li>
          <img src="Classic-Fluffy-Pancake-Recipe-6.jpg" alt="Pancakes">
          <div class="recipe-content">
            <a href="pancake.html">Pancakes</a>
            <button
              class="favorite-btn<?= in_array('Pancakes', $favorites) ? ' added' : '' ?>"
              data-name="Pancakes"
              <?= in_array('Pancakes', $favorites) ? 'disabled' : '' ?>>
              <?= in_array('Pancakes', $favorites) ? 'Added' : 'Add to Favorite' ?>
            </button>
          </div>
        </li>
      </ul>
    </div>
    <!-- Dinner -->
    <div class="menu-section">
      <h2>Dinner</h2>
      <ul>
        <li>
          <img src="Chicken-alfredo-3.jpg" alt="Chicken Alfredo Pasta">
          <div class="recipe-content">
            <a href="chicken alfrado pasta.html">Chicken Alfredo Pasta</a>
            <button
              class="favorite-btn<?= in_array('Chicken Alfredo Pasta', $favorites) ? ' added' : '' ?>"
              data-name="Chicken Alfredo Pasta"
              <?= in_array('Chicken Alfredo Pasta', $favorites) ? 'disabled' : '' ?>>
              <?= in_array('Chicken Alfredo Pasta', $favorites) ? 'Added' : 'Add to Favorite' ?>
            </button>
          </div>
        </li>
        <li>
          <img src="Grilled-Chicken-Caesar-Salad-Photograph.jpg" alt="Grilled Chicken Caesar Salad">
          <div class="recipe-content">
            <a href="chicken-caesar-salad.html">Grilled Chicken Caesar Salad</a>
            <button
              class="favorite-btn<?= in_array('Grilled Chicken Caesar Salad', $favorites) ? ' added' : '' ?>"
              data-name="Grilled Chicken Caesar Salad"
              <?= in_array('Grilled Chicken Caesar Salad', $favorites) ? 'disabled' : '' ?>>
              <?= in_array('Grilled Chicken Caesar Salad', $favorites) ? 'Added' : 'Add to Favorite' ?>
            </button>
          </div>
        </li>
      </ul>
    </div>
    <!-- Dessert -->
    <div class="menu-section">
      <h2>Dessert</h2>
      <ul>
        <li>
          <img src="1684846876915.jpg" alt="Tiramisu">
          <div class="recipe-content">
            <a href="tiramisu.html">Tiramisu</a>
            <button
              class="favorite-btn<?= in_array('Tiramisu', $favorites) ? ' added' : '' ?>"
              data-name="Tiramisu"
              <?= in_array('Tiramisu', $favorites) ? 'disabled' : '' ?>>
              <?= in_array('Tiramisu', $favorites) ? 'Added' : 'Add to Favorite' ?>
            </button>
          </div>
        </li>
        <li>
          <img src="chocolate-chip-cookies-23196-1.jpeg" alt="Chocolate Chip Cookies">
          <div class="recipe-content">
            <a href="ChococlateChips.html">Chocolate Chip Cookies</a>
            <button
              class="favorite-btn<?= in_array('Chocolate Chip Cookies', $favorites) ? ' added' : '' ?>"
              data-name="Chocolate Chip Cookies"
              <?= in_array('Chocolate Chip Cookies', $favorites) ? 'disabled' : '' ?>>
              <?= in_array('Chocolate Chip Cookies', $favorites) ? 'Added' : 'Add to Favorite' ?>
            </button>
          </div>
        </li>
      </ul>
    </div>
    <!-- Drinks -->
    <div class="menu-section">
      <h2>Drinks</h2>
      <ul>
        <li>
          <img src="colorful-beverages.jpg" alt="Drinks">
          <div class="recipe-content">
            <a href="Refreshing_Drink_extras.html">Explore Beverages</a>
            <button
              class="favorite-btn<?= in_array('Explore Beverages', $favorites) ? ' added' : '' ?>"
              data-name="Explore Beverages"
              <?= in_array('Explore Beverages', $favorites) ? 'disabled' : '' ?>>
              <?= in_array('Explore Beverages', $favorites) ? 'Added' : 'Add to Favorite' ?>
            </button>
          </div>
        </li>
      </ul>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
  
  $(document).ready(function() {
  $("#recipeSearch").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $(".menu-section ul li").filter(function() {
      $(this).toggle($(this).find('.recipe-content a').text().toLowerCase().indexOf(value) > -1)
    });
  });
});

    $(document).ready(function() {
      $(".favorite-btn").not('.added').on('click', function() {
        var btn = $(this);
        var recipeName = btn.data('name');
        btn.prop('disabled', true).text('Adding...');
        $.ajax({
          url: "add_favorite.php",
          type: "POST",
          data: {
            recipe_name: recipeName
          },
          success: function(response) {
            if (response.trim() === "Added" || response.trim().toLowerCase().includes("success")) {
              btn.text('Added').addClass('added');
            } else if (response.trim().toLowerCase().includes("already")) {
              btn.text('Added').addClass('added');
            } else {
              alert("Failed: " + response);
              btn.text('Add to Favorite').prop('disabled', false);
            }
          },
          error: function() {
            alert("Server error!");
            btn.text('Add to Favorite').prop('disabled', false);
          }
        });
      });
    });
  </script>
</body>
</html>
