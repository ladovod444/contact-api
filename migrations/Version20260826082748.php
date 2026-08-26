<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260826082748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_statistics ALTER phone DROP NOT NULL');
        $this->addSql('ALTER TABLE contact_statistics ALTER email DROP NOT NULL');
        $this->addSql('ALTER TABLE contact_statistics ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE contact_statistics ALTER created_at SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_statistics ALTER email SET NOT NULL');
        $this->addSql('ALTER TABLE contact_statistics ALTER phone SET NOT NULL');
        $this->addSql('ALTER TABLE contact_statistics ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE contact_statistics ALTER created_at DROP NOT NULL');
    }
}
