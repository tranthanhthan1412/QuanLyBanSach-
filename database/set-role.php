<?php
declare(strict_types=1);

// Role assignment is deliberately limited to the server's CLI.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$email = $argv[1] ?? '';
$role = $argv[2] ?? '';
if ($argc !== 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['Admin', 'User'], true)) {
    fwrite(STDERR, "Usage: php database/set-role.php <email> <Admin|User>\n");
    exit(1);
}

require dirname(__DIR__) . '/model/database.php';

try {
    $db = (new Database())->getConnection();
    $db->beginTransaction();
    $stmt = $db->prepare('SELECT maND FROM nguoidung WHERE email = ? FOR UPDATE');
    $stmt->execute([$email]);
    $userId = $stmt->fetchColumn();
    if ($userId === false) {
        throw new RuntimeException('Account not found. Register the account first.');
    }
    $stmt = $db->prepare('SELECT maVT FROM vaitro WHERE tenVT = ?');
    $stmt->execute([$role]);
    $roleId = $stmt->fetchColumn();
    if ($roleId === false) {
        throw new RuntimeException('Role not found. Run database/setup.php first.');
    }
    $stmt = $db->prepare('UPDATE nguoidung SET maVT = ? WHERE maND = ?');
    $stmt->execute([$roleId, $userId]);
    $db->commit();
    echo "Role updated: {$email} -> {$role}\n";
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, 'Role update failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
