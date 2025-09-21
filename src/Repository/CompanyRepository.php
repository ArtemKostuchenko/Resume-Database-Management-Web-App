<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function searchCompanies(string $q, array $orderBy, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($q) {
            $qb->andWhere('c.name LIKE :q OR c.address LIKE :q OR c.phone LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        foreach ($orderBy as $field => $order) {
            $qb->orderBy('c.' . $field, $order);
        }

        return  $qb->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();
    }

    public function countSearchCompanies(string $q): int
    {
        $qb = $this->createQueryBuilder('c')->select('COUNT(c.id)');

        if ($q) {
            $qb->andWhere('c.name LIKE :q OR c.address LIKE :q OR c.phone LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

//    /**
//     * @return Company[] Returns an array of Company objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Company
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
