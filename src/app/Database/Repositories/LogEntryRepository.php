<?php

namespace App\Database\Repositories;

use App\Database\Entities\Log;
use App\Database\Entities\LogEntry;
use App\Exceptions\NoSuchEntityException;
use App\Exceptions\SaveException;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;

class LogEntryRepository
{
    public const TABLE_NAME = 'log_entries';

    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * @var \ReflectionProperty
     */
    private \ReflectionProperty $updatedProperty;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->updatedProperty = new \ReflectionProperty(LogEntry::class, 'updatedAt');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param Log $log
     * @return int|mixed|string
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEntryCount(Log $log, ?Criteria $criteria = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(u.id)')
            ->from(LogEntry::class, 'u')
            ->where('u.log = :logIdentifier');
        $qb->setParameter('logIdentifier', $log);
        if ($criteria instanceof Criteria) {
            $qb->addCriteria($criteria);
        }
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Log $log
     * @param Criteria|null $criteria
     * @return int|null
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getEntries(Log $log, ?Criteria $criteria = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u')
            ->from(LogEntry::class, 'u')
            ->where('u.log = :logIdentifier');
        $qb->setParameter('logIdentifier', $log);
        if ($criteria instanceof Criteria) {
            $qb->addCriteria($criteria);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $id
     * @return LogEntry
     * @throws NoSuchEntityException
     */
    public function getById(string $id): LogEntry
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT u FROM ' . LogEntry::class . ' u WHERE u.id = ?1'
        );
        $query->setParameter(1, $id);
        $result = $query->getResult();
        if (empty($result)) {
            throw new NoSuchEntityException('Failed to find log by id "' . $id . '".');
        }
        return $result[array_keys($result)[0]];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function idExists(string $id): bool
    {
        try {
            $this->getById($id);
            return true;
        } catch (NoSuchEntityException $exception) {
            return false;
        }
    }

    /**
     * @param LogEntry $entry
     * @throws SaveException
     */
    public function save(LogEntry $entry)
    {
        try {
            if ($this->idExists($entry->getId())) {
                $this->markEntityAsUpdated($entry);
            }
            $this->persist($entry);
        } catch (\Throwable $throwable) {
            throw new SaveException('Failed to save Entry Entity.', $throwable->getCode(), $throwable);
        }
    }

    /**
     * @param LogEntry $entry
     */
    protected function markEntityAsUpdated(LogEntry $entry)
    {
        $this->updatedProperty->setAccessible(true);
        $this->updatedProperty->setValue($entry, new \DateTimeImmutable('now'));
        $this->updatedProperty->setAccessible(false);
    }

    /**
     * @param LogEntry $entry
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function persist(LogEntry $entry)
    {
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
    }
}