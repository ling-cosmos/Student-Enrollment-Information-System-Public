<?php
require_once 'config/constants.php';
require_once 'config/database.php';

try {
    $pdo = getPDO();
    echo "✅ Database connected!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
