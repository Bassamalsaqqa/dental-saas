<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-10">
    <!-- Page Header -->
    <div class="flex items-center space-x-6">
        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-[1.5rem] flex items-center justify-center text-white shadow-2xl shadow-indigo-500/30">
            <i class="fas fa-plus-circle text-3xl"></i>
        </div>
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Onboard New Clinic</h1>
            <p class="text-slate-500 font-medium">Instantiate a new tenant context, administrator, and subscription.</p>
        </div>
    </div>

    <!-- Onboarding Form -->
    <div class="backdrop-blur-xl bg-white/80 border border-white/40 rounded-[2.5rem] shadow-2xl shadow-slate-200 overflow-hidden">
        <form action="<?= base_url('controlplane/onboarding/clinic/create') ?>" method="POST">
            <?= csrf_field() ?>
            
            <div class="p-10 space-y-12">
                <!-- Section 1: Clinic Context -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center font-black text-xs">01</span>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Tenant Identity</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-700 uppercase tracking-wider ml-1">Clinic Name</label>
                            <input type="text" name="clinic_name" value="<?= old('clinic_name') ?>" required
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 transition-all font-bold text-slate-800 placeholder-slate-300"
                                   placeholder="e.g. Springfield Dental Care">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Administrative Access -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center font-black text-xs">02</span>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Authority Level 1 (Clinic Admin)</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-700 uppercase tracking-wider ml-1">Admin Full Name</label>
                            <input type="text" name="admin_name" value="<?= old('admin_name') ?>" required
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 transition-all font-bold text-slate-800 placeholder-slate-300"
                                   placeholder="John Doe">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-700 uppercase tracking-wider ml-1">Work Email</label>
                            <input type="email" name="admin_email" value="<?= old('admin_email') ?>" required
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 transition-all font-bold text-slate-800 placeholder-slate-300"
                                   placeholder="admin@springfield-dental.com">
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-black text-slate-700 uppercase tracking-wider ml-1">Generated Secret (Password)</label>
                            <div class="relative">
                                <input type="password" name="admin_password" required minlength="8" id="adminPassword"
                                       class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 transition-all font-bold text-slate-800 placeholder-slate-300"
                                       placeholder="Minimum 8 characters">
                                <button type="button" onclick="togglePassword()" class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Subscription Engine -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center font-black text-xs">03</span>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Engine Selection</h3>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 uppercase tracking-wider ml-1">Subscription Plan</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($plans as $plan): ?>
                                <label class="relative group cursor-pointer">
                                    <input type="radio" name="plan_id" value="<?= $plan['id'] ?>" required class="sr-only peer" <?= old('plan_id') == $plan['id'] ? 'checked' : '' ?>>
                                    <div class="p-6 bg-white border-2 border-slate-100 rounded-3xl transition-all duration-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/50 hover:bg-slate-50">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-black text-slate-900"><?= esc($plan['name']) ?></span>
                                            <div class="w-5 h-5 rounded-full border-2 border-slate-200 flex items-center justify-center peer-checked:border-indigo-600 group-hover:border-indigo-400 transition-colors">
                                                <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full scale-0 peer-checked:scale-100 transition-transform"></div>
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Active Plan Engine</p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-10 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <a href="<?= base_url('controlplane/dashboard') ?>" class="text-sm font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Abort Onboarding</a>
                <button type="submit" class="group relative flex items-center space-x-3 px-10 py-4 bg-indigo-600 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-xs hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-500/40">
                    <span>Initialize Clinic</span>
                    <i class="fas fa-rocket text-[10px] group-hover:-translate-y-1 transition-transform"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById('adminPassword');
    const icon = document.getElementById('passwordIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
<?= $this->endSection() ?>