<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class BasicAuthenticator extends AbstractGuardAuthenticator
{
    protected EntityManagerInterface $entityManager;

    protected UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization') && strpos($request->headers->get('Authorization'), 'Basic') === 0;
    }

    public function getCredentials(Request $request): array
    {
        return [
            'user' => $request->headers->get('PHP_AUTH_USER'),
            'password' => $request->headers->get('PHP_AUTH_PW'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): User
    {
        /** @var User $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneByEmail($credentials['user']);

        if (!$user) {
            throw new BadCredentialsException();
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $data = [
            'error' => [
                "message" => strtr($exception->getMessageKey(), $exception->getMessageData()),
                "status" => Response::HTTP_FORBIDDEN,
            ],
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $data = [
            'error' => [
                "message" => 'Authentication Required',
                "status" => Response::HTTP_UNAUTHORIZED,
            ],
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
