<?php

namespace App\Terminal;

enum Command: string
{
    case Help = 'help';
    case Ls = 'ls';
    case Cd = 'cd';
    case Cat = 'cat';
    case Open = 'open';
    case Whoami = 'whoami';
    case Download = 'download';
    case Clear = 'clear';

    public function usage(): string
    {
        return match ($this) {
            self::Ls => 'ls [path]',
            self::Cd => 'cd <path>',
            self::Cat => 'cat <file>',
            self::Open => 'open <file>',
            self::Download => 'download cv',
            self::Help, self::Whoami, self::Clear => $this->value,
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Help => 'List available commands',
            self::Ls => 'List a folder',
            self::Cd => 'Change the current folder',
            self::Cat => 'Print a file',
            self::Open => 'Open a file in the editor',
            self::Whoami => 'Short presentation',
            self::Download => 'Download the PDF resume',
            self::Clear => 'Clear the terminal',
        };
    }

    public function maxArguments(): int
    {
        return match ($this) {
            self::Help, self::Whoami, self::Clear => 0,
            self::Ls, self::Cd, self::Cat, self::Open, self::Download => 1,
        };
    }
}
