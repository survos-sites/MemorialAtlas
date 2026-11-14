<?php

declare(strict_types=1);

use App\Service\DuplicateFinder;

require dirname(__DIR__).'/vendor/autoload.php';
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };

$record = static fn (int $id, ?float $lat, ?float $lon, string ...$names): array =>
    ['id' => $id, 'latitude' => $lat, 'longitude' => $lon, 'names' => $names];

// Two photos of the Renzy stone, 5 m apart, with one reading missing a letter of the surname.
$renzyA = $record(2, 38.693361, -78.168478, 'Bernard Thomas Renzy', 'Sandra Welborn');
$renzyB = $record(3, 38.693351, -78.168462, 'Bernard Thomas Ren', 'Sandra Welborn Ren');
// Another stone with the same given name, 300 m away.
$farAway = $record(9, 38.696061, -78.168478, 'Bernard Thomas Renzy');
// A stone with no names read.
$blank = $record(10, 38.693361, -78.168478);
// Same names, no GPS: still a candidate, since the 11 November photos have coarse or missing positions.
$noGps = $record(11, null, null, 'Sandra Welborn');

$pairs = DuplicateFinder::candidates([$renzyA, $renzyB, $farAway, $blank, $noGps]);
$keys = array_map(static fn (array $p): string => $p['a'].'-'.$p['b'], $pairs);

$check(in_array('2-3', $keys, true), 'Two readings of one stone, close together, are a candidate pair.');
$check(!in_array('2-9', $keys, true) && !in_array('3-9', $keys, true), 'A stone 300 m away is not a candidate, even with the same names.');
$check(!array_filter($keys, static fn (string $k): bool => str_contains($k, '10')), 'A record without names is never a candidate.');
$check(in_array('2-11', $keys, true), 'A record without GPS is compared on names alone.');

$pair = $pairs[array_search('2-3', $keys, true)];
$check($pair['nameScore'] >= 0.5 && $pair['nameScore'] <= 1.0, 'The name score is the share of name words the two records have in common.');
$check($pair['distanceMeters'] !== null && $pair['distanceMeters'] < 10, 'The pair keeps the camera distance in metres.');

$groups = DuplicateFinder::groups([[2, 3], [3, 86], [40, 41]]);
$check($groups[2] === 2 && $groups[3] === 2 && $groups[86] === 2, 'Linked pairs form one group, named by the lowest record id.');
$check($groups[40] === 40 && $groups[41] === 40, 'Separate pairs form separate groups.');

echo "duplicate-finder: ok\n";
