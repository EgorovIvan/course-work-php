<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Exceptions\AppException;
use App\Http\Actions\Auth\LogIn;
use App\Http\Actions\Auth\LogOut;
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
use Psr\Log\LoggerInterface;

// Подключаем файл bootstrap.php
// и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';

// Получаем объект логгера из контейнера
$logger = $container->get(LoggerInterface::class);

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);
try {
    $path = $request->path();
} catch (HttpException $e) {
// Логируем сообщение с уровнем WARNING
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}
try {
    $method = $request->method();
} catch (HttpException $e) {
    // Логируем сообщение с уровнем WARNING
    $logger->warning($e->getMessage());
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
        '/login' => LogIn::class,
        '/logout' => LogOut::class,
        '/users/create' => CreateUser::class,
        '/posts/create' => CreatePost::class,
        '/posts/create_comment' => CreateComment::class,
        '/posts/create_like' => CreateLike::class,
        '/comments/create_like' => CreateLikeForComments::class,
    ],
];

if (!array_key_exists($method, $routes)
    || !array_key_exists($path, $routes[$method])) {
// Логируем сообщение с уровнем NOTICE
    $message = "Route not found: $method $path";
    $logger->notice($message);
    (new ErrorResponse($message))->send();
    return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];

try {
    // С помощью контейнера
// создаём объект нужного действия
    $action = $container->get($actionClassName);
// Пытаемся выполнить действие,
// при этом результатом может быть
// как успешный, так и неуспешный ответ
    $response = $action->handle($request);
} catch (AppException $e) {
// Отправляем неудачный ответ,
// если что-то пошло не так
    (new ErrorResponse($e->getMessage()))->send();
    $logger->error($e->getMessage(), ['exception' => $e]);

}

// Отправляем ответ
$response->send();



