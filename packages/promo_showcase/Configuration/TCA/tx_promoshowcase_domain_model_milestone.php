<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:promo_showcase/Resources/Private/Language/locallang_db.xlf:tx_promoshowcase_domain_model_milestone',
        'label' => 'title',
        'label_alt' => 'milestone_date',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'default_sortby' => 'milestone_date ASC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/actions/actions-clock.svg',
        'searchFields' => 'title,description',
    ],
    'columns' => [
        'milestone_type' => [
            'label' => 'LLL:EXT:promo_showcase/Resources/Private/Language/locallang_db.xlf:tx_promoshowcase_domain_model_milestone.milestone_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:promo_showcase/Resources/Private/Language/locallang_db.xlf:tx_promoshowcase_domain_model_milestone.milestone_type.careerStart',
                        'value' => 'career_start',
                    ],
                    [
                        'label' => 'LLL:EXT:promo_showcase/Resources/Private/Language/locallang_db.xlf:tx_promoshowcase_domain_model_milestone.milestone_type.extensionShipped',
                        'value' => 'extension_shipped',
                    ],
                ],
                'default' => 'extension_shipped',
                'required' => true,
            ],
        ],
        'title' => [
            'label' => 'LLL:EXT:promo_showcase/Resources/Private/Language/locallang_db.xlf:tx_promoshowcase_domain_model_milestone.title',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'eval' => 'trim',
            ],
        ],
        'milestone_date' => [
            'label' => 'LLL:EXT:promo_showcase/Resources/Private/Language/locallang_db.xlf:tx_promoshowcase_domain_model_milestone.milestone_date',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:promo_showcase/Resources/Private/Language/locallang_db.xlf:tx_promoshowcase_domain_model_milestone.description',
            'config' => [
                'type' => 'text',
                'rows' => 3,
                'eval' => 'trim',
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => 'milestone_type, title, milestone_date, description',
        ],
    ],
];
