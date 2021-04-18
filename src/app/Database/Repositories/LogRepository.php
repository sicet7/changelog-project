<?php

namespace App\Database\Repositories;

use App\Database\Entities\Log;
use App\Exceptions\DeleteException;
use App\Exceptions\NoSuchEntityException;
use App\Exceptions\SaveException;
use Doctrine\ORM\EntityManager;

class LogRepository
{
    public const TABLE_NAME = 'logs';

    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * @var \ReflectionProperty
     */
    private \ReflectionProperty $updatedProperty;

    /**
     * @var \ReflectionProperty
     */
    private \ReflectionProperty $deletedProperty;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->updatedProperty = new \ReflectionProperty(Log::class, 'updatedAt');
        $this->deletedProperty = new \ReflectionProperty(Log::class, 'deletedAt');
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param string $id
     * @return Log
     * @throws NoSuchEntityException
     */
    public function getById(string $id): Log
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT u FROM ' . Log::class . ' u WHERE u.id = ?1'
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
     * @param Log $log
     * @throws SaveException
     */
    public function save(Log $log)
    {
        try {
            if ($this->idExists($log->getId())) {
                $this->markEntityAsUpdated($log);
            }
            $this->persist($log);
        } catch (\Exception $exception) {
            throw new SaveException('Failed to save Log Entity.', $exception->getCode(), $exception);
        }
    }

    /**
     * @param Log $log
     * @param bool $deep
     * @throws DeleteException
     */
    public function delete(Log $log, bool $deep = false)
    {
        try {
            if ($deep) {
                $this->getEntityManager()->remove($log);
                $this->getEntityManager()->flush();
                return;
            }
            $this->markEntityAsDeleted($log);
            $this->persist($log);
        } catch (\Exception $exception) {
            throw new DeleteException('Failed to delete Log Entity.', $exception->getCode(), $exception);
        }
    }

    /**
     * @param bool $includeDeleted
     * @return array
     */
    public function getAllLogs(bool $includeDeleted = false)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT u FROM ' . Log::class . ' u' . (!$includeDeleted ? ' WHERE u.deletedAt IS NULL' : '')
        );
        return $query->getResult();
    }

    /**
     * @param Log $log
     */
    protected function markEntityAsUpdated(Log $log)
    {
        $this->updatedProperty->setAccessible(true);
        $this->updatedProperty->setValue($log, new \DateTimeImmutable('now'));
        $this->updatedProperty->setAccessible(false);
    }

    /**
     * @param Log $log
     */
    protected function markEntityAsDeleted(Log $log)
    {
        $this->deletedProperty->setAccessible(true);
        $this->deletedProperty->setValue($log, new \DateTimeImmutable('now'));
        $this->deletedProperty->setAccessible(false);
    }

    /**
     * @param Log $log
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function persist(Log $log)
    {
        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();
    }
}