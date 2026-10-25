    <!-- Notifications Dropdown - Outside main content to avoid overflow issues -->
    <div id="notifications-dropdown" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>" class="tenant-header-dropdown absolute hidden flex-col" role="dialog" aria-modal="false" aria-labelledby="notifications-title">
        <div class="tenant-header-dropdown__header shrink-0 px-4 py-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 id="notifications-title" class="text-sm font-black text-slate-950"><?= lang('UI.notificationCenterTitle') ?></h3>
                    <p id="notifications-summary" class="mt-1 text-xs font-bold text-slate-500"><?= lang('UI.notificationCenterDesc') ?></p>
                </div>
                <button type="button" onclick="markAllAsRead()" class="shrink-0 rounded-xl border border-blue-200 bg-white px-3 py-1.5 text-xs font-black text-blue-700 shadow-sm transition hover:bg-blue-50"><?= lang('UI.markAllAsRead') ?></button>
            </div>
        </div>
        <div id="notifications-list" class="min-h-0 flex-1 overflow-y-auto px-2 py-2.5" role="list" style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;"></div>
        <div class="tenant-header-dropdown__footer shrink-0 px-4 py-3">
            <a href="<?= base_url('notifications') ?>" class="inline-flex items-center text-sm font-black text-blue-600 hover:text-blue-800"><?= lang('UI.viewAllNotifications') ?></a>
        </div>
    </div>
