<?php

namespace App\ProjectTree;

final readonly class Folder
{
    public string $name;

    /** @var list<self|File> folders first, then files, alphabetically and case-insensitive */
    public array $children;

    /**
     * @param list<self|File> $children
     */
    public function __construct(
        public string $path,
        array $children,
    ) {
        $this->name = $path === '' ? 'resume-ide' : basename($path);

        usort(
            $children,
            static fn (self|File $a, self|File $b): int => ($b instanceof self) <=> ($a instanceof self)
                ?: strnatcasecmp($a->name, $b->name),
        );
        $this->children = $children;
    }

    /**
     * Resolves a path like a shell does from this folder: "/" and "." are ignored, ".." goes up and stops at this
     * folder, names are case-sensitive.
     */
    public function find(string $path): self|File|null
    {
        $node = $this;
        $parents = [];

        foreach (explode('/', $path) as $segment) {
            if ($node instanceof File) {
                return null;
            }

            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                $node = array_pop($parents) ?? $node;

                continue;
            }

            $parents[] = $node;
            $node = array_find($node->children, static fn (self|File $child): bool => $child->name === $segment);

            if ($node === null) {
                return null;
            }
        }

        return $node;
    }
}
