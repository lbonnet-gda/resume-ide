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
}
