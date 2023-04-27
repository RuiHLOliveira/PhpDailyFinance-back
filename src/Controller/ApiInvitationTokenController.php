<?php

namespace App\Controller;

use App\Entity\InvitationToken;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiInvitationTokenController extends AbstractController
{
    /**
     * @Route("/invitations", methods={"GET"})
     */
    public function index(ManagerRegistry $doctrine)
    {
        $user = $this->getUser();
        $invitations = $doctrine->getRepository(InvitationToken::class)->findAll(['user' => $user]);
        return new JsonResponse($invitations);
    }

    /**
     * @Route("/invitations", methods={"POST"})
     */
    public function store(Request $request, ManagerRegistry $doctrine)
    {
        try {
            $postData = $request->getContent();
            $postData = json_decode($postData);
            $newInvitationToken = $postData->newInvitationToken;
            $email = $postData->email;
            $user = $this->getUser();
            if($newInvitationToken != ''){
                // strchr()
            } elseif(($newInvitationToken == '')){
                $random_bytes = rand(0,1000000);
                $postData->set('newInvitationToken', $random_bytes);
            }
            $invToken = new InvitationToken();
            $invToken->setUser($user);
            $invToken->setEmail($email != '' ? $email : null);
            $invToken->setInvitationToken($newInvitationToken);

            $em = $doctrine->getManager();
            $em->persist($invToken);
            $em->flush();
            
            $invToken = $doctrine->getRepository(InvitationToken::class)
                ->findOneBy([
                'user' => $this->getUser(),
                'id' => $invToken->getId()
            ]);

            return new JsonResponse(compact('invToken'), 201);
        } catch (\Exception $e) {
            $message = "There was an error while storing your invitation token.";
            $message = $e->getMessage(); // or There was an error while storing your task.
            return new JsonResponse(compact('message'), 500);
        }
    }
}
