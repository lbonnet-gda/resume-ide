<?php

namespace App\Tests\ProjectTree;

use App\Entity\Interest;
use App\Entity\Language;
use App\Entity\Project;
use App\Entity\Skill;
use App\ProjectTree\File;
use App\ProjectTree\Folder;
use App\ProjectTree\ProjectTreeBuilder;
use PHPUnit\Framework\TestCase;

final class ProjectTreeBuilderTest extends TestCase
{
    public function testTreeHasEveryFileSorted(): void
    {
        self::assertSame(
            [
                'config/',
                'config/interests.yaml',
                'config/languages.yaml',
                'projects/',
                'projects/recipe-box.md',
                'projects/resume-ide.md',
                'src/',
                'src/Education/',
                'src/Education/IutDeLyon.php',
                'src/Education/UniversiteGrenobleAlpes.php',
                'src/Experience/',
                'src/Experience/AcmeCorp.php',
                'src/Experience/Globex.php',
                '.env',
                'composer.json',
                'README.md',
            ],
            array_map(
                static fn (File|Folder $node): string => $node instanceof Folder ? $node->path.'/' : $node->path,
                self::descendants(self::tree()),
            ),
        );
    }

    public function testExperiencesAndEducationsHaveDateHints(): void
    {
        $tree = self::tree();

        self::assertSame('2021 – now', self::file($tree, 'src/Experience/AcmeCorp.php')->dateHint);
        self::assertSame('2018 – 2021', self::file($tree, 'src/Experience/Globex.php')->dateHint);
        self::assertSame('2011 – 2013', self::file($tree, 'src/Education/IutDeLyon.php')->dateHint);
        self::assertSame('2015', self::file($tree, 'src/Education/UniversiteGrenobleAlpes.php')->dateHint);
        self::assertNull(self::file($tree, 'README.md')->dateHint);
        self::assertNull(self::file($tree, 'projects/recipe-box.md')->dateHint);
    }

    public function testFilesHaveTheirCodeBreadcrumb(): void
    {
        $tree = self::tree();

        self::assertSame(['App\\Experience', 'AcmeCorp'], self::file($tree, 'src/Experience/AcmeCorp.php')->breadcrumb);
        self::assertSame(['App\\Education', 'IutDeLyon'], self::file($tree, 'src/Education/IutDeLyon.php')->breadcrumb);
        self::assertSame(['Jane Doe'], self::file($tree, 'README.md')->breadcrumb);
        self::assertSame(['recipe-box'], self::file($tree, 'projects/recipe-box.md')->breadcrumb);
        self::assertSame(['require'], self::file($tree, 'composer.json')->breadcrumb);
        self::assertSame(['languages'], self::file($tree, 'config/languages.yaml')->breadcrumb);
        self::assertSame([], self::file($tree, '.env')->breadcrumb);
    }

    public function testTheOldestOfSeveralEntriesKeepsThePlainName(): void
    {
        $tree = new ProjectTreeBuilder()->build(
            Entities::profile(),
            [
                Entities::experience('Acme Corp', '2021-01-01'),
                Entities::experience('Acme Corp', '2016-01-01', '2018-01-01'),
            ],
            [
                Entities::education('IUT', '2012-01-01', '2013-01-01'),
                Entities::education('IUT', '2010-01-01', '2011-01-01'),
            ],
            [],
            [
                new Project()->setName('Box')->setDescription('Le premier.'),
                new Project()->setName('box')->setDescription('Le second.'),
            ],
            [],
            [],
        );

        $oldest = self::file($tree, 'src/Experience/AcmeCorp.php');
        $newest = self::file($tree, 'src/Experience/AcmeCorp2.php');
        self::assertSame('2016 – 2018', $oldest->dateHint);
        self::assertStringContainsString('final class AcmeCorp extends Experience', $oldest->content);
        self::assertSame('2021 – now', $newest->dateHint);
        self::assertStringContainsString('final class AcmeCorp2 extends Experience', $newest->content);

        self::assertSame('2010 – 2011', self::file($tree, 'src/Education/Iut.php')->dateHint);
        self::assertSame('2012 – 2013', self::file($tree, 'src/Education/Iut2.php')->dateHint);

        self::assertStringContainsString('Le premier.', self::file($tree, 'projects/box.md')->content);
        self::assertStringContainsString('Le second.', self::file($tree, 'projects/box-2.md')->content);
    }

    public function testEveryFileHasAFinalNewlineAndNeverThePhoneNumber(): void
    {
        foreach (self::descendants(self::tree()) as $node) {
            if ($node instanceof File) {
                self::assertStringEndsWith("\n", $node->content, $node->path);
                self::assertStringEndsNotWith("\n\n", $node->content, $node->path);
                self::assertStringNotContainsString('39 98', $node->content, $node->path);
            }
        }
    }

    private static function tree(): Folder
    {
        return new ProjectTreeBuilder()->build(
            Entities::profile(),
            [
                Entities::experience('Globex', '2018-03-01', '2021-08-31'),
                Entities::experience('Acme Corp', '2021-09-01'),
            ],
            [
                Entities::education('Université Grenoble Alpes', '2015-01-01', '2015-06-30'),
                Entities::education('IUT de Lyon'),
            ],
            [new Skill()->setName('PHP')->setCategory('Langages')->setLevel(5)],
            [
                new Project()->setName('resume-ide')->setDescription('Ce CV.'),
                new Project()->setName('recipe-box')->setDescription('Des recettes.'),
            ],
            [new Language()->setName('Français')->setLevel('Langue maternelle')],
            [new Interest()->setName('Trail')],
        );
    }

    private static function file(Folder $tree, string $path): File
    {
        $file = $tree->find($path);
        self::assertInstanceOf(File::class, $file, $path);

        return $file;
    }

    /**
     * @return list<File|Folder>
     */
    private static function descendants(Folder $folder): array
    {
        $nodes = [];

        foreach ($folder->children as $child) {
            $nodes[] = $child;

            if ($child instanceof Folder) {
                $nodes = [...$nodes, ...self::descendants($child)];
            }
        }

        return $nodes;
    }
}
