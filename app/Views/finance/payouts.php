<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php';$moneyMode='payouts';$stats=$stats??[]; ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?><link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-money.css') ?>?v=<?= esc(asset_version('assets/css/studio-money.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="money-workspace"><header class="money-heading"><div><h1><?= esc(lang('Studio.payoutPageTitle')) ?></h1><p><?= esc(lang('Studio.payoutReviewHelp')) ?></p></div><a class="button secondary" href="<?= base_url('finance') ?>"><?= esc(lang('Studio.moneyWorkspace')) ?></a></header>
<?= view('components/studio_workflow_navigation',['workflowArea'=>'partners']) ?>
<section class="money-partner-summary"><dl><?php foreach(['total_provider_share'=>'Finance.providerShareTotal','total_provider_paid'=>'Studio.paidInHistory','total_provider_balance'=>'Studio.amountOwedNow','total_clinic_share'=>'Finance.clinicShareTotal'] as $key=>$label): ?><div><dt><?= esc(lang($label)) ?></dt><dd><?= esc(\App\Services\ProviderCurrencySummary::format($stats,$key)) ?></dd></div><?php endforeach; ?></dl><details class="money-accounting-note"><summary><?= esc(lang('Studio.financialContext')) ?></summary><p><?= esc(lang('Finance.providerCurrencyWarning')) ?></p><p><?= esc(lang('Finance.providerVoidBalanceHelp')) ?></p><p><?= esc(lang('Finance.providerPayoutsGuidance')) ?></p></details></section>
<?= view('components/studio_money_table',['moneyMode'=>$moneyMode]) ?>
<?= view('components/studio_money_drawer',['moneyMode'=>$moneyMode]) ?><?= view('components/studio_money_config',['moneyMode'=>$moneyMode]) ?></div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><script src="<?= base_url('assets/js/studio-money.js') ?>?v=<?= esc(asset_version('assets/js/studio-money.js')) ?>" defer></script><?= $this->endSection() ?>
