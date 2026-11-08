<?php

namespace App\Tests\Twig\Components;

use App\DataFixtures\AppFixtures;
use App\Twig\Components\TerminalSession;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;
use Symfony\UX\LiveComponent\Test\TestLiveComponent;

final class TerminalSessionTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    private TestLiveComponent $terminal;

    protected function setUp(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        new ORMPurger($entityManager)->purge();
        new AppFixtures()->load($entityManager);

        $this->terminal = $this->createLiveComponent('TerminalSession');
    }

    public function testCommandsUpdateTheOutputAndTheCurrentFolder(): void
    {
        $this->terminal->call('run', ['line' => 'cd src'])->call('run', ['line' => 'ls <b>']);

        $component = $this->terminal->component();
        self::assertInstanceOf(TerminalSession::class, $component);
        self::assertSame('src', $component->cwd);

        $crawler = $this->terminal->render()->crawler();
        self::assertSame(
            ['jane@resume-ide:~$cd src', 'jane@resume-ide:~/src$ls <b>'],
            $crawler->filter('.terminal__log p')->each(static fn ($line): string => $line->text()),
        );
        self::assertSame('ls: <b>: No such file or directory', $crawler->filter('.terminal__log pre.terminal__error')->text());
        self::assertStringEndsWith('jane@resume-ide:~/src$', $crawler->filter('label.terminal__prompt')->text());

        $this->terminal->call('run', ['line' => 'clear']);

        self::assertCount(0, $this->terminal->render()->crawler()->filter('.terminal__log > *'));
    }

    public function testOutputKeepsOnlyTheLastEntries(): void
    {
        for ($i = 0; $i <= TerminalSession::MAX_ENTRIES; ++$i) {
            $this->terminal->call('run', ['line' => 'echo '.$i]);
        }

        $lines = $this->terminal->render()->crawler()->filter('.terminal__log p');
        self::assertCount(TerminalSession::MAX_ENTRIES, $lines);
        self::assertStringEndsWith('echo 1', $lines->first()->text());
    }

    public function testOpenNavigatesToTheFile(): void
    {
        $this->terminal->call('run', ['line' => 'cd src'])->call('run', ['line' => 'open Experience/AcmeCorp.php']);

        $this->assertComponentDispatchBrowserEvent($this->terminal, 'terminal:open')
            ->withPayload(['url' => '/src/Experience/AcmeCorp.php']);
    }

    public function testDownloadCvDownloadsThePdf(): void
    {
        $this->terminal->call('run', ['line' => 'download cv']);

        self::assertSame('/resume.pdf', $this->terminal->response()->headers->get('X-Live-Download-Url'));
    }

    /**
     * @param list<string> $listed
     */
    #[DataProvider('completions')]
    public function testCompletionReplacesTheLastWordAndListsSeveralCandidates(
        string $line,
        string $completedLine,
        array $listed,
    ): void {
        $this->terminal->call('complete', ['line' => $line]);

        $this->assertComponentDispatchBrowserEvent($this->terminal, 'terminal:complete')
            ->withPayload(['line' => $completedLine]);
        self::assertSame(
            $listed,
            $this->terminal->render()->crawler()->filter('.terminal__log pre')->each(static fn ($output): string => $output->text(null, false)),
        );
    }

    /**
     * @return iterable<string, array{string, string, list<string>}>
     */
    public static function completions(): iterable
    {
        yield 'single file' => ['cat  src/Experience/Acm', 'cat  src/Experience/AcmeCorp.php ', []];
        yield 'single folder' => ['ls src/Ex', 'ls src/Experience/', []];
        yield 'common prefix' => ['ls src/', 'ls src/E', ['Education/  Experience/']];
        yield 'nothing in common' => ['cat ', 'cat ', ['config/  projects/  src/  .env  composer.json  README.md']];
    }
}
