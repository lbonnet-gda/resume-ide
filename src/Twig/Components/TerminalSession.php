<?php

namespace App\Twig\Components;

use App\Entity\Profile;
use App\ProjectTree\ProjectTreeLoader;
use App\Terminal\Terminal;
use App\Terminal\TerminalAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveResponse;

#[AsLiveComponent]
final class TerminalSession
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    // Every entry goes back and forth with each request.
    public const int MAX_ENTRIES = 100;

    #[LiveProp]
    public string $cwd = '';

    /** @var list<array{prompt: string, line: string, output: string, isError: bool}> */
    #[LiveProp]
    public array $entries = [];

    private ?Terminal $terminal = null;

    public function __construct(
        private readonly ProjectTreeLoader $loader,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getPrompt(): string
    {
        return $this->terminal()->prompt($this->cwd);
    }

    #[LiveAction]
    public function run(#[LiveArg] string $line): ?LiveResponse
    {
        $result = $this->terminal()->execute($line, $this->cwd);

        if ($result->action === TerminalAction::Clear) {
            $this->entries = [];

            return null;
        }

        $this->addEntry($line, $result->output, $result->isError);
        $this->cwd = $result->cwd;

        if ($result->action === TerminalAction::Open) {
            $url = $this->urlGenerator->generate('app_file', ['path' => $result->filePath]);
            $this->dispatchBrowserEvent('terminal:open', ['url' => $url]);
        }

        if ($result->action === TerminalAction::Download) {
            return LiveResponse::downloadUrl($this->urlGenerator->generate('app_resume_pdf'));
        }

        return null;
    }

    #[LiveAction]
    public function complete(#[LiveArg] string $line): void
    {
        $candidates = $this->terminal()->complete($line, $this->cwd);

        if ($candidates === []) {
            return;
        }

        $lastWord = (string) preg_replace('/^.*\s/s', '', $line);
        $completion = $candidates[0];

        foreach ($candidates as $candidate) {
            while (!str_starts_with($candidate, $completion)) {
                $completion = substr($completion, 0, -1);
            }
        }

        if (count($candidates) === 1 && !str_ends_with($completion, '/')) {
            $completion .= ' ';
        }

        if (count($candidates) > 1) {
            $directory = (string) preg_replace('#[^/]*$#', '', $lastWord);
            $names = array_map(static fn (string $candidate): string => substr($candidate, strlen($directory)), $candidates);
            $this->addEntry($line, implode('  ', $names), false);
        }

        $completedLine = substr($line, 0, strlen($line) - strlen($lastWord)).$completion;
        $this->dispatchBrowserEvent('terminal:complete', ['line' => $completedLine]);
    }

    private function addEntry(string $line, string $output, bool $isError): void
    {
        $this->entries[] = [
            'prompt' => $this->terminal()->prompt($this->cwd),
            'line' => $line,
            'output' => $output,
            'isError' => $isError,
        ];
        $this->entries = array_slice($this->entries, -self::MAX_ENTRIES);
    }

    private function terminal(): Terminal
    {
        if ($this->terminal !== null) {
            return $this->terminal;
        }

        $root = $this->loader->load();
        $profile = $this->entityManager->getRepository(Profile::class)->findOneBy([], ['id' => 'ASC']);

        if ($root === null || $profile === null) {
            throw new ServiceUnavailableHttpException();
        }

        return $this->terminal = new Terminal($root, $profile);
    }
}
