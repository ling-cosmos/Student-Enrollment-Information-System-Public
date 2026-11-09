<?php
// index.php — Entry point. Just redirects.

require_once 'config/constants.php';
require_once 'includes/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!empty($_SESSION['user_id'])) {
    redirect(BASE_URL . '/admin/dashboard.php');
} else {
    redirect(BASE_URL . '/auth/login.php');
}