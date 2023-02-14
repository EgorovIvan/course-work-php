<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Exceptions\AppException;
use App\Blog\Commands\CreateUserCommand;
use App\Blog\Commands\CreatePostCommand;
use App\Blog\Commands\Arguments;
use Psr\Log\LoggerInterface;

// Подключаем файл bootstrap.php
// и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';


// Получаем объект логгера из контейнера
$logger = $container->get(LoggerInterface::class);

try {
    // При помощи контейнера создаём команду
    $command = $container->get(CreateUserCommand::class);
    $command->handle(Arguments::fromArgv($argv));
} catch (AppException $e) {
    // Логируем информацию об исключении.
// Объект исключения передаётся логгеру
// с ключом "exception".
// Уровень логирования – ERROR
    $logger->error($e->getMessage(), ['exception' => $e]);
}
