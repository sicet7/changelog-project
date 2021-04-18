<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Repositories\LogRepository;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210417161337 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Creates "' . LogRepository::TABLE_NAME . '" table';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable(LogRepository::TABLE_NAME);
        $this->makeIdColumn('id', $table);
        $this->makeNameColumn('name', $table);
        $this->makeDescriptionColumn('description', $table);
        $this->makeTimestampColumn('created_at', false, $table);
        $this->makeTimestampColumn('updated_at', true, $table)->setDefault(null);
        $this->makeTimestampColumn('deleted_at', true, $table)->setDefault(null);
    }

    /**
     * @inheritDoc
     */
    public function down(Schema $schema) : void
    {
        $schema->dropTable(LogRepository::TABLE_NAME);
    }

    /**
     * @param string $name
     * @param Table $table
     * @return Column
     */
    protected function makeIdColumn(string $name, Table $table)
    {
        $column = $table->addColumn($name, Types::STRING);
        $column->setLength(40);
        $column->setNotnull(true);
        $table->setPrimaryKey([$name]);
        return $column;
    }

    /**
     * @param string $name
     * @param Table $table
     * @return Column
     */
    protected function makeNameColumn(string $name, Table $table)
    {
        $column = $table->addColumn($name, Types::STRING);
        $column->setLength(200);
        $column->setNotnull(true);
        $table->addUniqueIndex([$name]);
        return $column;
    }

    /**
     * @param string $name
     * @param Table $table
     * @return Column
     */
    protected function makeDescriptionColumn(string $name, Table $table)
    {
        $column = $table->addColumn($name, Types::TEXT);
        $column->setNotnull(false);
        $column->setDefault(null);
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
