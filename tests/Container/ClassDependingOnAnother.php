<?php


namespace App\Blog\UnitTests\Container;


class ClassDependingOnAnother{
// Класс с двумя зависимостями
    public function __construct(
        private SomeClassWithoutDependencies $one,
        private SomeClassWithParameter $two,
    ) {
    }
}
