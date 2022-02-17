<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ('index' == $tmpl) {
    $view->extend('MauticBadgeGeneratorBundle:Badge:index.html.php');
}
/* @var \MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge $items */
?>
<?php if (count($items)): ?>
    <div class="table-responsive page-list">
        <table class="table table-hover table-striped table-bordered msgtable-list" id="msgTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#msgTable',
                        'routeBase'       => 'badge_generator',
                        'templateButtons' => [
                            'delete' => $permissions['lead:leads:deleteown']
                                || $permissions['lead:leads:deleteother'],
                        ],
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'badge',
                        'orderBy'    => 'e.name',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-msg-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'badge',
                        'orderBy'    => false,
                        'text'       => 'mautic.plugin.badge.generator.form.uploaded_source_pdf',
                        'class'      => 'col-msg-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'badge',
                        'orderBy'    => false,
                        'text'       => 'mautic.plugin.badge.generator.form.example',
                        'class'      => 'col-msg-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'badge',
                        'orderBy'    => 'e.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'col-msg-id visible-md visible-lg',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $view['security']->hasEntityAccess(
                                        $permissions['lead:leads:editown'],
                                        $permissions['lead:leads:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['lead:leads:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['lead:leads:deleteown'],
                                        $permissions['lead:leads:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase'  => 'badge_generator',
                                'nameGetter' => 'getName',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo $view['router']->url(
                            'mautic_badge_generator_action',
                            ['objectAction' => 'edit', 'objectId' => $item->getId()]
                        ); ?>" data-toggle="ajax">
                            <?php echo $item->getName(); ?>

                        </a>
                    </td>
                    <td>
                        <?php if ($item->getSource()): ?>

                            <a href="<?php echo $uploader->getFullUrl($item, 'source'); ?>" target="_blank">
                                <?php echo $item->getSource(); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo $view['router']->url(
                            'mautic_badge_generator_generate',
                            [
                                'objectId'  => $item->getId(),
                                'contactId' => 0,
                            ]
                        ); ?>" target="_blank">
                            <?php echo $view['translator']->trans('mautic.plugin.badge.generator.form.example_pdf'); ?>

                        </a>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="panel-footer">
            <?php echo $view->render(
                'MauticCoreBundle:Helper:pagination.html.php',
                [
                    'totalItems' => count($items),
                    'page'       => $page,
                    'limit'      => $limit,
                    'menuLinkId' => 'mautic_badge_generator_event_index',
                    'baseUrl'    => $view['router']->url('mautic_badge_generator_index'),
                    'sessionVar' => 'badge',
                ]
            ); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
