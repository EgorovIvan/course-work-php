<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Post;
use App\Blog\Comment;
use App\Blog\User;

$faker = Faker\Factory::create();
echo $faker->name();
echo $faker->email();
echo $faker->text();

$user = new User(1, 'Mark', 'Zuckerberg');

$post = new Post(1, $user, 'The Social Network',
    'A movie based on Zuckerberg and the founding years of Facebook, The Social Network was released on October 1, 2010');

$comment = new Comment(1, $user, $post,
    'You turned out to be a great entrepreneur, a visionary, and an incredible altruist');

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