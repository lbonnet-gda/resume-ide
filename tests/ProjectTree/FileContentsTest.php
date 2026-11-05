<?php

namespace App\Tests\ProjectTree;

use App\Entity\Interest;
use App\Entity\Language;
use App\Entity\Project;
use App\Entity\Skill;
use App\ProjectTree\File;
use App\ProjectTree\FileContents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

final class FileContentsTest extends TestCase
{
    public function testReadmeLinksEverySectionToItsFiles(): void
    {
        $readme = FileContents::readme(
            Entities::profile(),
            [
                Entities::experience('Acme Corp', '2021-09-01'),
                Entities::experience('Globex', '2018-03-01', '2021-08-31'),
                Entities::experience('Umbrella', '2015-03-01', '2015-08-31'),
            ],
            [
                new File('src/Experience/AcmeCorp2.php', ''),
                new File('src/Experience/Globex.php', ''),
                new File('src/Experience/Umbrella.php', ''),
            ],
            [Entities::education()],
            [new File('src/Education/IutDeLyon.php', '')],
            [new Project()->setName('Recipe Box')->setDescription('Des recettes.')],
            [new File('projects/recipe-box.md', '')],
        );

        self::assertSame(
            <<<'MARKDOWN'
                # Jane Doe

                **Développeuse PHP**

                Lyon, France ·
                [jane.doe@example.com](mailto:jane.doe@example.com) ·
                [LinkedIn](https://linkedin.example.com/in/jane-doe) ·
                [GitHub](https://github.example.com/jane-doe)

                Dix ans de PHP.

                ## Expériences

                - [Acme Corp](src/Experience/AcmeCorp2.php), Lead développeuse (2021 – aujourd'hui)
                - [Globex](src/Experience/Globex.php), Lead développeuse (2018 – 2021)
                - [Umbrella](src/Experience/Umbrella.php), Lead développeuse (2015)

                ## Formation

                - [IUT de Lyon](src/Education/IutDeLyon.php), DUT informatique (2011 – 2013)

                ## Projets

                - [Recipe Box](projects/recipe-box.md)

                ## En savoir plus

                - [Compétences](composer.json)
                - [Langues](config/languages.yaml)
                - [Centres d'intérêt](config/interests.yaml)

                MARKDOWN,
            $readme,
        );
    }

    public function testReadmeOmitsMissingContactLinks(): void
    {
        $readme = FileContents::readme(Entities::profile()->setLinkedinUrl(null)->setGithubUrl(null), [], [], [], [], [], []);

        self::assertStringContainsString(
            "Lyon, France ·\n[jane.doe@example.com](mailto:jane.doe@example.com)\n\nDix ans",
            $readme,
        );
    }

    public function testEnvIsParsableAndOmitsMissingLinks(): void
    {
        self::assertSame(
            [
                'EMAIL' => 'jane.doe@example.com',
                'LINKEDIN' => 'https://linkedin.example.com/in/jane-doe',
                'GITHUB' => 'https://github.example.com/jane-doe',
            ],
            new Dotenv()->parse(FileContents::env(Entities::profile())),
        );

        $profile = Entities::profile()->setLinkedinUrl(null)->setGithubUrl(null);
        self::assertSame(['EMAIL' => 'jane.doe@example.com'], new Dotenv()->parse(FileContents::env($profile)));
    }

    public function testComposerJsonMapsSkillsToVersionedPackages(): void
    {
        $skill = static fn (string $name, string $category, int $level): Skill => new Skill()
            ->setName($name)
            ->setCategory($category)
            ->setLevel($level);

        $json = FileContents::composerJson(Entities::profile(), [
            $skill('PHP', 'Langages', 5),
            $skill('GitHub Actions', 'Outils', 3),
            $skill('PHPStan', 'Qualité', 4),
            $skill('C', 'Langages', 2),
            $skill('C++', 'Langages', 1),
        ]);

        self::assertSame(
            [
                'name' => 'jane-doe/resume',
                'description' => 'Compétences : la version indique le niveau (1 à 5)',
                'require' => [
                    'langages/php' => '^5.0',
                    'outils/github-actions' => '^3.0',
                    'qualite/phpstan' => '^4.0',
                    'langages/c' => '^2.0',
                    'langages/c-2' => '^1.0',
                ],
            ],
            json_decode($json, true, flags: JSON_THROW_ON_ERROR),
        );
        self::assertStringContainsString('    "name": "jane-doe/resume",', $json);
    }

    public function testExperienceClass(): void
    {
        $content = FileContents::experience('AcmeCorp', Entities::experience());

        self::assertSame(
            <<<'PHP'
                <?php

                namespace App\Experience;

                use App\Resume\Experience;
                use DateTimeImmutable;

                final class AcmeCorp extends Experience
                {
                    public function __construct()
                    {
                        parent::__construct(
                            company: 'Acme Corp',
                            role: 'Lead développeuse',
                            location: 'Lyon',
                            startDate: new DateTimeImmutable('2021-09-01'),
                            endDate: null,
                            missions: [
                                'Encadrer une équipe',
                            ],
                            stack: [
                                'PHP',
                                'Symfony',
                            ],
                        );
                    }
                }

                PHP,
            $content,
        );
    }

    public function testEducationClassHasBothDatesAndWritesOutANullDescription(): void
    {
        $withoutDescription = FileContents::education('IutDeLyon', Entities::education());
        $withDescription = FileContents::education('IutDeLyon', Entities::education(description: 'Génie logiciel.'));

        self::assertStringContainsString(
            "            startDate: new DateTimeImmutable('2011-09-01'),\n"
            ."            endDate: new DateTimeImmutable('2013-06-30'),\n"
            ."            description: null,\n",
            $withoutDescription,
        );
        self::assertStringContainsString("description: 'Génie logiciel.',", $withDescription);
    }

    public function testPhpStringsStayValidWhateverTheData(): void
    {
        $content = FileContents::experience('AcmeCorp', Entities::experience(missions: [
            "Refonte d'une API",
            "L'option \"beta\" à 5$ avec un \\",
            'Le prix en $, avec un \\',
            "Sur\ndeux lignes d'écriture",
            "'; exit; //",
        ]));

        self::assertNotEmpty(token_get_all($content, TOKEN_PARSE));
        self::assertStringContainsString('"Refonte d\'une API",', $content);
        self::assertStringContainsString('"L\'option \"beta\" à 5\$ avec un \\\\",', $content);
        self::assertStringContainsString("'Le prix en $, avec un \\\\',", $content);
    }

    public function testProjectLinksAreOptional(): void
    {
        $project = new Project()->setName('recipe-box')->setDescription("Des recettes.\n\nEt des courses.");

        self::assertSame("# recipe-box\n\nDes recettes.\n\nEt des courses.\n", FileContents::project($project));

        $project->setRepositoryUrl('https://git.example.com');
        self::assertStringEndsWith("courses.\n\n- [Code source](https://git.example.com)\n", FileContents::project($project));

        $project->setUrl('https://example.com');
        self::assertStringEndsWith(
            "courses.\n\n- [Site](https://example.com)\n- [Code source](https://git.example.com)\n",
            FileContents::project($project),
        );
    }

    public function testLanguagesYaml(): void
    {
        $content = FileContents::languages([
            new Language()->setName('Français')->setLevel('Langue maternelle'),
            new Language()->setName('Anglais')->setLevel('Courant (C1)'),
        ]);

        self::assertSame(
            <<<'YAML'
                languages:
                    -   name: Français
                        level: Langue maternelle
                    -   name: Anglais
                        level: Courant (C1)

                YAML,
            $content,
        );
    }

    public function testInterestsYamlWritesNullsOutAndQuotesWhenRequired(): void
    {
        $interests = [
            ['Trail', 'Courses en montagne, du 20 au 50 km'],
            ['Cuisine', null],
            ['Lecture: romans', 'Polars # surtout'],
            ['true', '42'],
            ["L'été", '- de tout'],
        ];

        $content = FileContents::interests(array_map(
            static fn (array $interest): Interest => new Interest()->setName($interest[0])->setDescription($interest[1]),
            $interests,
        ));

        self::assertStringContainsString(
            "interests:\n    -   name: Trail\n        description: Courses en montagne, du 20 au 50 km\n",
            $content,
        );
        self::assertStringContainsString("    -   name: Cuisine\n        description: null\n", $content);
        self::assertSame(
            [
                'interests' => array_map(
                    static fn (array $interest): array => ['name' => $interest[0], 'description' => $interest[1]],
                    $interests,
                ),
            ],
            Yaml::parse($content),
        );
    }
}
