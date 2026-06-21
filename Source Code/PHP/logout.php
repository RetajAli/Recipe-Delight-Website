
<?php
// logout.php
session_start();

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Handle redirect
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index';
switch ($redirect) {
    case 'index':
        header("Location: index.php");
        break;
    default:
        header("Location: index.php");
}
exit();
?>