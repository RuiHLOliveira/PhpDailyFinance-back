<?php

namespace App\Controller;

use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Service\AuthService;
use App\Entity\InvitationToken;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use function PHPUnit\Framework\objectHasAttribute;

class AuthController extends AbstractController
{

    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @Route("/auth/register", name="register", methods={"POST"})
     */
    public function register(Request $request)
    {
        try {
            
            $requestData = $request->getContent();
            $requestData = json_decode($requestData);
            $password = $requestData->password;
            $repeatPassword = $requestData->repeatPassword;
            $email = $requestData->email;
            $invitationToken = $requestData->invitationToken;

            if($email == '') throw new BadRequestHttpException("email was not sent.");
            if($password == '') throw new BadRequestHttpException("password was not sent.");
            if($repeatPassword == '') throw new BadRequestHttpException("repeatPassword was not sent.");
            if($password !== $repeatPassword) throw new BadRequestHttpException("Passwords must be equal.");
            if($invitationToken == '') throw new BadRequestHttpException("Invitation Token was not sent.");

            
            $user = new User();
            $user->setPassword($password);
            $user->setEmail($email);

            $response = $this->authService->registerUser($user, $invitationToken);

            return $this->json($user,201);
        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage(),500);
        }
    }

    private function validateLoginData($requestData) {
        if( !property_exists($requestData, 'email') || $requestData->email == ''){
            throw new BadRequestHttpException("email was not sent.");
        }
        if( !property_exists($requestData, 'password') || $requestData->password == ''){
            throw new BadRequestHttpException("password was not sent.");
        }
    }

    /**
     * @Route("/auth/login", name="login", methods={"POST"})
     */
    public function login(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder)
    {
        try {
            $requestData = json_decode($request->getContent());
            $this->validateLoginData($requestData);
            $password = $requestData->password;
            $email = $requestData->email;

            //busca o usuario pelo email
            $user = $userRepository->findOneBy(['email' => $email]);

            //se não achar acusa erro
            if (!$user) {
                return $this->json([
                    'message' => 'email is wrong.',
                ], Response::HTTP_BAD_REQUEST);
            }
            
            //se não achar acusa erro
            if (!$encoder->isPasswordValid($user, $password)) {
                return $this->json([
                    'message' => 'password is wrong.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $tokens = $this->makeNewTokens($user);
            
            return $this->json([
                'message' => 'success!',
                'token' => $tokens['token'],
                'refreshToken' => $tokens['refreshToken']
            ]);
        } catch (\Throwable $th) {
            return new JsonResponse(['message' => $th->getMessage()],500);
        }
    }

    private function makeNewTokens(User $user) {
        
        $payload1 = [
            "username" => $user->getUserIdentifier(),
            "exp"  => (new \DateTime())->modify("+50 seconds")->getTimestamp(),
        ];
        $payload2 = [
            "username" => $user->getUserIdentifier(),
            "exp"  => (new \DateTime())->modify("+600 minutes")->getTimestamp(),
        ];

        $jwt = JWT::encode($payload1, $this->getParameter('jwt_secret'), 'HS256');

        $refreshJwt = JWT::encode($payload2, $this->getParameter('refresh_jwt_secret'), 'HS256');

        return [
            'token' => sprintf('Bearer %s', $jwt),
            'refreshToken' => sprintf('Bearer %s', $refreshJwt)
        ];
    }

    /**
     * @Route("/auth/refreshToken", name="refreshToken", methods={"POST"})
     */
    public function refreshToken(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder)
    {
        try {
            $requestData = json_decode($request->getContent());
            $refreshToken = $requestData->refreshToken;
            
            $refreshToken = str_replace('Bearer ', '', $refreshToken);
            $decodedRefreshToken = JWT::decode($refreshToken, new Key($this->getParameter('refresh_jwt_secret'), 'HS256'));

            //busca o usuario pelo email
            $user = $userRepository->findOneBy(['email' => $decodedRefreshToken->username]);
            //se não achar acusa erro
            if (!$user) {
                return $this->json([
                    'message' => 'email is wrong.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $tokens = $this->makeNewTokens($user);

            return new JsonResponse([
                'message' => 'success!',
                'token' => $tokens['token'],
                'refreshToken' => $tokens['refreshToken']
            ]);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return new JsonResponse(['message' => $e->getMessage()],500);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()],500);
        }
    }
}
