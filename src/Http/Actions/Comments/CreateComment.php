<?php


namespace App\Http\Actions\Comments;


use App\Blog\Comment;
use App\Blog\Exceptions\AuthException;
use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\UUID;
use App\Http\Actions\ActionInterface;
use App\Http\Auth\PasswordAuthenticationInterface;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\Response;
use App\Http\SuccessfulResponse;

class CreateComment implements ActionInterface
{
    public function __construct(
        private CommentsRepositoryInterface $commentsRepository,
        private PostsRepositoryInterface $postsRepository,
        private PasswordAuthenticationInterface $passwordAuthentication,
    ) {
    }

    /**
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    public function handle(Request $request): Response
    {

        try {
            $postId = new UUID($request->jsonBodyField('post_id'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $post = $this->postsRepository->get($postId);
        } catch (PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $author = $this->passwordAuthentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newCommentUuid = UUID::random();

        try {
            $comment = new Comment(
                $newCommentUuid,
                $post,
                $author,
                $request->jsonBodyField('text'),
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->commentsRepository->save($comment);

        return new SuccessfulResponse([
            'uuid' => (string)$newCommentUuid,
        ]);
    }
}