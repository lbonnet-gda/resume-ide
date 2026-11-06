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

    public function testEmptyDatabaseShowsASimplePage(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseStatusCodeSame(503);
        self::assertSelectorTextContains('h1', 'This résumé is empty');
    }
}
