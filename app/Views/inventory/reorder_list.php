<?php helper('asset'); include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-operations.css') ?>?v=<?= esc(asset_version('assets/css/studio-operations.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$tr=static fn(string $key,array $params=[])=>lang('Inventory.'.$key,$params);
$reorderItems=is_array($reorder_items??null)?$reorder_items:[];
$draftCount=0;$orderedCount=0;$supplierNames=[];
foreach($reorderItems as $item){
    if(($item['status']??'')==='draft')$draftCount++;
    if(($item['status']??'')==='ordered')$orderedCount++;
    $name=trim((string)($item['supplier_name']??''));if($name!=='')$supplierNames[$name]=true;
}
$canChange=has_permission('inventory','edit');
$statusLabels=[];foreach(['draft','ordered','received','cancelled'] as $status)$statusLabels[$status]=lang('Studio.reorder_'. $status);
$methodLabels=['phone'=>$tr('phone'),'email'=>$tr('emailShort'),'whatsapp'=>$tr('whatsApp'),'other'=>$tr('categoryOther')];
?>
<div class="reorder-workspace">
<header class="page-title-row registration-heading"><h1><?= esc($tr('reorderList')) ?></h1><div class="reorder-navigation"><a class="button secondary" href="<?= base_url('inventory/movements') ?>"><?= esc($tr('movements')) ?></a><a class="button primary" href="<?= base_url('inventory/low-stock') ?>"><?= esc($tr('lowStockItems')) ?></a><a class="button secondary" href="<?= base_url('inventory') ?>"><?= esc($tr('backToInventory')) ?></a></div></header>
<?php if(session('error')): ?><div class="form-notice form-notice--error" role="alert"><?= esc(session('error')) ?></div><?php endif; ?>
<section class="reorder-summary" aria-label="<?= esc($tr('reorderLines'),'attr') ?>"><div><strong><?= $draftCount ?></strong><span><?= esc($tr('draftLines')) ?></span></div><div><strong><?= $orderedCount ?></strong><span><?= esc($tr('orderedLines')) ?></span></div><div><strong><?= count($supplierNames) ?></strong><span><?= esc($tr('suppliersInvolved')) ?></span></div></section>
<section class="reorder-list" aria-labelledby="reorder-title">
    <header class="reorder-list-heading"><h2 id="reorder-title"><?= esc($tr('reorderLines')) ?></h2><div class="reorder-filters" aria-label="<?= esc($tr('status'),'attr') ?>"><button class="button secondary" data-reorder-filter="all" aria-pressed="true"><?= esc(lang('Studio.all')) ?></button><button class="button secondary" data-reorder-filter="draft" aria-pressed="false"><?= esc($statusLabels['draft']) ?></button><button class="button secondary" data-reorder-filter="ordered" aria-pressed="false"><?= esc($statusLabels['ordered']) ?></button></div></header>
    <?php if($reorderItems): ?>
    <div class="reorder-rows">
    <?php foreach($reorderItems as $row): $id=(int)$row['id'];$inventoryId=(int)($row['inventory_id']??0);$status=(string)($row['status']??'draft'); ?>
        <article class="reorder-row" data-reorder-status="<?= esc($status,'attr') ?>">
            <div class="reorder-item">
                <span class="reorder-symbol"><?= $studioIcon('box') ?></span>
                <div><h3><?php if($inventoryId>0): ?><a class="reorder-item-link" href="<?= base_url('inventory/'.$inventoryId) ?>"><?= esc($row['item_name']??('Item #'.$inventoryId)) ?></a><?php else: ?><?= esc($row['item_name']??$tr('item')) ?><?php endif; ?></h3><div class="reorder-item-context"><?php if(!empty($row['category'])): ?><span><?= esc($row['category']) ?></span><?php endif; ?><span><?= esc($tr('current')) ?> <b><?= (int)($row['current_qty']??0) ?> <?= esc($row['unit']??'') ?></b></span><span><?= esc($tr('minimum')) ?> <b><?= (int)($row['min_quantity']??0) ?></b></span></div></div>
            </div>
            <div class="reorder-supplier"><span class="reorder-label"><?= esc($tr('supplier')) ?></span><strong><?= esc(trim((string)($row['supplier_name']??''))?:$tr('noLinkedSupplier')) ?></strong><?php if(!empty($row['preferred_supplier_sku'])): ?><span><?= esc($row['preferred_supplier_sku']) ?></span><?php endif; ?><?php if(!empty($row['preferred_pack_size_label'])): ?><span><?= esc($row['preferred_pack_size_label']) ?></span><?php endif; ?></div>
            <?php if($canChange): ?>
            <form class="reorder-update" method="post" action="<?= base_url('inventory/reorder-list/'.$id.'/status') ?>">
                <?= csrf_field() ?>
                <div class="form-field"><label for="quantity-<?= $id ?>"><?= esc($tr('requested')) ?></label><input id="quantity-<?= $id ?>" type="number" name="requested_qty" min="1" value="<?= (int)($row['requested_qty']??1) ?>"></div>
                <div class="form-field"><label for="status-<?= $id ?>"><?= esc($tr('status')) ?></label><select id="status-<?= $id ?>" name="status" data-force-select2 data-allow-clear="false"><?php foreach($statusLabels as $value=>$label): ?><option value="<?= esc($value,'attr') ?>" <?= $status===$value?'selected':'' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
                <button type="submit" class="button primary"><?= esc($tr('apply')) ?></button>
            </form>
            <?php else: ?><div class="reorder-readonly"><span><?= esc($tr('requested')) ?> <b><?= (int)($row['requested_qty']??0) ?></b></span><span class="reorder-status"><?= esc($statusLabels[$status]??$status) ?></span></div><?php endif; ?>
            <details class="reorder-context"><summary><?= esc($tr('supplierContact')) ?></summary><div class="reorder-context-body">
                <div class="reorder-contact-information">
                    <?php if(!empty($row['notes'])): ?><p><?= esc($row['notes']) ?></p><?php endif; ?>
                    <div class="reorder-contact-links">
                    <?php if(!empty($row['supplier_phone'])): ?><a class="button secondary" href="tel:<?= esc($row['supplier_phone'],'attr') ?>"><?= esc($tr('call')) ?></a><?php endif; ?>
                    <?php if(!empty($row['supplier_email'])): ?><a class="button secondary" href="mailto:<?= esc($row['supplier_email'],'attr') ?>"><?= esc($tr('emailShort')) ?></a><?php endif; ?>
                    <?php if(!empty($row['supplier_whatsapp'])): ?><a class="button secondary" href="https://wa.me/<?= esc(preg_replace('/\D+/','',(string)$row['supplier_whatsapp']),'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($tr('whatsApp')) ?></a><?php endif; ?>
                    </div>
                    <p><?php if(!empty($row['supplier_contacted_at'])): ?><?= esc($tr('loggedVia',['method'=>$methodLabels[$row['supplier_contact_method']??'other']??$methodLabels['other']])) ?> · <?= esc(formatDateTime($row['supplier_contacted_at'])) ?><?php else: ?><?= esc($tr('noContactLoggedYet')) ?><?php endif; ?></p>
                </div>
                <?php if($canChange): ?><div class="reorder-contact-actions"><span class="reorder-label"><?= esc(lang('Studio.logSupplierContact')) ?></span><div class="reorder-contact-links"><?php foreach($methodLabels as $value=>$label): ?><form method="post" action="<?= base_url('inventory/reorder-list/'.$id.'/contacted') ?>"><?= csrf_field() ?><input type="hidden" name="method" value="<?= esc($value,'attr') ?>"><button type="submit" class="button secondary"><?= esc($label) ?></button></form><?php endforeach; ?></div><form class="reorder-remove" method="post" action="<?= base_url('inventory/reorder-list/'.$id.'/delete') ?>" data-reorder-remove data-confirm="<?= esc($tr('removeReorderLineConfirm'),'attr') ?>"><?= csrf_field() ?><button type="submit" class="button destructive"><?= esc($tr('removeLine')) ?></button></form></div><?php endif; ?>
            </div></details>
        </article>
    <?php endforeach; ?>
    </div><p class="reorder-filter-empty" hidden><?= esc(lang('UI.patientNoRecords')) ?></p>
    <?php else: ?><div class="reorder-empty"><?= $studioIcon('box') ?><h3><?= esc($tr('noReorderLinesYet')) ?></h3><p><?= esc($tr('addItemsFromLowStock')) ?></p><a class="button primary" href="<?= base_url('inventory/low-stock') ?>"><?= esc($tr('openLowStock')) ?></a></div><?php endif; ?>
</section>
</div>
<?php if($canChange): ?><dialog id="reorder-confirm-dialog" class="help-dialog" aria-labelledby="reorder-confirm-title"><header><h2 id="reorder-confirm-title"><?= esc($tr('removeLine')) ?></h2><button class="icon-button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>"><?= $studioIcon('close') ?></button></header><p class="reorder-confirm-message"></p><div class="discard-actions"><button class="button secondary" type="button" data-reorder-stay><?= esc(lang('UI.cancel')) ?></button><button class="button destructive" type="button" data-reorder-confirm><?= esc($tr('removeLine')) ?></button></div></dialog><?php endif; ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><script src="<?= base_url('assets/js/studio-reorder.js') ?>?v=<?= esc(asset_version('assets/js/studio-reorder.js')) ?>" defer></script><?= $this->endSection() ?>