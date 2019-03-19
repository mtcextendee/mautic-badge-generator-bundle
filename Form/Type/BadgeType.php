<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class BadgeType extends AbstractType
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * BadgeType conastructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {

        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventSubscriber(new CleanFormSubscriber(['properties' => 'clean']));

        $builder->add(
            'name',
            TextType::class,
            [
                'label'       => 'mautic.plugin.badge.generator.form.name',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    )
                ]
            ]
        );

        $transformer = new IdToEntityModelTransformer(
            $this->em,
            'MauticStageBundle:Stage'
        );

        $builder->add(
            $builder->create(
            'stage',
            'stage_list',
            [
                'label'       => 'mautic.plugin.badge.generator.form.stage',
                'empty_value'=> '',
                'multiple'=> false,
                'required'    => false,
            ]
        )->addModelTransformer($transformer)
        );

        $builder->add(
            'source',
            'file',
            [
                'label'      => 'mautic.plugin.badge.generator.form.source',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'   => 'form-control',
                ],
                'mapped'      => false,
                'constraints' => [
                    new File(
                        [
                            'mimeTypes' => [
                                'application/pdf',
                                'application/x-pdf',
                            ],
                            'mimeTypesMessage' => 'mautic.lead.avatar.types_invalid',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'width',
            NumberType::class,
            [
                'label'       => 'mautic.plugin.badge.generator.form.width',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    )
                ]
            ]
        );

        $builder->add(
            'height',
            NumberType::class,
            [
                'label'       => 'mautic.plugin.badge.generator.form.height',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    )
                ]
            ]
        );

        $builder->add(
            'properties',
            BadgePropertiesType::class,
            [
                'label'      => false,
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'   => 'form-control',
                ],
            ]
        );

        $builder->add(
            'buttons',
            'form_buttons'
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'badge';
    }
}
