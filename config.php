<?php
declare(strict_types=1);

// Database configuration. Use environment variables for deployment.
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'donate_device');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'Zee Tech Foundation');
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/donate-a-device-campaign');
define('SITE_EMAIL', 'info@zeetechfoundation.org');
define('SITE_PHONE', '+234 810 326 9627');

define('NAV_ITEMS', [
    ['href' => './', 'label' => 'Home'],
    ['href' => 'about', 'label' => 'About Us'],
    ['href' => 'campaign', 'label' => 'Campaign'],
    ['href' => 'donate', 'label' => 'Donate'],
    ['href' => 'apply', 'label' => 'Apply'],
    ['href' => 'partner', 'label' => 'Partner'],
    ['href' => 'impact', 'label' => 'Impact'],
    ['href' => 'contact', 'label' => 'Contact'],
]);

define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.example.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'username@example.com');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: 'password');
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
define('EMAIL_FROM', getenv('EMAIL_FROM') ?: 'no-reply@zeetechfoundation.org');
define('EMAIL_FROM_NAME', getenv('EMAIL_FROM_NAME') ?: 'Zee Tech Foundation');
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'info@zeetechfoundation.org');

define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
