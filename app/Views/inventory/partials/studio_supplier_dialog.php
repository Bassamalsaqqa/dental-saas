<?php include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?php if(has_permission('inventory','edit')): ?>
<dialog id="item-supplier-dialog" class="help-dialog" aria-labelledby="item-supplier-title"><header><h2 id="item-supplier-title"><?= esc(lang('Inventory.quickAddSupplierTitle')) ?></h2><button class="icon-button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>"><?= $studioIcon('close') ?></button></header>
<form id="item-supplier-form" class="item-supplier-form" action="<?= base_url('inventory/suppliers/quick-create') ?>" method="post" data-failed="<?= esc(lang('Inventory.failedToCreateSupplier'),'attr') ?>" data-success="<?= esc(lang('Inventory.supplierCreatedSelected'),'attr') ?>">
<?= csrf_field() ?>
<?php foreach(['name'=>'supplierNameRequired','contact_person'=>'contactPerson','phone'=>'phone','email'=>'email'] as $name=>$label): ?><div class="form-field"><label for="quick-supplier-<?= $name ?>"><?= esc(lang('Inventory.'.$label)) ?></label><input id="quick-supplier-<?= $name ?>" name="<?= $name ?>" type="<?= $name==='email'?'email':($name==='phone'?'tel':'text') ?>" <?= $name==='name'?'required':'' ?>></div><?php endforeach; ?>
<p id="item-supplier-feedback" role="status" hidden></p><div class="item-dialog-actions"><button class="button secondary" type="button" data-item-supplier-cancel><?= esc(lang('UI.cancel')) ?></button><button class="button primary" type="submit"><?= esc(lang('Inventory.createSupplier')) ?></button></div></form></dialog>
<?php endif; ?>
