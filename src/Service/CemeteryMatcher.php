<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Cemetery;

final class CemeteryMatcher
{
    /** @return list<Cemetery> */
    public function matches(iterable $cemeteries, ?float $latitude, ?float $longitude): array
    {
        if ($latitude === null || $longitude === null) return [];
        $matches = [];
        foreach ($cemeteries as $cemetery) {
            if ($cemetery->latitude === null || $cemetery->longitude === null || $cemetery->matchingRadiusMeters === null) continue;
            $a = sin(deg2rad($cemetery->latitude - $latitude) / 2) ** 2
                + cos(deg2rad($latitude)) * cos(deg2rad($cemetery->latitude)) * sin(deg2rad($cemetery->longitude - $longitude) / 2) ** 2;
            $distance = 6_371_000 * 2 * asin(sqrt(min(1.0, max(0.0, $a))));
            if ($distance <= $cemetery->matchingRadiusMeters) $matches[] = $cemetery;
        }
        return $matches;
    }
}
