<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use PDO;

final class AuthController extends Controller
{

    // =========================
    // ĐĂNG NHẬP
    // =========================

    public function login(): void
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';


            if ($email === '' || $password === '') {

                $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';

            } else {

                $database = new \Database();

                $db = $database->getConnection();


                $sql = "
                    SELECT
                        nd.maND,
                        nd.tenND,
                        nd.email,
                        nd.matKhau,
                        nd.maVT,
                        vt.tenVT

                    FROM nguoidung nd

                    INNER JOIN vaitro vt
                        ON nd.maVT = vt.maVT

                    WHERE nd.email = ?

                    LIMIT 1
                ";


                $stmt = $db->prepare($sql);

                $stmt->execute([
                    $email
                ]);


                $user = $stmt->fetch(PDO::FETCH_ASSOC);


                if (
                    $user
                    && password_verify(
                        $password,
                        $user['matKhau']
                    )
                ) {

                    // Tạo session user
                    $_SESSION['user'] = [

                        'id' => $user['maND'],

                        'name' => $user['tenND'],

                        'email' => $user['email'],

                        'role_id' => $user['maVT'],

                        'role' => $user['tenVT'],

                    ];


                    // Về trang chủ
                    header(
                        'Location: ' . url('home')
                    );

                    exit;

                } else {

                    $error =
                        'Email hoặc mật khẩu không chính xác.';

                }
            }
        }


        $this->render(
            'auth/form',
            [
                'title' => 'Đăng nhập',

                'isRegister' => false,

                'error' => $error,
            ]
        );
    }


    // =========================
    // ĐĂNG KÝ
    // =========================

    public function register(): void
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $name =
                trim($_POST['name'] ?? '');

            $email =
                trim($_POST['email'] ?? '');

            $password =
                $_POST['password'] ?? '';

            $confirm =
                $_POST['confirm'] ?? '';

            $phone =
                trim($_POST['phone'] ?? '');

            $address =
                trim($_POST['address'] ?? '');


            // =========================
            // VALIDATE
            // =========================

            if (
                $name === ''
                || $email === ''
                || $password === ''
            ) {

                $error =
                    'Vui lòng nhập đầy đủ thông tin bắt buộc.';

            } elseif (
                !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $error =
                    'Email không hợp lệ.';

            } elseif (
                strlen($password) < 6
            ) {

                $error =
                    'Mật khẩu phải có ít nhất 6 ký tự.';

            } elseif (
                $password !== $confirm
            ) {

                $error =
                    'Mật khẩu nhập lại không khớp.';

            } else {

                $database =
                    new \Database();

                $db =
                    $database->getConnection();


                // Kiểm tra email
                $check = $db->prepare(
                    "
                    SELECT maND
                    FROM nguoidung
                    WHERE email = ?
                    LIMIT 1
                    "
                );


                $check->execute([
                    $email
                ]);


                if ($check->fetch()) {

                    $error =
                        'Email này đã được đăng ký.';

                } else {

                    // Hash mật khẩu
                    $passwordHash =
                        password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );


                    // Tạo user
                    // maVT = 2 -> User
                    $sql = "
                        INSERT INTO nguoidung
                        (
                            tenND,
                            matKhau,
                            email,
                            SDT,
                            diaChi,
                            maVT
                        )

                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            2
                        )
                    ";


                    $stmt =
                        $db->prepare($sql);


                    $stmt->execute([
                        $name,
                        $passwordHash,
                        $email,
                        $phone,
                        $address
                    ]);


                    // Đăng ký xong -> login
                    header(
                        'Location: ' . url('login')
                    );

                    exit;
                }
            }
        }


        $this->render(
            'auth/form',
            [
                'title' => 'Đăng ký',

                'isRegister' => true,

                'error' => $error,
            ]
        );
    }


    // =========================
    // ĐĂNG XUẤT
    // =========================

    public function logout(): void
    {
        unset($_SESSION['user']);

        header(
            'Location: ' . url('home')
        );

        exit;
    }
}