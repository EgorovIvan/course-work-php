
<?php


require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Container\DIContainer;
use App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use App\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use App\Blog\Repositories\LikesRepository\SqliteLikesRepositoryForComments;
use App\Blog\Repositories\LikesRepository\SqliteLikesRepositoryForPosts;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;

// Создаём объект контейнера ..
$container = new DIContainer();
// .. и настраиваем его:
// 1. подключение к БД
$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
);
// 2. репозиторий статей
$container->bind(
    PostsRepositoryInterface::class,
    SqlitePostsRepository::class
);
// 3. репозиторий пользователей
$container->bind(
    UsersRepositoryInterface::class,
    SqliteUsersRepository::class
);
// 4. репозиторий комментариев
$container->bind(
    CommentsRepositoryInterface::class,
    SqliteCommentsRepository::class
);
// 5. репозиторий лайков для постов
$container->bind(
    LikesRepositoryInterface::class,
    SqliteLikesRepositoryForPosts::class
);
// 6. репозиторий лайков для комментариев
$container->bind(
    LikesRepositoryInterface::class,
    SqliteLikesRepositoryForComments::class
);


// Возвращаем объект контейнера
return $container;