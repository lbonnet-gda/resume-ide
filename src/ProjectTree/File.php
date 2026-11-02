<?php

namespace App\ProjectTree;

final readonly class File
{
    public string $name;

    public function __construct(
        public string $path,
        public string $content,
        public ?string $dateHint = null,
    ) {
        $this->name = basename($path);
    }
}
