<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220504201352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add comment table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bolt_comment (id INT AUTO_INCREMENT NOT NULL, author_name VARCHAR(255) NOT NULL, author_email VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, content_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bolt_comment');
    }
}
