<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261106092634 extends AbstractMigration
{
    private const array LEVELS = [
        'Débutant (A1)' => 'a1',
        'Élémentaire (A2)' => 'a2',
        'Intermédiaire (B1)' => 'b1',
        'Avancé (B2)' => 'b2',
        'Courant (C1)' => 'c1',
        'Bilingue (C2)' => 'c2',
        'Langue maternelle' => 'native',
    ];

    public function getDescription(): string
    {
        return 'Store language levels as codes instead of French text';
    }

    public function up(Schema $schema): void
    {
        foreach (self::LEVELS as $text => $code) {
            $this->addSql('UPDATE language SET level = ? WHERE level = ?', [$code, $text]);
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::LEVELS as $text => $code) {
            $this->addSql('UPDATE language SET level = ? WHERE level = ?', [$text, $code]);
        }
    }
}
