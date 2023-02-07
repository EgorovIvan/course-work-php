<?php


namespace App\Http\Actions\UnitTests\Posts;


use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Name;
use App\Blog\User;
use App\Blog\UUID;
use App\Blog\Post;
use App\Http\Actions\Posts\CreatePost;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Http\Actions\Users\FindByUsername;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;

class CreatePostTest extends TestCase
{
//Тест, проверяющий, что будет возвращён неудачный ответ,
// если пользователь не найден по UUID
    /**
     * @throws \JsonException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
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

//    /**
//     * @throws \App\Blog\Exceptions\InvalidArgumentException
//     * @throws \App\Blog\Exceptions\HttpException
//     * @throws \JsonException
//     */
//    public function testItReturnsSuccessfulResponse(): void
//    {
//        $json = '{
//            "author_uuid": "0b8818b7-537c-4ad0-8b4b-3de9bfe10fcc",
//            "text": "some text",
//            "title": "some title"
//        }';
//
//        $request = new Request([$json], [], '');
//
//        $authorId = new UUID($request->jsonBodyField('author_id'));
//
//        $user = new User(
//            $authorId,
//            'ivan',
//            new Name('Ivan', 'Nikitin')
//        );
//
//        $usersRepository = $this->usersRepository([
//            $user
//        ]);
//
//        $postsRepository = $this->usersRepository([
//            new Post(
//                UUID::random(),
//                $user,
//                'title',
//                'text'
//            ),
//        ]);
//        $action = new CreatePost($postsRepository, $usersRepository);
//        $response = $action->handle($request);
//// Проверяем, что ответ - удачный
//        $this->assertInstanceOf(SuccessfulResponse::class, $response);
//        $this->expectOutputString('{"success":true,"data":{"uuid":"???"}}');
//        $response->send();
//    }

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