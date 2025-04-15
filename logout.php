<?php
session_start();        // Initialize session data
session_unset();        // Unset all of the session variables
session_destroy();      // Destroy the session

// Optionally, clear any cookies if you are using them
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect to the login page (or homepage)
header("Location: auth.php?action=login");
exit();
?>
