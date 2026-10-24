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
<div class="v3-operational-view px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="v3-radius border v3-border v3-surface p-5 sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-semibold v3-accent-text"><?= esc(lang('UI.notifications')) ?></p>
                    <h1 class="text-2xl font-bold tracking-tight v3-text-ink sm:text-3xl"><?= esc(lang('UI.notificationPageTitle')) ?></h1>
                    <p class="max-w-2xl text-sm leading-6 v3-text-muted"><?= esc(lang('UI.notificationPageDesc')) ?></p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="min-w-[10rem] v3-radius border v3-border v3-surface px-4 py-3">
                        <p class="text-xs font-semibold v3-text-muted"><?= esc(lang('UI.notificationUnreadLabel')) ?></p>
                        <p class="mt-1 text-2xl font-bold v3-text-ink"><?= number_format($unreadNotifications) ?></p>
                    </div>
                    <div class="min-w-[10rem] v3-radius border v3-border v3-surface px-4 py-3">
                        <p class="text-xs font-semibold v3-text-muted"><?= esc(lang('UI.notificationTotalLabel')) ?></p>
                        <p class="mt-1 text-2xl font-bold v3-text-ink"><?= number_format($totalNotifications) ?></p>
                    </div>
                    <button type="button" onclick="markAllNotificationsPageAsRead()" class="inline-flex items-center justify-center v3-radius border v3-border v3-surface px-4 py-3 text-sm font-semibold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                        <?= esc(lang('UI.markAllAsRead')) ?>
                    </button>
                </div>
            </div>
        </section>

        <?php if (empty($notifications)): ?>
            <section class="v3-radius border v3-border v3-surface px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full v3-bg-soft v3-text-muted">
                    <i class="fas fa-bell-slash text-xl"></i>
                </div>
                <h2 class="mt-5 text-lg font-semibold v3-text-ink"><?= esc(lang('UI.notificationEmpty')) ?></h2>
                <p class="mt-2 text-sm leading-6 v3-text-muted"><?= esc(lang('UI.notificationEmptyDesc')) ?></p>
            </section>
        <?php else: ?>
            <section class="space-y-4">
                <?php foreach ($notifications as $notification): ?>
                    <?php
                    $notificationUrl = (string) ($notification['entity_url'] ?? base_url('notifications'));
                    $isRead = !empty($notification['is_read']);
                    ?>
                    <article class="v3-radius border v3-border v3-surface p-4 transition v3-border-default sm:p-5 cursor-pointer" onclick="handleNotificationCardClick(event, '<?= esc($notificationUrl, 'js') ?>')">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex min-w-0 flex-1 items-start gap-3 sm:gap-4">
                                <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center v3-radius <?= esc(match ($notification['color'] ?? 'gray') {
                                    'blue' => 'v3-bg-scheduled v3-text-primary',
                                    'green' => 'v3-bg-paid v3-text-primary',
                                    'yellow' => 'v3-bg-waiting v3-text-warning',
                                    'purple' => 'v3-bg-chair v3-text-chair',
                                    'red' => 'v3-bg-alert v3-text-danger',
                                    default => 'v3-bg-soft v3-text-muted',
                                }) ?>">
                                    <i class="<?= esc($notification['icon'] ?? 'fas fa-bell') ?> text-sm"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-sm font-semibold leading-6 v3-text-ink sm:text-base"><?= esc($notification['title'] ?? '') ?></h2>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= $isRead ? 'v3-bg-soft v3-text-muted' : 'v3-bg-scheduled v3-text-primary' ?>">
                                            <?= esc($isRead ? lang('UI.notificationReadLabel') : lang('UI.notificationUnreadLabel')) ?>
                                        </span>
                                        <?php if (!empty($notification['type_label'])): ?>
                                            <span class="inline-flex items-center rounded-full v3-bg-soft px-2.5 py-1 text-xs font-medium v3-text-muted">
                                                <?= esc($notification['type_label']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="mt-2 text-sm leading-6 v3-text-muted"><?= esc($notification['message'] ?? '') ?></p>

                                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs v3-text-muted">
                                        <span><?= esc($formatNotificationTime($notification['created_at'] ?? null)) ?></span>
                                        <?php if (!empty($notification['user_name'])): ?>
                                            <span><?= esc(str_replace('{name}', (string) $notification['user_name'], lang('UI.notificationByUser'))) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2 <?= $isRtl ? 'sm:pr-2' : 'sm:pl-2' ?>">
                                <?php if (!$isRead): ?>
                                    <button type="button" onclick="markNotificationPageAsRead(<?= (int) ($notification['id'] ?? 0) ?>, this)" class="inline-flex items-center v3-radius border v3-border px-3 py-2 text-xs font-semibold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                                        <?= esc(lang('UI.notificationMarkRead')) ?>
                                    </button>
                                <?php endif; ?>
                                <a href="<?= esc($notificationUrl) ?>" class="inline-flex items-center v3-radius v3-bg-forest px-3 py-2 text-xs font-semibold v3-text-on-primary transition v3-bg-forest v3-btn v3-btn--secondary">
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
