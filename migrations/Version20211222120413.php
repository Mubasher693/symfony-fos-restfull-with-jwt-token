<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211222120413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__post AS SELECT id, return FROM post');
        $this->addSql('DROP TABLE post');
        $this->addSql('CREATE TABLE post (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO post (id, title) SELECT id, return FROM __temp__post');
        $this->addSql('DROP TABLE __temp__post');
        $this->addSql('DROP INDEX IDX_8D93D649D60322AC');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, role_id, email, username, first_name, last_name, gender, created_at, updated_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role_id INTEGER DEFAULT NULL, email VARCHAR(128) NOT NULL COLLATE BINARY, username VARCHAR(128) NOT NULL COLLATE BINARY, first_name VARCHAR(128) DEFAULT NULL COLLATE BINARY, last_name VARCHAR(128) DEFAULT NULL COLLATE BINARY, gender VARCHAR(128) DEFAULT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES user_role (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user (id, role_id, email, username, first_name, last_name, gender, created_at, updated_at) SELECT id, role_id, email, username, first_name, last_name, gender, created_at, updated_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE INDEX IDX_8D93D649D60322AC ON user (role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__post AS SELECT id, title FROM post');
        $this->addSql('DROP TABLE post');
        $this->addSql('CREATE TABLE post (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, return VARCHAR(255) NOT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO post (id, return) SELECT id, title FROM __temp__post');
        $this->addSql('DROP TABLE __temp__post');
        $this->addSql('DROP INDEX IDX_8D93D649D60322AC');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, role_id, email, username, first_name, last_name, gender, created_at, updated_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role_id INTEGER DEFAULT NULL, email VARCHAR(128) NOT NULL, username VARCHAR(128) NOT NULL, first_name VARCHAR(128) DEFAULT NULL, last_name VARCHAR(128) DEFAULT NULL, gender VARCHAR(128) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO user (id, role_id, email, username, first_name, last_name, gender, created_at, updated_at) SELECT id, role_id, email, username, first_name, last_name, gender, created_at, updated_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE INDEX IDX_8D93D649D60322AC ON user (role_id)');
    }
}
