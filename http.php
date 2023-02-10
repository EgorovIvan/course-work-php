<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Exceptions\AppException;
use App\Http\Actions\Comments\CreateComment;
use App\Http\Actions\Likes\CreateLike;
use App\Http\Actions\Likes\CreateLikeForComments;
use App\Http\Actions\Posts\CreatePost;
use App\Http\Actions\Posts\FindByUuid;
use App\Http\Actions\Users\CreateUser;
use App\Http\Actions\Users\FindByUsername;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Blog\Exceptions\HttpException;

// Подключаем файл bootstrap.php
// и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';
$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);
try {
    $path = $request->path();
} catch (HttpException) {
    (new ErrorResponse)->send();
    return;
}
try {
    $method = $request->method();
} catch (HttpException) {
    (new ErrorResponse)->send();
    return;
}

$routes = [
// Добавили ещё один уровень вложенности
// для отделения маршрутов,
// применяемых к запросам с разными методами
    'GET' => [
        '/users/show' => FindByUsername::class,
        '/posts/show' => FindByUuid::class,
    ],
    'POST' => [
// Добавили новый маршрут
        '/users/create' => CreateUser::class,
        '/posts/create' => CreatePost::class,
        '/posts/create_comment' => CreateComment::class,
        '/posts/create_like' => CreateLike::class,
        '/comments/create_like' => CreateLikeForComments::class,
    ],
];

if (!array_key_exists($method, $routes)) {
    (new ErrorResponse("Route not found: $method $path"))->send();
    return;
}
if (!array_key_exists($path, $routes[$method])) {
    (new ErrorResponse("Route not found: $method $path"))->send();
    return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];

// С помощью контейнера
// создаём объект нужного действия
$action = $container->get($actionClassName);

try {
// Пытаемся выполнить действие,
// при этом результатом может быть
// как успешный, так и неуспешный ответ
    $response = $action->handle($request);
} catch (AppException $e) {
// Отправляем неудачный ответ,
// если что-то пошло не так
    (new ErrorResponse($e->getMessage()))->send();
}

// Отправляем ответ
$response->send();



