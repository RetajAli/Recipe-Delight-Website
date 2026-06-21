<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: Index.php");
    exit();
}

// DB connect and fetch saved recipes
$conn = new mysqli("localhost", "root", "", "recipe_delight");
if ($conn->connect_error) {
    die("Database error: " . $conn->connect_error);
}
$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$email = "";

// Get email
$email_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$email_stmt->bind_param("i", $user_id);
$email_stmt->execute();
$email_result = $email_stmt->get_result();
if ($email_result->num_rows === 1) {
    $email_row = $email_result->fetch_assoc();
    $email = $email_row['email'];
}
$email_stmt->close();

// Get favorite recipes
$fav_stmt = $conn->prepare("SELECT recipe_name FROM favorites WHERE user_id = ?");
$fav_stmt->bind_param("i", $user_id);
$fav_stmt->execute();
$fav_result = $fav_stmt->get_result();
$recipes = [];
while ($row = $fav_result->fetch_assoc()) {
    $recipes[] = $row["recipe_name"];
}
$fav_stmt->close();
$conn->close();
// left side profile and right side fav recipe
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - Recipe Delight</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4a4a4a;
      --accent: #a87c4f;
      --light-accent: #f8f1e9;
      --dark-accent: #8a6947;
      --white: #ffffff;
      --light-gray: #f5f5f5;
      --medium-gray: #e0e0e0;
      --dark-gray: #333333;
      --error: #e74c3c;
      --success: #2ecc71;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      --radius: 12px;
      --transition: all 0.3s ease;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { background-color: var(--light-gray); color: var(--dark-gray); line-height: 1.6; }
    header { background: linear-gradient(135deg, var(--accent), var(--dark-accent)); color: white; padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow); }
    .header-content { display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; }
    header h1 { font-size: 1.8rem; font-weight: 600; margin: 0; }
    .logout-btn { background-color: rgba(255, 255, 255, 0.2); color: white; padding: 0.6rem 1.2rem; border: none; border-radius: var(--radius); font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; transition: var(--transition);}
    .logout-btn:hover { background-color: rgba(255, 255, 255, 0.3); transform: translateY(-2px); }
    .main-container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; display: grid; grid-template-columns: 300px 1fr; gap: 2rem; }
    .profile-card { background-color: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); padding: 2rem; height: fit-content; }
    .profile-header { text-align: center; margin-bottom: 1.5rem; }
    .profile-pic { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 4px solid var(--light-accent); background-color: var(--medium-gray); display: flex; align-items: center; justify-content: center; color: var(--accent); font-size: 3rem; }
    .username { font-size: 1.5rem; font-weight: 700; color: var(--dark-gray); margin-bottom: 0.25rem; }
    .email { font-size: 0.9rem; color: var(--primary); margin-bottom: 1.5rem; }
    .stats { display: flex; justify-content: space-around; margin-bottom: 1.5rem; }
    .stat-item { text-align: center; }
    .stat-number { font-size: 1.5rem; font-weight: 700; color: var(--accent); }
    .stat-label { font-size: 0.8rem; color: var(--primary); }
    .back-btn { display: block; width: 100%; background-color: var(--accent); color: white; text-align: center; padding: 0.8rem; border-radius: var(--radius); text-decoration: none; font-weight: 600; transition: var(--transition); margin-top: 1rem; }
    .back-btn:hover { background-color: var(--dark-accent); transform: translateY(-2px); }
    .content-card { background-color: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); padding: 2rem; }
    .section-title { font-size: 1.3rem; font-weight: 600; color: var(--dark-gray); margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--light-accent); }
   .recipes-list {
  min-height: 300px; /* or adjust to match your empty state height */
}

    .recipe-card { background-color: var(--light-gray); border-radius: var(--radius); padding: 1.5rem; transition: var(--transition); border: 1px solid var(--medium-gray); position: relative; }
    .recipe-card:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);}
    .recipe-name { font-weight: 600; margin-bottom: 0.5rem; color: var(--dark-gray);}
    .recipe-actions { display: flex; gap: 0.5rem; margin-top: 1rem;}
    .action-btn { padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; border: none; cursor: pointer; transition: var(--transition);}
    .view-btn { background-color: var(--accent); color: white;}
    
	.remove-btn { background-color: var(--light-gray); color: var(--dark-gray); border: 1px solid var(--medium-gray);}
   .empty-state {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 300px;  /* adjust if needed */
  text-align: center;
  color: var(--primary);
  padding: 1rem;
}

.empty-state i {
  font-size: 4rem; /* larger icon */
  color: var(--medium-gray);
  margin-bottom: 1rem;
}

.empty-state h4 {
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  font-size: 1.1rem;
  margin-bottom: 0;
}

    .explore-btn { display: inline-block; background-color: var(--accent); color: white; padding: 0.8rem 1.5rem; border-radius: var(--radius); text-decoration: none; font-weight: 600; transition: var(--transition);}
    .explore-btn:hover { background-color: var(--dark-accent); transform: translateY(-2px);}
    @media (max-width: 768px) { .main-container { grid-template-columns: 1fr;} .profile-card { position: static;} .recipes-list { grid-template-columns: 1fr; } }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .fade-in { animation: fadeIn 0.6s ease-out forwards; }
  </style>
</head>
<body>
  <header>
    <div class="header-content">
      <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
     <a href="logout.php?redirect=index" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Log Out
      </a>
    </div>
  </header>

  <div class="main-container">
    <div class="profile-card fade-in">
      <div class="profile-header">
        <div class="profile-pic">
          <i class="fas fa-user"></i>
        </div>
        <h2 class="username"><?php echo htmlspecialchars($username); ?></h2>
        <p class="email"><?php echo htmlspecialchars($email); ?></p>
      </div>
      <div class="stats">
        <div class="stat-item">
          <div class="stat-number"><?php echo count($recipes); ?></div>
          <div class="stat-label">Saved Recipes</div>
        </div>
      </div>
      <a href="Menu.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Recipes
      </a>
    </div>
    <div class="content-card fade-in" style="animation-delay: 0.2s">
      <h3 class="section-title">
        <i class="fas fa-heart" style="color: var(--accent); margin-right: 10px;"></i>
        My Saved Recipes
      </h3>
      <?php if (count($recipes) > 0): ?>
        <div class="recipes-list">
          <?php foreach ($recipes as $recipe): ?>
            <div class="recipe-card">
              <h4 class="recipe-name"><?php echo htmlspecialchars($recipe); ?></h4>
              <div class="recipe-actions">
                <button class="action-btn view-btn" onclick="viewRecipe('<?php echo htmlspecialchars($recipe); ?>')">
                  <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn remove-btn" onclick="removeRecipe('<?php echo htmlspecialchars($recipe); ?>', this)">
                  <i class="fas fa-trash"></i> Remove
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="fas fa-book-open"></i>
                   <h4>No Saved Recipes Yet</h4>
          <p>You haven't saved any recipes to your collection.</p>
          
        </div>
      <?php endif; ?>
	  
	  <div style="text-align:center; margin-top: 2rem;">
	  <a href="https://www.allrecipes.com/" class="explore-btn">
            <i class="fas fa-utensils"></i> Explore More Recipes
          </a>
    </div>
  </div>

  <script>
    // Map recipe names to their pages
    function viewRecipe(recipeName) {
      const recipeLinks = {
        "Vegetable Omelette": "Omelette.html",
        "Pancakes": "pancake.html",
        "Chicken Alfredo Pasta": "chicken alfrado pasta.html",
        "Grilled Chicken Caesar Salad": "chicken-caesar-salad.html",
        "Tiramisu": "tiramisu.html",
        "Chocolate Chip Cookies": "ChococlateChips.html",
        "Explore Beverages": "Refreshing_Drink_extras.html"
      };
      const filename = recipeLinks[recipeName.trim()];
      if (filename) window.location.href = filename;
      else alert("Recipe page not found!");
    }

 function removeRecipe(recipeName) {
    // AJAX call to remove the recipe
    fetch('remove_recipe.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `recipe_name=${encodeURIComponent(recipeName)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the recipe card from the UI
            const recipeCards = document.querySelectorAll('.recipe-card');
            recipeCards.forEach(card => {
                if (card.querySelector('.recipe-name').textContent === recipeName) {
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 300);
                }
            });

            // Update the stats count
            const statNumber = document.querySelector('.stat-number');
            statNumber.textContent = parseInt(statNumber.textContent) - 1;

            // If no recipes left, show empty state
  if (parseInt(statNumber.textContent) === 0) {
  setTimeout(() => {
    const recipesList = document.querySelector('.recipes-list');
    recipesList.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-book-open"></i>
        <h4>No Saved Recipes Yet</h4>
        <p>You haven't saved any recipes to your collection.</p>
      </div>
    `;
    recipesList.style.minHeight = '300px';  // Add this line to keep height
  }, 300);
}


        } else {
            showToast('Error removing recipe', 'error');
        }
    })
    .catch(error => {
        showToast('Error removing recipe', 'error');
    });
}


    function showToast(message, type) {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.textContent = message;
      document.body.appendChild(toast);
      setTimeout(() => { toast.classList.add('show'); }, 10);
      setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
    }
    // Toast styles
    const style = document.createElement('style');
    style.textContent = `
      .toast {
        position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
        padding: 12px 24px; border-radius: 4px; color: white; font-weight: 500; opacity: 0;
        transition: opacity 0.3s ease; z-index: 1000;
      }
      .toast.show { opacity: 1; }
      .toast.success { background-color: var(--success); }
      .toast.error { background-color: var(--error); }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>
