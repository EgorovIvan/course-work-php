<?php


namespace App\Blog;


class User
{
    public function __construct(
        private UUID $uuid,
        private string $username,
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
     * @param string $username
     */
    public function setLogin(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return Name
     */
    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function userName(): string
    {
        return $this->username;
    }
}