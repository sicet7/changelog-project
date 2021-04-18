<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Repositories\LogEntryRepository;
use App\Database\Repositories\LogRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210417182908 extends AbstractMigration
{
    public const CONSTRAINT_NAME = 'log_entry_to_log';

    public function getDescription() : string
    {
        return 'Makes foreign key between "' .
            LogEntryRepository::TABLE_NAME . '" and "' .
            LogRepository::TABLE_NAME . '".';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf(
            !$schema->hasTable(LogEntryRepository::TABLE_NAME),
            'Missing Table: "' . LogEntryRepository::TABLE_NAME . '"'
        );
        $this->abortIf(
            !$schema->hasTable(LogRepository::TABLE_NAME),
            'Missing Table: "' . LogRepository::TABLE_NAME . '"'
        );
        $localTable = $schema->getTable(LogEntryRepository::TABLE_NAME);
        $foreignTable = $schema->getTable(LogRepository::TABLE_NAME);
        $localTable->addForeignKeyConstraint(
            $foreignTable,
            ['log_id'],
            ['id'],
            [],
            static::CONSTRAINT_NAME
        );
    }

    /**
     * @inheritDoc
     */
    public function down(Schema $schema) : void
    {
        $this->skipIf(
            !$schema->hasTable(LogEntryRepository::TABLE_NAME),
            'Skipping because table "' . LogEntryRepository::TABLE_NAME . '" doesn\'t exist.'
        );
        $localTable = $schema->getTable(LogEntryRepository::TABLE_NAME);
        $localTable->removeForeignKey(static::CONSTRAINT_NAME);
    }
}
