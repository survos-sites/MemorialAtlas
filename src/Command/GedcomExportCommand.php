<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Korin\Gedcom\{Gedcom, Version};
use Korin\Gedcom\Model\{GedcomDate, PersonalName};
use Korin\Gedcom\Syntax\{Document, Node};
use Symfony\Component\Console\Attribute\{AsCommand, Option};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Writes the people named in each stone's catalog record as a GEDCOM file.
 * Names, dates and inscriptions come from the stored record as inscribed. The file states no
 * relationships and no burial: the stone only shows that a person is named on it.
 */
#[AsCommand(name: 'app:gedcom:export', description: 'Write the people named on the stones as a GEDCOM file.')]
final class GedcomExportCommand
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urls,
    ) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Option(description: 'File to write')] string $output = 'var/mematlas.ged',
        #[Option(description: 'GEDCOM version: 5.5.1, 5.5.5 or 7.0', name: 'gedcom-version')] string $gedcomVersion = '5.5.1',
    ): int {
        $version = Version::tryFrom($gedcomVersion);
        if (null === $version) {
            $io->error(sprintf('Unknown GEDCOM version "%s".', $gedcomVersion));

            return 2;
        }

        $items = $this->em->getRepository(Item::class)->findBy(['recordType' => 'gravestone'], ['id' => 'ASC']);
        $ged = self::export(
            $items,
            fn (Item $item): string => $this->urls->generate('item_show', ['id' => $item->id], UrlGeneratorInterface::ABSOLUTE_URL),
            $version,
        );
        file_put_contents($output, $ged);

        $doc = Gedcom::read($output);
        $report = Gedcom::validate($doc);
        $io->success(sprintf('%d people from %d stones written to %s (GEDCOM %s).', count($doc->individuals()), count($doc->sources()), $output, $version->value));
        foreach ($report->diagnostics ?? [] as $diagnostic) {
            $io->warning((string) $diagnostic);
        }

        return $report->isValid() ? 0 : 1;
    }

    private const MONTHS = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

    /**
     * @param iterable<Item> $items
     * @param \Closure(Item): string|null $urlFor Public page of a stone, written in the source record
     */
    public static function export(iterable $items, ?\Closure $urlFor = null, Version $version = Version::V5_5_1): string
    {
        $doc = Document::create($version);
        $count = 0;

        $head = $doc->head() ?? throw new \LogicException('A new document has a header.');
        $head->addChild('SOUR', 'MEMATLAS')->addChild('NAME', 'Mematlas');
        $head->addChild('SUBM')->setPointer('@U1@');
        $doc->addRecord('SUBM', '@U1@')->addChild('NAME', 'Mematlas');

        foreach ($items as $item) {
            $people = array_values(array_filter(
                $item->aiResults['catalog']['record']['people'] ?? [],
                static fn (mixed $p): bool => is_array($p) && self::isNamed((string) ($p['name'] ?? '')),
            ));
            if ([] === $people) {
                continue;
            }

            $sourceXref = '@S'.++$count.'@';
            $source = $doc->addRecord('SOUR', $sourceXref);
            $source->addChild('TITL', $item->title);
            $note = 'Gravestone'.($item->cemetery ? ' in '.$item->cemetery->name : '').'. Names and dates are as read from the stone and have not been verified.';
            self::addText($source, 'NOTE', $note);
            if ($urlFor) {
                self::addText($source, 'TEXT', $urlFor($item));
            }

            foreach ($people as $p) {
                $name = trim((string) $p['name']);
                $person = $doc->addIndividual()->setName(self::name($name));
                foreach (['birth' => 'addBirth', 'death' => 'addDeath'] as $key => $add) {
                    $text = trim((string) ($p[$key] ?? ''));
                    if ('' === $text) {
                        continue;
                    }
                    $date = self::date($text);
                    if (null !== $date) {
                        $person->$add(GedcomDate::parse($date, $version));
                    } else {
                        self::addText($person->node(), 'NOTE', ucfirst($key).' as inscribed: '.$text);
                    }
                }
                if (!empty($p['inscription'])) {
                    self::addText($person->node(), 'NOTE', 'Inscription: '.$p['inscription']);
                }
                $citation = $person->node()->addChild('SOUR');
                $citation->setPointer($sourceXref);
                $citation->addChild('PAGE', 'Record '.($item->id ?? '?'));
            }
        }

        return Gedcom::serialize($doc);
    }

    /** GEDCOM date for inscriptions such as "Jan. 20, 1957", "9 Sept. 1946", "1977-12-16" or "1945"; null when it is not a date. */
    public static function date(string $text): ?string
    {
        $text = trim($text);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $text, $m)) {
            return self::compose((int) $m[3], (int) $m[2], (int) $m[1]);
        }
        if (preg_match('/^(\d{4})$/', $text)) {
            return $text;
        }
        if (preg_match('/^([A-Za-z]{3,9})\.?\s+(\d{1,2}),?\s+(\d{4})$/', $text, $m)) {
            return self::compose((int) $m[2], self::month($m[1]), (int) $m[3]);
        }
        if (preg_match('/^(\d{1,2})\s+([A-Za-z]{3,9})\.?,?\s+(\d{4})$/', $text, $m)) {
            return self::compose((int) $m[1], self::month($m[2]), (int) $m[3]);
        }
        if (preg_match('/^([A-Za-z]{3,9})\.?,?\s+(\d{4})$/', $text, $m)) {
            return self::compose(null, self::month($m[1]), (int) $m[2]);
        }

        return null;
    }

    private static function month(string $word): ?int
    {
        $index = array_search(strtoupper(substr($word, 0, 3)), self::MONTHS, true);

        return false === $index ? null : $index + 1;
    }

    private static function compose(?int $day, ?int $month, int $year): ?string
    {
        if (null === $month || $month < 1 || $month > 12 || $year < 1 || (null !== $day && !checkdate($month, $day, $year))) {
            return null;
        }

        return trim(($day ?? '').' '.self::MONTHS[$month - 1].' '.$year);
    }

    /** A name needs letters, and "[Illegible]" placeholders are not names. */
    private static function isNamed(string $name): bool
    {
        return 1 === preg_match('/\p{L}/u', $name) && !str_contains($name, '[') && !preg_match('/illegible|unknown/i', $name);
    }

    /** The stored name has no separate surname, so the last word is the surname; the inscribed spelling is kept. */
    private static function name(string $name): PersonalName
    {
        $words = preg_split('/\s+/', $name) ?: [$name];
        $surname = array_pop($words);

        return PersonalName::of(implode(' ', $words), $surname);
    }

    /** GEDCOM lines hold one line each, so a multi-line text becomes a value plus CONT lines. */
    private static function addText(Node $parent, string $tag, string $text): void
    {
        $lines = preg_split('/\R/', $text) ?: [$text];
        $node = $parent->addChild($tag, array_shift($lines));
        foreach ($lines as $line) {
            $node->addChild('CONT', $line);
        }
    }
}
