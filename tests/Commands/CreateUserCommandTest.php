<?php

namespace App\Blog\UnitTests\Commands;

use App\Blog\Commands\Arguments;
use App\Blog\Exceptions\ArgumentsException;
use App\Blog\Exceptions\CommandException;
use App\Blog\Commands\CreateUserCommand;
use App\Blog\Commands\DummyUsersRepository;
use App\Blog\Exceptions\UserNotFoundException;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\UnitTests\DummyLogger;
use App\Blog\User;
use App\Blog\UUID;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
// Проверяем, что команда создания пользователя бросает исключение,
// если пользователь с таким именем уже существует
    /**
     * @throws \App\Blog\Exceptions\ArgumentsException
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
// Создаём объект команды
// У команды одна зависимость - UsersRepositoryInterface
        $command = new CreateUserCommand(
            new DummyUsersRepository(),
            new DummyLogger()
        );
// здесь должна быть реализация UsersRepositoryInterface

// Описываем тип ожидаемого исключения
        $this->expectException(CommandException::class);
        // и его сообщение
        $this->expectExceptionMessage('User already exists: Ivan');
// Запускаем команду с аргументами
        $command->handle(new Arguments([
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'username' => 'Ivan',
            'password' => '123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin'
        ]));
    }

    // Функция возвращает объект типа UsersRepositoryInterface
    private function makeUsersRepository(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
        };
    }

    public function testItRequiresPassword(): void
    {
        $command = new CreateUserCommand(
            $this->makeUsersRepository(),
            new DummyLogger()
        );
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: password');
        $command->handle(new Arguments([
            'username' => 'Ivan',
        ]));
    }

// Тест проверяет, что команда действительно требует фамилию пользователя

    /**
     * @throws \App\Blog\Exceptions\CommandException
     */
    public function testItRequiresLastName(): void
    {
// Передаём в конструктор команды объект, возвращаемый нашей функцией
        $command = new CreateUserCommand($this->makeUsersRepository(),
            new DummyLogger()
        );
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: last_name');
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '123',
// Нам нужно передать имя пользователя,
// чтобы дойти до проверки наличия фамилии
            'first_name' => 'Ivan',
        ]));
    }
// Тест проверяет, что команда действительно требует имя пользователя

    /**
     * @throws \App\Blog\Exceptions\CommandException
     */
    public function testItRequiresFirstName(): void
    {
// Вызываем ту же функцию
        $command = new CreateUserCommand($this->makeUsersRepository(),
            new DummyLogger());
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: first_name');
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '123'
            ]));
    }

    // Тест, проверяющий, что команда сохраняет пользователя в репозитории

    /**
     * @throws \App\Blog\Exceptions\ArgumentsException
     * @throws \App\Blog\Exceptions\CommandException
     */
    public function testItSavesUserToRepository(): void
    {
// Создаём объект анонимного класса
        $usersRepository = new class implements UsersRepositoryInterface {
// В этом свойстве мы храним информацию о том,
// был ли вызван метод save
            private bool $called = false;

            public function save(User $user): void
            {
// Запоминаем, что метод save был вызван
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
// Этого метода нет в контракте UsersRepositoryInterface,
// но ничто не мешает его добавить.
// С помощью этого метода мы можем узнать,
// был ли вызван метод save
            public function wasCalled(): bool
            {
                return $this->called;
            }
        };
// Передаём наш мок в команду
        $command = new CreateUserCommand($usersRepository,
            new DummyLogger());
// Запускаем команду
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '1234',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]));
// Проверяем утверждение относительно мока,
// а не утверждение относительно команды
        $this->assertTrue($usersRepository->wasCalled());
    }

}
