<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Site Package: Base',
    'description' => 'Reusable, brand-neutral site package starter kit (TypoScript, Fluid, Content Blocks, container grid presets).',
    'category' => 'templates',
    'author' => 'Michal Podroužek',
    'author_email' => 'podrouzekmichal@gmail.com',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ],
    ],
];
