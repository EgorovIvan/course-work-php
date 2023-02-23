<?php

namespace App\Blog;

use JetBrains\PhpStorm\Pure;

class Comment
{
    public function __construct(
        private UUID $uuid,
        private Post $post_id,
        private User $author_id,
        private string $text
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
     * @return User
     */
    public function getAuthorId(): User
    {
        return $this->author_id;
    }

    /**
     * @return Post
     */
    public function getPostId(): Post
    {
        return $this->post_id;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    #[Pure] public function __toString()
    {
        return $this->getText();
    }
}