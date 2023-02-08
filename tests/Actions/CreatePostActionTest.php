<?php


namespace App\Http\UnitTests\Actions;


use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\User;
use App\Blog\UUID;
use App\Blog\Post;
use App\Http\Actions\Posts\CreatePost;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;

class CreatePostActionTest extends TestCase
{
    /**
     * @throws \JsonException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    //Тест, проверяющий, что будет возвращён неудачный ответ,
    // если пользователь не найден по UUID
    public function testItReturnsErrorResponseIfUserNotFoundByUuid(): void
    {
        //работает если в Response удалить header('Content-Type: application/json');
        $request = new Request([], [], '{"author_id": "0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc"}');

        $usersRepository = $this->usersRepository([]);
        $postsRepository = $this->postsRepository([]);

        $action = new CreatePost($postsRepository, $usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->expectOutputString('{"success":false,"reason":"Not found"}'
        );

        $response->send();
    }


    //Тест, проверяющий, что будет возвращён неудачный ответ,
    // если UUID пользователя введен в неверном формате
    public function testItReturnsErrorResponseIfUuidInInvalidFormat(): void
    {
        //работает если в Response удалить header('Content-Type: application/json');
        $request = new Request([], [], '{"author_id": "0b8818b7"}');

        $usersRepository = $this->usersRepository([]);
        $postsRepository = $this->postsRepository([]);

        $action = new CreatePost($postsRepository, $usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: 0b8818b7"}'
        );

        $response->send();
    }

    /**
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     * @throws \App\Blog\Exceptions\HttpException
     * @throws \JsonException|\App\Blog\Exceptions\UserNotFoundException
     */
//Тест, проверяющий, что будет возвращён удачный ответ,
// если пользователь найден
    public function testItReturnsSuccessfulResponse(): void
    {
        $connectionStub = $this->createStub(\PDO::class);
        $statementMock = $this->createMock(\PDOStatement::class);
        $statementMock->method('fetch')->willReturn([
                'uuid' => '0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc',
                'username' => 'ivan',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);
        $repository = new SqliteUsersRepository($connectionStub);
        $newUser = $repository->get(new UUID('0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc'));

        $request = new Request([], [], '{
            "author_id":"0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc",
            "title":"some title",
            "text": "some text"
            }');

        $authorId = new UUID($request->jsonBodyField('author_id'));

        $postsRepository = $this->postsRepository([
            new Post(
                $authorId,
                $newUser,
                'some title',
                'some text'
            ),
        ]);
        $action = new CreatePost($postsRepository, $repository);

        $response = $action->handle($request);
    // Проверяем, что ответ - удачный
        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"title":"some title","text":"some text"}}');
        $response->send();
    }

    /**
     * @throws \App\Blog\Exceptions\UserNotFoundException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     * @throws \JsonException
     * @throws \App\Blog\Exceptions\HttpException
     */
    //Тест, проверяющий, что будет возвращён неудачный ответ,
    // если пользователь найден
    public function testItReturnsErrorResponseIfRequestNotContainAllDataForPostCreation(): void
    {
        $connectionStub = $this->createStub(\PDO::class);
        $statementMock = $this->createMock(\PDOStatement::class);
        $statementMock->method('fetch')->willReturn([
            'uuid' => '0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc',
            'username' => 'ivan',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]);
        $connectionStub->method('prepare')->willReturn($statementMock);
        $repository = new SqliteUsersRepository($connectionStub);
        $newUser = $repository->get(new UUID('0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc'));

        $request = new Request([], [], '{
            "author_id":"0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc",
            "text": "some text"
            }');

        $authorId = new UUID($request->jsonBodyField('author_id'));

        $postsRepository = $this->postsRepository([
            new Post(
                $authorId,
                $newUser,
                'some title',
                'some text'
            ),
        ]);
        $action = new CreatePost($postsRepository, $repository);

        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such field: title"}');
        $response->send();
    }

    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface {
            public function __construct(
                private array $users
            )
            {
            }

            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && $username === $user->username()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Not found");
            }
        };
    }

    private function postsRepository(array $posts): PostsRepositoryInterface
    {
        return new class($posts) implements PostsRepositoryInterface {
            public function __construct(
                private array $posts
            )
            {
            }

            public function save(Post $posts): void
            {
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException("Not found");
            }
        };
    }
}