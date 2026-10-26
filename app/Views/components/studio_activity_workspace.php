<?php $activityPlatform=($activity_plane??'tenant')==='platform';$activityPrefix=$activityPlatform?'controlplane/activity-log':'activity-log'; ?>
<header class="page-title-row">
<div>
<h1>
<?= esc(lang($activityPlatform?'UI.cpSystemActivity':'ActivityLog.title')) ?>
</h1>
</div>
<div class="management-actions">
<button type="button" class="button secondary" data-activity-refresh>
<?= esc(lang('ActivityLog.refresh')) ?>
</button>
<a class="button primary" href="<?= base_url($activityPrefix.'/export') ?>" data-activity-export>
<?= esc(lang('ActivityLog.export')) ?>
</a>
</div>
</header>
<section class="staff-directory" id="activity-workspace" data-plane="<?= $activityPlatform?'platform':'tenant' ?>" data-url="<?= base_url($activityPrefix.'/api') ?>" data-base="<?= base_url() ?>" data-error="<?= esc(lang('ActivityLog.failedToLoadActivities'),'attr') ?>" data-empty="<?= esc(lang('ActivityLog.noActivitiesFound'),'attr') ?>" data-na="<?= esc(lang('ActivityLog.notAvailable'),'attr') ?>" data-page="<?= esc(lang('ActivityLog.pageInfo',['current'=>'{current}','max'=>'{max}']),'attr') ?>">
<form class="activity-filters <?= $activityPlatform?'activity-filters--platform':'' ?>" id="activity-filters">
<?php if($activityPlatform): ?>
<div class="form-field">
<label for="eventTypeFilter">
<?= esc(lang('Studio.platformEventType')) ?>
</label>
<select id="eventTypeFilter" name="event_type">
<option value="">
<?= esc(lang('ActivityLog.allTypes')) ?>
</option>
<?php foreach(($filters['event_types']??[]) as $type): ?>
<option value="<?= esc($type,'attr') ?>">
<?= esc(ucwords(str_replace('_',' ',$type))) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php else: ?>
<div class="form-field">
<label for="entityTypeFilter">
<?= esc(lang('ActivityLog.entityType')) ?>
</label>
<select id="entityTypeFilter" name="entity_type">
<option value="">
<?= esc(lang('ActivityLog.allTypes')) ?>
</option>
<?php foreach(($filters['entity_types']??[]) as $type): ?>
<option value="<?= esc($type,'attr') ?>">
<?= esc($type) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="actionFilter">
<?= esc(lang('ActivityLog.action')) ?>
</label>
<select id="actionFilter" name="action">
<option value="">
<?= esc(lang('ActivityLog.allActions')) ?>
</option>
<?php foreach(($filters['actions']??[]) as $action): ?>
<option value="<?= esc($action,'attr') ?>">
<?= esc($action) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<div class="form-field">
<label for="userFilter">
<?= esc(lang('ActivityLog.user')) ?>
</label>
<input type="search" id="userFilter" name="user" autocomplete="off" placeholder="<?= esc(lang('ActivityLog.searchByUserName'),'attr') ?>">
</div>
<button type="submit" class="button primary">
<?= esc(lang('ActivityLog.filter')) ?>
</button>
</form>
<div class="activity-counts" aria-live="polite">
<span <?= $activityPlatform?'hidden':'' ?>>
<?= esc(lang('ActivityLog.activities')) ?> <strong id="total-activities">—</strong>
</span>
<span>
<?= esc(lang('ActivityLog.filtered')) ?> <strong id="filtered-activities">—</strong>
</span>
</div>
<div id="loadingState" class="activity-state" role="status">
<?= esc(lang('ActivityLog.loadingActivities')) ?>
</div>
<div class="activity-state activity-state--error" id="activity-error" role="alert" hidden>
<p>
</p>
<button class="button secondary" type="button" data-activity-refresh>
<?= esc(lang('Studio.retry')) ?>
</button>
</div>
<div class="activity-state" id="emptyState" hidden>
<h2>
<?= esc(lang('ActivityLog.noActivitiesFound')) ?>
</h2>
<p>
<?= esc(lang('ActivityLog.noActivitiesMatchFilters')) ?>
</p>
</div>
<div class="platform-table-scroll">
<table id="activitiesTable" class="studio-grid platform-table" data-grid-widths="18,19,13,26,15,9" hidden>
<thead>
<tr>
<?php foreach(['activity','user','entity','description','time','ipAddress'] as $label): ?>
<th scope="col" data-key="<?= $label ?>">
<?= esc(lang('ActivityLog.'.$label)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody id="activitiesTableBody">
</tbody>
</table>
</div>
<footer class="staff-pagination" id="paginationContainer">
<div>
<button class="button secondary" id="prevPage" type="button">
<?= esc(lang('ActivityLog.previous')) ?>
</button>
<span id="pageInfo" aria-live="polite">
</span>
<button class="button secondary" id="nextPage" type="button">
<?= esc(lang('ActivityLog.next')) ?>
</button>
</div>
<div class="form-field activity-size">
<label for="pageSize">
<?= esc(lang('ActivityLog.show')) ?>
</label>
<select id="pageSize">
<?php foreach([10,25,50,100] as $size): ?>
<option value="<?= $size ?>" <?= $size===50?'selected':'' ?>>
<?= $size ?>
</option>
<?php endforeach; ?>
</select>
</div>
</footer>
</section>
