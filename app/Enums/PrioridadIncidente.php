<?php

namespace App\Enums;

enum PrioridadIncidente: string
{
    case BAJA = 'baja';
    case MEDIA = 'media';
    case ALTA = 'alta';
    case CRITICA = 'critica';

    public function etiqueta(): string
    {
        return match ($this) {
            self::BAJA => 'Baja',
            self::MEDIA => 'Media',
            self::ALTA => 'Alta',
            self::CRITICA => 'Crítica',
        };
    }
}
