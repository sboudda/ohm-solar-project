<?php

namespace App\Repository;

use App\Entity\PostalReferentiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostalReferentiel|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostalReferentiel|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostalReferentiel[]    findAll()
 * @method PostalReferentiel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostalReferentielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostalReferentiel::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(PostalReferentiel $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(PostalReferentiel $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByZipCodeLike($word)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.codePostal LIKE :word')
            ->setParameter('word', $word . '%')
            ->orderBy('p.codePostal', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function findByCityLike($word)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nomCommune LIKE :word')
            ->setParameter('word', $word . '%')
            ->orderBy('p.codePostal', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }
}
