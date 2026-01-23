<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Control Plane') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <?php if (!isset($disableDataTables) || !$disableDataTables): ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <?php endif; ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <?php if (!isset($disableDataTables) || !$disableDataTables): ?>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <?php endif; ?>
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <style>
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 30px 80px -50px rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(24px);
        }
        .glass-muted {
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 50px -40px rgba(15, 23, 42, 0.35);
            backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-indigo-50 to-emerald-50 min-h-screen">
    <?= view('components/toast') ?>

    <div class="flex min-h-screen">
        <?php $hideCpNav = $hide_cp_nav ?? false; ?>
        <?php if (!$hideCpNav): ?>
            <aside class="w-64 backdrop-blur-xl bg-white/80 border-r border-white/30 shadow-2xl shadow-indigo-500/10 z-40 transition-all duration-300 transform flex-shrink-0 lg:translate-x-0 -translate-x-full flex flex-col" id="sidebar">
                <div class="px-2 pb-2 pt-3 border-b border-white/30" id="sidebar-header">
                    <div class="flex items-center justify-between">
                        <a href="<?= base_url('controlplane/dashboard') ?>" class="flex items-center space-x-3 group cursor-pointer hover:bg-white/30 rounded-xl p-2 -m-2 transition-all duration-200">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-indigo-700 to-emerald-600 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <div class="relative w-10 h-10 bg-gradient-to-br from-slate-900 via-indigo-700 to-emerald-600 rounded-xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-shield-alt text-white text-lg"></i>
                                </div>
                            </div>
                            <div id="sidebar-title" class="min-w-0 flex-shrink-0">
                                <h1 class="text-lg font-black text-gray-900 whitespace-nowrap">Control Plane</h1>
                                <div class="flex items-center space-x-1">
                                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                                    <p class="text-xs text-gray-500 font-medium whitespace-nowrap">Global Admin</p>
                                </div>
                            </div>
                        </a>
                        <button class="group relative p-2 text-gray-600 hover:text-gray-900 hover:bg-white/50 rounded-xl transition-all duration-200 hover:scale-105 lg:block hidden" onclick="toggleSidebar()" title="Toggle Sidebar (Ctrl+B)">
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-emerald-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-bars relative z-10"></i>
                        </button>
                    </div>
                </div>

                <nav class="flex-1" id="sidebar-nav">
                    <div class="p-4">
                        <?php
                        $menuGroups = [
                            [
                                'label' => 'Platform',
                                'items' => [
                                    ['title' => 'Dashboard', 'url' => 'controlplane/dashboard', 'icon' => 'fas fa-tachometer-alt', 'color' => 'slate'],
                                ],
                            ],
                            [
                                'label' => 'Tenants',
                                'items' => [
                                    ['title' => 'Clinics', 'url' => 'controlplane/clinics', 'icon' => 'fas fa-hospital-user', 'color' => 'emerald'],
                                    ['title' => 'Onboarding', 'url' => 'controlplane/onboarding/clinic/create', 'icon' => 'fas fa-user-plus', 'color' => 'emerald'],
                                ],
                            ],
                            [
                                'label' => 'Plans & Billing',
                                'items' => [
                                    ['title' => 'Plans', 'url' => 'controlplane/plans', 'icon' => 'fas fa-layer-group', 'color' => 'indigo'],
                                    ['title' => 'Plan Requests', 'url' => 'controlplane/plan-requests', 'icon' => 'fas fa-inbox', 'color' => 'indigo'],
                                ],
                            ],
                            [
                                'label' => 'Templates',
                                'items' => [
                                    ['title' => 'Email Templates', 'url' => 'controlplane/settings#template-editor', 'icon' => 'fas fa-envelope-open-text', 'color' => 'teal'],
                                ],
                            ],
                            [
                                'label' => 'Governance',
                                'items' => [
                                    ['title' => 'Operations', 'url' => 'controlplane/operations', 'icon' => 'fas fa-cogs', 'color' => 'amber'],
                                ],
                            ],
                            [
                                'label' => 'System',
                                'items' => [
                                    ['title' => 'Settings', 'url' => 'controlplane/settings', 'icon' => 'fas fa-sliders-h', 'color' => 'teal'],
                                ],
                            ],
                        ];

                        $colorClasses = [
                            'slate' => ['from-slate-800 to-slate-900', 'shadow-slate-900/25', 'from-slate-50 to-slate-100', 'text-slate-800', 'shadow-slate-900/10', 'text-slate-600', 'text-slate-700'],
                            'emerald' => ['from-emerald-500 to-emerald-600', 'shadow-emerald-500/25', 'from-emerald-50 to-green-50', 'text-emerald-800', 'shadow-emerald-500/10', 'text-emerald-600', 'text-emerald-700'],
                            'indigo' => ['from-indigo-500 to-indigo-600', 'shadow-indigo-500/25', 'from-indigo-50 to-blue-50', 'text-indigo-800', 'shadow-indigo-500/10', 'text-indigo-600', 'text-indigo-700'],
                            'teal' => ['from-teal-500 to-teal-600', 'shadow-teal-500/25', 'from-teal-50 to-cyan-50', 'text-teal-800', 'shadow-teal-500/10', 'text-teal-600', 'text-teal-700'],
                            'amber' => ['from-amber-500 to-amber-600', 'shadow-amber-500/25', 'from-amber-50 to-yellow-50', 'text-amber-800', 'shadow-amber-500/10', 'text-amber-600', 'text-amber-700'],
                        ];
                        ?>
                        <?php foreach ($menuGroups as $group): ?>
                            <?php if (!empty($group['label'])): ?>
                                <div class="flex items-center space-x-2 mt-6 mb-4">
                                    <div class="w-2 h-2 bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-full animate-pulse"></div>
                                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider"><?= esc($group['label']) ?></h3>
                                </div>
                            <?php endif; ?>
                            <ul class="space-y-2">
                                <?php foreach ($group['items'] as $item): ?>
                                    <?php
                                    $navUrl = $item['url'];
                                    $navPath = explode('#', $navUrl)[0];
                                    $isActive = (strpos(uri_string(), $navPath) === 0);
                                    $colors = $colorClasses[$item['color']];
                                    ?>
                                    <li>
                                        <a href="<?= base_url($item['url']) ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $isActive ? 'bg-gradient-to-r ' . $colors[0] . ' text-white shadow-lg ' . $colors[1] . ' mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:' . $colors[2] . ' hover:' . $colors[3] . ' hover:shadow-md hover:' . $colors[4] . ' mx-1' ?>">
                                            <div class="absolute inset-0 bg-gradient-to-r <?= $colors[5] . ' ' . $colors[6] ?> rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                            <i class="<?= $item['icon'] ?> w-5 h-5 mr-3 relative z-10 <?= $isActive ? 'text-white' : $colors[5] . ' group-hover:' . $colors[6] ?>"></i>
                                            <span class="relative z-10"><?= esc($item['title']) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endforeach; ?>
                    </div>
                </nav>

                <div class="p-4 border-t border-white/30 backdrop-blur-xl bg-white/80 text-xs text-gray-500">
                    Control Plane v2
                </div>
            </aside>
        <?php endif; ?>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="sticky top-0 z-30 backdrop-blur-xl bg-white/70 border-b border-white/30 px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Control Plane</p>
                    <h1 class="text-xl font-bold text-gray-900"><?= esc($title ?? 'Dashboard') ?></h1>
                </div>
                <?php $userEmail = (isset($user) && $user && property_exists($user, 'email')) ? $user->email : 'Super Admin'; ?>
                <div class="flex items-center gap-3">
                    <button type="button" id="cp-command-trigger" class="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl bg-white/80 border border-white/60 text-slate-600 text-xs font-semibold shadow-sm hover:bg-white transition">
                        <i class="fas fa-magnifying-glass text-indigo-500"></i>
                        Super Search
                        <span class="ml-1 rounded-lg border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-500">Cmd+K</span>
                    </button>
                    <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full">Global Mode</span>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span><?= esc($userEmail) ?></span>
                    </div>
                    <form action="<?= base_url('controlplane/exit') ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                            Exit Control Plane
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-6 py-8 relative">
                <div class="pointer-events-none absolute inset-0 overflow-hidden">
                    <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-indigo-400/20 to-emerald-600/20 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-slate-400/15 to-emerald-500/20 rounded-full blur-3xl"></div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="relative mb-6 glass-card rounded-2xl px-4 py-3 text-emerald-800">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="relative mb-6 glass-card rounded-2xl px-4 py-3 text-rose-800">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <div class="relative">
                    <?= $this->renderSection('content') ?>
                </div>
            </main>
        </div>
    </div>

    <div id="cp-command-palette" class="fixed inset-0 z-50 hidden items-start justify-center bg-slate-900/40 px-4 py-10">
        <div class="w-full max-w-2xl rounded-3xl border border-white/30 bg-white/90 shadow-2xl backdrop-blur-xl">
            <div class="flex items-center gap-3 border-b border-slate-200 px-6 py-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-700 to-emerald-600 text-white shadow-lg">
                    <i class="fas fa-terminal"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400 font-semibold">Global Command Palette</p>
                    <p class="text-sm font-semibold text-slate-800">Search clinics, users, and navigation</p>
                </div>
                <button type="button" id="cp-command-close" class="rounded-full p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-4">
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <i class="fas fa-search text-indigo-500"></i>
                    <input id="cp-command-input" type="text" placeholder="Search clinics, users, or actions..."
                           class="w-full bg-transparent text-sm font-semibold text-slate-700 placeholder:text-slate-400 focus:outline-none">
                    <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-semibold text-slate-500">Esc</span>
                </div>
            </div>
            <div class="px-4 pb-6">
                <div id="cp-command-status" class="px-2 pb-2 text-xs text-slate-500"></div>
                <div id="cp-command-results" class="space-y-3"></div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            sidebar.classList.toggle('-translate-x-full');
        }

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                toggleSidebar();
            }
        });

        const cpPalette = document.getElementById('cp-command-palette');
        const cpTrigger = document.getElementById('cp-command-trigger');
        const cpClose = document.getElementById('cp-command-close');
        const cpInput = document.getElementById('cp-command-input');
        const cpResults = document.getElementById('cp-command-results');
        const cpStatus = document.getElementById('cp-command-status');
        let cpActiveIndex = -1;
        let cpItems = [];
        let cpTimer = null;

        function setCommandStatus(message) {
            if (!cpStatus) {
                return;
            }
            cpStatus.textContent = message || '';
        }

        function resetCommandItems() {
            cpItems = [];
            cpActiveIndex = -1;
            if (cpResults) {
                cpResults.textContent = '';
            }
        }

        function openCommandPalette() {
            if (!cpPalette) {
                return;
            }
            cpPalette.classList.remove('hidden');
            cpPalette.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setCommandStatus('Type at least 2 characters to search.');
            resetCommandItems();
            if (cpInput) {
                cpInput.value = '';
                cpInput.focus();
            }
        }

        function closeCommandPalette() {
            if (!cpPalette) {
                return;
            }
            cpPalette.classList.add('hidden');
            cpPalette.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function updateActiveItem() {
            cpItems.forEach((item, index) => {
                if (index === cpActiveIndex) {
                    item.classList.add('bg-indigo-50');
                } else {
                    item.classList.remove('bg-indigo-50');
                }
            });
        }

        function createSection(title) {
            const section = document.createElement('div');
            const heading = document.createElement('div');
            heading.className = 'px-3 text-xs font-semibold uppercase tracking-widest text-slate-400';
            heading.textContent = title;
            section.appendChild(heading);
            return { section, list: document.createElement('div') };
        }

        function createResultButton(label, meta, onClick) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm transition hover:border-indigo-100 hover:bg-indigo-50';
            const title = document.createElement('div');
            title.className = 'text-sm font-semibold text-slate-800';
            title.textContent = label;
            const subtitle = document.createElement('div');
            subtitle.className = 'text-xs text-slate-500';
            subtitle.textContent = meta;
            button.appendChild(title);
            button.appendChild(subtitle);
            button.addEventListener('click', onClick);
            return button;
        }

        function renderResults(payload) {
            resetCommandItems();
            if (!payload || !payload.results) {
                setCommandStatus('No results found.');
                return;
            }

            const sections = [];
            const clinics = payload.results.clinics || [];
            const users = payload.results.users || [];
            const links = payload.results.links || [];

            if (clinics.length > 0) {
                const { section, list } = createSection('Clinics');
                clinics.forEach((clinic) => {
                    const label = clinic.name || 'Clinic';
                    let detail = clinic.domain || '';
                    if (!detail && clinic.uuid) {
                        detail = `UUID ${clinic.uuid}`;
                    }
                    if (!detail) {
                        detail = clinic.slug || 'no-slug';
                    }
                    const meta = `ID ${clinic.id} · ${detail}`;
                    const button = createResultButton(label, meta, () => {
                        window.location.href = `<?= base_url('controlplane/clinics') ?>/${clinic.id}`;
                    });
                    list.appendChild(button);
                    cpItems.push(button);
                });
                list.className = 'mt-2 space-y-2';
                section.appendChild(list);
                sections.push(section);
            }

            if (users.length > 0) {
                const { section, list } = createSection('Users');
                users.forEach((user) => {
                    const label = user.email || 'User';
                    const meta = user.name ? user.name : 'Control Plane user';
                    const button = createResultButton(label, meta, () => {
                        window.location.href = `<?= base_url('controlplane/settings') ?>?user=${user.id}`;
                    });
                    list.appendChild(button);
                    cpItems.push(button);
                });
                list.className = 'mt-2 space-y-2';
                section.appendChild(list);
                sections.push(section);
            }

            if (links.length > 0) {
                const { section, list } = createSection('Navigation');
                links.forEach((link) => {
                    const label = link.title || 'Link';
                    const meta = link.url || '';
                    const button = createResultButton(label, meta, () => {
                        window.location.href = `<?= base_url() ?>${link.url}`;
                    });
                    list.appendChild(button);
                    cpItems.push(button);
                });
                list.className = 'mt-2 space-y-2';
                section.appendChild(list);
                sections.push(section);
            }

            if (sections.length === 0) {
                setCommandStatus('No results found.');
                return;
            }

            sections.forEach((section) => cpResults.appendChild(section));
            cpActiveIndex = 0;
            updateActiveItem();
            setCommandStatus(`${cpItems.length} results`);
        }

        function fetchCommandResults(query) {
            if (!query || query.length < 2) {
                resetCommandItems();
                setCommandStatus('Type at least 2 characters to search.');
                return;
            }

            setCommandStatus('Searching...');
            fetch(`<?= base_url('controlplane/search/global') ?>?q=${encodeURIComponent(query)}`)
                .then((response) => response.json())
                .then((data) => {
                    renderResults(data);
                })
                .catch(() => {
                    resetCommandItems();
                    setCommandStatus('Search failed. Try again.');
                });
        }

        function handleCommandInput() {
            const query = cpInput ? cpInput.value.trim() : '';
            clearTimeout(cpTimer);
            cpTimer = setTimeout(() => fetchCommandResults(query), 200);
        }

        if (cpTrigger) {
            cpTrigger.addEventListener('click', openCommandPalette);
        }

        if (cpClose) {
            cpClose.addEventListener('click', closeCommandPalette);
        }

        if (cpPalette) {
            cpPalette.addEventListener('click', (event) => {
                if (event.target === cpPalette) {
                    closeCommandPalette();
                }
            });
        }

        if (cpInput) {
            cpInput.addEventListener('input', handleCommandInput);
            cpInput.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    if (cpItems.length === 0) {
                        return;
                    }
                    cpActiveIndex = (cpActiveIndex + 1) % cpItems.length;
                    updateActiveItem();
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    if (cpItems.length === 0) {
                        return;
                    }
                    cpActiveIndex = (cpActiveIndex - 1 + cpItems.length) % cpItems.length;
                    updateActiveItem();
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    if (cpItems[cpActiveIndex]) {
                        cpItems[cpActiveIndex].click();
                    }
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    closeCommandPalette();
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            const isMac = navigator.platform.toUpperCase().includes('MAC');
            const cmdKey = isMac ? event.metaKey : event.ctrlKey;
            if (cmdKey && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                openCommandPalette();
            }
            if (event.key === 'Escape' && cpPalette && !cpPalette.classList.contains('hidden')) {
                closeCommandPalette();
            }
        });
    </script>

    <?php if (isset($loadSelect2) && $loadSelect2): ?>
    <script src="<?= base_url('assets/js/select2-init.js') ?>?v=<?= time() ?>"></script>
    <?php endif; ?>
</body>
</html>
