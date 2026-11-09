<?php
// config/constants.php
// App-wide constants. Include this file everywhere via header.php.

define('APP_NAME',    'SEIS - Student Enrollment Information System');
define('APP_VERSION', '1.0.0');

// Base URL — adjust to match your XAMPP folder name
define('BASE_URL', getenv('PORTFOLIO_DEMO') === 'true' ? '' : (getenv('APP_URL') ?: 'http://localhost:8000'));

// Absolute path shortcuts
define('ROOT_PATH',    dirname(__DIR__));           // /htdocs/seis
define('UPLOAD_PATH',  ROOT_PATH . '/assets/img/uploads/');
define('UPLOAD_URL',   BASE_URL  . '/assets/img/uploads/');

// Accepted image types for student photos
define('ALLOWED_IMG_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_FILE_SIZE',      2 * 1024 * 1024);   // 2MB

// Pagination
define('ROWS_PER_PAGE', 15);
