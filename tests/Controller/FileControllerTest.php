<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Education;
use App\Entity\Experience;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FileControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        new ORMPurger($this->entityManager)->purge();
    }

    public function testFileUrlRendersItsContentBreadcrumbAndTree(): void
    {
        new AppFixtures()->load($this->entityManager);

        $crawler = $this->client->request('GET', '/src/Experience/AcmeCorp.php');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('AcmeCorp.php');
        self::assertSelectorTextContains('.editor__code', 'final class AcmeCorp');
        self::assertSame(
            ['resume-ide', 'src', 'Experience', 'AcmeCorp.php'],
            $crawler->filter('.status-bar__path li')->each(static fn ($item): string => $item->text()),
        );
        self::assertSelectorTextContains('.tree a[aria-current="page"]', 'AcmeCorp.php');
        self::assertSelectorExists('.tree a[href="/config/languages.yaml"]');
        self::assertSelectorExists('.tree a[href="/.env"]');
        self::assertSelectorTextSame('.tab__link[aria-current="page"]', 'AcmeCorp.php');
    }

    public function testHomeShowsTheReadme(): void
    {
        new AppFixtures()->load($this->entityManager);

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex');
        self::assertPageTitleContains('README.md');
        self::assertSelectorTextContains('.editor__code', '# Jane Doe');
        self::assertSelectorTextContains('.tree a[href="/"][aria-current="page"]', 'README.md');
    }

    public function testUnknownPathAndFolderAreNotFoundInsideTheLayout(): void
    {
        new AppFixtures()->load($this->entityManager);

        foreach (['/src/Experience/Nope.php', '/src/Experience', '/src/../README.md'] as $url) {
            $this->client->request('GET', $url);

            self::assertResponseStatusCodeSame(404, $url);
            self::assertSelectorTextContains('.editor', 'File not found');
            self::assertSelectorExists('.tree a[href="/composer.json"]');
            self::assertSelectorNotExists('.tree [aria-current="page"]');
        }
    }

    public function testReadmeListsExperiencesAndEducationsFromTheDatabaseNewestFirst(): void
    {
        new AppFixtures()->load($this->entityManager);
        $this->entityManager->persist(
            new Experience()
                ->setCompany('Newest Company')
                ->setRole('Role')
                ->setLocation('Lyon')
                ->setStartDate(new DateTimeImmutable('2025-01-01'))
                ->setMissions([])
                ->setStack([]),
        );
        $this->entityManager->persist(
            new Education()
                ->setSchool('Newest School')
                ->setDegree('Degree')
                ->setLocation('Lyon')
                ->setStartDate(new DateTimeImmutable('2024-09-01'))
                ->setEndDate(new DateTimeImmutable('2025-06-30')),
        );
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/');

        preg_match_all(
            '/^- \[(.+)]\(src\/(?:Experience|Education)\//m',
            $crawler->filter('.editor__code')->text(normalizeWhitespace: false),
            $matches,
        );
        self::assertSame(
            [
                'Newest Company',
                'Acme Corp',
                'Globex',
                'Initech',
                'Umbrella Studio',
                'Newest School',
                'Université Grenoble Alpes',
                'IUT de Lyon',
            ],
            $matches[1],
        );
    }

    public function testViewModeComesFromTheUrlWithThePreviewByDefaultForTheReadmeOnly(): void
    {
        new AppFixtures()->load($this->entityManager);

        $expectedModes = [
            '/' => 'preview',
            '/composer.json' => 'code',
            '/?view=code' => 'code',
            '/composer.json?view=split' => 'split',
            '/composer.json?view=preview' => 'preview',
            '/composer.json?view=nope' => 'code',
        ];

        foreach ($expectedModes as $url => $mode) {
            $this->client->request('GET', $url);

            self::assertResponseIsSuccessful($url);
            self::assertSelectorExists(".file-view[data-mode=\"$mode\"] > .editor .editor__code", $url);
            self::assertSelectorExists(".file-view[data-mode=\"$mode\"] > .preview h1", $url);
            self::assertSelectorExists(".view-modes__button[data-mode=\"$mode\"][aria-current=\"true\"]", $url);
            self::assertSelectorCount(1, '.view-modes__button[aria-current]', $url);
        }
    }

    public function testLinksFromThePreviewKeepItWhenItIsTheOnlyPane(): void
    {
        new AppFixtures()->load($this->entityManager);

        $crawler = $this->client->request('GET', '/');

        self::assertSame(
            '/src/Experience/AcmeCorp.php?view=preview',
            $crawler->filter('.preview__timeline a')->first()->attr('href'),
        );

        $crawler = $this->client->request('GET', '/?view=split');

        self::assertSame('/src/Experience/AcmeCorp.php', $crawler->filter('.preview__timeline a')->first()->attr('href'));
    }

    public function testPreviewsShowTheResumeData(): void
    {
        new AppFixtures()->load($this->entityManager);

        $crawler = $this->client->request('GET', '/src/Experience/AcmeCorp.php');

        self::assertSelectorTextSame('.preview h1', 'Lead développeuse Symfony');
        self::assertSelectorTextContains('.preview__meta', "septembre 2021 – aujourd'hui");
        self::assertSelectorTextContains('.preview__meta', 'En poste');
        self::assertSelectorTextSame('.preview__timeline [aria-current="true"] .preview__timeline-title', 'Acme Corp');
        self::assertSelectorNotExists('.preview__timeline [aria-current="true"] a');
        self::assertCount(4, $crawler->filter('.preview__timeline li'));

        $crawler = $this->client->request('GET', '/config/languages.yaml');

        self::assertSame(
            ['Français' => 6, 'Anglais' => 5, 'Espagnol' => 3],
            array_combine(
                $crawler->filter('.preview__language strong')->each(static fn ($name): string => $name->text()),
                $crawler->filter('.preview__language')->each(
                    static fn ($card): int => $card->filter('.preview__dot.is-filled')->count(),
                ),
            ),
        );

        $crawler = $this->client->request('GET', '/projects/resume-ide.md');

        self::assertSame(
            ['recipe-box', 'trail-stats'],
            $crawler->filter('.preview__section .preview__mono a')->each(static fn ($link): string => $link->text()),
        );
    }

    public function testNoPageShowsThePhoneNumber(): void
    {
        new AppFixtures()->load($this->entityManager);

        $crawler = $this->client->request('GET', '/');
        $urls = $crawler->filter('.tree a[href^="/"]')->each(static fn ($link): string => (string) $link->attr('href'));

        self::assertCount(14, $urls);

        foreach ($urls as $url) {
            $this->client->request('GET', $url.'?view=split');

            self::assertResponseIsSuccessful($url);
            self::assertStringNotContainsString('39 98', (string) $this->client->getResponse()->getContent(), $url);
        }
    }

    public function testEmptyDatabaseShowsASimplePage(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseStatusCodeSame(503);
        self::assertSelectorTextContains('h1', 'This résumé is empty');
    }
}
