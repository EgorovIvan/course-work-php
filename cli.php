<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Exceptions\ArgumentsException;
use App\Blog\Post;
use App\Blog\Comment;
use App\Blog\Name;
use App\Blog\User;
use App\Blog\UUID;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use App\Blog\Exceptions\CommandException;
use App\Blog\Exceptions\AppException;
use App\Blog\Commands\CreateUserCommand;
use App\Blog\Commands\CreatePostCommand;
use App\Blog\Commands\Arguments;

// Создаём объект SQLite-репозитория
$usersRepository = new SqliteUsersRepository(
    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
);

$postsRepository = new SqlitePostsRepository(
    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
);

$commentsRepository = new SqliteCommentsRepository(
    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
);

//$command = new CreateUserCommand($usersRepository);
//try {
//    $command->handle(Arguments::fromArgv($argv));
//} catch (AppException $e) {
//    echo "{$e->getMessage()}\n";
//}

$user = $usersRepository->get(new UUID('3b919225-1657-4f14-bba7-ed02a639d6dc'));
$command2 = new CreatePostCommand($postsRepository);
try {
    $command2->handle(Arguments::fromArgv($argv), $user);
} catch (AppException $e) {
    echo "{$e->getMessage()}\n";
} catch (ArgumentsException $e) {
}


//$postsRepository->save(new Post(
//    UUID::random(),
//    $user,
//    'PHP 3',
//    'Версия PHP 3.0 подверглась значительной переработке, определившей современный облик и стиль языка программирования...'
//));

//$post = $postsRepository->get(new UUID('919c66d0-1db8-4b64-8393-6dac614f8269'));
//var_dump($post);

//$post =$postsRepository->get(new UUID('92b6dd82-37d8-41a2-9646-8f2dfd6fcb58'));
//$user = $usersRepository->get(new UUID('0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc'));
//
//$commentsRepository->save(new Comment(
//    UUID::random(),
//    $post,
//    $user,
//    'К концу 1998 года PHP использовался десятками тысяч пользователей'
//));

//$comment = $commentsRepository->get(new UUID('9bb15821-8f88-457f-a065-6b86070f3de9'));
//var_dump($comment);