<?php


namespace App\Http\UnitTests\Actions;


use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Name;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\UnitTests\DummyLogger;
use App\Blog\User;
use App\Blog\UUID;
use App\Blog\Post;
use App\Http\Actions\Posts\CreatePost;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Http\Auth\AuthenticationInterface;
use App\Http\Auth\TokenAuthenticationInterface;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;

class CreatePostActionTest extends TestCase
{
    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface
        {
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
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid == $user->uuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException('Cannot find user: ' . $uuid);
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException('Not found');
            }
        };
    }
    private function identification(): TokenAuthenticationInterface
    {
        return new class() implements TokenAuthenticationInterface {
            public function __construct(
            )
            {

            }

            public function user(Request $request): User
            {
                throw new UserNotFoundException("Not found");
            }
        };
    }

    private function postsRepository(): PostsRepositoryInterface
    {
        return new class() implements PostsRepositoryInterface {
            private bool $called = false;

            public function __construct(
            )
            {
            }

            public function save(Post $post): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException("Not found");
            }
            public function getCalled(): bool
            {
                return $this->called;
            }
        };
    }
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

//        $usersRepository = $this->usersRepository([]);
        $postsRepository = $this->postsRepository();
        $identification = $this->identification();

        $action = new CreatePost($postsRepository, $identification, new DummyLogger());

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

//        $usersRepository = $this->usersRepository([]);
        $postsRepository = $this->postsRepository();
        $identification = $this->identification();

        $action = new CreatePost($postsRepository, $identification, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: 0b8818b7"}'
        );

        $response->send();
    }

    /**
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     * @throws \JsonException
     */
//Тест, проверяющий, что будет возвращён удачный ответ,
// если пользователь найден
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{"user_id":"3b919225-1657-4f14-bba7-ed02a639d6dc","title":"title","text":"text"}');

        $postsRepository = $this->postsRepository();

        $usersRepository = $this->usersRepository([
            new User(
                new UUID('3b919225-1657-4f14-bba7-ed02a639d6dc'),
                'username',
                'password',
                new Name('name', 'surname'),
            ),
        ]);
        $identification = $this->identification($usersRepository);
        $action = new CreatePost($postsRepository, $identification, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->setOutputCallback(function ($data){
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            $dataDecode['data']['uuid'] = "3b919225-1657-4f14-bba7-ed02a639d6dc";
            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true,"data":{"uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}}');

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

//        $repository = new SqliteUsersRepository($connectionStub, new DummyLogger());
        $identification = $this->identification();
//        $newUser = $repository->get(new UUID('0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc'));

        $request = new Request([], [], '{}');

//        $newUser = $identification->user($request);
//        $authorId = new UUID($request->jsonBodyField('author_id'));
//        $header = $request->header('Authorization');

        $postsRepository = $this->postsRepository();
        $action = new CreatePost($postsRepository, $identification, new DummyLogger());

        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such field: title"}');
        $response->send();
    }

}