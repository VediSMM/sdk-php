<?php

declare(strict_types=1);

$paths = [__DIR__ . '/src', __DIR__ . '/tests'];
if (is_dir(__DIR__ . '/examples')) {
    $paths[] = __DIR__ . '/examples';
}

$finder = PhpCsFixer\Finder::create()
    ->in($paths)
    ->ignoreDotFiles(false)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@PHP81Migration' => true,
        'declare_strict_types' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_align' => false,
    ])
    ->setFinder($finder);
