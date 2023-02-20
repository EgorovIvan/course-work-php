<?php


namespace App\Blog;


class User
{
    public function __construct(
        private UUID $uuid,
        private string $username,
        private string $hashedPassword,
        private Name $name
    )
    {}

    public function __toString(): string
    {
        return "User $this->uuid with name $this->name and login $this->username";
    }

    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function userName(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setLogin(string $username): void
    {
        $this->username = $username;
    }

    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public static function createFrom(
        string $username,
        string $password,
        Name $name
    ): self {
        // Генерируем UUID
        $uuid = UUID::random();
        return new self(
            $uuid,
            $username,
            // Передаём сгенерированный UUID
            // в функцию хеширования пароля
            self::hash($password, $uuid),
            $name
        );
    }
    private static function hash(string $password, UUID $uuid): string
    {
        // Используем UUID в качестве соли
        return hash('sha256', $uuid . $password);
    }

    public function checkPassword(string $password): bool
    {
        // Передаём UUID пользователя
        // в функцию хеширования пароля
        return $this->hashedPassword === self::hash($password, $this->uuid);
    }


    /**
     * @return Name
     */
    public function name(): Name
    {
        return $this->name;
    }
}