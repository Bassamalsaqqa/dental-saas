<?php helper('asset');$ct=static fn($key)=>esc(lang('Studio.cr'.$key)); ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-contract-review.css') ?>?v=<?= esc(asset_version('assets/css/studio-contract-review.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $return_to = $return_to ?? null; ?>
<?php
$snapshotRows = [];
if (!empty($snapshot['data_snapshot_json'])) {
    $decodedSnapshotRows = json_decode($snapshot['data_snapshot_json'], true);
    if (is_array($decodedSnapshotRows)) {
        $snapshotRows = array_values(array_filter($decodedSnapshotRows, static function ($row) {
            return !empty($row['condition_type']) && ($row['condition_type'] !== 'healthy');
        }));
    }
}
?>

<header class="page-title-row">
<h1>
<?= $ct('Title') ?> #<?= (int)$contract['id'] ?>
</h1>
<div class="contract-top-actions">
<a class="button secondary" href="<?= esc(workflow_back('patient/'.$patient['id'],$return_to??null)) ?>">
<?= $ct('Back') ?>
</a>
<a class="button secondary" href="<?= esc(workflow_context_url('patient/'.$patient['id'],$return_to??null)) ?>">
<?= $ct('Patient') ?>
</a>
</div>
</header>
<?php foreach(['success','error'] as $kind):if(session($kind)): ?>
<p class="form-notice <?= $kind==='error'?'form-notice--error':'' ?>" role="status">
<?= esc(session($kind)) ?>
</p>
<?php endif;endforeach; ?>
<div class="contract-context">
<a href="<?= esc(workflow_context_url('patient/'.$patient['id'],$return_to??null)) ?>">
<strong>
<?= esc($patient['first_name'].' '.$patient['last_name']) ?>
</strong>
</a>
<span>
<?= esc(($contract['status']??'draft')==='signed'?lang('Studio.crSigned'):(($contract['status']??'draft')==='draft'?lang('Studio.crDraft'):$contract['status'])) ?>
</span>
<span>
<?= esc($contract['currency']??'USD') ?> <?= number_format((float)($contract['total_cost']??0),2) ?>
</span>
</div>
<div class="contract-review-layout">
<div class="contract-review-main">
<section class="form-section">
<header>
<h2>
<?= $ct('Chart') ?>
</h2>
</header>
<div class="contract-native-snapshot">
<?php if(!empty($snapshot['svg_snapshot'])): ?>
<?= $snapshot['svg_snapshot'] ?>
<?php elseif(!empty($snapshotRows)): ?>
<p>
<?= $ct('RecordedConditions') ?>
</p>
<table class="studio-grid" data-grid-widths="16,30,54" aria-label="<?= $ct('RecordedConditions') ?>">
<thead>
<tr>
<?php foreach(['Tooth','Condition','Notes'] as $key): ?>
<th scope="col">
<?= $ct($key) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($snapshotRows as $row): ?>
<tr>
<td>
<?= esc((string)($row['tooth_number']??'—')) ?>
</td>
<td>
<?= esc(ucwords(str_replace('_',' ',(string)($row['condition_type']??'')))) ?>
</td>
<td>
<?= esc((string)(($row['condition_description']??'')?:($row['treatment_notes']??'—'))) ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>
<?= $ct('NoSnapshot') ?>
</p>
<?php endif; ?>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= $ct('Items') ?>
</h2>
</header>
<table class="studio-grid" data-grid-widths="17,10,48,10,15" aria-label="<?= $ct('Items') ?>">
<thead>
<tr>
<?php foreach(['Code','Tooth','Description','Quantity','Price'] as $key): ?>
<th scope="col">
<?= $ct($key) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php if(!empty($items)):foreach($items as $item): ?>
<tr>
<td>
<?= esc($item['procedure_code']??'-') ?>
</td>
<td>
<?= esc($item['tooth_no']??'-') ?>
</td>
<td>
<?= esc($item['description']??'-') ?>
</td>
<td>
<?= esc($item['qty']??1) ?>
</td>
<td>
<?= number_format((float)($item['price']??0),2) ?>
</td>
</tr>
<?php endforeach;else: ?>
<tr>
<td colspan="5">
<?= $ct('NoItems') ?>
</td>
</tr>
<?php endif; ?>
</tbody>
</table>
<?php if(!empty($contract['cost_details'])): ?>
<div class="contract-terms">
<h3>
<?= $ct('CostDetails') ?>
</h3>
<p>
<?= nl2br(esc($contract['cost_details'])) ?>
</p>
</div>
<?php endif; ?>
<div class="contract-total">
<strong>
<?= $ct('Total') ?>
</strong>
<span>
<?= esc($contract['currency']??'USD') ?> <?= number_format((float)($contract['total_cost']??0),2) ?>
</span>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= $ct('Terms') ?>
</h2>
</header>
<?php foreach(['terms_conditions'=>'TermsConditions','payment_terms'=>'PaymentTerms','duration_plan'=>'Duration','notes'=>'Notes'] as $field=>$key):if(!empty($contract[$field])): ?>
<div class="contract-terms">
<h3>
<?= $ct($key) ?>
</h3>
<p>
<?= nl2br(esc($contract[$field])) ?>
</p>
</div>
<?php endif;endforeach; ?>
<?php $creatorName=trim(($contract['creator_first_name']??'').' '.($contract['creator_last_name']??'')); ?>
<dl class="contract-origin">
<dt>
<?= $ct('CreatedBy') ?>
</dt>
<dd>
<?= $creatorName!==''?'Dr. '.esc($creatorName):$ct('Unknown') ?>
</dd>
</dl>
<?php if(empty($contract['terms_conditions'])&&empty($contract['payment_terms'])&&empty($contract['duration_plan'])&&empty($contract['notes'])): ?>
<p>
<?= $ct('NoTerms') ?>
</p>
<?php endif; ?>
</section>
</div>
<aside class="contract-review-side">
<section class="form-section contract-actions">
<h2>
<?= $ct('Actions') ?>
</h2>
<?php if(($contract['status']??'draft')==='draft'): ?>
<a class="button primary" href="<?= esc(workflow_context_url('contracts/'.$contract['id'].'/edit',$return_to??null)) ?>">
<?= $ct('EditDraft') ?>
</a>
<?php endif; ?>
<a class="button secondary" href="<?= base_url('contracts/'.$contract['id'].'/print') ?>">
<?= $ct('Print') ?>
</a>
<?php if(!empty($contract['signed_attachment_id'])): ?>
<a class="button secondary" href="<?= base_url('file/download/'.$contract['signed_attachment_id']) ?>">
<?= $ct('Download') ?>
</a>
<?php endif; ?>
</section>
<section class="form-section" id="signatureSection">
<h2>
<?= $ct('Signature') ?>
</h2>
<?php if(!empty($signature)): ?>
<dl class="contract-origin">
<dt>
<?= $ct('SignedBy') ?>
</dt>
<dd>
<?= esc($signature['signer_name']??'Patient') ?>
</dd>
<dt>
<?= $ct('SignedAt') ?>
</dt>
<dd>
<?= esc($signature['signed_at']??'') ?>
</dd>
</dl>
<?php if(!empty($signature['signature_data'])): ?>
<img class="contract-signed-evidence" src="<?= esc($signature['signature_data']) ?>" alt="<?= $ct('Signature') ?>">
<?php endif; ?>
<?php elseif(($contract['status']??'draft')==='draft'&&!empty($signature_enabled)):$signAction=base_url('contracts/'.$contract['id'].'/sign');if(!empty($return_to))$signAction.='?return='.urlencode($return_to);$defaultSignerName=trim(($patient['first_name']??'').' '.($patient['last_name']??'')); ?>
<form class="studio-form" action="<?= $signAction ?>" method="post" id="signatureForm">
<?= csrf_field() ?>
<input type="hidden" name="signature_data" id="signatureData">
<?php if(!empty($return_to)): ?>
<input type="hidden" name="return" value="<?= esc($return_to) ?>">
<?php endif; ?>
<div class="form-field">
<label for="contract-signer">
<?= $ct('TypedName') ?>
</label>
<input id="contract-signer" name="signer_name" type="text" required value="<?= esc($defaultSignerName,'attr') ?>">
</div>
<div class="form-field">
<label for="signatureCanvas">
<?= $ct('Draw') ?>
</label>
<canvas id="signatureCanvas" height="150" style="touch-action:none;-ms-touch-action:none">
</canvas>
</div>
<div class="contract-signing-actions">
<button class="button secondary" type="button" id="clearSignatureBtn">
<?= $ct('Clear') ?>
</button>
<button class="button primary" type="submit">
<?= $ct('Sign') ?>
</button>
</div>
</form>
<?php else: ?>
<p class="form-notice">
<?= $ct('SignatureUnavailable') ?>
</p>
<?php endif; ?>
</section>
</aside>
</div>
<script>
    const canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let drawing = false;
        canvas.style.touchAction = 'none';

        const getScale = () => {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return { rect, scaleX, scaleY };
        };

        const startDraw = (e) => {
            if (e.cancelable) e.preventDefault();
            drawing = true;
            ctx.beginPath();
            ctx.moveTo(getX(e), getY(e));
        };
        const endDraw = (e) => {
            if (e && e.cancelable) e.preventDefault();
            drawing = false;
        };
        const draw = (e) => {
            if (e.cancelable) e.preventDefault();
            if (!drawing) return;
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#111827';
            ctx.lineTo(getX(e), getY(e));
            ctx.stroke();
        };
        const getX = (e) => {
            const point = e.touches ? e.touches[0] : e;
            const { rect, scaleX } = getScale();
            return (point.clientX - rect.left) * scaleX;
        };
        const getY = (e) => {
            const point = e.touches ? e.touches[0] : e;
            const { rect, scaleY } = getScale();
            return (point.clientY - rect.top) * scaleY;
        };

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mouseup', endDraw);
        canvas.addEventListener('mouseleave', endDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchend', endDraw, { passive: false });
        canvas.addEventListener('touchcancel', endDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });

        document.getElementById('clearSignatureBtn')?.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        document.getElementById('signatureForm')?.addEventListener('submit', () => {
            const dataUrl = canvas.toDataURL('image/png');
            document.getElementById('signatureData').value = dataUrl;
        });
    }
</script>
<?= $this->endSection() ?>