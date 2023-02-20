<?php


namespace App\Http\Actions\Posts;


use App\Blog\Exceptions\AuthException;
use App\Blog\Exceptions\HttpException;
use App\Blog\Post;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\UUID;
use App\Http\Actions\ActionInterface;
use App\Http\Auth\PasswordAuthenticationInterface;
use App\Http\Auth\TokenAuthenticationInterface;
use App\Http\Request;
use App\Http\Response;
use App\Http\ErrorResponse;
use App\Http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreatePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private TokenAuthenticationInterface $tokenAuthentication,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    public function handle(Request $request): Response
    {
        try {
            $author = $this->tokenAuthentication->user($request);

        } catch (AuthException $e) {

            return new ErrorResponse($e->getMessage());
        }

        // Генерируем UUID для новой статьи
        $newPostUuid = UUID::random();

        try {
        // Пытаемся создать объект статьи
        // из данных запроса
            $post = new Post(
                $newPostUuid,
                $author,
                $request->jsonBodyField('title'),
                $request->jsonBodyField('text'),
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
        // Сохраняем новую статью в репозитории
        $this->postsRepository->save($post);

        // Логируем UUID новой статьи
        $this->logger->info("Post created: $newPostUuid");

        // Возвращаем успешный ответ,
        // содержащий UUID новой статьи
        return new SuccessfulResponse([
            'title' => $post->getTitle(),
            'text' => $post->getText(),
        ]);
    }
}
