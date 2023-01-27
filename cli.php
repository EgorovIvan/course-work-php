<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Post;
use App\Blog\Comment;
use App\Blog\User;

$faker = Faker\Factory::create();
echo $faker->name();
echo $faker->email();
echo $faker->text();

$user = new User(
    $faker->randomDigitNotNull(),
    $faker->firstName(),
    $faker->lastName()
);

$post = new Post($faker->randomDigitNotNull(), $user, 'The Social Network',
    $faker->text);

$comment = new Comment($faker->randomDigitNotNull(), $user, $post,
    $faker->text);

switch ($argv[1]) {
    case 'user':
        echo $user;
        break;
    case 'post':
        echo $post;
        break;
    case 'comment':
        echo $comment;
        break;
    default:
        break;
}