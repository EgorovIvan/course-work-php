<?php


namespace App\Blog\Repositories\UsersRepository;

use App\Blog\Name;
use App\Blog\User;
use App\Blog\UUID;
use App\Blog\Exceptions\UserNotFoundException;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    public function __construct(
        private PDO $connection,
        private LoggerInterface $logger
    )
    {
    }

    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (uuid, username, password, first_name, last_name)
                    VALUES (:uuid, :username, :password, :first_name, :last_name)'
        );
        $statement->execute([
            ':uuid' => (string)$user->uuid(),
            ':username' => $user->userName(),
            ':password' => $user->hashedPassword(),
            ':first_name' => $user->name()->first(),
            ':last_name' => $user->name()->last(),
        ]);

        $userUuid = (string)$user->uuid();
        $this->logger->info("User data saved: $userUuid");
    }

    /**
     * @throws UserNotFoundException|\App\Blog\Exceptions\InvalidArgumentException
     */
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE uuid = ?'
        );
        $statement->execute([(string)$uuid]);

        return $this->getUser($statement, $uuid);
    }

    /**
     * @throws UserNotFoundException|\App\Blog\Exceptions\InvalidArgumentException
     */
    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );
        $statement->execute([
            ':username' => $username,
        ]);
        return $this->getUser($statement, $username);
    }

    /**
     * @throws UserNotFoundException|\App\Blog\Exceptions\InvalidArgumentException
     */
    private function getUser(PDOStatement $statement, string $username): User
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $this->logger->warning("Cannot find user: $username");
            throw new UserNotFoundException(
                "Cannot find user: $username"
            );
        }

        return new User(
            new UUID($result['uuid']),
            (string)$result['username'],
            (string)$result['password'],
            new Name(
                $result['first_name'],
                $result['last_name']),
        );
    }
}