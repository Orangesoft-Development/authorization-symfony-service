<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200310100328 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE accounts_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE auth_credentials_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sessions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sms_codes_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE accounts (id INT NOT NULL, enabled BOOLEAN NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(180) DEFAULT NULL, birthday DATE DEFAULT NULL, country_id VARCHAR(2) DEFAULT NULL, city_id INT DEFAULT NULL, avatar_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE auth_credentials (id INT NOT NULL, account_id INT NOT NULL, method VARCHAR(255) CHECK(method IN (\'phone\', \'apple\', \'google\', \'facebook\')) NOT NULL, name VARCHAR(180) NOT NULL, username VARCHAR(180) NOT NULL, password VARCHAR(180) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36175A96F85E0677 ON auth_credentials (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36175A9635C246D5 ON auth_credentials (password)');
        $this->addSql('CREATE INDEX IDX_36175A969B6B5FBA ON auth_credentials (account_id)');
        $this->addSql('COMMENT ON COLUMN auth_credentials.method IS \'(DC2Type:AuthMethodType)\'');
        $this->addSql('CREATE TABLE sessions (id INT NOT NULL, credential_id INT DEFAULT NULL, access_token TEXT NOT NULL, refresh_token VARCHAR(128) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A609D13B6A2DD68 ON sessions (access_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A609D13C74F2195 ON sessions (refresh_token)');
        $this->addSql('CREATE INDEX IDX_9A609D132558A7A5 ON sessions (credential_id)');
        $this->addSql('CREATE TABLE sms_codes (id INT NOT NULL, phone VARCHAR(180) NOT NULL, salt VARCHAR(255) DEFAULT NULL, sms_code VARCHAR(180) DEFAULT NULL, sms_code_sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, sms_code_sent_number INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_671E1810444F97DD ON sms_codes (phone)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_671E1810CC38ACCE ON sms_codes (sms_code)');
        $this->addSql('ALTER TABLE auth_credentials ADD CONSTRAINT FK_36175A969B6B5FBA FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sessions ADD CONSTRAINT FK_9A609D132558A7A5 FOREIGN KEY (credential_id) REFERENCES auth_credentials (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE auth_credentials DROP CONSTRAINT FK_36175A969B6B5FBA');
        $this->addSql('ALTER TABLE sessions DROP CONSTRAINT FK_9A609D132558A7A5');
        $this->addSql('DROP SEQUENCE accounts_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE auth_credentials_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sessions_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sms_codes_id_seq CASCADE');
        $this->addSql('DROP TABLE accounts');
        $this->addSql('DROP TABLE auth_credentials');
        $this->addSql('DROP TABLE sessions');
        $this->addSql('DROP TABLE sms_codes');
    }
}
