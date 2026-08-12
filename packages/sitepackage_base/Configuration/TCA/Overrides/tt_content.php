<?php

declare(strict_types=1);

defined('TYPO3') or die();

// Two starter grid presets. Keep this list small and generic — a project
// that needs more layout variety should add presets here, in base, not in
// a brand package, so they stay reusable across relaunches.
call_user_func(static function (): void {
    $registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\B13\Container\Tca\Registry::class);

    $registry->configureContainer(
        (new \B13\Container\Tca\ContainerConfiguration(
            'sitepackage-1col',
            'One Column',
            'A single full-width content column.',
            [
                [
                    ['name' => 'Content', 'colPos' => 210],
                ],
            ]
        ))->setIcon('container-1col')
    );

    $registry->configureContainer(
        (new \B13\Container\Tca\ContainerConfiguration(
            'sitepackage-2col',
            'Two Columns',
            'Two equal-width content columns, side by side.',
            [
                [
                    ['name' => 'Left', 'colPos' => 220],
                    ['name' => 'Right', 'colPos' => 221],
                ],
            ]
        ))->setIcon('container-2col')
    );

    // Card grid: desktop 3 columns / tablet 2 / mobile 1 (see base.css
    // .grid-cards). Intended for Service Card and Project Card content
    // blocks — the `allowed` restriction below is declarative documentation
    // of that intent, not enforced: it only takes effect with
    // EXT:content_defender installed, which does not yet support TYPO3 13.
    // Editors are trusted to place the right block here for now; revisit if
    // content_defender adds v13 support.
    $registry->configureContainer(
        (new \B13\Container\Tca\ContainerConfiguration(
            'sitepackage-cardgrid3',
            'Card Grid (3 columns)',
            'A responsive grid (3 / 2 / 1 columns) for Service Card or Project Card content blocks.',
            [
                [
                    [
                        'name' => 'Cards',
                        'colPos' => 230,
                        'allowed' => ['CType' => 'sitepackage_service_card, sitepackage_project_card'],
                    ],
                ],
            ]
        ))->setIcon('container-3col')
    );

    // Asymmetric 8/4 split (see base.css .grid-split-8-4). Wide slot first,
    // narrow slot second — matches the Home bento teaser and the Contact
    // form/sidebar layout. A mirrored 4/8 variant is not registered yet;
    // add "sitepackage-split48" the same way if a future page needs it.
    $registry->configureContainer(
        (new \B13\Container\Tca\ContainerConfiguration(
            'sitepackage-split84',
            'Split 8/4',
            'An asymmetric two-column layout: a wide 8-column slot and a narrow 4-column slot.',
            [
                [
                    ['name' => 'Wide (8 col)', 'colPos' => 240],
                    ['name' => 'Narrow (4 col)', 'colPos' => 241],
                ],
            ]
        ))->setIcon('container-2col-left')
    );
});
