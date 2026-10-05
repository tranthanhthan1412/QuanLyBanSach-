<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function config(string $key): mixed
{
    static $config;
    $config ??= require ROOT_PATH . '/config/app.php';
    return $config[$key] ?? null;
}

function base_url(): string
{
    $configured = config('base_url');
    if ($configured !== null) {
        return rtrim($configured, '/');
    }
    $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    return $directory === '/' || $directory === '.' ? '' : rtrim($directory, '/');
}

function url(string $route = 'home', array $query = []): string
{
    return base_url() . '/index.php?' . http_build_query(['route' => $route] + $query);
}

function asset(string $path): string
{
    $directory = defined('PUBLIC_ENTRY') && PUBLIC_ENTRY ? '/assets/' : '/public/assets/';
    return base_url() . $directory . ltrim($path, '/');
}

function money(int $value): string
{
    return number_format($value, 0, ',', '.') . ' ₫';
}

function query(string $key, string $default = ''): string
{
    return isset($_GET[$key]) && is_string($_GET[$key]) ? trim($_GET[$key]) : $default;
}

function normalize_search(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $groups = ['a' => 'àáạảãâầấậẩẫăằắặẳẵ', 'e' => 'èéẹẻẽêềếệểễ', 'i' => 'ìíịỉĩ', 'o' => 'òóọỏõôồốộổỗơờớợởỡ', 'u' => 'ùúụủũưừứựửữ', 'y' => 'ỳýỵỷỹ', 'd' => 'đ'];
    foreach ($groups as $replacement => $characters) {
        $text = preg_replace('/[' . $characters . ']/u', $replacement, $text);
    }
    return $text;
}

function icon(string $name, string $class = ''): string
{
    $paths = [
        'book' => '<path d="M12 7v14m0-14C9 4 5 4 2 5v15c4-1 7-1 10 1 3-2 6-2 10-1V5c-3-1-7-1-10 2Z"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'cart' => '<path d="M2 3h3l3 13h11l3-10H6"/><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>',
        'user' => '<circle cx="12" cy="7" r="4"/><path d="M5 22v-3a7 7 0 0 1 14 0v3"/>',
        'users' => '<circle cx="9" cy="7" r="3"/><path d="M2 21v-3a7 7 0 0 1 14 0v3m0-18a4 4 0 0 1 0 8m3 3a6 6 0 0 1 3 5v2"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'chevron' => '<path d="m9 5 7 7-7 7"/>',
        'truck' => '<path d="M1 4h13v13H1zm13 5h5l4 5v3h-9"/><circle cx="5" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>',
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V3h8v4M8 7v14m8-14v14"/>',
        'chip' => '<rect x="5" y="5" width="14" height="14" rx="2"/><path d="M9 9h6v6H9zM9 2v3m6-3v3M9 19v3m6-3v3M2 9h3m-3 6h3m14-6h3m-3 6h3"/>',
        'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>',
        'smile' => '<circle cx="12" cy="12" r="9"/><path d="M8 14s1 3 4 3 4-3 4-3M8 9h.01M16 9h.01M11 3c4 0 4 4 0 4"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/>',
        'bulb' => '<path d="M8 16a7 7 0 1 1 8 0l-1 3H9zm1 6h6"/>',
        'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 5 10 8L22 5"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'box' => '<path d="m12 2 10 5v11l-10 5-10-5V7Zm0 10v11M2 7l10 5 10-5M7 4.5l10 5"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 11v6m0-10h.01"/>',
        'shield' => '<path d="m12 2 9 4v6c0 5-9 10-9 10S3 17 3 12V6Z"/><path d="m8 12 3 3 5-6"/>',
    ];
    return '<svg class="icon ' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['book']) . '</svg>';
}
