<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:FormTheme:form_simple.html.php');

?>
<?php
$view['slots']->start('primaryFormContent');
/** @var \MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge $item */
$item = $entity;
?>
<style>
    .btn-save {
        display: none
    }
</style>
<div class="row">
    <div class="col-md-3">
        <?php echo $view['form']->row($form['name']); ?>
    </div>
    <div class="col-md-3">
        <?php echo $view['form']->row($form['width']); ?>
    </div>
    <div class="col-md-3">
        <?php echo $view['form']->row($form['height']); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?php echo $view['form']->row($form['source']); ?>
    </div>
    <div class="col-md-6">
        <?php if ($item->getSource()): ?>
            <br>
            <br>
            <?php echo $view['translator']->trans('mautic.plugin.badge.generator.form.uploaded_source_pdf'); ?>:
            <a href="<?php echo $uploader->getFullUrl($item, 'source'); ?>" target="_blank">
                <?php echo $item->getSource(); ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $view['form']->row($form['stage']); ?>
    </div>
    <div class="col-md-3">
        <?php echo $view['form']->row($form['properties']['mapping']); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?php echo $view['form']->row($form['properties']['tags']); ?>
    </div>
    <div class="col-md-5">
        <?php echo $view['form']->row($form['properties']['restriction']); ?>
    </div>
</div>
<hr>

<div class="row">
    <?php for ($i = 1; $i <= $numberOfTextBlock; ++$i): ?>
        <div class="col-md-6">
            <h4><?php echo $view['translator']->trans('mautic.plugin.badge.generator.form.text').' '.$i; ?> </h4>
            <hr>
            <?php echo $view['form']->row($form['properties']['text'.$i]['fields']); ?>

            <div class="row">
                <?php foreach ($form['properties']['text'.$i] as $alias => $child): ?>
                    <div class="form-group col-xs-6">
                        <?php echo $view['form']->label($child); ?>
                        <?php echo $view['form']->widget($child);
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endfor; ?>
</div>


<div class="row">
    <?php for ($i = 1; $i <= $numberOfImagesBlock; ++$i): ?>
        <div class="col-md-6">
            <h4><?php echo $view['translator']->trans('mautic.plugin.badge.generator.form.image').' '.$i; ?> </h4>
            <hr>
            <div class="row">
                <?php foreach ($form['properties']['image'.$i] as $alias => $child): ?>
                <div class="form-group col-xs-6">
                    <?php echo $view['form']->label($child); ?>
                    <?php echo $view['form']->widget($child); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endfor; ?>
</div>


<?php if (!empty($form['properties']['barcode'])): ?>
    <div class="row">
        <div class="col-md-6">
            <h4><?php echo $view['translator']->trans('mautic.plugin.badge.generator.form.barcode.generator'); ?></h4>
            <hr>
            <div class="row">
                <?php foreach ($form['properties']['barcode'] as $alias => $child): ?>
                    <div class="form-group col-xs-6">
                        <?php echo $view['form']->label($child); ?>
                        <?php echo $view['form']->widget($child); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-6">
            <h4><?php echo $view['translator']->trans('mautic.plugin.badge.generator.form.qrcode.generator'); ?></h4>
            <hr>
            <div class="row">
                <?php foreach ($form['properties']['qrcode'] as $alias => $child): ?>
                    <div class="form-group col-xs-6">
                        <?php echo $view['form']->label($child); ?>
                        <?php echo $view['form']->widget($child); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="ide">
    <?php echo $view['form']->rest($form); ?>
</div>


<?php $view['slots']->stop(); ?>

