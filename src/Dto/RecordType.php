<?php

declare(strict_types=1);

namespace App\Dto;

enum RecordType: string
{
    case Gravestone = 'gravestone';
    case PlaceReference = 'place_reference';
    case Document = 'document';
    case Object = 'object';
    case Uncertain = 'uncertain';
}
