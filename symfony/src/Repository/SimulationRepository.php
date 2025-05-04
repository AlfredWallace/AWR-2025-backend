<?php

namespace App\Repository;

use App\Entity\Simulation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Simulation>
 *
 * @method Simulation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Simulation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Simulation[]    findAll()
 * @method Simulation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Simulation::class);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return parent::getEntityManager();
    }
}
