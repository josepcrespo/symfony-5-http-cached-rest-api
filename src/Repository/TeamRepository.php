<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function numOfPlayers($team_id): int
    {
        return $this->createQueryBuilder('t')
            ->select('count(t.id)')
            ->andWhere('t.id = :teamIdentifier')
            ->InnerJoin('t.players', 'player')
            ->setParameter('teamIdentifier', $team_id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function salaryExpense($team_id): int
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id = :teamIdentifier')
            ->InnerJoin('t.players', 'player')
            ->select('sum(player.salary) as salary')
            ->setParameter('teamIdentifier', $team_id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // /**
    //  * @return Team[] Returns an array of Team objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Team
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
