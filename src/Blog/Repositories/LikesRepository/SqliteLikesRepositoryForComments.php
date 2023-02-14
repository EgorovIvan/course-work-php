<?php


namespace App\Blog\Repositories\LikesRepository;

use App\Blog\Exceptions\CommentNotFoundException;
use App\Blog\Like;
use App\Blog\UUID;
use PDO;
use Psr\Log\LoggerInterface;

class SqliteLikesRepositoryForComments implements LikesRepositoryInterface
{
    public function __construct(
        private PDO $connection,
        private LoggerInterface $logger
    )
    {}

    public function save(Like $like): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likes_for_comments (uuid, comment_id, user_id)
                    VALUES (:uuid, :comment_id, :user_id)'
        );
        $statement->execute([
            ':uuid' => (string)$like->uuid(),
            ':comment_id' => $like->getObjectId(),
            ':user_id' => $like->getUserId()
        ]);

        $likeUuid = (string)$like->uuid();
        $this->logger->info("Like saved: $likeUuid");
    }

    public function getByObjectUuid(string $comment_id): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_for_comments WHERE comment_id = ?'
        );
        $statement->execute([$comment_id]);

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($result === null) {
            $this->logger->warning("Cannot find comment: $comment_id");
            throw new CommentNotFoundException(
                "Cannot find comment: $comment_id"
            );
        }
        return $result;

    }
}