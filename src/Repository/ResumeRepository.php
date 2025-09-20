<?php

namespace App\Repository;

use App\Entity\Resume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resume>
 *
 * @method Resume|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resume|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resume[]    findAll()
 * @method Resume[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResumeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resume::class);
    }

    public function searchResumes(string $q, array $orderBy, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('r');

        if ($q) {
            $qb->andWhere('r.position_title LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        foreach ($orderBy as $field => $order) {
            $qb->orderBy('r.' . $field, $order);
        }

        return  $qb->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();
    }

    public function countResumes(string $q): int
    {
        $qb = $this->createQueryBuilder('r')->select('COUNT(r.id)');

        if($q) {
            $qb->andWhere('r.position_title LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

//    /**
//     * @return Resume[] Returns an array of Resume objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Resume
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
