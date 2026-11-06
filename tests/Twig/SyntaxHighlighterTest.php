<?php

namespace App\Tests\Twig;

use App\Twig\SyntaxHighlighter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SyntaxHighlighterTest extends KernelTestCase
{
    public function testPhpStringsAreOneTokenEvenWithEscapedQuotesAndKeywords(): void
    {
        $code = <<<'PHP'
            <?php

            final class A extends B
            {
                    parent::__construct(
                        role: 'Lead <dev>',
                        note: "it's \"final\"",
                        endDate: null,
                    );
            }

            PHP;

        self::assertSame(
            <<<'HTML'
                <span class="syntax-keyword">&lt;?php</span>

                <span class="syntax-keyword">final</span> <span class="syntax-keyword">class</span> A <span class="syntax-keyword">extends</span> B
                {
                        <span class="syntax-keyword">parent</span>::<span class="syntax-function">__construct</span>(
                            <span class="syntax-parameter">role</span>: <span class="syntax-string">&#039;Lead &lt;dev&gt;&#039;</span>,
                            <span class="syntax-parameter">note</span>: <span class="syntax-string">&quot;it&#039;s \&quot;final\&quot;&quot;</span>,
                            <span class="syntax-parameter">endDate</span>: <span class="syntax-keyword">null</span>,
                        );
                }

                HTML,
            self::highlighter()->highlight($code, 'A.php'),
        );
    }

    public function testMarkdownLinkTargetsAreLinksToFilesUrlsAndEmailsOnly(): void
    {
        $code = <<<'MARKDOWN'
            # Jane & co

            **Dev**

            - [Acme](src/Experience/Acme.php), [Home](README.md)
            [Site](https://example.com/?a=1&b=2) · [Mail](mailto:jane@example.com)
            [empty]() [z](//evil.example.com)

            MARKDOWN;

        self::assertSame(
            <<<'HTML'
                <span class="syntax-heading"># Jane &amp; co</span>

                <span class="syntax-bold">**Dev**</span>

                <span class="syntax-bullet">- </span><span class="syntax-link-text">[Acme]</span><span class="syntax-string">(<span class="syntax-target"><a href="/src/Experience/Acme.php">src/Experience/Acme.php</a></span>)</span>, <span class="syntax-link-text">[Home]</span><span class="syntax-string">(<span class="syntax-target"><a href="/">README.md</a></span>)</span>
                <span class="syntax-link-text">[Site]</span><span class="syntax-string">(<span class="syntax-target"><a class="is-external" href="https://example.com/?a=1&amp;b=2" target="_blank" rel="noopener noreferrer" aria-label="https://example.com/?a=1&amp;b=2 (new tab)">https://example.com/?a=1&amp;b=2</a></span>)</span> · <span class="syntax-link-text">[Mail]</span><span class="syntax-string">(<span class="syntax-target"><a class="is-external" href="mailto:jane@example.com">mailto:jane@example.com</a></span>)</span>
                [empty]() <span class="syntax-link-text">[z]</span><span class="syntax-string">(<span class="syntax-target">//evil.example.com</span>)</span>

                HTML,
            self::highlighter()->highlight($code, 'README.md'),
        );
    }

    public function testJsonKeysAreToldApartFromValuesAtEveryDepth(): void
    {
        $code = <<<'JSON'
            {
                "require": {
                    "say/hi": "\"hi\": now"
                }
            }

            JSON;

        self::assertSame(
            <<<'HTML'
                {
                    <span class="syntax-property">&quot;require&quot;</span>: {
                        <span class="syntax-property">&quot;say/hi&quot;</span>: <span class="syntax-string">&quot;\&quot;hi\&quot;: now&quot;</span>
                    }
                }

                HTML,
            self::highlighter()->highlight($code, 'composer.json'),
        );
    }

    public function testYamlValuesAreStringsExceptNull(): void
    {
        $code = <<<'YAML'
            interests:
                -   name: 'Note: ok'
                    description: null

            YAML;

        self::assertSame(
            <<<'HTML'
                <span class="syntax-key">interests</span>:
                    -   <span class="syntax-key">name</span>: <span class="syntax-string">&#039;Note: ok&#039;</span>
                        <span class="syntax-key">description</span>: <span class="syntax-keyword">null</span>

                HTML,
            self::highlighter()->highlight($code, 'interests.yaml'),
        );
    }

    public function testEnvEmailsAndUrlsAreLinks(): void
    {
        $code = <<<'ENV'
            # Contact
            EMAIL=jane@example.com
            GITHUB=https://github.example.com/jane
            CITY=Lyon

            ENV;

        self::assertSame(
            <<<'HTML'
                <span class="syntax-comment"># Contact</span>
                <span class="syntax-property">EMAIL</span>=<span class="syntax-string"><a class="is-external" href="mailto:jane@example.com">jane@example.com</a></span>
                <span class="syntax-property">GITHUB</span>=<span class="syntax-string"><a class="is-external" href="https://github.example.com/jane" target="_blank" rel="noopener noreferrer" aria-label="https://github.example.com/jane (new tab)">https://github.example.com/jane</a></span>
                <span class="syntax-property">CITY</span>=<span class="syntax-string">Lyon</span>

                HTML,
            self::highlighter()->highlight($code, '.env'),
        );
    }

    public function testVeryLongStringIsStillOneToken(): void
    {
        $text = str_repeat('a', 20000);

        self::assertSame(
            '<span class="syntax-string">&quot;'.$text.'&quot;</span>',
            self::highlighter()->highlight('"'.$text.'"', 'composer.json'),
        );
    }

    public function testUnknownFileTypeIsOnlyEscaped(): void
    {
        self::assertSame('&lt;b&gt; &amp; co', self::highlighter()->highlight('<b> & co', 'notes.txt'));
    }

    private static function highlighter(): SyntaxHighlighter
    {
        return self::getContainer()->get(SyntaxHighlighter::class);
    }
}
