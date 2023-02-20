<?php


namespace App\Http\Auth;


use App\Blog\Exceptions\AuthException;
use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\User;
use App\Http\Request;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;

class PasswordAuthentication implements PasswordAuthenticationInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    ) {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        // 1. Идентифицируем пользователя
        try {
            $username = $request->jsonBodyField('username');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }
        try {
            $user = $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
            throw new AuthException($e->getMessage());
        }
        // 2. Аутентифицируем пользователя
        // Проверяем, что предъявленный пароль
        // соответствует сохранённому в БД
        try {
            $password = $request->jsonBodyField('password');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }

        // Вычисляем SHA-256-хеш предъявленного пароля
        $hash = hash('sha256', $password);

        if ($hash !== $user->hashedPassword()) {
            // Если пароли не совпадают — бросаем исключение
            throw new AuthException('Wrong password');
        }
        // Пользователь аутентифицирован
        return $user;
    }
}