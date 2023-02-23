<?php declare(strict_types=1);



namespace App\Http;


// Класс неуспешного ответа

use JetBrains\PhpStorm\ArrayShape;

class SuccessfulResponse extends Response
{
    protected const SUCCESS = true;
    // Успешный ответ содержит массив с данными,
    // по умолчанию - пустой
    public function __construct(
        private array $data = []
    ) {
    }
    // Реализация абстрактного метода
    // родительского класса
    #[ArrayShape(['data' => "array"])] protected function payload(): array
    {
        return ['data' => $this->data];
    }
}
