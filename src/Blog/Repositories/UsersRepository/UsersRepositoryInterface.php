<?php


namespace App\Blog\Repositories\UsersRepository;

use App\Blog\User;
use App\Blog\UUID;

interface UsersRepositoryInterface
{
    public function save(User $user): void;
    public function get(UUID $uuid): User;
    public function getByUsername(string $username): User;
}