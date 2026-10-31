<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
    ->append([
        __DIR__.'/bin/console',
        __FILE__,
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        // PhpStorm does not align PHPDoc tags and chops down long throw statements.
        'phpdoc_align' => ['align' => 'left'],
        'single_line_throw' => false,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
