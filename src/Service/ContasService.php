<?php

namespace App\Service;

use App\Entity\Conta;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ContasService
{
    
    private $doctrine;
    private $encoder;

    public function __construct(
        ManagerRegistry $doctrine,
        UserPasswordEncoderInterface $encoder
    ) {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
    }

    public function index(User $usuario, array $filter = [], array $orderBy = null)
    {
        try {
            $filter['usuario'] = $usuario;
            $filter['deleted_at'] = null;
            
            $contas = $this->doctrine->getRepository(Conta::class)->findBy($filter, $orderBy);

            return $contas;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function find(int $id, User $usuario) {
        try {
            $conta = $this->doctrine->getRepository(Conta::class)->find($id);
            if($conta == null) {
                throw new NotFoundHttpException('Conta não encontrada');
            }
            return $conta;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function create(Conta $conta, User $usuario) {
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $conta->setCreatedAt(new DateTimeImmutable());
            $conta->setUsuario($usuario);
            
            $entityManager->persist($conta);

            $entityManager->flush();

            $entityManager->getConnection()->commit();

            return $conta;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function update(Conta $conta, User $usuario) {
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $conta->setUpdatedAt(new DateTimeImmutable());
            
            $entityManager->persist($conta);
            $entityManager->flush();
            $entityManager->getConnection()->commit();

            return $conta;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function delete(Conta $conta, User $usuario)
    {
        
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $conta->setDeletedAt(new DateTimeImmutable());
            
            $entityManager->persist($conta);

            $entityManager->flush();
            $entityManager->getConnection()->commit();

            return $conta;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

}