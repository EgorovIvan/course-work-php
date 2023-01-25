<?php

namespace App\Article;

class Article
{
    public function __construct(
        private int $id,
        private int $author_id,
        private string $header,
        private string $text
    )
    {}
}