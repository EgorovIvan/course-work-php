<?php

namespace App\User;

class User
{
    public function __construct(
        private int $id,
        private string $firstName,
        private string $lastName
    )
    {}
}