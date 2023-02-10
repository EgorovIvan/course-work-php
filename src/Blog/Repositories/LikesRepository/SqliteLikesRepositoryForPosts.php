<?php


namespace App\Blog\Repositories\LikesRepository;

use App\Blog\Like;
use PDO;

class SqliteLikesRepositoryForPosts implements LikesRepositoryInterface
{
    public function __construct(
        private PDO $connection
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
    }

    public function getByObjectUuid(string $post_id): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes WHERE post_id = ?'
        );
        $statement->execute([$post_id]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}