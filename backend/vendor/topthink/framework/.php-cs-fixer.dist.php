<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in(__DIR__)
;

return (new Config())
    ->setRules([
        '@PhpCsFixer'                 => true,
        '@PHP8x0Migration'            => true,
        'binary_operator_spaces'      => [
            'default'   => 'align_single_space_minimal',
            'operators' => [
                '=>' => 'align_single_space_minimal_by_scope',
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'continue',
                'declare',
                'return',
                'throw',
                'try',
            ],
        ],
        'concat_space'                => [
            'spacing' => 'one',
        ],
        'global_namespace_import'     => [
            'import_classes'   => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'ordered_class_elements'      => [
            'order' => [
                'use_trait',
                'case',
                'constant',
                'property',
                'construct',
            ],
        ],
    ])
    ->setFinder($finder)
;
