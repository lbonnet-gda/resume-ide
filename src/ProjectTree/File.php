<?php

namespace App\ProjectTree;

final readonly class File
{
    public string $name;

    /**
     * @param list<string> $breadcrumb where the top of the file sits in its code structure
     */
    public function __construct(
        public string $path,
        public string $content,
        public ?string $dateHint = null,
        public array $breadcrumb = [],
    ) {
        $this->name = basename($path);
    }
}
