<?php

declare(strict_types=1);

namespace App\Image;

enum ImagePreset: string
{
    case Thumb = 'thumb';
    case Detail = 'detail';
}
