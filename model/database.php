<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    public static function settings(): array
    {
        return require dirname(__DIR__) . '/config/database.php';
    }

    public static function connect(bool $selectDatabase = true): PDO
    {
        $config = self::settings();
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $config['name'])) {
            throw new RuntimeException('Tên database chỉ được chứa chữ, số và dấu gạch dưới.');
        }
        $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';charset=utf8mb4';
        if ($selectDatabase) {
            $dsn .= ';dbname=' . $config['name'];
        }
        return new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_TIMEOUT => 5,
        ]);
    }

    public function getConnection(): PDO
    {
        return self::$connection ??= self::connect();
    }
}
