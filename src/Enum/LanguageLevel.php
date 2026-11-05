<?php

namespace App\Enum;

enum LanguageLevel: string
{
    case A1 = 'Débutant (A1)';
    case A2 = 'Élémentaire (A2)';
    case B1 = 'Intermédiaire (B1)';
    case B2 = 'Avancé (B2)';
    case C1 = 'Courant (C1)';
    case C2 = 'Bilingue (C2)';
    case Native = 'Langue maternelle';

    public function label(): string
    {
        return match ($this) {
            self::A1 => 'A1 Beginner',
            self::A2 => 'A2 Elementary',
            self::B1 => 'B1 Intermediate',
            self::B2 => 'B2 Upper intermediate',
            self::C1 => 'C1 Advanced',
            self::C2 => 'C2 Proficient',
            self::Native => 'Native',
        };
    }
}
