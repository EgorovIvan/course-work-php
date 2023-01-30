<?php


namespace App\Blog\Commands;

use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Post;
use App\Blog\User;
use App\Blog\UUID;

class CreatePostCommand
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
    ) {
    }

    /**
     * @throws \App\Blog\Exceptions\ArgumentsException
     */
    public function handle(Arguments $arguments, User $user): void
    {
        $title = $arguments->get('title');
        $text = $arguments->get('text');

        $this->postsRepository->save(new Post(
            UUID::random(),
            $user,
            $title,
            $text
        ));
    }

}