<?php

return [
    'name'        => 'Badge Generator',
    'description' => 'Badge Generator for Mautic',
    'author'      => 'mtcextendee.com',
    'version'     => '1.0.0',
    'services' => [
        'events' => [
            'mautic.badge.button.subscriber'=>[
                'class'=> \MauticPlugin\MauticBadgeGeneratorBundle\EventListener\ButtonSubscriber::class,
                'arguments' => [
                    'mautic.badge.model.badge',
                    'mautic.helper.integration',
                    'mautic.badge.url.generator'
                ],
            ],
            'mautic.badge.page.subscriber' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\EventListener\PageSubscriber::class,
                'arguments' => [
                    'mautic.badge.token.replacer',
                    'mautic.lead.model.lead'
                ],
            ],
            'mautic.badge.email.subscriber' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\EventListener\EmailSubscriber::class,
                'arguments' => [
                    'mautic.badge.token.replacer'
                ],
            ],
            'mautic.badge.token.subscriber' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\EventListener\TokensSubscriber::class,
                'arguments' => [
                    'mautic.badge.model.badge'
                ],
            ]
        ],
        'models' => [
            'mautic.badge.model.badge' => [
                'class' => MauticPlugin\MauticBadgeGeneratorBundle\Model\BadgeModel::class,
            ],
        ],
        'others' => [
            'mautic.badge.uploader' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\Uploader\BadgeUploader::class,
                'arguments' => [
                    'mautic.helper.file_uploader',
                    'mautic.helper.core_parameters',
                    'mautic.helper.paths',
                ],
            ],
            'mautic.badge.generator' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\Generator\BadgeGenerator::class,
                'arguments' => [
                    'mautic.badge.model.badge',
                    'mautic.lead.model.lead',
                    'mautic.badge.uploader',
                    'mautic.helper.core_parameters',
                    'mautic.helper.integration',
                    'mautic.badge.barcode.generator',
                    'mautic.badge.qrcode.generator',
                    'templating.helper.assets',
                    'mautic.helper.paths'

                ],
            ],
            'mautic.badge.barcode.generator' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\Generator\BarcodeGenerator::class,
                'arguments' => [
                    'router'
                ],
            ],
            'mautic.badge.qrcode.generator' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\Generator\QRcodeGenerator::class,
                'arguments' => [
                    'router'
                ],
            ],
            'mautic.badge.token.replacer' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeTokenReplacer::class,
                'arguments' => [
                    'mautic.badge.url.generator'
                ],
            ],
            'mautic.badge.url.generator' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeUrlGenerator::class,
                'arguments' => [
                    'router',
                    'mautic.badge.hash.generator'
                ],
            ],

            'mautic.badge.hash.generator' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeHashGenerator::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.security'
                ],
            ],
        ],
        'forms'=>[
            'mautic.form.type.badge' => [
                'class' => MauticPlugin\MauticBadgeGeneratorBundle\Form\Type\BadgeType::class,
                'alias' => 'badge',
                'arguments'=>[
                    'doctrine.orm.entity_manager',
                ]
            ],
            'mautic.form.type.badge.properties' => [
                'class' => MauticPlugin\MauticBadgeGeneratorBundle\Form\Type\BadgePropertiesType::class,
                'arguments'=>[
                    'mautic.helper.integration',
                    'translator'
                ]
            ],
        ]
    ],
    'routes'      => [
        'main' =>[
            'mautic_badge_generator_index'  => [
                'path'       => '/badge/generator/{page}',
                'controller' => 'MauticBadgeGeneratorBundle:Badge:index',
            ],
            'mautic_badge_generator_action' => [
                'path'       => '/badge/generator/{objectAction}/{objectId}',
                'controller' => 'MauticBadgeGeneratorBundle:Badge:execute',
            ],
        ],
        'public' => [
            'mautic_badge_generator_event' => [
                'path'       => '/badge/generator_test',
                'controller' => 'MauticBadgeGeneratorBundle:BadgeGenerator:send',
            ],

            'mautic_badge_generator_generate' => [
                'path'       => '/badge/generator/{objectId}/{contactId}/{hash}',
                'controller' => 'MauticBadgeGeneratorBundle:Badge:generate',
                'defaults'   => [
                    'hash' => '',
                ],
            ],


        ],
    ],
    'menu'        => [
        'main' => [
            'items' => [
                'mautic.plugin.badge.generator' => [
                    'route'    => 'mautic_badge_generator_index',
                    'iconClass' => 'fa fa-id-badge',
                    'priority' => 70,
                    'checks'   => [
                        'integration' => [
                            'BadgeGenerator' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'parameters' => [
        'badge_image_directory'         => 'badges',
        'badge_custom_font_path_to_ttf' => false,
        'badge_text_block_count' => 4,
    ],
];
