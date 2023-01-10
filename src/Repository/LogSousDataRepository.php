<?php

namespace App\Repository;

use App\Entity\LogSousData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogSousData|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogSousData|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogSousData[]    findAll()
 * @method LogSousData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogSousDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogSousData::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(LogSousData $entity, bool $flush = true): void
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
    public function remove(LogSousData $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
