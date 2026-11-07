<?php
// auth/logout.php
// Destroys the session completely and redirects to login.

require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';

session_start();
session_unset();   // Clear all session variables
session_destroy(); // Destroy the session file on the server

// Also clear the session cookie from the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

redirect(BASE_URL . '/auth/login.php');