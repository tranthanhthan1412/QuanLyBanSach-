<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class AuthController extends Controller
{
    public function login(): void
    {
        $this->render('auth/form', ['title' => 'Đăng nhập', 'isRegister' => false]);
    }

    public function register(): void
    {
        $this->render('auth/form', ['title' => 'Đăng ký', 'isRegister' => true]);
    }
}
