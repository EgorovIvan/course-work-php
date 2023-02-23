<?php


namespace App\Blog\Repositories\PostsRepository;

use App\Blog\Post;
use App\Blog\UUID;
use App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use App\Blog\Exceptions\PostNotFoundException;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    public function __construct(
        private PDO $connection,
        private LoggerInterface $logger
    )
    {}

    public function save(Post $post): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts (uuid, author_id, title, text)
                    VALUES (:uuid, :author_id, :title, :text)'
        );
        $statement->execute([
            ':uuid' => (string)$post->uuid(),
            ':author_id' => $post->getAuthorId()->uuid(),
            ':title' => $post->getTitle(),
            ':text' => $post->getText(),
        ]);

        $postUuid = (string)$post->uuid();
        $this->logger->info("Post saved: $postUuid");
    }

    /**
     * @throws PostNotFoundException
     * @throws \App\Blog\Exceptions\UserNotFoundException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE uuid = ?'
        );
        $statement->execute([(string)$uuid]);

        return $this->getPost($statement, $uuid);
    }

    /**
     * @throws PostNotFoundException
     * @throws \App\Blog\Exceptions\UserNotFoundException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    private function getPost(PDOStatement $statement, string $post): Post
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $this->logger->warning("Cannot find post: $post");
            throw new PostNotFoundException(
                "Cannot find post: $post}"
            );
        }

        $usersRepository = new SqliteUsersRepository($this->connection, $this->logger);

        $user = $usersRepository->get(new UUID($result['author_id']));

        return new Post(
            new UUID($result['uuid']),
            $user,
            $result['title'],
            $result['text']
        );
    }
}