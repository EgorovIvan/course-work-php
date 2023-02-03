<?php


namespace App\Blog\Commands;

use App\Blog\Name;
use App\Blog\User;
use App\Blog\UUID;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\Exceptions\UserNotFoundException;


// Dummy - чучуло, манекен
class DummyUsersRepository implements UsersRepositoryInterface
{
    public function save(User $user): void
    {
// Ничего не делаем
    }
    public function get(UUID $uuid): User
    {
// И здесь ничего не делаем
        throw new UserNotFoundException("Not found");
    }
    public function getByUsername(string $username): User
    {
// Нас интересует реализация только этого метода
// Для нашего теста не важно, что это будет за пользователь,
// поэтому возвращаем совершенно произвольного
        return new User(UUID::random(), "user123", new Name("first", "last"));
    }
}
