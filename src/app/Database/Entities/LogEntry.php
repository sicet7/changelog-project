<?php

namespace App\Database\Entities;

use App\Database\EntityInterface;
use App\Database\Repositories\LogEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ramsey\Uuid\Uuid;

class LogEntry implements EntityInterface
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @var string|null
     */
    private ?string $initiatedBy = null;

    /**
     * @var string|null
     */
    private ?string $tech = null;

    /**
     * @var string|null
     */
    private ?string $changeDescription = null;

    /**
     * @var string|null
     */
    private ?string $device = null;

    /**
     * @var string|null
     */
    private ?string $rollbackDescription = null;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var \DateTimeImmutable
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @var \DateTimeImmutable|null
     */
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var \DateTimeImmutable|null
     */
    private ?\DateTimeImmutable $deletedAt = null;

    /**
     * LogEntry constructor.
     * @param Log $log
     */
    public function __construct(Log $log)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new \DateTimeImmutable('now');
        $this->log = $log;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getInitiatedBy(): ?string
    {
        return $this->initiatedBy;
    }

    /**
     * @return string|null
     */
    public function getTech(): ?string
    {
        return $this->tech;
    }

    /**
     * @return string|null
     */
    public function getChangeDescription(): ?string
    {
        return $this->changeDescription;
    }

    /**
     * @return string|null
     */
    public function getDevice(): ?string
    {
        return $this->device;
    }

    /**
     * @return string|null
     */
    public function getRollbackDescription(): ?string
    {
        return $this->rollbackDescription;
    }

    /**
     * @return Log
     */
    public function getLog(): Log
    {
        return $this->log;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @param string $initiatedBy
     */
    public function setInitiatedBy(string $initiatedBy): void
    {
        $this->initiatedBy = $initiatedBy;
    }

    /**
     * @param string $tech
     */
    public function setTech(string $tech): void
    {
        $this->tech = $tech;
    }

    /**
     * @param string $changeDescription
     */
    public function setChangeDescription(string $changeDescription): void
    {
        $this->changeDescription = $changeDescription;
    }

    /**
     * @param string|null $device
     */
    public function setDevice(?string $device): void
    {
        $this->device = $device;
    }

    /**
     * @param string|null $rollbackDescription
     */
    public function setRollbackDescription(?string $rollbackDescription): void
    {
        $this->rollbackDescription = $rollbackDescription;
    }

    /**
     * @inheritDoc
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable(LogEntryRepository::TABLE_NAME);
        $builder->setCustomRepositoryClass(LogEntryRepository::class);

        $builder->createField('id', Types::STRING)
            ->makePrimaryKey()
            ->nullable(false)
            ->length(40)
            ->build();

        $builder->createField('initiatedBy', Types::STRING)
            ->nullable(false)
            ->length(200)
            ->columnName('initiated_by')
            ->build();

        $builder->createField('tech', Types::STRING)
            ->nullable(false)
            ->length(200)
            ->build();

        $builder->createField('changeDescription', Types::TEXT)
            ->nullable(false)
            ->columnName('change_description')
            ->build();

        $builder->createField('device', Types::STRING)
            ->nullable(true)
            ->length(200)
            ->build();

        $builder->createField('rollbackDescription', Types::TEXT)
            ->nullable(true)
            ->columnName('rollback_description')
            ->build();

        $builder->addManyToOne('log', Log::class, 'entries');

        $builder->createField('createdAt', Types::DATETIMETZ_IMMUTABLE)
            ->nullable(false)
            ->columnName('created_at')
            ->build();

        $builder->createField('updatedAt', Types::DATETIMETZ_IMMUTABLE)
            ->nullable(true)
            ->columnName('updated_at')
            ->build();

        $builder->createField('deletedAt', Types::DATETIMETZ_IMMUTABLE)
            ->nullable(true)
            ->columnName('deleted_at')
            ->build();
    }
}