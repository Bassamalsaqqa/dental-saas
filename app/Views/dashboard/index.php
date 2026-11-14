<?php helper('asset'); include APPPATH.'Views/layouts/partials/view_permissions.php'; include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-create-dialog.css') ?>?v=<?= esc(asset_version('assets/css/studio-create-dialog.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-dashboard.css') ?>?v=<?= esc(asset_version('assets/css/studio-dashboard.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$studioVisits = [];
foreach (($today_routing['groups'] ?? []) as $rows) {
    foreach ($rows as $row) {
        $studioVisits[] = $row;
    }
}
usort($studioVisits, static fn($a, $b) => strcmp((string)($a['appointment_time'] ?? ''), (string)($b['appointment_time'] ?? '')));

$studioUpcoming = $upcoming_appointments ?? [];
$studioInitialTab = empty($studioVisits) && !empty($studioUpcoming) ? 'upcoming' : 'today';
$studioCanVisits = !empty($can_route_appointments);
$studioCanEditVisits = $studioCanVisits && $v4Can('appointments', 'edit');

$studioStageLabels = [
    'scheduled' => 'appointmentStageScheduled',
    'confirmed' => 'appointmentStageConfirmed',
    'arrived'   => 'appointmentStageArrived',
    'ready'     => 'appointmentStageReady',
    'in_chair'  => 'appointmentStageInChair',
    'checkout'  => 'appointmentStageCheckout',
    'completed' => 'appointmentStageCompleted',
    'cancelled' => 'appointmentStageCancelled',
    'no_show'   => 'appointmentStageNoShow'
];

$waitingStages = ['arrived', 'ready'];

$studioDocs = $clinical_documentation_queue ?? [];
$studioRecovery = $appointment_recovery_queue ?? [];
$studioAccepted = $accepted_treatment_queue ?? [];
?>

<div class="dashboard-workspace">
  <!-- Page Header: Single compact H1, clinic date, primary Book + secondary Register actions -->
  <div class="dashboard-header page-title-row studio-contour" role="banner">
    <div class="dashboard-title-group">
      <h1><?= esc(lang('Studio.clinicWork')) ?><span class="title-period">.</span></h1>
      <span class="dashboard-date-chip date-chip">
        <?= $studioIcon('calendar') ?>
        <span><?= esc(formatDate(date('Y-m-d'))) ?></span>
      </span>
    </div>
    <div class="dashboard-actions-group heading-actions">
      <?php if ($v4Can('patients', 'create')): ?>
        <a class="dashboard-btn-secondary button secondary" data-patient-create-open href="<?= base_url('patient/create?return_to=dashboard') ?>">
          <?= $studioIcon('plus') ?>
          <span><?= esc(lang('Studio.register')) ?></span>
        </a>
      <?php endif ?>
      <?php if ($v4Can('appointments', 'create')): ?>
        <a class="dashboard-btn-primary button primary" data-booking-create-open href="<?= base_url('appointment/create?return_to=dashboard') ?>">
          <?= $studioIcon('calendar') ?>
          <span><?= esc(lang('Studio.book')) ?></span>
        </a>
      <?php endif ?>
    </div>
  </div>

  <!-- 2-Column Operational Grid -->
  <div class="dashboard-grid desk-layout">
    <!-- Main Column: Today / Upcoming Schedule Desk -->
    <section class="appointment-desk" aria-labelledby="desk-title">
      <h2 id="desk-title" class="sr-only"><?= esc(lang('Studio.day')) ?></h2>

      <?php if ($studioCanVisits):
        $studioInitialRows = $studioInitialTab === 'today' ? $studioVisits : $studioUpcoming;
        $countAll = count($studioInitialRows);
        $countWaiting = 0;
        $countInChair = 0;
        $countOther = 0;
        foreach ($studioInitialRows as $r) {
            $st = (string)($r['status'] ?? 'scheduled');
            if (in_array($st, $waitingStages, true)) {
                $countWaiting++;
            } elseif ($st === 'in_chair') {
                $countInChair++;
            } else {
                $countOther++;
            }
        }
      ?>
      <!-- Toolbar: Navigation Tabs & Restrained Plain Filter Controls -->
      <div class="dashboard-toolbar desk-controls">
        <div class="dashboard-tabs segment" role="tablist" aria-label="<?= esc(lang('Studio.day')) ?>">
          <button type="button" id="tab-today" class="dashboard-tab-btn" role="tab" data-tab="today" aria-controls="panel-today" aria-selected="<?= $studioInitialTab === 'today' ? 'true' : 'false' ?>">
            <span><?= esc(lang('Studio.todayTab')) ?></span>
            <span class="dashboard-tab-badge" id="count-tab-today"><?= count($studioVisits) ?></span>
          </button>
          <button type="button" id="tab-upcoming" class="dashboard-tab-btn" role="tab" data-tab="upcoming" aria-controls="panel-upcoming" aria-selected="<?= $studioInitialTab === 'upcoming' ? 'true' : 'false' ?>">
            <span><?= esc(lang('Studio.upcoming')) ?></span>
            <span class="dashboard-tab-badge" id="count-tab-upcoming"><?= count($studioUpcoming) ?></span>
          </button>
        </div>

        <div class="visit-filters-bar" role="toolbar" aria-label="<?= esc(lang('Studio.filtered')) ?>">
          <div class="filter-group" role="radiogroup" aria-label="<?= esc(lang('Studio.filtered')) ?>">
            <button type="button" class="filter-btn active" data-filter="all" role="radio" aria-checked="true">
              <span><?= esc(lang('Studio.all')) ?></span>
              <span class="filter-count" dir="ltr">(<?= $countAll ?>)</span>
            </button>
            <button type="button" class="filter-btn" data-filter="waiting" role="radio" aria-checked="false">
              <span><?= esc(lang('Studio.lane_waiting')) ?></span>
              <span class="filter-count" dir="ltr">(<?= $countWaiting ?>)</span>
            </button>
            <button type="button" class="filter-btn" data-filter="in_chair" role="radio" aria-checked="false">
              <span><?= esc(lang('UI.appointmentStageInChair')) ?></span>
              <span class="filter-count" dir="ltr">(<?= $countInChair ?>)</span>
            </button>
            <button type="button" class="filter-btn" data-filter="other" role="radio" aria-checked="false" <?= $countOther === 0 ? 'hidden' : '' ?>>
              <span><?= esc(lang('Finance.other')) ?></span>
              <span class="filter-count" dir="ltr">(<?= $countOther ?>)</span>
            </button>
          </div>
          <label for="visit-filter" class="sr-only"><?= esc(lang('Studio.filtered')) ?></label>
          <select id="visit-filter" class="sr-only" aria-label="<?= esc(lang('Studio.filtered')) ?>">
            <option value="all"><?= esc(lang('Studio.all')) ?></option>
            <option value="waiting"><?= esc(lang('Studio.lane_waiting')) ?></option>
            <option value="in_chair"><?= esc(lang('UI.appointmentStageInChair')) ?></option>
            <option value="other"><?= esc(lang('Finance.other')) ?></option>
            <?php foreach ($studioStageLabels as $status => $label): ?>
              <option value="<?= esc($status) ?>"><?= esc(lang('UI.'.$label)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <?php foreach (['today' => $studioVisits, 'upcoming' => $studioUpcoming] as $tab => $rows): ?>
        <div role="tabpanel" id="panel-<?= $tab ?>" aria-labelledby="tab-<?= $tab ?>" <?= $tab !== $studioInitialTab ? 'hidden' : '' ?>>

          <?php if (!$rows): ?>
            <div class="empty-agenda">
              <div class="empty-calendar-icon"><?= $studioIcon('calendar') ?></div>
              <h3><?= esc(lang('Studio.'.($tab === 'today' ? 'noToday' : 'noUpcoming'))) ?></h3>
              <p><?= esc(lang('Studio.'.($tab === 'today' ? 'noTodayHelp' : 'noUpcomingHelp'))) ?></p>
              <?php if ($v4Can('appointments', 'create')): ?>
                <a class="dashboard-btn-secondary button secondary" href="<?= base_url('appointment/create') ?>">
                  <?= $studioIcon('plus') ?>
                  <span><?= esc(lang('Studio.book')) ?></span>
                </a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <!-- Single Accessible Visit Deck: Desktop aligned rows -> Mobile flat cards -->
            <div class="visit-deck visit-table-scroll grid-scroll" tabindex="0" role="region" aria-label="<?= esc(lang('Studio.visits'), 'attr') ?>">
              <!-- Desktop Column Headers -->
              <div class="visit-header-row hidden md:grid" aria-hidden="true">
                <div class="col-head col-head-time"><?= esc(lang('Studio.time')) ?> (<?= esc(lang('Studio.duration')) ?>)</div>
                <div class="col-head col-head-identity"><?= esc(lang('Studio.name')) ?></div>
                <div class="col-head col-head-resource"><?= esc(lang('Studio.resource')) ?></div>
                <div class="col-head col-head-status"><?= esc(lang('Studio.status')) ?></div>
                <div class="col-head col-head-action visit-action-head"><?= esc(lang('Studio.actions')) ?></div>
              </div>

              <!-- Single Semantic Representation per Visit -->
              <div class="visit-list" role="list">
                <?php foreach ($rows as $i => $visit):
                  $visitId = (int)$visit['id'];
                  $patientId = (int)($visit['patient_id'] ?? 0);
                  $name = trim(($visit['first_name'] ?? '').' '.($visit['last_name'] ?? ''));
                  $stage = (string)($visit['status'] ?? 'scheduled');
                  $encounter = $visit['encounter_action'] ?? null;
                  $encounterType = $encounter['type'] ?? 'fallback';
                  $hasEncounterAction = $encounterType !== 'fallback' && !empty($encounter['url']);
                  $encounterUrl = $hasEncounterAction ? ($encounter['url'] ?? '') : '';
                  $encounterLabelKey = $hasEncounterAction ? ($encounter['label_key'] ?? '') : '';
                  $encounterLabel = $hasEncounterAction ? lang('UI.'.$encounterLabelKey) : '';
                  $encounterAccessibleLabel = $hasEncounterAction ? ($encounterLabel.($name !== '' ? ' - '.$name : '')) : '';
                ?>
                <article class="visit-entry" role="listitem"
                  data-stage="<?= esc($stage, 'attr') ?>"
                  data-visit-id="<?= $visitId ?>"
                  data-patient-name="<?= esc($name, 'attr') ?>"
                  data-encounter-type="<?= esc($encounterType, 'attr') ?>"
                  data-encounter-url="<?= esc($encounterUrl, 'attr') ?>"
                  data-encounter-label="<?= esc($encounterLabel, 'attr') ?>"
                  data-encounter-aria-label="<?= esc($encounterAccessibleLabel, 'attr') ?>">

                  <!-- Mobile Card Top: Identity + Resource & Status + Time -->
                  <div class="visit-card-meta md:contents">
                    <!-- Column 1: Time + Duration Combined -->
                    <div class="visit-col visit-col-time">
                      <a class="visit-link" href="<?= esc(workflow_context_url('appointment/'.$visitId, 'dashboard')) ?>" aria-label="<?= esc(lang('Studio.details').' - '.$name, 'attr') ?>">
                        <span class="visit-time-combined visit-card-time" dir="ltr">
                          <strong class="visit-time-val"><?= esc(formatTime($visit['appointment_time'] ?? '')) ?></strong>
                          <span class="visit-duration-val">(<?= isset($visit['duration']) ? esc((string)(int)$visit['duration'].' '.lang('Studio.minutes')) : '-' ?>)</span>
                        </span>
                        <?php if ($tab === 'upcoming' || !empty($visit['appointment_date'])): ?>
                          <small class="visit-date-val"><?= esc(formatDate($visit['appointment_date'] ?? date('Y-m-d'))) ?></small>
                        <?php endif; ?>
                      </a>
                    </div>

                    <!-- Column 2: Patient Identity -->
                    <div class="visit-col visit-col-identity visit-card-identity">
                      <a class="visit-person" href="<?= esc(workflow_context_url('patient/'.$patientId, 'dashboard')) ?>">
                        <span class="patient-avatar avatar-tone-<?= $i % 4 ?>"><?= esc($studioInitials($name)) ?></span>
                        <span class="patient-info">
                          <strong class="patient-name"><?= esc($name ?: lang('UI.patientUnknown')) ?></strong>
                          <small class="patient-code"><?= esc($visit['patient_number'] ?? $visit['patient_code'] ?? lang('Studio.created')) ?></small>
                          <?php if (!empty($visit['phone'])): ?>
                            <small class="patient-phone" dir="ltr"><?= esc($visit['phone']) ?></small>
                          <?php endif; ?>
                        </span>
                      </a>
                    </div>

                    <!-- Column 3: Resource / Chair -->
                    <div class="visit-col visit-col-resource">
                      <span class="resource-badge">
                        <span class="resource-name"><?= esc(trim((string)($visit['chair_name'] ?? '')) ?: lang('Studio.notAssigned')) ?></span>
                      </span>
                    </div>

                    <!-- Column 4: Status Badge -->
                    <div class="visit-col visit-col-status visit-card-status-wrap">
                      <span class="status-badge status-<?= esc($stage, 'attr') ?>">
                        <span></span>
                        <span class="status-badge-text"><?= esc(lang('UI.'.($studioStageLabels[$stage] ?? 'appointmentStageScheduled'))) ?></span>
                      </span>
                    </div>
                  </div>

                  <!-- Column 5: Direct Visible Actions (Desktop Right-aligned / Mobile Full-width Cards) -->
                  <div class="visit-col visit-col-action visit-action">
                    <div class="visit-action-group">
                      <?php if ($tab === 'today'): ?>
                        <?php if ($stage === 'in_chair'): ?>
                          <?php if ($hasEncounterAction): ?>
                            <a class="button secondary encounter-btn" href="<?= esc($encounterUrl, 'attr') ?>" aria-label="<?= esc($encounterAccessibleLabel, 'attr') ?>">
                              <?= esc($encounterLabel) ?>
                            </a>
                          <?php endif; ?>
                          <?php if ($studioCanEditVisits): ?>
                            <form method="post" action="<?= base_url('appointment/'.$visitId.'/transition') ?>" class="inline-transition-form" data-appointment-transition>
                              <?= csrf_field() ?>
                              <input type="hidden" name="status" value="checkout">
                              <input type="hidden" name="expected_status" value="in_chair">
                              <input type="hidden" name="return_to" value="dashboard">
                              <button type="submit" class="button primary transition-btn" aria-label="<?= esc(lang('UI.appointmentActionReadyForReception').($name !== '' ? ' - '.$name : ''), 'attr') ?>">
                                <?= esc(lang('UI.appointmentActionReadyForReception')) ?>
                              </button>
                            </form>
                          <?php endif; ?>
                          <?php if (!$hasEncounterAction): ?>
                            <a class="button secondary" href="<?= esc(workflow_context_url('appointment/'.$visitId, 'dashboard')) ?>" aria-label="<?= esc(lang('UI.appointmentActionView').($name !== '' ? ' - '.$name : ''), 'attr') ?>">
                              <?= esc(lang('UI.appointmentActionView')) ?>
                            </a>
                          <?php endif; ?>
                        <?php elseif ($studioCanEditVisits): ?>
                          <?php if ($stage === 'scheduled' || $stage === 'confirmed'): ?>
                            <form method="post" action="<?= base_url('appointment/'.$visitId.'/transition') ?>" class="inline-transition-form" data-appointment-transition>
                              <?= csrf_field() ?>
                              <input type="hidden" name="status" value="arrived">
                              <input type="hidden" name="expected_status" value="<?= esc($stage, 'attr') ?>">
                              <input type="hidden" name="return_to" value="dashboard">
                              <button type="submit" class="button primary transition-btn" aria-label="<?= esc(lang('UI.appointmentActionArrival').($name !== '' ? ' - '.$name : ''), 'attr') ?>">
                                <?= esc(lang('UI.appointmentActionArrival')) ?>
                              </button>
                            </form>
                          <?php elseif ($stage === 'arrived'): ?>
                            <form method="post" action="<?= base_url('appointment/'.$visitId.'/transition') ?>" class="inline-transition-form" data-appointment-transition>
                              <?= csrf_field() ?>
                              <input type="hidden" name="status" value="ready">
                              <input type="hidden" name="expected_status" value="arrived">
                              <input type="hidden" name="return_to" value="dashboard">
                              <button type="submit" class="button secondary transition-btn" aria-label="<?= esc(lang('UI.appointmentActionReady').($name !== '' ? ' - '.$name : ''), 'attr') ?>">
                                <?= esc(lang('UI.appointmentActionReady')) ?>
                              </button>
                            </form>
                          <?php elseif ($stage === 'ready'): ?>
                            <form method="post" action="<?= base_url('appointment/'.$visitId.'/transition') ?>" class="inline-transition-form" data-appointment-transition>
                              <?= csrf_field() ?>
                              <input type="hidden" name="status" value="in_chair">
                              <input type="hidden" name="expected_status" value="ready">
                              <input type="hidden" name="return_to" value="dashboard">
                              <button type="submit" class="button primary transition-btn" aria-label="<?= esc(lang('UI.appointmentActionStartVisit').($name !== '' ? ' - '.$name : ''), 'attr') ?>">
                                <?= esc(lang('UI.appointmentActionStartVisit')) ?>
                              </button>
                            </form>
                          <?php else: ?>
                            <a class="button secondary" href="<?= esc(workflow_context_url('appointment/'.$visitId, 'dashboard')) ?>" aria-label="<?= esc(lang('UI.appointmentActionView').($name !== '' ? ' - '.$name : ''), 'attr') ?>">
                              <?= esc(lang('UI.appointmentActionView')) ?>
                            </a>
                          <?php endif; ?>
                        <?php else: ?>
                          <a class="button secondary" href="<?= esc(workflow_context_url('appointment/'.$visitId, 'dashboard')) ?>" aria-label="<?= esc(lang('UI.appointmentActionView').($name !== '' ? ' - '.$name : ''), 'attr') ?>">
                            <?= esc(lang('UI.appointmentActionView')) ?>
                          </a>
                        <?php endif; ?>
                      <?php else: ?>
                        <a class="button secondary" href="<?= esc(workflow_context_url('appointment/'.$visitId, 'dashboard')) ?>" aria-label="<?= esc(lang('UI.appointmentActionView').($name !== '' ? ' - '.$name : ''), 'attr') ?>">
                          <?= esc(lang('UI.appointmentActionView')) ?>
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </article>
                <?php endforeach; ?>
              </div>
            </div>
            <p class="filter-empty" hidden><?= esc(lang('Studio.emptyList')) ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <div class="desk-footer">
          <a class="text-link" href="<?= base_url('appointments') ?>"><?= esc(lang('Studio.allVisits')) ?></a>
          <?php if ($studioCanVisits): ?>
            <a class="text-link" href="<?= base_url('appointment/calendar') ?>">
              <?= $studioIcon('calendar') ?>
              <span><?= esc(lang('Studio.calendar')) ?></span>
            </a>
          <?php endif; ?>
          <span><?= esc(lang('Studio.today')) ?> · <?= esc(formatDate(date('Y-m-d'))) ?></span>
        </div>
      <?php else: ?>
        <div class="empty-agenda">
          <h3><?= esc(lang('Studio.quick')) ?></h3>
          <p><?= esc(lang('Studio.records')) ?></p>
        </div>
      <?php endif; ?>
    </section>

    <!-- Side Column: 3 Canonical Attention Queues (Desktop Rail & Mobile Disclosure) + Recent Patients -->
    <aside class="attention-panel" aria-labelledby="attention-title">
      <!-- Desktop Rail -->
      <div class="attention-rail hidden md:block">
        <h2 id="attention-title" class="attention-rail-heading"><?= esc(lang('Studio.attention')) ?></h2>
        <div class="attention-queue-list">
          <?php foreach ([
            ['examinations', 'examination?documentation=ready_to_sign', 'notes', (int)($studioDocs['ready_to_sign_count'] ?? 0)],
            ['appointments', 'appointments?view=all', 'recover', (int)($studioRecovery['count'] ?? 0)],
            ['treatments', 'treatment', 'accepted', (int)($studioAccepted['count'] ?? 0)]
          ] as [$module, $url, $label, $count]): if (!$v4Can($module, 'view')) continue; ?>
            <a class="attention-queue-link attention-queue-link--<?= $label ?>" href="<?= base_url($url) ?>">
              <span class="attention-queue-label">
                <span class="attention-queue-dot"></span>
                <span><?= esc(lang('Studio.'.$label.'Task')) ?></span>
              </span>
              <span class="attention-queue-count" dir="ltr"><?= $count ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Mobile Collapsible Disclosure -->
      <details class="attention-disclosure md:hidden" open>
        <summary class="attention-summary">
          <span class="flex items-center gap-2">
            <?= $studioIcon('chart') ?>
            <span><?= esc(lang('Studio.attention')) ?></span>
          </span>
          <?= $studioIcon('down') ?>
        </summary>
        <div class="attention-disclosure-body">
          <?php foreach ([
            ['examinations', 'examination?documentation=ready_to_sign', 'notes', (int)($studioDocs['ready_to_sign_count'] ?? 0)],
            ['appointments', 'appointments?view=all', 'recover', (int)($studioRecovery['count'] ?? 0)],
            ['treatments', 'treatment', 'accepted', (int)($studioAccepted['count'] ?? 0)]
          ] as [$module, $url, $label, $count]): if (!$v4Can($module, 'view')) continue; ?>
            <a class="attention-queue-link attention-queue-link--<?= $label ?>" href="<?= base_url($url) ?>">
              <span class="attention-queue-label">
                <span class="attention-queue-dot"></span>
                <span><?= esc(lang('Studio.'.$label.'Task')) ?></span>
              </span>
              <span class="attention-queue-count" dir="ltr"><?= $count ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </details>

      <!-- Recent Patients Access (Preserved context and permissions) -->
      <?php if ($v4Can('patients', 'view')): ?>
        <section class="recent-patients-card" aria-labelledby="recent-title">
          <div class="recent-patients-header">
            <h3 id="recent-title" class="recent-patients-title"><?= esc(lang('Studio.recent')) ?></h3>
            <a class="recent-patients-link" href="<?= base_url('patients') ?>"><?= esc(lang('Studio.registry')) ?></a>
          </div>
          <div class="recent-patients-list">
            <?php foreach (array_slice($recent_patients ?? [], 0, 4) as $i => $person):
              $name = trim(($person['first_name'] ?? '').' '.($person['last_name'] ?? ''));
            ?>
              <a class="recent-patient-item" href="<?= esc(workflow_context_url('patient/'.(int)$person['id'], 'dashboard')) ?>">
                <span class="patient-avatar avatar-tone-<?= $i % 4 ?>"><?= esc($studioInitials($name)) ?></span>
                <span class="patient-info">
                  <strong class="patient-name"><?= esc($name) ?></strong>
                  <small class="patient-code" dir="ltr"><?= esc($person['patient_id'] ?? '') ?></small>
                </span>
              </a>
            <?php endforeach; ?>
            <?php if (empty($recent_patients)): ?>
              <p class="filter-empty"><?= esc(lang('Studio.noPatients')) ?></p>
            <?php endif; ?>
          </div>
        </section>
      <?php endif; ?>
    </aside>
  </div>
</div>

<?php if ($v4Can('patients', 'create')): ?>
  <?= view('patient/partials/studio_intake_dialog', ['studioIcon' => $studioIcon, 'intakeReturn' => 'dashboard', 'studioCanCreatePatient' => true]) ?>
<?php endif ?>
<?php if ($v4Can('appointments', 'create')): ?>
  <?= view('appointment/partials/studio_create_dialog', array_merge($this->getData(), ['studioIcon' => $studioIcon, 'bookingReturnTo' => 'dashboard', 'studioCanCreateAppointment' => true])) ?>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<div id="today-transitions-config" hidden
  data-action-arrival="<?= esc(lang('UI.appointmentActionArrival'), 'attr') ?>"
  data-action-ready="<?= esc(lang('UI.appointmentActionReady'), 'attr') ?>"
  data-action-start-visit="<?= esc(lang('UI.appointmentActionStartVisit'), 'attr') ?>"
  data-action-ready-for-reception="<?= esc(lang('UI.appointmentActionReadyForReception'), 'attr') ?>"
  data-action-view="<?= esc(lang('UI.appointmentActionView'), 'attr') ?>"
  data-status-updating="<?= esc(lang('UI.appointmentUpdating'), 'attr') ?>"
  data-status-stale="<?= esc(lang('UI.appointmentStatusStale'), 'attr') ?>"
  data-status-error="<?= esc(lang('UI.appointmentTransitionError'), 'attr') ?>"
  data-status-uncertain="<?= esc(lang('UI.appointmentUncertainOutcome'), 'attr') ?>"
  data-base-url="<?= esc(base_url('appointment/'), 'attr') ?>"
  data-can-edit="<?= $studioCanEditVisits ? '1' : '0' ?>"
  <?php foreach ($studioStageLabels as $s => $lbl): ?>
  data-label-<?= esc($s, 'attr') ?>="<?= esc(lang('UI.'.$lbl), 'attr') ?>"
  <?php endforeach; ?>
></div>
<script src="<?= base_url('assets/js/studio-today-transitions.js') ?>?v=<?= esc(asset_version('assets/js/studio-today-transitions.js')) ?>" defer></script>
<?= $this->endSection() ?>
