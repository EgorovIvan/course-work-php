<?php


namespace App\Blog\Repositories\LikesRepository;

use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Like;
use PDO;
use Psr\Log\LoggerInterface;

class SqliteLikesRepositoryForPosts implements LikesRepositoryForPostsInterface
{
    public function __construct(
        private PDO $connection,
        private LoggerInterface $logger
    )
    {}

    public function save(Like $like): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likes (uuid, post_id, user_id)
                    VALUES (:uuid, :post_id, :user_id)'
        );
        $statement->execute([
            ':uuid' => (string)$like->uuid(),
            ':post_id' => $like->getObjectId(),
            ':user_id' => $like->getUserId()
        ]);

        $likeUuid = (string)$like->uuid();
        $this->logger->info("Like saved: $likeUuid");
    }

    /**
     * @throws PostNotFoundException
     */
    public function getByObjectUuid(string $post_id): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes WHERE post_id = ?'
        );
        $statement->execute([$post_id]);

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($result === null) {
            $this->logger->warning("Cannot find comment: $post_id");
            throw new PostNotFoundException(
                "Cannot find comment: $post_id"
            );
        }
        return $result;
    }
}