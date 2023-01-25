<?php

namespace App\Comment;

class Comment
{
    public function __construct(
        private int $id,
        private int $author_id,
        private int $post_id,
        private string $text
    )
    {}
}