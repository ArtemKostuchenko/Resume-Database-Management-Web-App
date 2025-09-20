<?php

namespace App\Repository;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 *
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    public function searchApplications(string $q, array $orderBy, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('r', 'c')
            ->join('a.resume', 'r')
            ->join('a.company', 'c');

        if ($q) {
            $qb->andWhere('r.position_title LIKE :q OR c.name LIKE :q')->setParameter('q', '%' . $q . '%');
        }


        foreach ($orderBy as $field => $order) {
            $qb->orderBy('a.' . $field, $order);
        }

        return $qb->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();
    }

    public function countApplications(string $q): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.resume', 'r')
            ->join('a.company', 'c');

        if ($q) {
            $qb->andWhere('r.position_title LIKE :q OR c.name LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

//    /**
//     * @return Application[] Returns an array of Application objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Application
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
