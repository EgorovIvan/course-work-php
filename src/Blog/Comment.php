<?php

namespace App\Blog;

class Comment
{
    public function __construct(
        private int $id,
        private User $author_id,
        private Post $post_id,
        private string $text
    )
    {}

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function __toString()
    {
        return $this->getText();
    }
}