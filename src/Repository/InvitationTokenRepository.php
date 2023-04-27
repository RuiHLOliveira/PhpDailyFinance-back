<?php

namespace App\Repository;

use App\Entity\InvitationToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvitationToken>
 *
 * @method InvitationToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvitationToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvitationToken[]    findAll()
 * @method InvitationToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvitationToken::class);
    }

    public function add(InvitationToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InvitationToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
