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
/** @var \MauticPlugin\MauticRecommenderBundle\Entity\RecommenderTemplate $recommender */
$item = $entity;
?>
<style>
    .btn-save {display:none }
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
    <div class="col-md-3">
        <?php echo $view['form']->row($form['stage']); ?>
    </div>

    <div class="col-md-6">
        <?php echo $view['form']->row($form['source']); ?>
    </div>
    <div class="col-md-6">
        <br />
        <?php if ($item->getSource()): ?>
            <?php echo $view['translator']->trans('mautic.plugin.badge.generator.form.uploaded'); ?>:
            <br />
            <a href="<?php echo $uploader->getFullUrl($item, 'source'); ?>" target="_blank">
                <?php echo $item->getSource(); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
<hr>

<div class="row">
<?php for($i=1; $i <= $numberOfTextBlock; $i++): ?>
    <div class="col-md-6">
        <h4><?php echo $view['translator']->trans('mautic.plugin.badge.generator.form.text').' '.$i; ?> </h4>
        <hr>
        <?php echo $view['form']->row($form['properties']['text'.$i]); ?>
    </div>
    <?php endfor; ?>
</div>



<?php if(!empty($form['properties']['barcode'])): ?>
<div class="row">
    <div class="col-md-6">
        <h4><?php echo $view['translator']->trans('mautic.plugin.badge.generator.form.barcode.generator'); ?></h4>
        <hr>

        <?php echo $view['form']->widget($form['properties']['barcode']); ?>
    </div>
</div>
<?php endif; ?>

<div class="ide">
    <?php echo $view['form']->rest($form); ?>
</div>


<?php $view['slots']->stop(); ?>

