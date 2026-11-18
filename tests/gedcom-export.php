<?php

declare(strict_types=1);

use App\Entity\{Cemetery, Item};
use App\Service\GedcomExporter;
use Korin\Gedcom\{Gedcom, Version};

require dirname(__DIR__).'/vendor/autoload.php';
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };

$check(GedcomExporter::date('Jan. 20, 1957') === '20 JAN 1957', 'Abbreviated month with a period.');
$check(GedcomExporter::date('SEPT 25, 1970') === '25 SEP 1970', 'SEPT is read as September.');
$check(GedcomExporter::date('DECEMBER 1, 1937') === '1 DEC 1937', 'Full upper-case month.');
$check(GedcomExporter::date('9 Sept. 1946') === '9 SEP 1946', 'Day before month.');
$check(GedcomExporter::date('APR 4 1939') === '4 APR 1939', 'No comma.');
$check(GedcomExporter::date('1977-12-16') === '16 DEC 1977', 'ISO date.');
$check(GedcomExporter::date('January 1826') === 'JAN 1826', 'Month and year.');
$check(GedcomExporter::date('1945') === '1945', 'Year only.');
$check(GedcomExporter::date('CHICAGO, WASHINGTON') === null, 'Text that is not a date gives no date.');
$check(GedcomExporter::date('Feb. 30, 1950') === null, 'An impossible day gives no date.');

$cemetery = new Cemetery();
$cemetery->name = "St. Peter's churchyard";

$stone = new Item('Gravestone of Bernard Thomas Renzy and Sandra Welborn');
$stone->cemetery = $cemetery;
$stone->aiResults['catalog'] = ['record' => ['people' => [
    ['name' => 'BERNARD THOMAS RENZY', 'birth' => 'Jan. 20, 1957', 'death' => 'Jan. 20, 2015', 'inscription' => "Captain, U.S. Army Reserves\nBeloved husband"],
    ['name' => 'SANDRA WELBORN', 'birth' => 'Aug. 26, 1940', 'death' => null, 'inscription' => null],
]]];
$unreadable = new Item('Gravestone with surname RENZY, illegible given names and dates');
$unreadable->aiResults['catalog'] = ['record' => ['people' => [
    ['name' => 'RENZY', 'birth' => null, 'death' => null, 'inscription' => "RENZY\n[Illegible given names]"],
    ['name' => '[Illegible]', 'birth' => null, 'death' => null, 'inscription' => null],
]]];
$noCatalog = new Item('Not processed yet');

$ged = (new GedcomExporter())->export([$stone, $unreadable, $noCatalog], static fn (Item $i): string => 'https://mematlas.test/item/'.($i->id ?? 0));
$doc = Gedcom::parse($ged);
$check($doc->version() === Version::V5_5_1, 'The default version is 5.5.1.');
$check(Gedcom::validate($doc)->isValid(), 'The file validates: '.json_encode(Gedcom::validate($doc)));

$people = $doc->individuals();
$check(count($people) === 3, 'Three named people: two on the first stone, the surname on the second. Got '.count($people));
$bernard = $people[0];
$check($bernard->name()?->surname === 'RENZY' && $bernard->name()->given === 'BERNARD THOMAS', 'The last word is the surname, the inscribed spelling is kept.');
$check($bernard->birth()?->date()?->toGedcom() === '20 JAN 1957', 'Birth date.');
$check($bernard->death()?->date()?->toGedcom() === '20 JAN 2015', 'Death date.');
$check($people[1]->death() === null, 'A missing death date stays missing.');
$check(str_contains($ged, 'Captain, U.S. Army Reserves'), 'The inscription is quoted verbatim.');
$check(str_contains($ged, 'Beloved husband'), 'A multi-line inscription keeps its second line.');
$check(str_contains($ged, "St. Peter's churchyard"), 'The source names the cemetery.');
$check(str_contains($ged, 'https://mematlas.test/item/'), 'The source links to the stone page.');
$check(!str_contains($ged, 'Illegible]') || str_contains($ged, 'Illegible given names'), 'An unreadable name is not exported as a person.');
$check(!str_contains($ged, 'Not processed yet'), 'An item without a catalog record is skipped.');
$check(count($doc->sources()) === 2, 'One source per stone that has people.');

echo "ok\n";
