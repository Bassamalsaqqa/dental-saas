<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="flex min-h-[60vh] items-center justify-center">
    <div class="w-full max-w-md bg-white border border-slate-200 shadow-xl rounded-2xl p-8 text-center">
        <div class="mb-6">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-slate-400 text-2xl"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-900 mb-2">Restricted Access</h1>
            <p class="text-sm text-slate-500">Control Plane entry requires Global Mode.</p>
        </div>

        <form action="<?= base_url('controlplane/enter') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="w-full flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all">
                Enter Global Mode
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
