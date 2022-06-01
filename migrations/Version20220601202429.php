<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220601202429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'bolt extension comments entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bolt_comment (id INT AUTO_INCREMENT NOT NULL, comment_id INT DEFAULT NULL, content_id INT NOT NULL, author_name VARCHAR(255) NOT NULL, author_email VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, status VARCHAR(191) DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME DEFAULT NULL, published_at DATETIME DEFAULT NULL, depublished_at DATETIME DEFAULT NULL, INDEX IDX_9F1A4C59F8697D13 (comment_id), INDEX IDX_9F1A4C5984A0A3ED (content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bolt_comment ADD CONSTRAINT FK_9F1A4C59F8697D13 FOREIGN KEY (comment_id) REFERENCES bolt_comment (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bolt_comment DROP FOREIGN KEY FK_9F1A4C59F8697D13');
        $this->addSql('DROP TABLE bolt_comment');
    }
}
