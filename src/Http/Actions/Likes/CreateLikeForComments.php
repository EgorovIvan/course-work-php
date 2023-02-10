<?php


namespace App\Http\Actions\Likes;

use App\Blog\Exceptions\CommentNotFoundException;
use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Like;
use App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use App\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\UUID;
use App\Http\Actions\ActionInterface;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\Response;
use App\Http\SuccessfulResponse;

class CreateLikeForComments implements ActionInterface
{
    public function __construct(
        private LikesRepositoryInterface $likesRepository,
        private CommentsRepositoryInterface $commentsRepository,
        private UsersRepositoryInterface $usersRepository,
    ) {
    }

    /**
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     * @throws \JsonException
     */
    public function handle(Request $request): Response
    {
        try {
            $commentId = new UUID($request->jsonBodyField('comment_id'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $comment = $this->commentsRepository->get($commentId);
        } catch (CommentNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $userId = new UUID($request->jsonBodyField('user_id'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $user = $this->usersRepository->get($userId);
        } catch (UserNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $commentLikes = $this->likesRepository->getByObjectUuid($commentId);

        foreach ($commentLikes as $comment)
        {
            if ($comment['comment_id'] == $commentId && $comment['user_id'] == $userId) {
                return new ErrorResponse('You have already liked');
            }
        }

        $newLikeUuid = UUID::random();

        try {
            $like = new Like(
                $newLikeUuid,
                $commentId,
                $userId,
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