<?php


namespace App\Http\Auth;


use App\Blog\User;
use App\Http\Request;

interface AuthenticationInterface
{
    // Контракт описывает единственный метод,
    // получающий пользователя из запроса
    public function user(Request $request): User;
}