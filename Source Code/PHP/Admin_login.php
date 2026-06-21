<?php
// Start the session
session_start();

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header("Location: admin_dashboard.php");
    exit();
}

$errors = []; // Array to collect error messages

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get input values
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if both fields are filled
    if (empty($login) || empty($password)) {
        $errors[] = "Please enter username/email and password.";
    } else {
        // Connect to the database
        $conn = new mysqli("localhost", "root", "", "recipe_delight");
        if ($conn->connect_error) die("DB connection error");

        // Check if login is an email or username
        $is_email = filter_var($login, FILTER_VALIDATE_EMAIL);
        $sql = $is_email ? 
            "SELECT * FROM admins WHERE email = ?" : 
            "SELECT * FROM admins WHERE username = ?";

        // Prepare and run query
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        // If one user is found
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check password
            if (password_verify($password, $user['password'])) {
                // Save user info in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = 1;

                // Redirect to admin dashboard
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "User not found.";
        }

        // Close connection
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login - Recipe Delight</title>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    /* Define color palette and shared values */
    :root {
      --primary: #4a4a4a;
      --accent: #a87c4f;
      --dark-accent: #8a6947;
      --light-gray: #f5f5f5;
      --white: #ffffff;
      --error: #e74c3c;
      --shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      --radius: 14px;
      --transition: all 0.3s ease;
    }

    /* Basic page setup */
    body {
      background-image: url("Background.jpg"); /* Add your own image here */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-color: var(--light-gray);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    /* Login box container */
    .login-box {
      background: var(--white);
      padding: 2.5rem 3rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      width: 360px;
      animation: fadeIn 0.5s ease-out;
    }

    /* Animation effect */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Heading */
    h1 {
      color: var(--accent);
      text-align: center;
      margin-bottom: 1.8rem;
    }

    /* Input fields */
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px 14px;
      margin-bottom: 1rem;
      border-radius: var(--radius);
      border: 1px solid #ccc;
      font-size: 1rem;
      transition: var(--transition);
    }

    input[type="text"]:focus, input[type="password"]:focus {
      border-color: var(--accent);
      outline: none;
    }

    /* Login button */
    button {
      width: 100%;
      background: var(--accent);
      color: var(--white);
      padding: 12px;
      font-size: 1rem;
      font-weight: 600;
      border: none;
      border-radius: var(--radius);
      cursor: pointer;
      transition: var(--transition);
    }

    button:hover {
      background: var(--dark-accent);
    }

    /* Error message style */
    .error {
      color: var(--error);
      margin-bottom: 1rem;
      font-size: 0.9rem;
      text-align: center;
    }

    /* Link to user site */
    .alt-link {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 1.5rem;
      color: var(--accent);
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
    }

    .alt-link i {
      font-size: 1rem;
    }

    .alt-link:hover {
      color: var(--dark-accent);
    }
  </style>
</head>

<body>
  <!-- Login Form UI -->
  <div class="login-box">
    <h1>Admin Login</h1>

    <!-- Display errors if any -->
    <?php if ($errors): ?>
      <div class="error"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <!-- Login form -->
    <form method="POST" action="">
      <input type="text" name="login" placeholder="Username or Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Log In</button>
    </form>

    <!-- Link to user (non-admin) section -->
    <a href="Index.php" class="alt-link">
      <i class="fas fa-utensils"></i>
      I'm a regular user — take me to the main site
    </a>
  </div>
</body>
</html>