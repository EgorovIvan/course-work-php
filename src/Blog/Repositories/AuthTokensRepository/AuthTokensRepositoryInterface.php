<?php


namespace App\Blog\Repositories\AuthTokensRepository;


use App\Blog\AuthToken;

interface AuthTokensRepositoryInterface
{
    public function save(AuthToken $authToken): void;
    public function get(string $token): AuthToken;
    public function update(AuthToken $authToken): void;

}