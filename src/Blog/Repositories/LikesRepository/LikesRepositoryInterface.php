<?php


namespace App\Blog\Repositories\LikesRepository;


use App\Blog\Like;

interface LikesRepositoryInterface
{
    public function save(Like $like): void;
    public function getByObjectUuid(string $object_id): array;
}