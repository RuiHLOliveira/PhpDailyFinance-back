<?php

namespace App\Service;

use App\Entity\ClasseMovimento;
use App\Entity\Conta;
use App\Entity\TipoMovimento;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClasseMovimentosService
{
    
    private $doctrine;
    private $encoder;

    public function __construct(ManagerRegistry $doctrine,  UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
    }

    public function index(User $usuario, array $orderBy = null) {

        try {
            $classesMovimentos = $this->doctrine->getRepository(ClasseMovimento::class)->findBy(['usuario' => $usuario], $orderBy);
            return $classesMovimentos;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function find(int $id, User $usuario) {
        try {
            $classeMovimento = $this->doctrine->getRepository(ClasseMovimento::class)->findOneBy(['id' => $id, 'usuario' => $usuario]);
            return $classeMovimento;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function create(ClasseMovimento $classeMovimento, User $usuario) {
        
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $classeMovimento->setCreatedAt(new DateTimeImmutable());
            $classeMovimento->setUsuario($usuario);
            
            $entityManager->persist($classeMovimento);

            $entityManager->flush();
            $entityManager->getConnection()->commit();

            return $classeMovimento;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function update(ClasseMovimento $classeMovimento, User $usuario) {
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $classeMovimento->setUpdatedAt(new DateTimeImmutable());
            
            $entityManager->persist($classeMovimento);
            $entityManager->flush();
            $entityManager->getConnection()->commit();

            return $classeMovimento;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function delete(ClasseMovimento $classeMovimento, User $usuario)
    {
        
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $classeMovimento->setDeletedAt(new DateTimeImmutable());
            
            $entityManager->persist($classeMovimento);

            $entityManager->flush();
            $entityManager->getConnection()->commit();

            return $classeMovimento;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

}