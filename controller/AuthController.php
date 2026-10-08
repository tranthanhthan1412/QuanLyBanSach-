<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use PDOException;

final class AuthController extends Controller
{
    public function login(): void
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = post_string('email');
            $password = post_string('password', false);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 72) {
                $error = 'Vui lòng nhập email hợp lệ và mật khẩu.';
            } else {
                $db = (new \Database())->getConnection();
                $stmt = $db->prepare('SELECT nd.maND, nd.tenND, nd.email, nd.matKhau, nd.maVT, nd.biKhoa, vt.tenVT
                    FROM nguoidung nd INNER JOIN vaitro vt ON nd.maVT = vt.maVT WHERE nd.email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user && !(int) $user['biKhoa'] && in_array($user['tenVT'], ['Admin', 'User'], true)
                    && password_verify($password, $user['matKhau'])) {
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $_SESSION['user'] = [
                        'id' => (int) $user['maND'], 'name' => $user['tenND'], 'email' => $user['email'],
                        'role_id' => (int) $user['maVT'], 'role' => $user['tenVT'],
                    ];
                    header('Location: ' . url($user['tenVT'] === 'Admin' ? 'admin' : 'account'), true, 303);
                    exit;
                }
                $error = 'Email hoặc mật khẩu không chính xác.';
            }
        }
        $success = $_SESSION['auth_success'] ?? '';
        unset($_SESSION['auth_success']);
        $this->render('auth/form', ['title' => 'Đăng nhập', 'isRegister' => false, 'error' => $error, 'success' => $success]);
    }

    public function register(): void
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = post_string('name');
            $email = post_string('email');
            $password = post_string('password', false);
            $confirm = post_string('confirm', false);
            if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
                $error = 'Họ tên cần từ 2 đến 80 ký tự.';
            } elseif (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email không hợp lệ hoặc dài quá 100 ký tự.';
            } elseif (strlen($password) < 6 || strlen($password) > 72) {
                $error = 'Mật khẩu cần từ 6 đến 72 byte (ký tự có dấu có thể chiếm nhiều byte).';
            } elseif ($password !== $confirm) {
                $error = 'Mật khẩu nhập lại không khớp.';
            } else {
                $db = (new \Database())->getConnection();
                $role = $db->prepare('SELECT maVT FROM vaitro WHERE tenVT = ? LIMIT 1');
                $role->execute(['User']);
                $roleId = $role->fetchColumn();
                if ($roleId === false) {
                    throw new \RuntimeException('Missing User role. Run database/setup.php.');
                }
                try {
                    $stmt = $db->prepare('INSERT INTO nguoidung (tenND, matKhau, email, maVT) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$name, password_hash($password, PASSWORD_DEFAULT), $email, $roleId]);
                    $_SESSION['auth_success'] = 'Đăng ký thành công. Bạn có thể đăng nhập.';
                    header('Location: ' . url('login'), true, 303);
                    exit;
                } catch (PDOException $exception) {
                    if (($exception->errorInfo[1] ?? null) !== 1062) {
                        throw $exception;
                    }
                    $error = 'Email này đã được đăng ký.';
                }
            }
        }
        $this->render('auth/form', ['title' => 'Đăng ký', 'isRegister' => true, 'error' => $error]);
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: ' . url('home'), true, 303);
        exit;
    }
}
