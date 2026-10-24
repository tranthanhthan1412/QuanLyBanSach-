<?php
declare(strict_types=1);

$settings = [
    'enabled' => false,
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => '',
    'password' => '',
    'from_email' => '',
    'from_name' => 'BookStore',
];
if (is_file(__DIR__ . '/mail.local.php')) {
    $settings = array_replace($settings, require __DIR__ . '/mail.local.php');
}
foreach (array_keys($settings) as $key) {
    $value = getenv('MAIL_' . strtoupper($key));
    if ($value !== false) { $settings[$key] = $value; }
}
$settings['enabled'] = filter_var($settings['enabled'], FILTER_VALIDATE_BOOLEAN);
$settings['port'] = (int) $settings['port'];
return $settings;
