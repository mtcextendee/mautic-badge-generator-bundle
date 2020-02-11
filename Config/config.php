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
                    'mautic.badge.url.generator',
                    'mautic.badge.generator'
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
            ],
            'mautic.badge.inject.custom.content.subscriber' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\EventListener\InjectCustomContentSubscriber::class,
                'arguments' => [
                    'mautic.helper.templating',
                    'mautic.badge.model.badge',
                    'mautic.helper.integration',
                    'mautic.badge.url.generator',
                    'mautic.badge.generator'
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
                    'mautic.helper.paths',
                    'mautic.badge.url.generator'
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
                    'mautic.badge.hash.generator',
                    'mautic.helper.encryption'
                ],
            ],

            'mautic.badge.hash.generator' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeHashGenerator::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.security'
                ],
            ],

            'mautic.badge.rounded.image.generator' => [
                'class'     => \MauticPlugin\MauticBadgeGeneratorBundle\Generator\RoundedImageGenerator::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.paths'
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
            'mautic.form.type.badge.properties.text' => [
                'class' => \MauticPlugin\MauticBadgeGeneratorBundle\Form\Type\BadgeTextType::class,
                'arguments'=>[
                    'mautic.lead.model.field',
                    'mautic.badge.uploader'
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
            'mautic_badge_generator_list' => [
                'path'       => '/badge/contacts',
                'controller' => 'MauticBadgeGeneratorBundle:Badge:listView',
            ],
            'mautic_badge_generator_contacts' => [
                'path'       => '/badge/generator/contacts/{objectId}/page/{page}',
                'controller' => 'MauticBadgeGeneratorBundle:Badge:contacts',
            ],
        ],
        'public' => [
            'mautic_badge_generator_generate' => [
                'path'       => '/badge/generator/{objectId}/{contactId}/{hash}',
                'controller' => 'MauticBadgeGeneratorBundle:Badge:generate',
                'defaults'   => [
                    'hash' => '',
                ],
            ],
            'mautic_badge_generator_image_rounded' => [
                'path'       => '/badge/image/rounded/{encryptImageUrl}/{width}',
                'controller' => 'MauticBadgeGeneratorBundle:Badge:image',
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
                'mautic.plugin.badge.generator.index' => [
                    'route'    => 'mautic_badge_generator_index',
                    'iconClass' => 'fa fa-id-badge',
                    'priority' => 70,
                    'parent'   => 'mautic.plugin.badge.generator',
                    'checks'   => [
                        'integration' => [
                            'BadgeGenerator' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                ],
                'mautic.plugin.badge.generator.contacts' => [
                    'route'    => 'mautic_badge_generator_list',
                    'iconClass' => 'fa fa-user',
                    'priority' => 70,
                    'parent'   => 'mautic.plugin.badge.generator',
                    'checks'   => [
                        'integration' => [
                            'BadgeGenerator' => [
                                'enabled' => true,
                                'features' => [
                                    'contacts_grid_to_print',
                                ],
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
        'rounded_image_directory'=>'badge'
    ],
];
