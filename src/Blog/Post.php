<?php

namespace App\Blog;


class Post
{
    public function __construct(
        private UUID $uuid,
        private User $author_id,
        private string $title,
        private string $text
    )
    {}

    public function __toString()
    {
        return $this->getTitle() . ' >>> ' . $this->getText();
    }

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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
}