<?php

namespace App\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFilter;

/**
 * Highlights the files of the project tree only: each grammar covers what FileContents writes, not the whole language.
 */
final readonly class SyntaxHighlighter
{
    /**
     * One regular expression per token type, by file extension. When several match at the same position, the first
     * one wins.
     */
    private const array GRAMMARS = [
        'php' => [
            'string' => '\'(?:[^\'\\\\]++|\\\\.)*+\'|"(?:[^"\\\\]++|\\\\.)*+"',
            'keyword' => '<\?php|\b(?:namespace|use|final|class|extends|public|function|parent|new|null)\b',
            'function' => '\b__construct\b',
            'parameter' => '\b[a-zA-Z]+(?=: )',
        ],
        'md' => [
            'heading' => '^#{1,6} .*$',
            'bullet' => '^- ',
            'bold' => '\*\*[^*\n]+\*\*',
            'link' => '\[(?<label>[^]\n]*)]\((?<target>[^)\n]+)\)',
        ],
        'json' => [
            'property' => '"(?:[^"\\\\]++|\\\\.)*+"(?=:)',
            'string' => '"(?:[^"\\\\]++|\\\\.)*+"',
        ],
        'yaml' => [
            'keyword' => '(?<=: )null$',
            'string' => '(?<=: ).+$',
            'key' => '[a-z]+(?=:)',
        ],
        'env' => [
            'comment' => '^#.*$',
            'property' => '^[A-Z_]+(?==)',
            'string' => '(?<==).+$',
        ],
    ];

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[AsTwigFilter('highlight', isSafe: ['html'])]
    public function highlight(string $code, string $fileName): string
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $grammar = self::GRAMMARS[$extension] ?? [];

        if ($grammar === []) {
            return self::escape($code);
        }

        $alternatives = array_map(
            static fn (string $type, string $regex): string => "(?<$type>$regex)",
            array_keys($grammar),
            $grammar,
        );
        $flags = PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL;
        preg_match_all('~'.implode('|', $alternatives).'~mu', $code, $matches, $flags);
        $html = '';
        $position = 0;

        foreach ($matches as $match) {
            [$text, $offset] = $match[0];
            $type = array_find_key($grammar, static fn (string $regex, string $type): bool => $match[$type][0] !== null);
            $html .= self::escape(substr($code, $position, $offset - $position));
            $html .= match (true) {
                $type === 'link' => $this->markdownLink((string) $match['label'][0], (string) $match['target'][0]),
                $type === 'string' && $extension === 'env' => $this->envValue((string) $text),
                default => self::span((string) $type, (string) $text),
            };
            $position = $offset + strlen((string) $text);
        }

        return $html.self::escape(substr($code, $position));
    }

    private function markdownLink(string $label, string $target): string
    {
        return self::span('link-text', "[$label]")
            .'<span class="syntax-string">(<span class="syntax-target">'.$this->link($target, $target).'</span>)</span>';
    }

    private function envValue(string $value): string
    {
        if (preg_match('~^https?://~', $value) === 1) {
            return '<span class="syntax-string">'.$this->link($value, $value).'</span>';
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
            return '<span class="syntax-string">'.$this->link('mailto:'.$value, $value).'</span>';
        }

        return self::span('string', $value);
    }

    private function link(string $target, string $label): string
    {
        if (preg_match('~^https?://~', $target) === 1) {
            return sprintf(
                '<a class="is-external" href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s (new tab)">%s</a>',
                self::escape($target),
                self::escape($label),
                self::escape($label),
            );
        }

        if (str_starts_with($target, 'mailto:')) {
            return sprintf('<a class="is-external" href="%s">%s</a>', self::escape($target), self::escape($label));
        }

        // Only a relative path is a file of the project: "//host" would be a link to another site.
        if (preg_match('~^[\w.-][\w./-]*$~', $target) === 1) {
            return sprintf(
                '<a href="%s">%s</a>',
                self::escape($this->urlGenerator->generate('app_file', ['path' => $target])),
                self::escape($label),
            );
        }

        return self::escape($label);
    }

    private static function span(string $type, string $text): string
    {
        return sprintf('<span class="syntax-%s">%s</span>', $type, self::escape($text));
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE);
    }
}
