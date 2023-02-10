<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Exceptions\AppException;
use App\Blog\Commands\CreateUserCommand;
use App\Blog\Commands\CreatePostCommand;
use App\Blog\Commands\Arguments;

// Подключаем файл bootstrap.php
// и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';

try {
    // При помощи контейнера создаём команду
    $command = $container->get(CreateUserCommand::class);
    $command->handle(Arguments::fromArgv($argv));
} catch (AppException $e) {
    echo "{$e->getMessage()}\n";
}
