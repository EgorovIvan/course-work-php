<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Commands\CreateUser;
use App\Blog\Commands\DeletePost;
use App\Blog\Commands\FakeData\PopulateDB;
use App\Blog\Commands\UpdateUser;
use Symfony\Component\Console\Application;

// Подключаем файл bootstrap.php
// и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';

// Создаём объект приложения
$application = new Application();

// Перечисляем классы команд
$commandsClasses = [
    CreateUser::class,
    DeletePost::class,
    UpdateUser::class,
    PopulateDB::class,
];
foreach ($commandsClasses as $commandClass) {
// Посредством контейнера
// создаём объект команды
    $command = $container->get($commandClass);
// Добавляем команду к приложению
    $application->add($command);
}

// Запускаем приложение
$application->run();
