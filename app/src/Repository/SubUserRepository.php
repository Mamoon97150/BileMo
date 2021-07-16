<?php

namespace App\Repository;

use App\Entity\SubUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SubUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubUser[]    findAll()
 * @method SubUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubUser::class);
    }

    // /**
    //  * @return SubUser[] Returns an array of SubUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SubUser
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
