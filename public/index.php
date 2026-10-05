<?php
declare(strict_types=1);

// Keep existing /public/ URLs and PHP's built-in server working.
define('PUBLIC_ENTRY', true);
require dirname(__DIR__) . '/index.php';
