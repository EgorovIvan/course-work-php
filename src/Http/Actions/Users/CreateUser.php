<?php


namespace App\Http\Actions\Users;

use App\Blog\Exceptions\HttpException;
use App\Blog\Exceptions\InvalidArgumentException;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Name;
use App\Blog\Post;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
use App\Http\Actions\ActionInterface;
use App\Http\Request;
use App\Http\Response;
use App\Http\ErrorResponse;
use App\Http\SuccessfulResponse;

class CreateUser  implements ActionInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
    ) {
    }

    /**
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    public function handle(Request $request): Response
    {
        try {
            $username = $request->jsonBodyField('username');
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
//        try {
//            $username = $this->usersRepository->getByUsername($username);
//        } catch (UserNotFoundException $e) {
//            return new ErrorResponse($e->getMessage());
//        }

        $newUserUuid = UUID::random();

        try {
            $user = new User(
                $newUserUuid,
                $username,
                new Name($request->jsonBodyField('first_name'),$request->jsonBodyField('last_name'))
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
// Сохраняем новую статью в репозитории
        $this->usersRepository->save($user);
// Возвращаем успешный ответ,
// содержащий UUID новой статьи
        return new SuccessfulResponse([
            'username' => $username,
            'first_name' => $user->name()->first(),
            'last_name' => $user->name()->last(),
        ]);
    }
}
