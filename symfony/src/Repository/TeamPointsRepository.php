<?php

namespace App\Repository;

use App\Entity\Simulation;
use App\Entity\Team;
use App\Entity\TeamPoints;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamPoints>
 *
 * @method TeamPoints|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamPoints|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamPoints[]    findAll()
 * @method TeamPoints[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamPointsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamPoints::class);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return parent::getEntityManager();
    }
    
    /**
     * Find the most recent TeamPoints entry for a team in a simulation before a specific order
     */
    public function findMostRecentTeamPointsBeforeOrder(Simulation $simulation, Team $team, int $order): ?TeamPoints
    {
        return $this->createQueryBuilder('tp')
            ->where('tp.simulation = :simulation')
            ->andWhere('tp.team = :team')
            ->andWhere('tp.order < :order')
            ->setParameter('simulation', $simulation)
            ->setParameter('team', $team)
            ->setParameter('order', $order)
            ->orderBy('tp.order', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
