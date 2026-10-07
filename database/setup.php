<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/model/database.php';

try {
    $config = Database::settings();
    $server = Database::connect(false);
    $server->exec('CREATE DATABASE IF NOT EXISTS `' . $config['name'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $db = Database::connect();
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    foreach (explode(';', $schema) as $statement) {
        if (trim($statement) !== '') {
            $db->exec($statement);
        }
    }

    // Additive upgrade for databases created by the previous commit.
    if (!$db->query("SHOW COLUMNS FROM sach LIKE 'hinhAnh'")->fetch()) {
        $db->exec('ALTER TABLE sach ADD COLUMN hinhAnh VARCHAR(255) NULL');
    }

    $db->beginTransaction();
    $role = $db->prepare('INSERT INTO vaitro (tenVT, moTa) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM vaitro WHERE tenVT = ?)');
    foreach (['Admin' => 'Quản trị viên', 'User' => 'Khách hàng'] as $name => $description) {
        $role->execute([$name, $description, $name]);
    }

    // Seed only a new, empty catalog. Never overwrite existing user data.
    $empty = true;
    foreach (['sach', 'theloai', 'tacgia', 'nhaxuatban'] as $table) {
        if ((int) $db->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn() > 0) {
            $empty = false;
        }
    }
    if ($empty) {
        $categories = [];
        $insertCategory = $db->prepare('INSERT INTO theloai (tenTL) VALUES (?)');
        foreach (require dirname(__DIR__) . '/config/categories.php' as $category) {
            $insertCategory->execute([$category['name']]);
            $categories[$category['slug']] = (int) $db->lastInsertId();
        }
        $authors = [];
        $publishers = [];
        $insertAuthor = $db->prepare('INSERT INTO tacgia (tenTG) VALUES (?)');
        $insertPublisher = $db->prepare('INSERT INTO nhaxuatban (tenNXB) VALUES (?)');
        $insertBook = $db->prepare('INSERT INTO sach (tenSach, moTa, giaTien, tonKho, maTL, maTG, maNXB, hinhAnh) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $books = (static fn (): array => require __DIR__ . '/seed.php')();
        foreach ($books as $book) {
            if (!isset($authors[$book['author']])) {
                $insertAuthor->execute([$book['author']]);
                $authors[$book['author']] = (int) $db->lastInsertId();
            }
            if (!isset($publishers[$book['publisher']])) {
                $insertPublisher->execute([$book['publisher']]);
                $publishers[$book['publisher']] = (int) $db->lastInsertId();
            }
            $insertBook->execute([
                $book['title'], $book['description'], $book['price'], $book['stock'],
                $categories[$book['category']], $authors[$book['author']],
                $publishers[$book['publisher']], $book['image'],
            ]);
        }
    }
    $db->commit();
    echo "Database ready: {$config['name']}\n";
    echo $empty ? "Seeded 8 categories and 24 illustrative books.\n" : "Existing catalog preserved; sample catalog skipped.\n";
    echo "Roles ready. No accounts or orders were created. Register through the website.\n";
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, 'Setup failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
