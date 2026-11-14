<?php

declare(strict_types=1);

namespace App\Dto;

enum SymbolCategory: string
{
    case Music = 'music';
    case Sport = 'sport';
    case Hobby = 'hobby';
    case Work = 'work';
    case Military = 'military';
    case Faith = 'faith';
    case Portrait = 'portrait';
    case Nature = 'nature';
    case Other = 'other';
}
