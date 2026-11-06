<?php

namespace App\ProjectTree;

final readonly class File
{
    public string $name;

    /**
     * @param string $preview template of the preview in templates/preview/
     * @param array<string, mixed> $previewData variables of the preview template
     * @param list<string> $breadcrumb where the top of the file sits in its code structure
     */
    public function __construct(
        public string $path,
        public string $content,
        public string $preview,
        public array $previewData = [],
        public ?string $dateHint = null,
        public array $breadcrumb = [],
    ) {
        $this->name = basename($path);
    }
}
