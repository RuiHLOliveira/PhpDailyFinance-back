<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\InvitationToken;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthService
{
    
    private $doctrine;
    private $encoder;

    public function __construct(ManagerRegistry $doctrine,  UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
    }

    public function registerUser(User $user, string $invitationTokenString) {
        
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $invitationToken = $this->doctrine->getRepository(InvitationToken::class)->findOneBy([
                'invitation_token' => $invitationTokenString,
                'active' => true
            ]);
            
            if($invitationToken == null) throw new NotFoundHttpException("Invitation Token not found or already used.");
                
            $invitationTokenEmail = $invitationToken->getEmail();
            if($invitationTokenEmail !== null && $invitationTokenEmail !== $user->getEmail()) throw new NotFoundHttpException("This email can't use this Invitation Token.");

            $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));

            $entityManager->persist($user);

            $invitationToken->setActive(false);
            $entityManager->persist($invitationToken);
            
            $entityManager->flush();

            $entityManager->getConnection()->commit();

            return $user;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

}