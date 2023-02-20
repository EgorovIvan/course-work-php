<?php


namespace App\Http\Actions\Posts;


use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\PostNotFoundException;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\UUID;
use App\Http\Actions\ActionInterface;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\Response;
use App\Http\SuccessfulResponse;

class FindByUuid implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            // Пытаемся получить искомое имя пользователя из запроса
            $uuid = $request->query('uuid');
        } catch (HttpException $e) {
            // Если в запросе нет параметра username -
            // возвращаем неуспешный ответ,
            // сообщение об ошибке берём из описания исключения
            return new ErrorResponse($e->getMessage());
        }
        try {
            // Пытаемся найти пользователя в репозитории
            $post = $this->postsRepository->get(new UUID($uuid));
        } catch (PostNotFoundException $e) {
            // Если пользователь не найден -
            // возвращаем неуспешный ответ
            return new ErrorResponse($e->getMessage());
        } catch (InvalidArgumentException $e) {
        }
        // Возвращаем успешный ответ
        return new SuccessfulResponse([
            'uuid' => $uuid,
            'title' => $post->getTitle(),
            'text' => $post->getText(),
        ]);
    }
}