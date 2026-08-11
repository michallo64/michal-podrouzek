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
});
