<?php

namespace App\ProjectTree;

final readonly class File
{
    public string $name;

    /**
     * @param string $preview template of the preview in templates/preview/
     * @param array<string, mixed> $previewData variables of the preview template
     * @param list<string> $breadcrumb where the top of the file sits in its code structure
     * @param string $title title of link previews
     * @param string $description summary of the page, for search engines and link previews
     */
    public function __construct(
        public string $path,
        public string $content,
        public string $preview,
        public string $title,
        public string $description,
        public array $previewData = [],
        public ?string $dateHint = null,
        public array $breadcrumb = [],
    ) {
        $this->name = basename($path);
    }
}
