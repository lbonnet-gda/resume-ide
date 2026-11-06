<?php

namespace App\Enum;

enum LanguageLevel: string
{
    case A1 = 'a1';
    case A2 = 'a2';
    case B1 = 'b1';
    case B2 = 'b2';
    case C1 = 'c1';
    case C2 = 'c2';
    case Native = 'native';

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

    public function frenchLabel(): string
    {
        return match ($this) {
            self::A1 => 'Débutant (A1)',
            self::A2 => 'Élémentaire (A2)',
            self::B1 => 'Intermédiaire (B1)',
            self::B2 => 'Avancé (B2)',
            self::C1 => 'Courant (C1)',
            self::C2 => 'Bilingue (C2)',
            self::Native => 'Langue maternelle',
        };
    }
}
