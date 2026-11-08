<?php

namespace App\Tests\ProjectTree;

use App\ProjectTree\File;
use App\ProjectTree\Folder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FolderTest extends TestCase
{
    public function testChildrenAreSortedFoldersFirstThenCaseInsensitive(): void
    {
        $root = new Folder('', [
            new File('README.md', '', 'readme', '', ''),
            new File('composer.json', '', 'skills', '', ''),
            new File('.env', '', 'contact', '', ''),
            new Folder('src', []),
            new File('apple.md', '', 'project', '', ''),
            new Folder('Config', []),
            new File('item10.md', '', 'project', '', ''),
            new File('item2.md', '', 'project', '', ''),
        ]);

        self::assertSame(
            ['Config', 'src', '.env', 'apple.md', 'composer.json', 'item2.md', 'item10.md', 'README.md'],
            array_map(static fn (File|Folder $child): string => $child->name, $root->children),
        );
    }

    /**
     * @return iterable<string, array{string, ?string}>
     */
    public static function paths(): iterable
    {
        yield 'dotfile' => ['.env', '.env'];
        yield 'nested file' => ['src/Experience/AcmeCorp.php', 'src/Experience/AcmeCorp.php'];
        yield 'folder' => ['src/Experience', 'src/Experience'];
        yield 'empty path is the root' => ['', ''];
        yield 'leading, trailing and repeated slashes' => ['/src//Experience/', 'src/Experience'];
        yield 'current folder' => ['./src/./Experience', 'src/Experience'];
        yield 'parent folder' => ['src/Experience/../../README.md', 'README.md'];
        yield 'parent of the root stays at the root' => ['../../README.md', 'README.md'];
        yield 'unknown file' => ['nope.md', null];
        yield 'names are case-sensitive' => ['readme.md', null];
        yield 'file used as a folder' => ['README.md/nope', null];
    }

    #[DataProvider('paths')]
    public function testFindResolvesPaths(string $path, ?string $expectedPath): void
    {
        $root = new Folder('', [
            new Folder('src', [
                new Folder('src/Experience', [new File('src/Experience/AcmeCorp.php', '', 'experience', '', '')]),
            ]),
            new File('.env', '', 'contact', '', ''),
            new File('README.md', '', 'readme', '', ''),
        ]);

        self::assertSame($expectedPath, $root->find($path)?->path);
    }
}
