<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * @return Comment[] Returns an array of Comments objects
     */
    public function findByUserTarget($idC, $idT)
    {
        $sends = $this->createQueryBuilder('comment')
            ->andWhere('comment.user =:val1')
            ->setParameter('val1', $idC)
            ->andWhere('comment.target =:val2')
            ->setParameter('val2', $idT)
            ->orderBy('comment.created_at', 'ASC')
            ->getQuery()
            ->getResult();
        $receved = $this->createQueryBuilder('comment')
            ->andWhere('comment.user =:val1')
            ->setParameter('val1', $idT)
            ->andWhere('comment.target =:val2')
            ->setParameter('val2', $idC)
            ->orderBy('comment.created_at', 'ASC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($sends as $send) {
            array_push($result, $send);
        }
        foreach ($receved as $get) {
            array_push($result, $get);
        }

        return $result;
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
