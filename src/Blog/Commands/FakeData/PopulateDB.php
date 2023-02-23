<?php


namespace App\Blog\Commands\FakeData;


use App\Blog\Comment;
use App\Blog\Name;
use App\Blog\Post;
use App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use App\Blog\User;
use App\Blog\UUID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDB extends Command
{
    // Внедряем генератор тестовых данных и
    // репозитории пользователей и статей
    public function __construct(
        private \Faker\Generator $faker,
        private UsersRepositoryInterface $usersRepository,
        private PostsRepositoryInterface $postsRepository,
        private CommentsRepositoryInterface $commentsRepository,
    ) {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->setName('fake-data:populate-db')
            ->setDescription('Populates DB with fake data')
            ->addOption(
                'users-number',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Users number',
            )
            ->addOption(
                'posts-number',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Posts number',
            )
            ->addOption(
                'comments-number',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Comments number',
            );
    }

    /**
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {

        $users = [];
        $usersNumber = $input->getOption('users-number');

        for ($i = 0; $i < $usersNumber; $i++) {
            $user = $this->createFakeUser();
            $users[] = $user;
            $output->writeln('User created: ' . $user->username());
        }

        $posts = [];
        $postsNumber = $input->getOption('posts-number');

        foreach ($users as $user) {
            for ($i = 0; $i < $postsNumber; $i++) {
                $post = $this->createFakePost($user);
                $posts[] = $post;
                $output->writeln('Post created: ' . $post->getTitle());
            }
        }

        $commentsNumber = $input->getOption('comments-number');

        foreach ($posts as $post) {
            foreach ($users as $user) {
                for ($i = 0; $i < $commentsNumber; $i++) {
                    $comment = $this->createFakeComment($post, $user);
                    $output->writeln('Comment created: ' . $comment->getText());
                }
            }
        }

        return Command::SUCCESS;
    }
    private function createFakeUser(): User
    {
        $user = User::createFrom(
            // Генерируем имя пользователя
            $this->faker->userName,
            // Генерируем пароль
            $this->faker->password,
            new Name(
                // Генерируем имя
                $this->faker->firstName,
                // Генерируем фамилию
                $this->faker->lastName
            )
        );
        // Сохраняем пользователя в репозиторий
        $this->usersRepository->save($user);
        return $user;
    }

    /**
     * @throws \App\Blog\Exceptions\InvalidArgumentException
     */
    private function createFakePost(User $author): Post
    {
        $post = new Post(
            UUID::random(),
            $author,
            // Генерируем предложение не длиннее шести слов
            $this->faker->sentence(6, true),
            // Генерируем текст
            $this->faker->realText
        );
        // Сохраняем статью в репозиторий
        $this->postsRepository->save($post);
        return $post;
    }

    private function createFakeComment(Post $post, User $author): Comment
    {
        $comment = new Comment(
            UUID::random(),
            $post,
            $author,
            $this->faker->realText
        );
        $this->commentsRepository->save($comment);
        return $comment;
    }
}