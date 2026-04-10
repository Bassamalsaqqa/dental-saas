<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$isRtl = service('request')->getLocale() === 'ar';
$formatNotificationTime = static function (?string $dateString) use ($isRtl): string {
    if (!$dateString) {
        return lang('UI.notificationJustNow');
    }

    try {
        $date = new DateTimeImmutable($dateString);
    } catch (Throwable $e) {
        return lang('UI.notificationJustNow');
    }

    $now = new DateTimeImmutable('now');
    $diffMinutes = max(0, (int) floor(($now->getTimestamp() - $date->getTimestamp()) / 60));

    if ($diffMinutes < 1) {
        return lang('UI.notificationJustNow');
    }

    if ($diffMinutes < 60) {
        return $diffMinutes === 1
            ? lang('UI.notificationMinuteAgo')
            : str_replace('{count}', (string) $diffMinutes, lang('UI.notificationMinutesAgo'));
    }

    if ($diffMinutes < 1440) {
        $hours = (int) floor($diffMinutes / 60);
        return $hours === 1
            ? lang('UI.notificationHourAgo')
            : str_replace('{count}', (string) $hours, lang('UI.notificationHoursAgo'));
    }

    $days = (int) floor($diffMinutes / 1440);
    return $days === 1
        ? lang('UI.notificationDayAgo')
        : str_replace('{count}', (string) $days, lang('UI.notificationDaysAgo'));
};

$totalNotifications = count($notifications ?? []);
$unreadNotifications = (int) ($unread_count ?? 0);
?>
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-semibold text-blue-600"><?= esc(lang('UI.notifications')) ?></p>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"><?= esc(lang('UI.notificationPageTitle')) ?></h1>
                    <p class="max-w-2xl text-sm leading-6 text-slate-600"><?= esc(lang('UI.notificationPageDesc')) ?></p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="min-w-[10rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold text-slate-500"><?= esc(lang('UI.notificationUnreadLabel')) ?></p>
                        <p class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($unreadNotifications) ?></p>
                    </div>
                    <div class="min-w-[10rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold text-slate-500"><?= esc(lang('UI.notificationTotalLabel')) ?></p>
                        <p class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($totalNotifications) ?></p>
                    </div>
                    <button type="button" onclick="markAllNotificationsPageAsRead()" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc(lang('UI.markAllAsRead')) ?>
                    </button>
                </div>
            </div>
        </section>

        <?php if (empty($notifications)): ?>
            <section class="rounded-3xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fas fa-bell-slash text-xl"></i>
                </div>
                <h2 class="mt-5 text-lg font-semibold text-slate-900"><?= esc(lang('UI.notificationEmpty')) ?></h2>
                <p class="mt-2 text-sm leading-6 text-slate-500"><?= esc(lang('UI.notificationEmptyDesc')) ?></p>
            </section>
        <?php else: ?>
            <section class="space-y-4">
                <?php foreach ($notifications as $notification): ?>
                    <?php
                    $notificationUrl = (string) ($notification['entity_url'] ?? base_url('notifications'));
                    $isRead = !empty($notification['is_read']);
                    ?>
                    <article class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md sm:p-5 cursor-pointer" onclick="handleNotificationCardClick(event, '<?= esc($notificationUrl, 'js') ?>')">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex min-w-0 flex-1 items-start gap-3 sm:gap-4">
                                <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl <?= esc(match ($notification['color'] ?? 'gray') {
                                    'blue' => 'bg-blue-50 text-blue-600',
                                    'green' => 'bg-emerald-50 text-emerald-600',
                                    'yellow' => 'bg-amber-50 text-amber-600',
                                    'purple' => 'bg-violet-50 text-violet-600',
                                    'red' => 'bg-rose-50 text-rose-600',
                                    default => 'bg-slate-100 text-slate-600',
                                }) ?>">
                                    <i class="<?= esc($notification['icon'] ?? 'fas fa-bell') ?> text-sm"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-sm font-semibold leading-6 text-slate-900 sm:text-base"><?= esc($notification['title'] ?? '') ?></h2>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= $isRead ? 'bg-slate-100 text-slate-600' : 'bg-blue-50 text-blue-700' ?>">
                                            <?= esc($isRead ? lang('UI.notificationReadLabel') : lang('UI.notificationUnreadLabel')) ?>
                                        </span>
                                        <?php if (!empty($notification['type_label'])): ?>
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                                <?= esc($notification['type_label']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="mt-2 text-sm leading-6 text-slate-600"><?= esc($notification['message'] ?? '') ?></p>

                                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
                                        <span><?= esc($formatNotificationTime($notification['created_at'] ?? null)) ?></span>
                                        <?php if (!empty($notification['user_name'])): ?>
                                            <span><?= esc(str_replace('{name}', (string) $notification['user_name'], lang('UI.notificationByUser'))) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2 <?= $isRtl ? 'sm:pr-2' : 'sm:pl-2' ?>">
                                <?php if (!$isRead): ?>
                                    <button type="button" onclick="markNotificationPageAsRead(<?= (int) ($notification['id'] ?? 0) ?>, this)" class="inline-flex items-center rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <?= esc(lang('UI.notificationMarkRead')) ?>
                                    </button>
                                <?php endif; ?>
                                <a href="<?= esc($notificationUrl) ?>" class="inline-flex items-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800">
                                    <?= esc(lang('UI.notificationOpenItem')) ?>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</div>

<script>
function handleNotificationCardClick(event, url) {
    if (!url) {
        return;
    }

    const interactiveTarget = event.target.closest('a, button, input, select, textarea, label');
    if (interactiveTarget) {
        return;
    }

    window.location.href = url;
}

function markNotificationPageAsRead(id, button) {
    if (!id) {
        return;
    }

    fetch('<?= base_url('notifications/mark-read') ?>/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': typeof csrfToken !== 'undefined' ? csrfToken : ''
        },
        body: JSON.stringify({
            [csrfTokenName]: typeof csrfToken !== 'undefined' ? csrfToken : ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token && window.refreshCsrfToken) {
            window.refreshCsrfToken(data.csrf_token);
        }

        if (!data.success) {
            throw new Error(data.message || 'mark-read-failed');
        }

        window.location.reload();
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllNotificationsPageAsRead() {
    fetch('<?= base_url('notifications/mark-read') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': typeof csrfToken !== 'undefined' ? csrfToken : ''
        },
        body: JSON.stringify({
            [csrfTokenName]: typeof csrfToken !== 'undefined' ? csrfToken : ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token && window.refreshCsrfToken) {
            window.refreshCsrfToken(data.csrf_token);
        }

        if (!data.success) {
            throw new Error(data.message || 'mark-all-read-failed');
        }

        window.location.reload();
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}
</script>
<?= $this->endSection() ?>
