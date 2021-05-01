<?php

namespace App\Database\Entities;

use App\Database\EntityInterface;
use App\Database\Repositories\LogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ramsey\Uuid\Uuid;

class Log implements EntityInterface
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @var string|null
     */
    private ?string $name = null;

    /**
     * @var string|null
     */
    private ?string $description = null;

    /**
     * @var Collection&Selectable
     */
    private $entries;

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
     * Log constructor.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new \DateTimeImmutable('now');
        $this->entries = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return Collection&Selectable
     */
    public function getEntries()
    {
        return $this->entries;
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
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable(LogRepository::TABLE_NAME);
        $builder->setCustomRepositoryClass(LogRepository::class);

        $builder->createField('id', Types::STRING)
            ->makePrimaryKey()
            ->nullable(false)
            ->length(40)
            ->build();

        $builder->createField('name', Types::STRING)
            ->unique(true)
            ->nullable(false)
            ->length(200)
            ->build();

        $builder->createField('description', Types::TEXT)
            ->nullable(true)
            ->build();

        $builder->createOneToMany('entries', LogEntry::class)
            ->mappedBy('log')
            ->setOrderBy(['createdAt' => 'DESC'])
            ->fetchExtraLazy()
            ->build();

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