<section id="notifications-dropdown" dir="<?= !empty($is_rtl)?'rtl':'ltr' ?>" class="v4-notification-center hidden" role="dialog" aria-modal="false" aria-labelledby="notifications-title">
<header><div><h2 id="notifications-title"><?= esc(lang('UI.notificationCenterTitle')) ?></h2><p id="notifications-summary"><?= esc(lang('UI.notificationCenterDesc')) ?></p></div><button type="button" class="v4-button v4-button--small" onclick="markAllAsRead()"><?= esc(lang('UI.markAllAsRead')) ?></button></header>
<div id="notifications-list" role="list"></div>
<footer><a class="v4-text-link" href="<?= base_url('notifications') ?>"><?= esc(lang('UI.viewAllNotifications')) ?></a></footer>
</section>
