<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Promo Showcase',
    'description' => 'Backend dashboard widgets that live-calculate years of TYPO3 experience and extensions shipped from a small TCA-driven milestone table.',
    'category' => 'module',
    'author' => 'Michal Podroužek',
    'author_email' => 'podrouzekmichal@gmail.com',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'dashboard' => '13.4.0-13.4.99',
            'extbase' => '13.4.0-13.4.99',
        ],
    ],
];
