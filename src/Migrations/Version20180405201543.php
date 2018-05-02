<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180405201543 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pv_infraction');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pv_infraction (pv_id INT NOT NULL, infraction_id INT NOT NULL, INDEX IDX_B79F0773E8A4F4B0 (pv_id), INDEX IDX_B79F07737697C467 (infraction_id), PRIMARY KEY(pv_id, infraction_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_infraction ADD CONSTRAINT FK_B79F07737697C467 FOREIGN KEY (infraction_id) REFERENCES infraction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_infraction ADD CONSTRAINT FK_B79F0773E8A4F4B0 FOREIGN KEY (pv_id) REFERENCES pv (id) ON DELETE CASCADE');
    }
}
