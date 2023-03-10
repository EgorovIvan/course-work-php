<?php


use App\Blog\Container\DIContainer;
use App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use App\Blog\Repositories\AuthTokensRepository\SqliteAuthTokensRepository;
use App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use App\Blog\Repositories\LikesRepository\LikesRepositoryForCommentsInterface;
use App\Blog\Repositories\LikesRepository\LikesRepositoryForPostsInterface;
use App\Blog\Repositories\LikesRepository\SqliteLikesRepositoryForComments;
use App\Blog\Repositories\LikesRepository\SqliteLikesRepositoryForPosts;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Http\Actions\Auth\BearerTokenAuthentication;
use App\Http\Auth\AuthenticationInterface;
use App\Http\Auth\JsonBodyUuidIdentification;
use App\Http\Auth\PasswordAuthentication;
use App\Http\Auth\PasswordAuthenticationInterface;
use App\Http\Auth\TokenAuthenticationInterface;
use Dotenv\Dotenv;
use Faker\Provider\Lorem;
use Faker\Provider\ru_RU\Internet;
use Faker\Provider\ru_RU\Person;
use Faker\Provider\ru_RU\Text;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/vendor/autoload.php';

// Загружаем переменные окружения из файла .env
Dotenv::createImmutable(__DIR__)->safeLoad();

// Создаём объект контейнера ..
$container = new DIContainer();
// .. и настраиваем его:
// 1. подключение к БД
$container->bind(
    PDO::class,
    // Берём путь до файла базы данных SQLite
    // из переменной окружения SQLITE_DB_PATH
    new PDO('sqlite:' . __DIR__ . '/' . $_SERVER['SQLITE_DB_PATH'])
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
    LikesRepositoryForPostsInterface::class,
    SqliteLikesRepositoryForPosts::class
);
// 6. репозиторий лайков для комментариев
$container->bind(
    LikesRepositoryForCommentsInterface::class,
    SqliteLikesRepositoryForComments::class
);

// Выносим объект логгера в переменную
$logger = (new Logger('blog'));

// Включаем логирование в файлы,
// если переменная окружения LOG_TO_FILES
// содержит значение 'yes'
if ('yes' === $_SERVER['LOG_TO_FILES']) {
    $logger
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.log'
        ))
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.error.log',
            level: Logger::ERROR,
            bubble: false,
        ));
}
// Включаем логирование в консоль,
// если переменная окружения LOG_TO_CONSOLE
// содержит значение 'yes'
if ('yes' === $_SERVER['LOG_TO_CONSOLE']) {
    $logger
        ->pushHandler(
            new StreamHandler("php://stdout")
        );
}
$container->bind(
    LoggerInterface::class,
    $logger
);

$container->bind(
    AuthenticationInterface::class,
    JsonBodyUuidIdentification::class
);

$container->bind(
    PasswordAuthenticationInterface::class,
    PasswordAuthentication::class
);

$container->bind(
    TokenAuthenticationInterface::class,
    BearerTokenAuthentication::class
);

$container->bind(
    AuthTokensRepositoryInterface::class,
    SqliteAuthTokensRepository::class
);

// Создаём объект генератора тестовых данных
$faker = new \Faker\Generator();
// Инициализируем необходимые нам виды данных
$faker->addProvider(new Person($faker));
$faker->addProvider(new Text($faker));
$faker->addProvider(new Internet($faker));
$faker->addProvider(new Lorem($faker));
// Добавляем генератор тестовых данных
// в контейнер внедрения зависимостей
$container->bind(
    \Faker\Generator::class,
    $faker
);

// Возвращаем объект контейнера
return $container;