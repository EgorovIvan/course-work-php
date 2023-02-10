<?php


namespace App\Blog\Container;


use App\Blog\Exceptions\NotFoundException;
use ReflectionClass;

class DIContainer
{
// Метод has из PSR-11
    public function has(string $type): bool
    {
// Здесь мы просто пытаемся создать
// объект требуемого типа
        try {
            $this->get($type);
        } catch (NotFoundException $e) {
// Возвращаем false, если объект не создан...
            return false;
        }
// и true, если создан
        return true;
    }


    // Массив правил создания объектов
    private array $resolvers = [];

    // Теперь правилами могут быть
    // не только строки (имена классов), но и объекты
    // Так что убираем указание типа у второго аргумента
    // и заодно переименовываем его в $resolver
    public function bind(string $type, $resolver)
    {
        $this->resolvers[$type] = $resolver;
    }

    /**
     * @throws \App\Blog\Exceptions\NotFoundException
     * @throws \ReflectionException
     */
    public function get(string $type): object
    {
        if (array_key_exists($type, $this->resolvers)) {
            $typeToCreate = $this->resolvers[$type];
// Если в контейнере для запрашиваемого типа
// уже есть готовый объект — возвращаем его
            if (is_object($typeToCreate)) {
                return $typeToCreate;
            }
            return $this->get($typeToCreate);
        }
        if (!class_exists($type)) {
            throw new NotFoundException("Cannot resolve type: $type");
        }

        // Создаём объект рефлексии для запрашиваемого класса
        $reflectionClass = new ReflectionClass($type);
// Исследуем конструктор класса
        $constructor = $reflectionClass->getConstructor();
// Если конструктора нет -
// просто создаём объект нужного класса
        if (null === $constructor) {
            return new $type();
        }
// В этот массив мы будем собирать
// объекты зависимостей класса
        $parameters = [];
// Проходим по всем параметрам конструктора
// (зависимостям класса)
        foreach ($constructor->getParameters() as $parameter) {
// Узнаем тип параметра конструктора
// (тип зависимости)
            $parameterType = $parameter->getType()->getName();
// Получаем объект зависимости из контейнера
            $parameters[] = $this->get($parameterType);
        }
// Создаём объект нужного нам типа
// с параметрами
        return new $type(...$parameters);
    }
}