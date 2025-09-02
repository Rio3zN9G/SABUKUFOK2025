<?php
// Pengaturan dasar
define('SITE_NAME', 'SatuBudaya Kuningan');
define('ADMIN_PASSWORD', 'buzza25'); // Password admin

// Pengaturan database
define('DB_HOST', 'localhost');
define('DB_NAME', 'satubudaya_kuningan');
define('DB_USER', 'root');
define('DB_PASS', '');

// Pengaturan upload
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
?>