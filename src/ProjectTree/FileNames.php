<?php

namespace App\ProjectTree;

use function Symfony\Component\String\u;

final class FileNames
{
    /**
     * @param string $prefix added when the name does not start with a letter
     */
    public static function className(string $name, string $prefix): string
    {
        $className = u($name)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', ' ')
            ->replaceMatches('/\b[A-Z]{2,}\b/', self::lowercaseMatch(...))
            ->title(true)
            ->replace(' ', '')
            ->toString();

        return preg_match('/^[A-Za-z]/', $className) === 1 ? $className : $prefix.$className;
    }

    public static function slug(string $name): string
    {
        return u($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();
    }

    /**
     * Keeps the first occurrence of a name and numbers the next ones from 2. The comparison is case-insensitive, like
     * PHP class names.
     *
     * @param list<string> $names
     *
     * @return list<string>
     */
    public static function unique(array $names, string $separator = ''): array
    {
        $used = [];
        $uniqueNames = [];

        foreach ($names as $name) {
            $uniqueName = $name;

            for ($number = 2; isset($used[strtolower($uniqueName)]); ++$number) {
                $uniqueName = $name.$separator.$number;
            }

            $used[strtolower($uniqueName)] = true;
            $uniqueNames[] = $uniqueName;
        }

        return $uniqueNames;
    }

    /**
     * @param array{string} $match
     */
    private static function lowercaseMatch(array $match): string
    {
        return strtolower($match[0]);
    }
}
