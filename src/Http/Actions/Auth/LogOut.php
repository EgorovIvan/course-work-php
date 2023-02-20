<?php


namespace App\Http\Actions\Auth;


use App\Blog\AuthToken;
use App\Blog\Exceptions\AuthException;
use App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use App\Http\Actions\ActionInterface;
use App\Http\Auth\TokenAuthenticationInterface;
use App\Http\ErrorResponse;
use App\Http\Request;
use App\Http\Response;
use App\Http\SuccessfulResponse;
use DateTimeImmutable;

class LogOut implements ActionInterface
{
    private const HEADER_PREFIX = 'Bearer ';

    public function __construct(
        private TokenAuthenticationInterface $tokenAuthentication,
        private AuthTokensRepositoryInterface $authTokensRepository
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {
        try {
            $user = $this->tokenAuthentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newAuthToken = new AuthToken(
            bin2hex(random_bytes(40)),
            $user->uuid(),
            (new DateTimeImmutable())->modify('+1 day')
        );

        $this->authTokensRepository->update($newAuthToken);

        return new SuccessfulResponse([
            'token' => $newAuthToken->token(),
        ]);
    }
}