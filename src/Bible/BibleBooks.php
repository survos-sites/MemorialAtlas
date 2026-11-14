<?php

declare(strict_types=1);

namespace App\Bible;

/** Normalizes the book of a scripture citation, so "Ps.", "Psalm" and "PSALMS" become one facet value. */
final class BibleBooks
{
    public const BOOKS = ['Genesis', 'Exodus', 'Leviticus', 'Numbers', 'Deuteronomy', 'Joshua', 'Judges', 'Ruth', '1 Samuel', '2 Samuel',
        '1 Kings', '2 Kings', '1 Chronicles', '2 Chronicles', 'Ezra', 'Nehemiah', 'Esther', 'Job', 'Psalms', 'Proverbs', 'Ecclesiastes',
        'Song of Songs', 'Isaiah', 'Jeremiah', 'Lamentations', 'Ezekiel', 'Daniel', 'Hosea', 'Joel', 'Amos', 'Obadiah', 'Jonah', 'Micah',
        'Nahum', 'Habakkuk', 'Zephaniah', 'Haggai', 'Zechariah', 'Malachi', 'Matthew', 'Mark', 'Luke', 'John', 'Acts', 'Romans',
        '1 Corinthians', '2 Corinthians', 'Galatians', 'Ephesians', 'Philippians', 'Colossians', '1 Thessalonians', '2 Thessalonians',
        '1 Timothy', '2 Timothy', 'Titus', 'Philemon', 'Hebrews', 'James', '1 Peter', '2 Peter', '1 John', '2 John', '3 John', 'Jude', 'Revelation',
        'Tobit', 'Judith', 'Wisdom', 'Sirach', 'Baruch', '1 Maccabees', '2 Maccabees'];

    private const ALIASES = ['psalm' => 'Psalms', 'ps' => 'Psalms', 'song of solomon' => 'Song of Songs', 'canticles' => 'Song of Songs',
        'revelations' => 'Revelation', 'rev' => 'Revelation', 'apocalypse' => 'Revelation', 'ecclesiasticus' => 'Sirach',
        'wisdom of solomon' => 'Wisdom', 'mt' => 'Matthew', 'matt' => 'Matthew', 'mk' => 'Mark', 'lk' => 'Luke', 'jn' => 'John',
        'rom' => 'Romans', 'cor' => 'Corinthians', 'phil' => 'Philippians', 'heb' => 'Hebrews', 'prov' => 'Proverbs', 'isa' => 'Isaiah',
        'gen' => 'Genesis', 'eccl' => 'Ecclesiastes', 'tim' => 'Timothy', 'thess' => 'Thessalonians', 'pet' => 'Peter'];

    public static function normalize(?string $book): ?string
    {
        if ($book === null || trim($book) === '') return null;
        $key = strtolower(trim(preg_replace('/[^A-Za-z0-9 ]+/', ' ', $book)));
        $key = preg_replace(['/^(i{1,3}|first|second|third)\b/', '/\s+/'], ['', ' '], $key) ?? $key;
        $number = match (true) {
            (bool) preg_match('/^(3|iii|third)\b/i', trim($book)) => '3 ',
            (bool) preg_match('/^(2|ii|second)\b/i', trim($book)) => '2 ',
            (bool) preg_match('/^(1|i|first)\b/i', trim($book)) => '1 ',
            default => '',
        };
        $key = trim(preg_replace('/^[123]\s*/', '', $key) ?? $key);
        $name = self::ALIASES[$key] ?? null;
        if ($name === null) {
            foreach (self::BOOKS as $candidate) {
                if (strtolower(preg_replace('/^[123] /', '', $candidate) ?? '') === $key) { $name = preg_replace('/^[123] /', '', $candidate); break; }
            }
        }
        if ($name === null) return null;
        $full = in_array($number.$name, self::BOOKS, true) ? $number.$name : $name;

        return in_array($full, self::BOOKS, true) ? $full : null;
    }
}
