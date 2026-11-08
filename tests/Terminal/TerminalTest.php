<?php

namespace App\Tests\Terminal;

use App\ProjectTree\ProjectTreeBuilder;
use App\Terminal\Terminal;
use App\Terminal\TerminalAction;
use App\Tests\ProjectTree\Entities;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TerminalTest extends TestCase
{
    public function testBlankInputDoesNothing(): void
    {
        $result = $this->terminal()->execute('   ', 'src');

        self::assertSame('src', $result->cwd);
        self::assertSame('', $result->output);
        self::assertFalse($result->isError);
    }

    public function testHelpListsEveryCommand(): void
    {
        $expected = <<<'TEXT'
            Available commands:
              help          List available commands
              ls [path]     List a folder
              cd <path>     Change the current folder
              cat <file>    Print a file
              open <file>   Open a file in the editor
              whoami        Short presentation
              download cv   Download the PDF resume
              clear         Clear the terminal
            TEXT;

        self::assertSame($expected, $this->terminal()->execute('help', '')->output);
    }

    public function testUnknownCommandSuggestsHelp(): void
    {
        $result = $this->terminal()->execute('sudo hire jane now', 'src');

        self::assertTrue($result->isError);
        self::assertSame('sudo: command not found. Type "help" to list available commands.', $result->output);
        self::assertSame('src', $result->cwd);
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function listings(): iterable
    {
        yield 'root, sorted like the tree' => ['ls', '', 'config/  projects/  src/  .env  composer.json  README.md'];
        yield 'current folder is a subfolder' => ['ls', 'src', 'Education/  Experience/'];
        yield 'file' => ['ls ../README.md', 'src', 'README.md'];
        yield 'extra spaces' => ['  ls   Experience  ', 'src', 'AcmeCorp.php'];
        yield 'options are ignored' => ['ls -la Experience', 'src', 'AcmeCorp.php'];
    }

    #[DataProvider('listings')]
    public function testLs(string $input, string $cwd, string $expectedOutput): void
    {
        self::assertSame($expectedOutput, $this->terminal()->execute($input, $cwd)->output);
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function directoryChanges(): iterable
    {
        yield 'sibling' => ['cd ../Education', 'src/Experience', 'src/Education'];
        yield 'absolute path' => ['cd /config', 'src/Experience', 'config'];
        yield 'path from home' => ['cd ~/config', 'src/Experience', 'config'];
        yield 'no argument goes home' => ['cd', 'src/Experience', ''];
    }

    #[DataProvider('directoryChanges')]
    public function testCd(string $input, string $cwd, string $expectedCwd): void
    {
        $result = $this->terminal()->execute($input, $cwd);

        self::assertFalse($result->isError);
        self::assertSame($expectedCwd, $result->cwd);
        self::assertSame('', $result->output);
    }

    public function testCatPrintsTheFileFromTheCurrentFolder(): void
    {
        $result = $this->terminal()->execute('cat ../Experience/AcmeCorp.php', 'src/Education');

        self::assertStringContainsString('final class AcmeCorp extends Experience', $result->output);
        self::assertNull($result->action);
    }

    public function testOpenAsksToOpenTheResolvedFile(): void
    {
        $result = $this->terminal()->execute('open ../Experience/AcmeCorp.php', 'src/Education');

        self::assertSame(TerminalAction::Open, $result->action);
        self::assertSame('src/Experience/AcmeCorp.php', $result->filePath);
        self::assertSame('Opened src/Experience/AcmeCorp.php in the editor.', $result->output);
        self::assertSame('src/Education', $result->cwd);
    }

    public function testWhoamiPresentsTheProfileWithoutThePhone(): void
    {
        $output = $this->terminal()->execute('whoami', '')->output;

        self::assertSame('Jane Doe, Développeuse PHP (Lyon, France)', $output);
    }

    public function testDownloadCvAsksForTheDownload(): void
    {
        self::assertSame(TerminalAction::Download, $this->terminal()->execute('download cv', '')->action);
    }

    public function testClearAsksToClearAndPrintsNothing(): void
    {
        $result = $this->terminal()->execute('clear', 'src');

        self::assertSame(TerminalAction::Clear, $result->action);
        self::assertSame('', $result->output);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function errors(): iterable
    {
        yield 'ls on nothing' => ['ls nope', 'ls: nope: No such file or directory'];
        yield 'cd on nothing' => ['cd nope', 'cd: nope: No such file or directory'];
        yield 'cd on a file' => ['cd ~/README.md', 'cd: ~/README.md: Not a directory'];
        yield 'cat without argument' => ['cat', 'cat: missing file operand'];
        yield 'cat on a folder' => ['cat ..', 'cat: ..: Is a directory'];
        yield 'cat stays in the tree' => ['cat ../../etc/passwd', 'cat: ../../etc/passwd: No such file or directory'];
        yield 'open on a folder' => ['open /config', 'open: /config: Is a directory'];
        yield 'download of something else' => ['download /README.md', 'usage: download cv'];
        yield 'too many paths' => ['cd /src /config', 'cd: too many arguments'];
    }

    #[DataProvider('errors')]
    public function testErrorsKeepTheCurrentFolder(string $input, string $expectedOutput): void
    {
        $result = $this->terminal()->execute($input, 'src');

        self::assertTrue($result->isError);
        self::assertSame($expectedOutput, $result->output);
        self::assertSame('src', $result->cwd);
        self::assertNull($result->action);
    }

    /**
     * @return iterable<string, array{string, string, list<string>}>
     */
    public static function completions(): iterable
    {
        yield 'single command name' => ['  he', '', ['help']];
        yield 'command names sorted' => ['c', '', ['cat', 'cd', 'clear']];
        yield 'unknown command name' => ['co', '', []];
        yield 'download target' => ['download ', '', ['cv']];
        yield 'unknown download target' => ['download x', '', []];
        yield 'download takes a single target' => ['download cv ', '', []];
        yield 'empty word lists the current folder' => ['ls ', 'src', ['Education/', 'Experience/']];
        yield 'folders get a slash, sorted like the tree' => ['cat c', '', ['config/', 'composer.json']];
        yield 'keeps the typed path' => ['cat ../src/Experience/A', 'config', ['../src/Experience/AcmeCorp.php']];
        yield 'keeps home' => ['cat ~/sr', 'config', ['~/src/']];
        yield 'unknown folder' => ['cat nope/', '', []];
    }

    /**
     * @param list<string> $expectedCandidates
     */
    #[DataProvider('completions')]
    public function testComplete(string $input, string $cwd, array $expectedCandidates): void
    {
        self::assertSame($expectedCandidates, $this->terminal()->complete($input, $cwd));
    }

    public function testPromptShowsTheCurrentFolderFromHome(): void
    {
        $terminal = $this->terminal();

        self::assertSame('jane@resume-ide:~$', $terminal->prompt(''));
        self::assertSame('jane@resume-ide:~/src/Experience$', $terminal->prompt('src/Experience'));
    }

    private function terminal(): Terminal
    {
        $profile = Entities::profile();
        $root = new ProjectTreeBuilder()->build(
            $profile,
            [Entities::experience()],
            [Entities::education()],
            [],
            [],
            [],
            [],
        );

        return new Terminal($root, $profile);
    }
}
