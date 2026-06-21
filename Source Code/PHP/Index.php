<?php
session_start();

// Database configuration (same as register)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'recipe_delight';

// Initialize variables
$login_identifier = $password = '';
$errors = array();

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process login form when submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $login_identifier = trim($_POST['login_identifier']);
    $password = trim($_POST['password']);
    
    // Validate inputs
    if (empty($login_identifier)) {
        $errors[] = "Username or email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, try to login
    if (empty($errors)) {
        // Check if login identifier is email or username
        $is_email = filter_var($login_identifier, FILTER_VALIDATE_EMAIL);
        
        // Prepare SQL based on whether identifier is email or username
        $sql = $is_email 
            ? "SELECT id, username, email, password FROM users WHERE email = ?"
            : "SELECT id, username, email, password FROM users WHERE username = ?";
            
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login_identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
				
				
				
				if (password_verify($password, $user['password'])) {
    // Password is correct, start session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    
    // *** SET COOKIE for username ***
    setcookie('username', $user['username'], time() + 3600, '/');  // 1 hour

    // Redirect to home page or dashboard
    header("Location: Menu.php");
    exit();
}

                // Redirect to home page or dashboard
                header("Location: Menu.php");
                exit();
            } else {
                $errors[] = "Incorrect password";
            }
        } else {
            $errors[] = "User not found";
        }
        
        $stmt->close();
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recipe Delight - Home</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    /* Your existing CSS remains exactly the same */
	.menu-recipes-btn {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 12px 20px;
      background-color: var(--accent);
      color: white;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      z-index: 1000;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
    }
    
    .menu-recipes-btn:hover {
      background-color: var(--dark-brown);
      transform: translateY(-2px);
    }
    :root {
      --beige: #fcf8f3;
      --olive: #4e443c;
      --dark-brown: #2c1f1a;
      --accent: #a87c4f;
      --white: #ffffff;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      --error: #e74c3c;
      --success: #2ecc71;
    }

    * {
      margin: 0; padding: 0; box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background-color: var(--beige);
      color: var(--dark-brown);
    }

    header {
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                  url('vegetarian-pizza-1024x1024.jpg') no-repeat center center/cover;
      height: 65vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: white;
      position: relative;
      padding: 0 20px;
    }

    header::before {
      content: ""; position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.45);
    }

    header h1, header p { z-index: 1; }

    header h1 {
      font-size: 3.2rem; font-weight: 800;
      margin-bottom: 12px; color: #fff;
      text-shadow: 0 3px 6px rgba(0,0,0,0.4);
    }

    header p {
      font-size: 1.25rem; font-weight: 400;
      max-width: 700px; color: #f1f1f1;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .login-card {
      background-color: var(--white);
      padding: 36px; border-radius: 20px;
      box-shadow: var(--shadow);
      margin: 50px auto; max-width: 450px;
      width: 95%; text-align: center;
      animation: fadeInUp 1s ease-in-out;
    }

    .login-card h2 { 
      margin-bottom: 20px; 
      font-size: 1.6rem; 
      color: var(--dark-brown); 
    }

    .login-card input {
      width: 100%; padding: 14px; margin-bottom: 18px;
      border-radius: 10px; border: 1px solid #ccc;
      font-size: 1rem; background-color: #f9f9f9;
      transition: all 0.3s ease;
    }

    .login-card input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(168, 124, 79, 0.2);
      outline: none;
    }

    .password-container { position: relative; }

    .toggle-password {
      position: absolute; right: 12px; top: 50%;
      transform: translateY(-50%);
      cursor: pointer; color: #999;
      transition: color 0.3s ease;
    }

    .toggle-password:hover {
      color: var(--accent);
    }

    .login-card button {
      width: 100%; background-color: var(--accent); color: white;
      padding: 14px; border: none; border-radius: 10px;
      font-size: 1rem; font-weight: 600;
      cursor: pointer; transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .login-card button:hover { 
      background-color: var(--dark-brown); 
      transform: translateY(-2px);
    }

    .login-card button:active {
      transform: translateY(0);
    }

    .register-link {
      display: inline-block; margin-top: 12px;
      font-size: 0.95rem; color: var(--accent);
      text-decoration: none; font-weight: 600;
      transition: color 0.3s ease;
    }

    .register-link:hover { 
      text-decoration: underline;
      color: var(--dark-brown);
    }

    .features {
      background-color: var(--white);
      padding: 80px 20px 40px; text-align: center;
    }

    .features h2 {
      font-size: 2rem; margin-bottom: 40px;
      color: var(--dark-brown);
    }

    .feature-list {
      display: flex; flex-wrap: wrap;
      justify-content: center; gap: 60px;
    }

    .feature {
      max-width: 280px; padding: 20px;
      background-color: var(--beige);
      border-radius: 12px;
      box-shadow: var(--shadow);
      transition: transform 0.3s ease;
    }

    .feature:hover {
      transform: translateY(-5px);
    }

    .feature i {
      font-size: 2.2rem; margin-bottom: 12px;
      color: var(--accent);
    }

    .feature h3 { font-size: 1.2rem; font-weight: 600; margin-bottom: 10px; }
    .feature p { font-size: 0.95rem; color: #555; }

    .testimonial {
      background-color: #fff9f4;
      padding: 60px 20px; text-align: center;
    }

    .testimonial h2 { font-size: 1.9rem; margin-bottom: 30px; color: var(--dark-brown); }
    .testimonial blockquote {
      font-style: italic; max-width: 700px;
      margin: 0 auto; font-size: 1.05rem;
      color: #444; line-height: 1.6;
    }
    .testimonial cite {
      display: block; margin-top: 15px;
      font-weight: 600; color: var(--accent);
    }

    footer {
      background-color: var(--olive);
      color: white; text-align: center;
      padding: 24px 10px; font-size: 0.95rem;
    }

    .error-message {
      color: var(--error);
      background-color: rgba(231, 76, 60, 0.1);
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid rgba(231, 76, 60, 0.3);
      font-weight: 600;
    }

    .success-message {
      color: var(--success);
      background-color: rgba(46, 204, 113, 0.1);
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid rgba(46, 204, 113, 0.3);
      font-weight: 600;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
      header h1 {
        font-size: 2.5rem;
      }
      
      .feature-list {
        flex-direction: column;
        align-items: center;
        gap: 30px;
      }
	  
.register-container {
margin-top: 14px;
font-size: 0.95rem;
display: flex;
justify-content: center;
gap: 6px;
align-items: center;
color: var(--accent);
}

/* Admin button styling */
.admin-btn {
display: inline-block;
margin-top: 16px;
background-color: var(--accent);
color: white;
padding: 12px 20px;
border-radius: 10px;
text-decoration: none;
font-weight: 600;
transition: all 0.3s ease;
}
.admin-btn:hover {
background-color: var(--dark-brown);
transform: translateY(-2px);
}
.admin-btn i {
margin-right: 8px;  
	  
    }
  </style>
</head>
<body>
 <?php if (isset($_SESSION['logged_in'])): ?>
    <a href="Menu.php" class="menu-recipes-btn">
      <i class="fas fa-utensils"></i> Menu Recipes
    </a>
  <?php endif; ?>

  <header>
    <h1>Welcome to Recipe Delight</h1>
    <p>Simple. Delicious. Yours. Discover and cook meals you'll love — easily and beautifully.</p>
  </header>

  <div class="login-card">
    <h2>Login</h2>
    
    <?php if (!empty($errors)): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i> <?php echo $errors[0]; ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['registered']) && $_GET['registered'] == 'true'): ?>
      <div class="success-message">
        <i class="fas fa-check-circle"></i> Registration successful! Please login.
      </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['logout']) && $_GET['logout'] == 'true'): ?>
      <div class="success-message">
        <i class="fas fa-check-circle"></i> You have been logged out successfully.
      </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
      <input type="text" name="login_identifier" placeholder="Email or Username" required
             value="<?php echo htmlspecialchars($login_identifier); ?>">
      
      <div class="password-container">
        <input type="password" name="password" placeholder="Password" id="password" required>
        <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
      </div>
      
      <button type="submit">
        <i class="fas fa-sign-in-alt"></i> Login
      </button>
      
      <!-- New block: Register side by side --> <div class="register-container"> <span>Don't have an account?</span> 
	  <a href="Register.php" class="register-link">Register Now</a> </div> <!-- Admin login button below --><pre>
	 
	 <a href="admin_login.php" class="admin-btn"> <i class="fas fa-user-shield"></i> Admin Portal </a></pre>
    </form>
  </div>

  <section class="features">
    <h2>Why Use Recipe Delight?</h2>
    <div class="feature-list">
      <div class="feature">
        <i class="fas fa-clipboard-check"></i>
        <h3>Easy-to-follow recipes</h3>
        <p>Step-by-step instructions to make cooking a breeze</p>
      </div>
      <div class="feature">
        <i class="fas fa-heart"></i>
        <h3>Save your favorites</h3>
        <p>Collect and organize recipes you love in one place</p>
      </div>
      <div class="feature">
        <i class="fas fa-user-plus"></i>
        <h3>Share your creations</h3>
        <p>Join our community of home cooks and inspire others</p>
      </div>
    </div>
  </section>

  <section class="testimonial">
    <h2>What Our Users Say</h2>
    <blockquote>
      "Recipe Delight changed the way I cook at home. It's easy to follow and beautifully designed. I save time and eat better — every single day!"
      <cite>— Fatima A., Dubai</cite>
    </blockquote>
  </section>

  <footer>
    &copy; 2025 Recipe Delight. All rights reserved.
  </footer>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById("password");
      const toggleIcon = document.querySelector(".toggle-password");
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
      } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
      }
    }
  </script>
</body>
</html>