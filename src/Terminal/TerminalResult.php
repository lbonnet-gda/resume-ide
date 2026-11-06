<?php

namespace App\Terminal;

final readonly class TerminalResult
{
    /**
     * @param string $cwd current directory after the command, relative to the root: "" or "src/Experience"
     * @param ?string $filePath the file to open with TerminalAction::Open
     */
    public function __construct(
        public string $cwd,
        public string $output = '',
        public bool $isError = false,
        public ?TerminalAction $action = null,
        public ?string $filePath = null,
    ) {
    }
}
