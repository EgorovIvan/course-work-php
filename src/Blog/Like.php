<?php


namespace App\Blog;


class Like
{
    public function __construct(
        private UUID $uuid,
        private string $like_object_id,
        private string $user_id,
    )
    {}

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
    public function getObjectId(): string
    {
        return $this->like_object_id;
    }

    /**
     * @param string $like_object_id
     */
    public function setObjectId(string $like_object_id): void
    {
        $this->like_object_id = $like_object_id;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->user_id;
    }

    /**
     * @param string $user_id
     */
    public function setUserId(string $user_id): void
    {
        $this->user_id = $user_id;
    }
}