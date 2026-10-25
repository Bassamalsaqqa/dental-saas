<?php helper('asset'); $cpEmail=is_object($user??null)?(string)($user->email??''):(string)($user['email']??''); $hideCpNav=!empty($hide_cp_nav); ?>
<!DOCTYPE html>
<html lang="<?= esc($locale ?? 'en') ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? lang('UI.cpControlPlane')) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= esc(asset_version('assets/css/tailwind.css')) ?>">
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
    <link rel="stylesheet" href="<?= base_url('assets/css/v4-operations.css') ?>?v=<?= esc(asset_version('assets/css/v4-operations.css')) ?>">
    <meta name="theme-color" content="#162421">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/v4-system.css') ?>?v=<?= esc(asset_version('assets/css/v4-system.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/v4-icons.css') ?>?v=<?= esc(asset_version('assets/css/v4-icons.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/v4-control-plane.css') ?>?v=<?= esc(asset_version('assets/css/v4-control-plane.css')) ?>">
<?php include APPPATH.'Views/layouts/partials/csrf_runtime.php'; ?>
</head><body class="denta-v4 v4-platform">
<a href="#cp-workspace" class="v4-skip"><?= esc(lang('V4.skip')) ?></a><?= view('components/toast') ?>
<div class="v4-app">
<?php if(!$hideCpNav): ?>
<aside id="sidebar" class="v4-sidebar v4-platform-nav sidebar-mobile-hidden">
<a class="v4-brand" href="<?= base_url('controlplane/dashboard') ?>"><span class="v4-brand-word">Denta<span>+</span></span></a><p class="v4-platform-authority"><i class="fas fa-shield" aria-hidden="true"></i><?= esc(lang('UI.cpGlobalAdmin')) ?></p>
<?php $cpGroups=['UI.cpTenants'=>[['dashboard','UI.menuDashboard','fa-house'],['clinics','UI.cpClinics','fa-building'],['users','UI.cpUsers','fa-users'],['onboarding/clinic/create','UI.cpOnboarding','fa-user-plus']],'V4.platformBusiness'=>[['plans','UI.cpPlans','fa-layer-group'],['invoices','UI.cpInvoices','fa-file-invoice-dollar'],['plan-requests','UI.cpRequests','fa-inbox']],'V4.operations'=>[['operations','UI.cpOperations','fa-sliders'],['whatsapp','UI.cpWhatsAppOps','fa-whatsapp'],['activity-log','UI.cpSystemActivity','fa-clock'],['settings','UI.cpSettings','fa-cog']]]; ?>
<nav id="sidebar-nav"><?php foreach($cpGroups as $group=>$items): ?><section class="v4-nav-group"><h2><?= esc(lang($group)) ?></h2><?php foreach($items as [$path,$label,$icon]): ?><?php $active=str_starts_with(trim(uri_string(),'/'),'controlplane/'.$path); ?><a class="v4-nav-item <?= $active?'is-active':'' ?>" href="<?= base_url('controlplane/'.$path) ?>" aria-current="<?= $active?'page':'false' ?>"><i class="fas <?= $icon ?>" aria-hidden="true"></i><span><?= esc(lang($label)) ?></span></a><?php endforeach; ?></section><?php endforeach; ?><?php if(has_role('super_admin')): ?><a class="v4-nav-item" href="<?= base_url('controlplane/roles') ?>"><i class="fas fa-shield" aria-hidden="true"></i><span><?= esc(lang('V4.platformRoles')) ?></span></a><?php endif; ?></nav>
<footer><p><?= esc($cpEmail) ?></p><form method="post" action="<?= base_url('controlplane/exit') ?>"><?= csrf_field() ?><button class="v4-platform-exit" type="submit"><?= esc(lang('UI.cpExitControlPlane')) ?></button></form></footer>
</aside><?php endif; ?>
<div id="cp-sidebar-backdrop" class="v4-sidebar-scrim hidden" onclick="closeCpSidebar()"></div>
<div id="main-content" class="v4-app-main"><header class="v4-topbar"><div class="v4-topbar-location"><?php if(!$hideCpNav): ?><button type="button" class="v4-icon-button v4-cp-mobile-toggle" id="cp-nav-toggle" onclick="toggleSidebar()" aria-controls="sidebar" aria-expanded="false" aria-label="<?= esc(lang('UI.mainMenu')) ?>"><i class="fas fa-bars" aria-hidden="true"></i></button><?php endif; ?><span><?= esc(lang('UI.cpControlPlane')) ?></span></div><div class="v4-topbar-actions"><?php if(!$hideCpNav): ?><button type="button" id="cp-command-trigger" class="v4-button v4-cp-search" aria-label="<?= esc(lang('V4.platformSearch')) ?>"><i class="fas fa-search" aria-hidden="true"></i><span><?= esc(lang('V4.platformSearch')) ?></span><kbd>Ctrl K</kbd></button><?php endif; ?><nav class="v4-languages" aria-label="<?= esc(lang('UI.settingsLanguage')) ?>" dir="ltr"><?php foreach(['en'=>'EN','ar'=>'AR'] as $code=>$label): ?><a href="<?= base_url('language/switch/'.$code.'?return_to='.urlencode(current_url(true)->__toString())) ?>" lang="<?= $code ?>" aria-current="<?= ($locale??'en')===$code?'page':'false' ?>"><?= $label ?></a><?php endforeach; ?></nav><span class="v4-badge"><?= esc(lang('UI.cpGlobalAdmin')) ?></span></div></header>
<main id="cp-workspace" class="v4-workspace"><?= $this->renderSection('content') ?></main></div></div>
<div id="cp-command-palette" class="v4-platform-command hidden" role="dialog" aria-modal="true" aria-labelledby="cp-command-title"><section><header><div><h2 id="cp-command-title"><?= esc(lang('V4.platformSearch')) ?></h2><p><?= esc(lang('UI.cpSearchPlaceholder')) ?></p></div><button id="cp-command-close" class="v4-icon-button" aria-label="<?= esc(lang('UI.cpClose')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header><label class="v4-cp-command-search"><i class="fas fa-search" aria-hidden="true"></i><input id="cp-command-input" type="search" placeholder="<?= esc(lang('UI.cpSearchPlaceholder'),'attr') ?>" aria-label="<?= esc(lang('V4.platformSearch')) ?>"></label><div id="cp-command-status" role="status"></div><div id="cp-command-results"></div><footer><kbd>Enter</kbd> <?= esc(lang('UI.cpOpen')) ?> <kbd>Esc</kbd> <?= esc(lang('UI.cpClose')) ?></footer></section></div>
    <script>
        function toggleSidebar(){const side=document.getElementById('sidebar'),back=document.getElementById('cp-sidebar-backdrop');if(!side)return;const opening=side.classList.contains('sidebar-mobile-hidden');side.classList.toggle('sidebar-mobile-hidden',!opening);back.classList.toggle('hidden',!opening);document.getElementById('cp-nav-toggle')?.setAttribute('aria-expanded',String(opening));if(innerWidth<768){document.getElementById('main-content').inert=opening;document.body.style.overflow=opening?'hidden':'';if(opening)side.querySelector('a')?.focus();}}

        function closeCpSidebar(){if(innerWidth>=768)return;document.getElementById('sidebar')?.classList.add('sidebar-mobile-hidden');document.getElementById('cp-sidebar-backdrop')?.classList.add('hidden');document.getElementById('main-content').inert=false;document.body.style.overflow='';document.getElementById('cp-nav-toggle')?.setAttribute('aria-expanded','false');document.getElementById('cp-nav-toggle')?.focus();}

        document.addEventListener('keydown', event=>{
            const side=document.getElementById('sidebar'),palette=document.getElementById('cp-command-palette');
            const scope=palette&&!palette.classList.contains('hidden')?palette:(innerWidth<768&&side&&!side.classList.contains('sidebar-mobile-hidden')?side:null);
            if(!scope)return;
            if(event.key==='Escape'&&scope===side){event.preventDefault();closeCpSidebar();return;}
            if(event.key!=='Tab')return;
            const items=[...scope.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),[tabindex="0"]')].filter(el=>el.getClientRects().length);
            if(!items.length)return;
            if(event.shiftKey&&document.activeElement===items[0]){event.preventDefault();items.at(-1).focus();}
            else if(!event.shiftKey&&document.activeElement===items.at(-1)){event.preventDefault();items[0].focus();}
        });
        window.addEventListener('resize',()=>{if(innerWidth>=768){document.getElementById('cp-sidebar-backdrop')?.classList.add('hidden');if(document.getElementById('cp-command-palette')?.classList.contains('hidden')){document.getElementById('main-content').inert=false;document.body.style.overflow='';}}});
        function bindCpSidebarScrollBridge() {
            const sidebar = document.getElementById('sidebar');
            const nav = document.getElementById('sidebar-nav');
            if (!sidebar || !nav || sidebar.dataset.scrollBridgeBound === 'true') return;

            const isScrollableTarget = (target) => {
                if (!(target instanceof Element)) return false;
                return !!target.closest('#sidebar-nav, #sidebar-nav *');
            };

            sidebar.addEventListener('wheel', function(event) {
                if (isScrollableTarget(event.target)) return;
                if (nav.scrollHeight <= nav.clientHeight) return;

                nav.scrollTop += event.deltaY;
                event.preventDefault();
            }, { passive: false });

            sidebar.dataset.scrollBridgeBound = 'true';
        }

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                toggleSidebar();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('cp-sidebar-backdrop');
            if (!sidebar) return;
            bindCpSidebarScrollBridge();
            if (window.innerWidth < 768) {
                sidebar.classList.add('sidebar-mobile-hidden');
            } else {
                sidebar.classList.remove('sidebar-mobile-hidden');
            }
            if (backdrop) {
                backdrop.classList.add('hidden');
            }
            document.body.classList.remove('overflow-hidden');
        });

        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('cp-sidebar-backdrop');
            if (!sidebar) return;
            if (window.innerWidth < 768) {
                sidebar.classList.add('sidebar-mobile-hidden');
            } else {
                sidebar.classList.remove('sidebar-mobile-hidden');
            }
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
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
        const cpI18n = {
            typeToSearch: <?= json_encode(lang('UI.cpTypeToSearch')) ?>,
            noResults: <?= json_encode(lang('UI.cpNoResults')) ?>,
            searching: <?= json_encode(lang('UI.cpSearching')) ?>,
            searchFailed: <?= json_encode(lang('UI.cpSearchFailed')) ?>,
            clinicsSection: <?= json_encode(lang('UI.cpClinicsSection')) ?>,
            usersSection: <?= json_encode(lang('UI.cpUsersSection')) ?>,
            navigationSection: <?= json_encode(lang('UI.cpNavigationSection')) ?>,
            recentSection: <?= json_encode(lang('UI.cpRecentSection')) ?>,
            recentSearch: <?= json_encode(lang('UI.cpRecentSearch')) ?>,
            controlPlaneUser: <?= json_encode(lang('UI.cpControlPlaneUser')) ?>,
            fallbackLink: <?= json_encode(lang('UI.cpLinkFallback')) ?>,
            fallbackClinic: <?= json_encode(lang('UI.cpClinicFallback')) ?>,
            fallbackUser: <?= json_encode(lang('UI.cpUserFallback')) ?>
        };

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
            document.querySelector('.v4-app').inert=true;
            document.body.classList.add('overflow-hidden');
            setCommandStatus(cpI18n.typeToSearch);
            resetCommandItems();
            renderRecentSearches();
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
            document.querySelector('.v4-app').inert=false;
            document.getElementById('cp-command-trigger')?.focus();
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

        function createSection(title, iconClass) {
            const section = document.createElement('div');
            const heading = document.createElement('div');
            heading.className = 'px-3 text-xs font-semibold uppercase tracking-widest text-slate-400 flex items-center gap-2';
            if (iconClass) {
                const icon = document.createElement('i');
                icon.className = `${iconClass} text-slate-400`;
                heading.appendChild(icon);
            }
            const label = document.createElement('span');
            label.textContent = title;
            heading.appendChild(label);
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
                setCommandStatus(cpI18n.noResults);
                return;
            }

            const sections = [];
            const clinics = payload.results.clinics || [];
            const users = payload.results.users || [];
            const links = payload.results.links || [];

            if (clinics.length > 0) {
                const { section, list } = createSection(cpI18n.clinicsSection, 'fas fa-hospital-user');
                clinics.forEach((clinic) => {
                    const label = clinic.name || cpI18n.fallbackClinic;
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
                const { section, list } = createSection(cpI18n.usersSection, 'fas fa-user');
                users.forEach((user) => {
                    const label = user.email || cpI18n.fallbackUser;
                    const meta = user.name ? user.name : cpI18n.controlPlaneUser;
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
                const { section, list } = createSection(cpI18n.navigationSection, 'fas fa-compass');
                links.forEach((link) => {
                    const label = link.title || cpI18n.fallbackLink;
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
                setCommandStatus(cpI18n.noResults);
                renderRecentSearches();
                return;
            }

            sections.forEach((section) => cpResults.appendChild(section));
            cpActiveIndex = 0;
            updateActiveItem();
            setCommandStatus(`${cpItems.length} results`);
        }

        function getRecentSearches() {
            const raw = localStorage.getItem('cp_command_recent');
            if (!raw) {
                return [];
            }
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        function saveRecentSearch(query) {
            if (!query || query.length < 2) {
                return;
            }
            const existing = getRecentSearches().filter((item) => item !== query);
            const next = [query, ...existing].slice(0, 5);
            localStorage.setItem('cp_command_recent', JSON.stringify(next));
        }

        function renderRecentSearches() {
            const recent = getRecentSearches();
            if (!cpResults) {
                return;
            }
            if (recent.length === 0) {
                return;
            }
            const { section, list } = createSection(cpI18n.recentSection, 'fas fa-clock');
            recent.forEach((term) => {
                const button = createResultButton(term, cpI18n.recentSearch, () => {
                    if (cpInput) {
                        cpInput.value = term;
                        handleCommandInput();
                    }
                });
                list.appendChild(button);
                cpItems.push(button);
            });
            list.className = 'mt-2 space-y-2';
            section.appendChild(list);
            cpResults.appendChild(section);
            if (cpItems.length > 0 && cpActiveIndex === -1) {
                cpActiveIndex = 0;
                updateActiveItem();
            }
        }

        function fetchCommandResults(query) {
            if (!query || query.length < 2) {
                resetCommandItems();
                setCommandStatus(cpI18n.typeToSearch);
                renderRecentSearches();
                return;
            }

            setCommandStatus(cpI18n.searching);
            fetch(`<?= base_url('controlplane/search/global') ?>?q=${encodeURIComponent(query)}`)
                .then((response) => response.json())
                .then((data) => {
                    saveRecentSearch(query);
                    renderResults(data);
                })
                .catch(() => {
                    resetCommandItems();
                    setCommandStatus(cpI18n.searchFailed);
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

    <script>
        (function () {
            const clinicDateFormat = '<?= esc(settings()->get('date_format', 'd/m/Y')) ?>';

            function clinicDateFormatLabel(format) {
                return String(format || 'd/m/Y').replace('Y', 'YYYY').replace('m', 'MM').replace('d', 'DD');
            }

            function formatClinicDateFromIso(isoDate) {
                if (!isoDate || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) return isoDate || '';
                const [year, month, day] = isoDate.split('-');
                if (clinicDateFormat === 'm/d/Y') return `${month}/${day}/${year}`;
                if (clinicDateFormat === 'd/m/Y') return `${day}/${month}/${year}`;
                return isoDate;
            }

            function parseClinicDateToIso(rawValue) {
                if (!rawValue) return '';
                const value = String(rawValue).trim();
                if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value;

                const isValidCalendarDate = (year, month, day) => {
                    const y = Number(year), m = Number(month), d = Number(day);
                    if (!Number.isInteger(y) || !Number.isInteger(m) || !Number.isInteger(d)) return false;
                    const probe = new Date(Date.UTC(y, m - 1, d));
                    return probe.getUTCFullYear() === y && (probe.getUTCMonth() + 1) === m && probe.getUTCDate() === d;
                };

                const match = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                if (!match) return null;

                let day = match[1], month = match[2], year = match[3];
                if (clinicDateFormat === 'm/d/Y') {
                    [month, day] = [match[1], match[2]];
                }
                month = String(month).padStart(2, '0');
                day = String(day).padStart(2, '0');
                if (!isValidCalendarDate(year, month, day)) return null;
                return `${year}-${month}-${day}`;
            }

            function initializeClinicDateInputs() {
                const fields = document.querySelectorAll('input[data-clinic-date="1"]');
                if (!fields.length) return;
                const formatHint = clinicDateFormatLabel(clinicDateFormat);
                const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

                fields.forEach((field) => {
                    const current = (field.value || '').trim();
                    if (current && /^\d{4}-\d{2}-\d{2}$/.test(current)) {
                        field.value = formatClinicDateFromIso(current);
                    }

                    const syncDateFieldChrome = () => {
                        const nativePickerActive = field.type === 'date';
                        if (nativePickerActive) {
                            if (isRtl) {
                                field.style.paddingLeft = '2.75rem';
                                field.style.paddingRight = '2.75rem';
                            } else {
                                field.style.paddingLeft = '1rem';
                                field.style.paddingRight = '2.75rem';
                            }
                        } else {
                            field.style.removeProperty('padding-left');
                            field.style.removeProperty('padding-right');
                        }
                    };

                    field.type = 'text';
                    field.setAttribute('placeholder', formatHint);
                    field.setAttribute('autocomplete', 'off');
                    field.inputMode = 'numeric';
                    field.setAttribute('title', `${formatHint} (type manually or press Alt+Down for picker)`);
                    syncDateFieldChrome();

                    const switchToPickerMode = () => {
                        const raw = (field.value || '').trim();
                        const iso = parseClinicDateToIso(raw);
                        field.setCustomValidity('');
                        field.type = 'date';
                        if (iso) field.value = iso;
                        if (raw === '') field.value = '';
                        syncDateFieldChrome();
                        if (typeof field.showPicker === 'function') {
                            try { field.showPicker(); } catch (e) {}
                        }
                    };

                    const switchToDisplayMode = () => {
                        if (field.type !== 'date') return;
                        const iso = (field.value || '').trim();
                        field.type = 'text';
                        field.value = iso ? formatClinicDateFromIso(iso) : '';
                        field.setCustomValidity('');
                        syncDateFieldChrome();
                    };

                    field.addEventListener('click', () => { if (field.type !== 'date') switchToPickerMode(); });
                    field.addEventListener('dblclick', () => { if (field.type !== 'date') switchToPickerMode(); });
                    field.addEventListener('keydown', (event) => {
                        if (event.altKey && event.key === 'ArrowDown') {
                            event.preventDefault();
                            switchToPickerMode();
                        }
                    });
                    field.addEventListener('input', () => { field.setCustomValidity(''); });
                    field.addEventListener('change', () => { field.setCustomValidity(''); });
                    field.addEventListener('blur', switchToDisplayMode);
                });

                document.querySelectorAll('form').forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        for (const input of form.querySelectorAll('input[data-clinic-date="1"]')) {
                            input.setCustomValidity('');
                            const raw = (input.value || '').trim();
                            if (raw === '') {
                                continue;
                            }
                            const iso = parseClinicDateToIso(raw);
                            if (!iso) {
                                input.setCustomValidity(`Use date format ${formatHint}`);
                                input.reportValidity();
                                event.preventDefault();
                                return;
                            }
                            input.setCustomValidity('');
                            input.value = iso;
                        }
                    }, true);
                });
            }

            window.parseClinicDateToIso = window.parseClinicDateToIso || parseClinicDateToIso;
            document.addEventListener('DOMContentLoaded', initializeClinicDateInputs);
        })();
    </script>

    <?php if (isset($loadSelect2) && $loadSelect2): ?>
    <script src="<?= base_url('assets/js/select2-init.js') ?>?v=<?= time() ?>"></script>
    <?php endif; ?>
</body>
</html>
