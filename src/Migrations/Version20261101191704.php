<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261101191704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the résumé tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE education (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, school VARCHAR(255) NOT NULL, degree VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('CREATE TABLE experience (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, company VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, missions CLOB NOT NULL, stack CLOB NOT NULL)');
        $this->addSql('CREATE TABLE interest (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, position INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE language (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, position INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE profile (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, summary CLOB NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, linkedin_url VARCHAR(255) DEFAULT NULL, github_url VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, url VARCHAR(255) DEFAULT NULL, repository_url VARCHAR(255) DEFAULT NULL, position INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE skill (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, level INTEGER NOT NULL, position INTEGER NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE education');
        $this->addSql('DROP TABLE experience');
        $this->addSql('DROP TABLE interest');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE skill');
    }
}
