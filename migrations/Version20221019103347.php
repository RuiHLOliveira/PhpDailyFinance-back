<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221019103347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE classe_movimento (id SERIAL NOT NULL, usuario_id INT NOT NULL, nome VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6AB4A932DB38439E ON classe_movimento (usuario_id)');
        $this->addSql('COMMENT ON COLUMN classe_movimento.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN classe_movimento.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN classe_movimento.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE conta (id SERIAL NOT NULL, usuario_id INT NOT NULL, nome VARCHAR(255) NOT NULL, saldo NUMERIC(10, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_485A16C3DB38439E ON conta (usuario_id)');
        $this->addSql('COMMENT ON COLUMN conta.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN conta.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN conta.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE invitation_token (id SERIAL NOT NULL, user_id INT DEFAULT NULL, invitation_token VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_33FC351AA76ED395 ON invitation_token (user_id)');
        $this->addSql('CREATE TABLE movimento (id SERIAL NOT NULL, classe_id INT DEFAULT NULL, tipomovimento_id INT NOT NULL, conta_id INT NOT NULL, usuario_id INT NOT NULL, descricao VARCHAR(255) NOT NULL, valor NUMERIC(10, 2) NOT NULL, data_movimento TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5BE0E9158F5EA509 ON movimento (classe_id)');
        $this->addSql('CREATE INDEX IDX_5BE0E9153883922B ON movimento (tipomovimento_id)');
        $this->addSql('CREATE INDEX IDX_5BE0E915628EE05C ON movimento (conta_id)');
        $this->addSql('CREATE INDEX IDX_5BE0E915DB38439E ON movimento (usuario_id)');
        $this->addSql('COMMENT ON COLUMN movimento.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN movimento.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN movimento.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE tipo_movimento (id SERIAL NOT NULL, usuario_id INT NOT NULL, nome VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B98E565BDB38439E ON tipo_movimento (usuario_id)');
        $this->addSql('COMMENT ON COLUMN tipo_movimento.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN tipo_movimento.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN tipo_movimento.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE classe_movimento ADD CONSTRAINT FK_6AB4A932DB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conta ADD CONSTRAINT FK_485A16C3DB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invitation_token ADD CONSTRAINT FK_33FC351AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movimento ADD CONSTRAINT FK_5BE0E9158F5EA509 FOREIGN KEY (classe_id) REFERENCES classe_movimento (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movimento ADD CONSTRAINT FK_5BE0E9153883922B FOREIGN KEY (tipomovimento_id) REFERENCES tipo_movimento (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movimento ADD CONSTRAINT FK_5BE0E915628EE05C FOREIGN KEY (conta_id) REFERENCES conta (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movimento ADD CONSTRAINT FK_5BE0E915DB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tipo_movimento ADD CONSTRAINT FK_B98E565BDB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE classe_movimento DROP CONSTRAINT FK_6AB4A932DB38439E');
        $this->addSql('ALTER TABLE conta DROP CONSTRAINT FK_485A16C3DB38439E');
        $this->addSql('ALTER TABLE invitation_token DROP CONSTRAINT FK_33FC351AA76ED395');
        $this->addSql('ALTER TABLE movimento DROP CONSTRAINT FK_5BE0E9158F5EA509');
        $this->addSql('ALTER TABLE movimento DROP CONSTRAINT FK_5BE0E9153883922B');
        $this->addSql('ALTER TABLE movimento DROP CONSTRAINT FK_5BE0E915628EE05C');
        $this->addSql('ALTER TABLE movimento DROP CONSTRAINT FK_5BE0E915DB38439E');
        $this->addSql('ALTER TABLE tipo_movimento DROP CONSTRAINT FK_B98E565BDB38439E');
        $this->addSql('DROP TABLE classe_movimento');
        $this->addSql('DROP TABLE conta');
        $this->addSql('DROP TABLE invitation_token');
        $this->addSql('DROP TABLE movimento');
        $this->addSql('DROP TABLE tipo_movimento');
        $this->addSql('DROP TABLE "user"');
    }
}
