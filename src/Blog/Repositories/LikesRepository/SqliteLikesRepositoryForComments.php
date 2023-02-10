<?php


namespace App\Blog\Repositories\LikesRepository;

use App\Blog\Like;
use PDO;

class SqliteLikesRepositoryForComments implements LikesRepositoryInterface
{
    public function __construct(
        private PDO $connection
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
    }

    public function getByObjectUuid(string $comment_id): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_for_comments WHERE comment_id = ?'
        );
        $statement->execute([$comment_id]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}