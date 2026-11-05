<?php

$finder = new PhpCsFixer\Finder()
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

return new PhpCsFixer\Config()
    ->setRules([
        '@Symfony' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'if', 'for', 'foreach', 'while', 'switch', 'try', 'throw'],
        ],
        // Omitted options fall back to the fixer's defaults, not to the @Symfony values.
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'new_expression_parentheses' => ['use_parentheses' => false],
        // PhpStorm does not align PHPDoc tags and chops down long throw statements.
        'phpdoc_align' => ['align' => 'left'],
        'single_line_throw' => false,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
