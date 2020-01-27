<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200127090704 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ads (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, category_id_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, modify_date DATETIME NOT NULL, INDEX IDX_7EC9F6209D86650F (user_id_id), INDEX IDX_7EC9F6209777D11E (category_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ads_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, modify_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ads_photos (id INT AUTO_INCREMENT NOT NULL, ad_id INT NOT NULL, file VARCHAR(255) NOT NULL, INDEX IDX_92D699C74F34D596 (ad_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(60) NOT NULL, name VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, phone_number VARCHAR(40) NOT NULL, user_type VARCHAR(20) NOT NULL, city VARCHAR(100) NOT NULL, blocked TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ads ADD CONSTRAINT FK_7EC9F6209D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE ads ADD CONSTRAINT FK_7EC9F6209777D11E FOREIGN KEY (category_id_id) REFERENCES ads_categories (id)');
        $this->addSql('ALTER TABLE ads_photos ADD CONSTRAINT FK_92D699C74F34D596 FOREIGN KEY (ad_id) REFERENCES ads (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ads_photos DROP FOREIGN KEY FK_92D699C74F34D596');
        $this->addSql('ALTER TABLE ads DROP FOREIGN KEY FK_7EC9F6209777D11E');
        $this->addSql('ALTER TABLE ads DROP FOREIGN KEY FK_7EC9F6209D86650F');
        $this->addSql('DROP TABLE ads');
        $this->addSql('DROP TABLE ads_categories');
        $this->addSql('DROP TABLE ads_photos');
        $this->addSql('DROP TABLE users');
    }
}
