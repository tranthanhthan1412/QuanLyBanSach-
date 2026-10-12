<?php
declare(strict_types=1);

// Copy this file to mail.local.php and enter your own SMTP credentials there.
return [
    'enabled' => true,
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls', // tls: STARTTLS, ssl: implicit TLS, none: local test SMTP only
    'username' => 'your-account@gmail.com',
    'password' => 'YOUR_GOOGLE_APP_PASSWORD',
    'from_email' => 'your-account@gmail.com',
    'from_name' => 'BookStore',
];
