<?php

namespace App\Repository;

use App\Entity\RugbyMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RugbyMatch>
 *
 * @method RugbyMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method RugbyMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method RugbyMatch[]    findAll()
 * @method RugbyMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RugbyMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RugbyMatch::class);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return parent::getEntityManager();
    }
}
