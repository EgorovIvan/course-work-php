<?php


namespace App\Blog\Repositories\UnitTests\PostsRepository;


use App\Blog\UnitTests\DummyLogger;
use App\Blog\User;
use App\Blog\Post;
use App\Blog\Name;
use App\Blog\UUID;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Exceptions\PostNotFoundException;
use PHPUnit\Framework\TestCase;

class SqlitePostsRepositoryTest extends TestCase
{
    public function testItSavesPostToDatabase(): void
    {
        $connectionStub = $this->createStub(\PDO::class);

        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '919c66d0-1db8-4b64-8393-6dac614f8269',
                ':author_id' => new UUID('123e4567-e89b-12d3-a456-426614174000'),
                ':title' => 'title',
                ':text' => 'text'
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqlitePostsRepository($connectionStub, new DummyLogger());

        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'ivan123',
            'some_password',
            new Name(
                'Ivan',
                'Nikitin'
            )
        );

        $repository->save(
            new Post(
                new UUID('919c66d0-1db8-4b64-8393-6dac614f8269'),
                $user,
                'title',
                'text'
            )
        );
    }

    /**
     * @throws \App\Blog\Exceptions\PostNotFoundException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     * @throws \App\Blog\Exceptions\UserNotFoundException
     */
    public function testItReturnsPostByUuid(): void
    {
        $connectionStub = $this->createStub(\PDO::class);

        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => new UUID('919c66d0-1db8-4b64-8393-6dac614f8269'),
            'author_id' => new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'title' => 'title',
            'text' => 'text',
            'username' => 'ivan123',
            'password' => 'password123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqlitePostsRepository($connectionStub, new DummyLogger());

         $post = $repository->get(new UUID('919c66d0-1db8-4b64-8393-6dac614f8269'));

        $this->assertSame('919c66d0-1db8-4b64-8393-6dac614f8269', (string)$post->uuid());
    }

    /**
     * @throws \App\Blog\Exceptions\PostNotFoundException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     * @throws \App\Blog\Exceptions\UserNotFoundException
     */
    public function testItThrowsAnExceptionWhenPostNotFound(): void
    {
        $connectionStub = $this->createStub(\PDO::class);

        $statementStub = $this->createStub(\PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);

        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqlitePostsRepository($connectionStub, new DummyLogger());

        $this->expectException(PostNotFoundException::class);

        $this->expectExceptionMessage('Cannot find post: 919c66d0-1db8-4b64-8393-6dac614f8269');

        $repository->get(new UUID('919c66d0-1db8-4b64-8393-6dac614f8269'));
    }
}