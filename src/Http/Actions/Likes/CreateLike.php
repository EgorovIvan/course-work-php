<?php


namespace App\Http\Actions\Likes;

use App\Blog\Exceptions\AuthException;
use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Like;
use App\Blog\Repositories\LikesRepository\LikesRepositoryForPostsInterface;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\UUID;
use App\Http\Actions\ActionInterface;
use App\Http\Auth\PasswordAuthenticationInterface;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\Response;
use App\Http\SuccessfulResponse;

class CreateLike implements ActionInterface
{
    public function __construct(
        private LikesRepositoryForPostsInterface $likesRepository,
        private PostsRepositoryInterface $postsRepository,
        private PasswordAuthenticationInterface $passwordAuthentication,
    ) {
    }

    /**
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     * @throws \JsonException
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

        $postLikes = $this->likesRepository->getByObjectUuid($postId);
        $userId = $author->uuid();

        foreach ($postLikes as $post)
        {
            if ($post['post_id'] == $postId && $post['user_id'] == $userId) {
                return new ErrorResponse('You have already liked');
            }
        }

        $newLikeUuid = UUID::random();

        try {
            $like = new Like(
                $newLikeUuid,
                $postId,
                (string)$userId,
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->likesRepository->save($like);

        return new SuccessfulResponse([
            'uuid' => (string)$newLikeUuid,
        ]);
    }
}