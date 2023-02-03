<?php

namespace App\Blog\Repositories\UnitTests\CommentsRepository;


use App\Blog\Comment;
use App\Blog\Exceptions\CommentNotFoundException;
use App\Blog\Name;
use App\Blog\Post;
use App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use App\Blog\User;
use App\Blog\UUID;
use PHPUnit\Framework\TestCase;


class SqliteCommentsRepositoryTest extends TestCase
{
    public function testItSavesCommentToDatabase(): void
    {
        $connectionStub = $this->createStub(\PDO::class);

        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '9bb15821-8f88-457f-a065-6b86070f3de9',
                ':post_id' => new UUID('919c66d0-1db8-4b64-8393-6dac614f8269'),
                ':author_id' => new UUID('123e4567-e89b-12d3-a456-426614174000'),
                ':text' => 'text'
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteCommentsRepository($connectionStub);

        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'ivan123',
            new Name('Ivan', 'Nikitin')
        );

        $post = new Post(
            new UUID('919c66d0-1db8-4b64-8393-6dac614f8269'),
            $user,
            'title',
            'text'
        );

        $repository->save(
            new Comment(
                new UUID('9bb15821-8f88-457f-a065-6b86070f3de9'),
                $post,
                $user,
                'text'
            )
        );
    }

    /**
     * @throws \App\Blog\Exceptions\PostNotFoundException
     * @throws \App\Blog\Exceptions\UserNotFoundException
     * @throws \App\Blog\Exceptions\InvalidArgumentException|\App\Blog\Exceptions\CommentNotFoundException
     */
    public function testItReturnsCommentByUuid(): void
    {
        $connectionStub = $this->createStub(\PDO::class);

        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '9bb15821-8f88-457f-a065-6b86070f3de9',
            'post_id' => '919c66d0-1db8-4b64-8393-6dac614f8269',
            'author_id' => new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'title' => 'title',
            'text' => 'text',

            'username' => 'ivan123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteCommentsRepository($connectionStub);

        $comment = $repository->get(new UUID('9bb15821-8f88-457f-a065-6b86070f3de9'));

        $this->assertSame('9bb15821-8f88-457f-a065-6b86070f3de9', (string)$comment->uuid());
    }
//

    /**
     * @throws \App\Blog\Exceptions\PostNotFoundException
     * @throws \App\Blog\Exceptions\UserNotFoundException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    public function testItThrowsAnExceptionWhenCommentNotFound(): void
    {
        $connectionStub = $this->createStub(\PDO::class);

        $statementStub = $this->createStub(\PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);

        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqliteCommentsRepository($connectionStub);

        $this->expectException(CommentNotFoundException::class);

        $this->expectExceptionMessage('Cannot find comment: 9bb15821-8f88-457f-a065-6b86070f3de9');

        $repository->get(new UUID('9bb15821-8f88-457f-a065-6b86070f3de9'));
    }
}

