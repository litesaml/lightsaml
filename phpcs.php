<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in(__DIR__)
;

return (new Config())
    ->setRules([
        '@PSR12' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'method_chaining_indentation' => true,
        'operator_linebreak' => ['position' => 'beginning'],
        'ordered_class_elements' => ['order' => ['use_trait', 'constant', 'property', 'method']],
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['const', 'class', 'function']],
        'phpdoc_align' => true,
        'phpdoc_separation' => true,
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'return_type_declaration' => true,
        'simplified_null_return' => true,
        'single_trait_insert_per_statement' => true,
        'concat_space' =>  ['spacing' => 'one'],
        'class_attributes_separation' => ['elements' => ['method' => 'one']],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
        'no_unused_imports' => true,
        'fully_qualified_strict_types' => ['import_symbols' => true],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
