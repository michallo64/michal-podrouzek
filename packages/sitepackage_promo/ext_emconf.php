<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Site Package: Promo',
    'description' => 'Brand layer (CSS custom properties, site identity, content) for the personal promo/portfolio site. Depends on sitepackage_base.',
    'category' => 'templates',
    'author' => 'Michal Podroužek',
    'author_email' => 'podrouzekmichal@gmail.com',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'sitepackage_base' => '1.0.0-1.0.99',
        ],
    ],
];
