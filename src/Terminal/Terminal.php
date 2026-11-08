<?php

namespace App\Terminal;

use App\Entity\Profile;
use App\ProjectTree\File;
use App\ProjectTree\FileNames;
use App\ProjectTree\Folder;

final readonly class Terminal
{
    public function __construct(
        private Folder $root,
        private Profile $profile,
    ) {
    }

    public function prompt(string $cwd): string
    {
        $user = FileNames::slug(explode(' ', $this->profile->getName())[0]);

        return sprintf('%s@%s:%s$', $user, $this->root->name, rtrim('~/'.$cwd, '/'));
    }

    /**
     * @param string $cwd same format as TerminalResult::$cwd
     */
    public function execute(string $input, string $cwd): TerminalResult
    {
        $arguments = preg_split('/\s+/', $input, flags: PREG_SPLIT_NO_EMPTY) ?: [];
        $name = array_shift($arguments);

        if ($name === null) {
            return new TerminalResult($cwd);
        }

        $command = Command::tryFrom($name);

        if ($command === null) {
            return $this->error($cwd, $name.': command not found. Type "help" to list available commands.');
        }

        if ($command === Command::Ls) {
            $arguments = array_values(
                array_filter($arguments, static fn (string $argument): bool => !str_starts_with($argument, '-')),
            );
        }

        if (count($arguments) > $command->maxArguments()) {
            return $this->error($cwd, $name.': too many arguments');
        }

        $argument = $arguments[0] ?? null;

        return match ($command) {
            Command::Help => new TerminalResult($cwd, $this->help()),
            Command::Ls => $this->ls($argument, $cwd),
            Command::Cd => $this->cd($argument, $cwd),
            Command::Cat => $this->cat($argument, $cwd),
            Command::Open => $this->open($argument, $cwd),
            Command::Whoami => new TerminalResult($cwd, $this->whoami()),
            Command::Download => $this->download($argument, $cwd),
            Command::Clear => new TerminalResult($cwd, action: TerminalAction::Clear),
        };
    }

    /**
     * @param string $input the whole command line
     *
     * @return list<string> what can replace the last word of the line, folders ending with "/"
     */
    public function complete(string $input, string $cwd): array
    {
        $words = preg_split('/\s+/', ltrim($input)) ?: [''];

        if (count($words) === 1) {
            $commands = array_map(static fn (Command $command): string => $command->value, Command::cases());
            sort($commands);

            return array_values(
                array_filter($commands, static fn (string $command): bool => str_starts_with($command, $words[0])),
            );
        }

        if ($words[0] === Command::Download->value) {
            return count($words) === 2 && str_starts_with('cv', $words[1]) ? ['cv'] : [];
        }

        if (preg_match('#\s(\S*/)?([^\s/]*)$#', ltrim($input), $matches) !== 1) {
            return [];
        }

        [, $directory, $prefix] = $matches;
        $folder = $this->find($directory, $cwd);

        if (!$folder instanceof Folder) {
            return [];
        }

        $candidates = [];

        foreach ($folder->children as $child) {
            if (str_starts_with($child->name, $prefix)) {
                $candidates[] = $directory.$this->listedName($child);
            }
        }

        return $candidates;
    }

    private function help(): string
    {
        $lines = ['Available commands:'];

        foreach (Command::cases() as $command) {
            $lines[] = sprintf('  %-14s%s', $command->usage(), $command->description());
        }

        return implode("\n", $lines);
    }

    private function ls(?string $path, string $cwd): TerminalResult
    {
        $path ??= '.';
        $node = $this->find($path, $cwd);

        if ($node === null) {
            return $this->noSuchFile('ls', $path, $cwd);
        }

        if ($node instanceof File) {
            return new TerminalResult($cwd, $node->name);
        }

        return new TerminalResult($cwd, implode('  ', array_map($this->listedName(...), $node->children)));
    }

    private function cd(?string $path, string $cwd): TerminalResult
    {
        $path ??= '~';
        $node = $this->find($path, $cwd);

        if ($node === null) {
            return $this->noSuchFile('cd', $path, $cwd);
        }

        if ($node instanceof File) {
            return $this->error($cwd, sprintf('cd: %s: Not a directory', $path));
        }

        return new TerminalResult($node->path);
    }

    private function cat(?string $path, string $cwd): TerminalResult
    {
        $file = $this->findFile('cat', $path, $cwd);

        return $file instanceof File ? new TerminalResult($cwd, $file->content) : $file;
    }

    private function open(?string $path, string $cwd): TerminalResult
    {
        $file = $this->findFile('open', $path, $cwd);

        if (!$file instanceof File) {
            return $file;
        }

        return new TerminalResult(
            $cwd,
            sprintf('Opened %s in the editor.', $file->path),
            action: TerminalAction::Open,
            filePath: $file->path,
        );
    }

    private function whoami(): string
    {
        return sprintf(
            '%s, %s (%s)',
            $this->profile->getName(),
            $this->profile->getTitle(),
            $this->profile->getLocation(),
        );
    }

    private function download(?string $target, string $cwd): TerminalResult
    {
        if ($target !== 'cv') {
            return $this->error($cwd, 'usage: download cv');
        }

        return new TerminalResult($cwd, 'Downloading the PDF resume...', action: TerminalAction::Download);
    }

    private function findFile(string $command, ?string $path, string $cwd): File|TerminalResult
    {
        if ($path === null) {
            return $this->error($cwd, $command.': missing file operand');
        }

        $node = $this->find($path, $cwd);

        if ($node === null) {
            return $this->noSuchFile($command, $path, $cwd);
        }

        return $node instanceof File ? $node : $this->error($cwd, sprintf('%s: %s: Is a directory', $command, $path));
    }

    private function find(string $path, string $cwd): Folder|File|null
    {
        if ($path === '~' || str_starts_with($path, '~/')) {
            return $this->root->find(substr($path, 1));
        }

        return $this->root->find(str_starts_with($path, '/') ? $path : $cwd.'/'.$path);
    }

    private function listedName(Folder|File $node): string
    {
        return $node instanceof Folder ? $node->name.'/' : $node->name;
    }

    private function noSuchFile(string $command, string $path, string $cwd): TerminalResult
    {
        return $this->error($cwd, sprintf('%s: %s: No such file or directory', $command, $path));
    }

    private function error(string $cwd, string $message): TerminalResult
    {
        return new TerminalResult($cwd, $message, isError: true);
    }
}
