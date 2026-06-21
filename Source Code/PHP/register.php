<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'recipe_delight';

// Initialize variables
$username = $email = $password = '';
$errors = array();
$message = '';

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form when submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['pass']);
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Username already taken";
        }
        $stmt->close();
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered";
        }
        $stmt->close();
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    // If no errors, register user
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $message = "Registration successful! You can now login.";
            // Clear form
            $username = $email = $password = '';
        } else {
            $errors[] = "Registration failed. Please try again.";
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
  <title>Register Account</title>
  <style>
    :root {
      --primary: #4a4a4a; /* Neutral dark gray */
      --hover: #2e2e2e;
      --input-border: #ccc;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      --radius: 16px;
      --error: #e74c3c;
      --success: #2ecc71;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: url('wooden-spoon-condiments-background-wallpaper-preview.jpg') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      color: var(--primary);
    }

    header {
      text-align: center;
      padding: 30px 15px;
      background-color: rgba(255, 255, 255, 0.85);
      box-shadow: var(--shadow);
    }

    header h1 {
      font-size: 3rem;
      color: var(--primary);
    }

    .back-link a {
      color: var(--primary);
      font-size: 1rem;
      text-decoration: none;
      transition: 0.3s;
    }

    .back-link a:hover {
      color: var(--hover);
    }

    main.container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
    }

    .register-card {
      background: rgba(255, 255, 255, 0.95);
      padding: 50px 40px;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 520px;
      animation: fadeIn 0.8s ease-in-out;
      backdrop-filter: blur(10px);
    }

    .register-form h2 {
      text-align: center;
      color: var(--primary);
      margin-bottom: 25px;
    }

    .register-form label {
      font-weight: 600;
      display: block;
      margin: 15px 0 5px;
    }

    .input-group {
      position: relative;
      margin-bottom: 5px;
    }

    .input-group input {
      width: 100%;
      padding: 12px 40px 12px 38px;
      border: 1px solid var(--input-border);
      border-radius: var(--radius);
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }

    .input-group input:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(74, 74, 74, 0.2);
    }

    .input-group i {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      color: #999;
    }

    .toggle-password {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 14px;
      color: var(--primary);
    }

    .submit-btn {
      width: 100%;
      padding: 14px;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: var(--radius);
      font-size: 1rem;
      margin-top: 25px;
      cursor: pointer;
      transition: 0.3s;
    }

    .submit-btn:hover {
      background: var(--hover);
    }

    .login-redirect {
      text-align: center;
      margin-top: 20px;
      font-size: 0.95rem;
    }

    .login-redirect a {
      color: var(--primary);
      text-decoration: none;
      font-weight: bold;
    }

    .login-redirect a:hover {
      color: var(--hover);
    }

    footer.footer {
      text-align: center;
      padding: 20px;
      background: rgba(255, 255, 255, 0.85);
      color: #777;
      font-size: 0.9rem;
      box-shadow: var(--shadow);
    }

    .msg {
      padding: 12px;
      margin-bottom: 20px;
      border-radius: var(--radius);
      text-align: center;
      font-weight: 600;
    }

    .error {
      background-color: rgba(231, 76, 60, 0.2);
      color: var(--error);
      border: 1px solid var(--error);
    }

    .success {
      background-color: rgba(46, 204, 113, 0.2);
      color: var(--success);
      border: 1px solid var(--success);
    }

    .error-text {
      color: var(--error);
      font-size: 0.85rem;
      margin-bottom: 10px;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 500px) {
      .register-card {
        padding: 30px 20px;
      }

      header h1 {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>

<header>
  <h1>REGISTER NOW</h1>
  <div class="back-link">
    <a href="Index.php">&larr; Back to Home</a>
  </div>
</header>

<main class="container">
  <div class="register-card">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="register-form">
      <h2>Create Your Account</h2>
      
      <?php if (!empty($errors)): ?>
        <div class="msg error">
          <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
          <?php endforeach; ?>
        </div>
      <?php elseif (!empty($message)): ?>
        <div class="msg success"><?php echo $message; ?></div>
      <?php endif; ?>

      <label for="username">Username</label>
      <div class="input-group">
        <i>👤</i>
        <input type="text" id="username" name="username" required placeholder="Enter your username" value="<?php echo htmlspecialchars($username); ?>">
      </div>

      <label for="email">Email</label>
      <div class="input-group">
        <i>📧</i>
        <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>">
      </div>

      <label for="pass">Password</label>
      <div class="input-group">
        <i>🔒</i>
        <input type="password" id="pass" name="pass" required placeholder="Enter your password">
        <span class="toggle-password" onclick="togglePassword()">Show</span>
      </div>
      <small class="error-text">Minimum 8 characters</small>

      <button type="submit" class="submit-btn">Register</button>

      <div class="login-redirect">
        Already have an account?
        <a href="Index.php">Login now</a>
      </div>
    </form>
  </div>
</main>

<footer class="footer">
  &copy; 2025 Recipe Delight. All rights reserved.
</footer>
<script>
//allow user to show/hide password by clicking "show"
  function togglePassword() {
    const input = document.getElementById("pass");
    const toggle = document.querySelector(".toggle-password");
    if (input.type === "password") {
      input.type = "text";
      toggle.textContent = "Hide";
    } else {
      input.type = "password";
      toggle.textContent = "Show";
    }
  }
</script>

</body>
</html>