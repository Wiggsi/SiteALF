<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180404133108 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE appel_cog (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, created_date DATETIME NOT NULL, first_name VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, tel VARCHAR(10) DEFAULT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_19E2D49F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE criminel (id INT AUTO_INCREMENT NOT NULL, birthdate DATE NOT NULL, first_name VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, wanted TINYINT(1) NOT NULL, dangerous INT NOT NULL, prison VARCHAR(50) NOT NULL, commentaires LONGTEXT DEFAULT NULL, tel VARCHAR(10) DEFAULT NULL, adncode INT DEFAULT NULL, photo_code INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gendarme (id INT AUTO_INCREMENT NOT NULL, unit_id INT NOT NULL, grade_id INT NOT NULL, opj TINYINT(1) NOT NULL, commentaires LONGTEXT DEFAULT NULL, INDEX IDX_1AA064BF8BD700D (unit_id), INDEX IDX_1AA064BFE19A1A8 (grade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE grade (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, abrv VARCHAR(10) NOT NULL, officier TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE infraction (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, unit_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_date DATETIME NOT NULL, updated_date DATETIME NOT NULL, INDEX IDX_5A8A6C8DF675F31B (author_id), INDEX IDX_5A8A6C8DF8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, opj_id INT NOT NULL, created_date DATETIME NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, resume VARCHAR(255) NOT NULL, importance INT NOT NULL, updated_date DATETIME NOT NULL, INDEX IDX_D780BF00F675F31B (author_id), INDEX IDX_D780BF007C29F7A6 (opj_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_criminel (pv_id INT NOT NULL, criminel_id INT NOT NULL, INDEX IDX_F03AFF61E8A4F4B0 (pv_id), INDEX IDX_F03AFF617ED15D5D (criminel_id), PRIMARY KEY(pv_id, criminel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_infraction (pv_id INT NOT NULL, infraction_id INT NOT NULL, INDEX IDX_B79F0773E8A4F4B0 (pv_id), INDEX IDX_B79F07737697C467 (infraction_id), PRIMARY KEY(pv_id, infraction_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unit (id INT AUTO_INCREMENT NOT NULL, chef_id INT NOT NULL, name VARCHAR(255) NOT NULL, abrv VARCHAR(255) NOT NULL, comment LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_DCBB0C53150A48F1 (chef_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, gendarme_id INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, username VARCHAR(30) NOT NULL, birthdate DATE NOT NULL, email VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_date_time DATETIME NOT NULL, blocked TINYINT(1) NOT NULL, last_login DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D6497AD74A60 (gendarme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appel_cog ADD CONSTRAINT FK_19E2D49F675F31B FOREIGN KEY (author_id) REFERENCES gendarme (id)');
        $this->addSql('ALTER TABLE gendarme ADD CONSTRAINT FK_1AA064BF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE gendarme ADD CONSTRAINT FK_1AA064BFE19A1A8 FOREIGN KEY (grade_id) REFERENCES grade (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE pv ADD CONSTRAINT FK_D780BF00F675F31B FOREIGN KEY (author_id) REFERENCES gendarme (id)');
        $this->addSql('ALTER TABLE pv ADD CONSTRAINT FK_D780BF007C29F7A6 FOREIGN KEY (opj_id) REFERENCES gendarme (id)');
        $this->addSql('ALTER TABLE pv_criminel ADD CONSTRAINT FK_F03AFF61E8A4F4B0 FOREIGN KEY (pv_id) REFERENCES pv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_criminel ADD CONSTRAINT FK_F03AFF617ED15D5D FOREIGN KEY (criminel_id) REFERENCES criminel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_infraction ADD CONSTRAINT FK_B79F0773E8A4F4B0 FOREIGN KEY (pv_id) REFERENCES pv (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_infraction ADD CONSTRAINT FK_B79F07737697C467 FOREIGN KEY (infraction_id) REFERENCES infraction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53150A48F1 FOREIGN KEY (chef_id) REFERENCES gendarme (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497AD74A60 FOREIGN KEY (gendarme_id) REFERENCES gendarme (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pv_criminel DROP FOREIGN KEY FK_F03AFF617ED15D5D');
        $this->addSql('ALTER TABLE appel_cog DROP FOREIGN KEY FK_19E2D49F675F31B');
        $this->addSql('ALTER TABLE pv DROP FOREIGN KEY FK_D780BF00F675F31B');
        $this->addSql('ALTER TABLE pv DROP FOREIGN KEY FK_D780BF007C29F7A6');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53150A48F1');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497AD74A60');
        $this->addSql('ALTER TABLE gendarme DROP FOREIGN KEY FK_1AA064BFE19A1A8');
        $this->addSql('ALTER TABLE pv_infraction DROP FOREIGN KEY FK_B79F07737697C467');
        $this->addSql('ALTER TABLE pv_criminel DROP FOREIGN KEY FK_F03AFF61E8A4F4B0');
        $this->addSql('ALTER TABLE pv_infraction DROP FOREIGN KEY FK_B79F0773E8A4F4B0');
        $this->addSql('ALTER TABLE gendarme DROP FOREIGN KEY FK_1AA064BF8BD700D');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF8BD700D');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('DROP TABLE appel_cog');
        $this->addSql('DROP TABLE criminel');
        $this->addSql('DROP TABLE gendarme');
        $this->addSql('DROP TABLE grade');
        $this->addSql('DROP TABLE infraction');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE pv');
        $this->addSql('DROP TABLE pv_criminel');
        $this->addSql('DROP TABLE pv_infraction');
        $this->addSql('DROP TABLE unit');
        $this->addSql('DROP TABLE user');
    }
}
