<?php

namespace App\Security;
use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Doctrine\ORM\EntityManagerInterface;
use SebastianBergmann\Type\MixedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class JwtAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $params;

    public function __construct(EntityManagerInterface $em, ContainerBagInterface $params)
    {
        $this->em = $em;
        $this->params = $params;
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        $data = [ 
            'message' => 'Authentication Required'
        ];
        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request): bool
    {
        // return $request->headers->has('Authorization');
        return $request->getPathInfo() !== '/auth/login' && $request->getPathInfo() !== '/auth/register';
    }

    public function getCredentials(Request $request)
    {
        try {
            $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));
            return JWT::decode($token, new Key($this->params->get('jwt_secret'), 'HS256'));
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        try {

            if(!is_object($credentials) || !property_exists($credentials, 'username')){
                return null;
            }

            $username = $credentials->username;

            return $this->em->getRepository(User::class)->findOneBy(['email' => $username]);

        } catch (\Exception $exception) {
            throw new AuthenticationException($exception->getMessage());
        }
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return is_object($credentials) && property_exists($credentials, 'username');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        return null;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}