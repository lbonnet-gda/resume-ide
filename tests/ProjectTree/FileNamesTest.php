<?php

namespace App\Tests\ProjectTree;

use App\ProjectTree\FileNames;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FileNamesTest extends TestCase
{
    /**
     * @return iterable<array{string, string}>
     */
    public static function classNames(): iterable
    {
        yield ['Acme Corp', 'AcmeCorp'];
        yield ['Université Grenoble Alpes', 'UniversiteGrenobleAlpes'];
        yield ['IUT de Lyon', 'IutDeLyon'];
        yield ['GitHub France', 'GitHubFrance'];
        yield ['L’Oréal S.A.', 'LOrealSA'];
        yield ['3M', 'Company3M'];
        yield ['***', 'Company'];
    }

    #[DataProvider('classNames')]
    public function testClassNameIsAValidPhpClassName(string $name, string $expected): void
    {
        self::assertSame($expected, FileNames::className($name, 'Company'));
    }

    public function testSlugIsLowercaseAscii(): void
    {
        self::assertSame('qualite-du-code-2', FileNames::slug(' Qualité du code (2) '));
    }

    public function testUniqueNumbersDuplicatesFromTwo(): void
    {
        self::assertSame(['Acme', 'Globex', 'Acme2', 'Acme3'], FileNames::unique(['Acme', 'Globex', 'Acme', 'Acme']));
    }

    public function testUniqueIgnoresCaseAndAvoidsExistingNumberedNames(): void
    {
        self::assertSame(['Acme2', 'Acme', 'ACME3'], FileNames::unique(['Acme2', 'Acme', 'ACME']));
    }
}
