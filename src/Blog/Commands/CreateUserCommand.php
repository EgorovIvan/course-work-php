<?php

namespace App\Blog\Commands;

use App\Blog\Name;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
use App\Blog\Exceptions\CommandException;
use Psr\Log\LoggerInterface;


class CreateUserCommand
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws CommandException
     * @throws \App\Blog\Exceptions\ArgumentsException|\App\Blog\Exceptions\InvalidArgumentException
     */
    public function handle(Arguments $arguments): void
    {
        // Логируем информацию о том, что команда запущена
        // Уровень логирования – INFO
        $this->logger->info("Create user command started");

        $username = $arguments->get('username');

        //Получаем пароль для нового пользователя
        $password = $arguments->get('password');

        // Вычисляем SHA-256-хеш пароля
        $hash = hash('sha256', $password);

        if ($this->userExists($username)) {
            // Логируем сообщение с уровнем WARNING
            $this->logger->warning("User already exists: $username");
            throw new CommandException("User already exists: $username");
        }

        $newUserUuid = UUID::random();

        $this->usersRepository->save(
            new User(
                $newUserUuid,
                $username,
                // Добавили пароль
                $hash,
                new Name(
                    $arguments->get('first_name'),
                    $arguments->get('last_name')
                ),
            )
        );

        // Логируем информацию о новом пользователе
        $this->logger->info("User created: $newUserUuid");
    }

    private function userExists(string $username): bool
    {
        try {
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }

}