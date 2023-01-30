<?php


namespace App\Blog\Repositories\CommentsRepository;

use App\Blog\Comment;
use App\Blog\UUID;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use App\Blog\Exceptions\CommentNotFoundException;
use PDO;
use PDOStatement;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    public function __construct(
        private PDO $connection
    )
    {}

    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, post_id, author_id, text)
                    VALUES (:uuid, :post_id, :author_id, :text)'
        );
        $statement->execute([
            ':uuid' => (string)$comment->uuid(),
            ':post_id' => $comment->getPostId()->uuid(),
            ':author_id' => $comment->getAuthorId()->uuid(),
            ':text' => $comment->getText(),
        ]);
    }

    /**
     * @throws CommentNotFoundException
     * @throws \App\Blog\Exceptions\UserNotFoundException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     * @throws \App\Blog\Exceptions\PostNotFoundException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE uuid = ?'
        );
        $statement->execute([(string)$uuid]);

        return $this->getComment($statement, $uuid);
    }

    /**
     * @throws CommentNotFoundException
     * @throws \App\Blog\Exceptions\UserNotFoundException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     * @throws \App\Blog\Exceptions\PostNotFoundException
     */
    private function getComment(PDOStatement $statement, string $comment): Comment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (false === $result) {
            throw new CommentNotFoundException(
                "Cannot find comment: $comment"
            );
        }

        $postsRepository = new SqlitePostsRepository($this->connection);
        $usersRepository = new SqliteUsersRepository($this->connection);

        $post = $postsRepository->get(new UUID($result['post_id']));
        $user = $usersRepository->get(new UUID($result['author_id']));

        return new Comment(
            new UUID($result['uuid']),
            $post,
            $user,
            $result['text']
        );
    }
}