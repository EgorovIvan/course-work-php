<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Exceptions\AppException;
use App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Http\Actions\Comments\CreateComment;
use App\Http\Actions\Posts\CreatePost;
use App\Http\Actions\Posts\FindByUuid;
use App\Http\Actions\Users\FindByUsername;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Blog\Exceptions\HttpException;

$request = new Request(
    $_GET,
    $_SERVER,
// Читаем поток, содержащий тело запроса
    file_get_contents('php://input'),
);

try {
// Пытаемся получить путь из запроса
    $path = $request->path();
} catch (HttpException) {
// Отправляем неудачный ответ,
// если по какой-то причине
// не можем получить путь
    (new ErrorResponse)->send();
// Выходим из программы
    return;
}

try {
// Пытаемся получить HTTP-метод запроса
    $method = $request->method();
} catch (HttpException) {
// Возвращаем неудачный ответ,
// если по какой-то причине
// не можем получить метод
    (new ErrorResponse)->send();
    return;
}

$routes = [
// Добавили ещё один уровень вложенности
// для отделения маршрутов,
// применяемых к запросам с разными методами
    'GET' => [
        '/users/show' => new FindByUsername(
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/posts/show' => new FindByUuid(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
    ],
    'POST' => [
// Добавили новый маршрут
        '/posts/create' => new CreatePost(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            ),
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/posts/comment' => new CreateComment(
            new SqliteCommentsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            ),
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            ),
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            ),

        ),
    ],
];

// Если у нас нет маршрута для пути из запроса -
// отправляем неуспешный ответ
if (!array_key_exists($method, $routes)) {
    (new ErrorResponse('Not found'))->send();
    return;
}

// Ищем маршрут среди маршрутов для этого метода
if (!array_key_exists($path, $routes[$method])) {
    (new ErrorResponse('Not found'))->send();
    return;
}

// Выбираем действие по методу и пути
$action = $routes[$method][$path];

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



