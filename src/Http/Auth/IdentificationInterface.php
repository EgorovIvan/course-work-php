<?php


namespace App\Http\Auth;


use App\Blog\User;
use App\Http\Request;

interface IdentificationInterface
{
// Контракт описывает единственный метод,
// получающий пользователя из запроса
    public function user(Request $request): User;
}