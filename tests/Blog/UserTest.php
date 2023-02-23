<?php


namespace App\UnitTests\Blog;

use App\Blog\Name;
use App\Blog\User;
use App\Blog\UUID;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{


    public function testItReturnsUuid()
    {
        $user = $this->getUser();

        $value = (string)$user->uuid();

        $this->assertSame('123e4567-e89b-12d3-a456-426614174000', $value);
    }

    public function testItReturnsUsername()
    {
        $user = $this->getUser();

        $value = $user->userName();

        $this->assertSame('ivan123', $value);
    }

    public function testItReturnsFirstName()
    {
        $user = $this->getUser();

        $value = $user->name()->first();

        $this->assertSame('Ivan', $value);
    }

    public function testItReturnsLastName()
    {
        $user = $this->getUser();

        $value = $user->name()->last();

        $this->assertSame('Nikitin', $value);
    }

    public function testItSetsUsername()
    {
        $user = $this->getUser();

        $user->setLogin('Mark@gmail.com');

        $value = $user->userName();

        $this->assertSame('Mark@gmail.com', $value);
    }

    private function getUser(): User
    {
        return new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'ivan123',
            '1234',
            new Name('Ivan', 'Nikitin')
        );
    }
}