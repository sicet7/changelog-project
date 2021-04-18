<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Repositories\LogEntryRepository;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210417172909 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Creates "' . LogEntryRepository::TABLE_NAME . '" table.';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable(LogEntryRepository::TABLE_NAME);
        $this->makeIdColumn('id', $table, true);
        $this->makeStringColumn('initiated_by', $table, false);
        $this->makeStringColumn('tech', $table, false);
        $this->makeTextColumn('change_description', $table, false);
        $this->makeStringColumn('device', $table, true);
        $this->makeTextColumn('rollback_description', $table, true);
        $this->makeIdColumn('log_id', $table, false);
        $this->makeTimestampColumn('created_at', false, $table);
        $this->makeTimestampColumn('updated_at', true, $table)->setDefault(null);
        $this->makeTimestampColumn('deleted_at', true, $table)->setDefault(null);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable(LogEntryRepository::TABLE_NAME);
    }

    /**
     * @param string $name
     * @param Table $table
     * @param bool $primary
     * @return Column
     */
    protected function makeIdColumn(string $name, Table $table, bool $primary)
    {
        $column = $table->addColumn($name, Types::STRING);
        $column->setLength(40);
        $column->setNotnull(true);
        if ($primary) {
            $table->setPrimaryKey([$name]);
        }
        return $column;
    }

    /**
     * @param string $name
     * @param Table $table
     * @param bool $nullable
     * @return Column
     */
    protected function makeStringColumn(string $name, Table $table, bool $nullable)
    {
        $column = $table->addColumn($name, Types::STRING);
        $column->setLength(200);
        $column->setNotnull(!$nullable);
        return $column;
    }

    /**
     * @param string $name
     * @param Table $table
     * @param bool $nullable
     * @return Column
     */
    protected function makeTextColumn(string $name, Table $table, bool $nullable)
    {
        $column = $table->addColumn($name, Types::TEXT);
        $column->setNotnull(!$nullable);
        return $column;
    }

    /**
     * @param string $name
     * @param bool $nullable
     * @param Table $table
     * @return Column
     */
    protected function makeTimestampColumn(
        string $name,
        bool $nullable,
        Table $table
    ) {
        $column = $table->addColumn($name, Types::DATETIMETZ_IMMUTABLE);
        $column->setNotnull(!$nullable);
        return $column;
    }
}
