<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Conta;
use DateTimeImmutable;
use App\Entity\TipoMovimento;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TipoMovimentosService
{
    private $cacheExpireTime = 24 * 60 * 60; //24h * 60m * 60s
    private $doctrine;
    private $encoder;
    private $cacheAdapter;

    public function __construct(ManagerRegistry $doctrine,  UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
        $this->cacheAdapter = new TagAwareAdapter(new FilesystemAdapter());
    }

    private function buildCacheKey(User $usuario, array $orderBy = null){
        $orderByBuild = [];
        if($orderBy != null ){
            foreach (array_keys($orderBy) as $key => $arrkey) {
                $orderByBuild[] = $arrkey.','.$orderBy[$arrkey];
            }
            $orderByBuild = implode('-',$orderByBuild);
        }

        $key = "tipomovimentos-index-user" . $usuario->getId();
        $key .= !empty($orderByBuild) ? "-orderBy-" . $orderByBuild : '';
        return $key;
    }

    private function buildTagKeys(User $usuario){
        $tags = ["tipomovimentos-index", "user-" . $usuario->getId()];
        return $tags;
    }

    public function index(User $usuario, array $orderBy = null) {

        try {
            $cacheKey = $this->buildCacheKey($usuario, $orderBy);
            $tiposMovimentos = $this->cacheAdapter->get($cacheKey, function (ItemInterface $item) use ($usuario, $orderBy) {
                $item->expiresAfter($this->cacheExpireTime);
                $item->tag($this->buildTagKeys($usuario));
                $tiposMovimentos = $this->doctrine->getRepository(TipoMovimento::class)->findBy(['usuario' => $usuario], $orderBy);
                return $tiposMovimentos;
            });
            return $tiposMovimentos;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function find(int $id, User $usuario) {
        try {
            $tiposMovimento = $this->doctrine->getRepository(TipoMovimento::class)->find($id);
            if($tiposMovimento == null) {
                throw new NotFoundHttpException('TipoMovimento não encontrado');
            }
            return $tiposMovimento;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function create(TipoMovimento $tipoMovimento, User $usuario) {
        
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $tipoMovimento->setCreatedAt(new DateTimeImmutable());
            $tipoMovimento->setUsuario($usuario);
            
            $entityManager->persist($tipoMovimento);

            $entityManager->flush();
            $entityManager->getConnection()->commit();

            $this->cacheAdapter->invalidateTags($this->buildTagKeys($usuario));

            return $tipoMovimento;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function update(TipoMovimento $tipoMovimento, User $usuario) {
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $tipoMovimento->setUpdatedAt(new DateTimeImmutable());
            
            $entityManager->persist($tipoMovimento);
            $entityManager->flush();
            $entityManager->getConnection()->commit();

            return $tipoMovimento;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function delete(TipoMovimento $tipoMovimento, User $usuario) {
        
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $tipoMovimento->setDeletedAt(new DateTimeImmutable());
            
            $entityManager->persist($tipoMovimento);

            $entityManager->flush();
            $entityManager->getConnection()->commit();

            return $tipoMovimento;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }
}