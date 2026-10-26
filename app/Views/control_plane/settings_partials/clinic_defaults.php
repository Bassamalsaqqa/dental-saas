<?php $defaultTimezone = $settings['default_timezone'] ?? 'Asia/Jerusalem';$defaultDateFormat = $settings['default_date_format'] ?? 'd/m/Y';$defaultTimeFormat = (string) ($settings['default_time_format'] ?? '12');$defaultCurrency = $settings['default_currency'] ?? 'ILS';$defaultCurrencyPosition = $settings['default_currency_position'] ?? 'after'; ?>
<section id="tab-system-preferences" class="settings-content" hidden aria-labelledby="nav-system-preferences">
<?php $settingOpen('controlplane/settings/tenant-defaults','Defaults'); ?>
<p class="settings-context">
<?= $st('NewClinics') ?>
</p>
<input type="hidden" name="redirect_tab" value="system-preferences">
<input type="hidden" name="default_working_hours_start" value="<?= esc($settings['default_working_hours_start'] ?? '09:00') ?>">
        <input type="hidden" name="default_working_hours_end" value="<?= esc($settings['default_working_hours_end'] ?? '17:00') ?>">
        <?php $hiddenWorkingDefaults = $settings['default_working_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']; ?>
        <?php foreach ($hiddenWorkingDefaults as $hiddenDay): ?>
            <input type="hidden" name="default_working_days[]" value="<?= esc($hiddenDay) ?>">
        <?php endforeach; ?>
        <input type="hidden" name="default_appointment_duration" value="<?= esc((int) ($settings['default_appointment_duration'] ?? 30)) ?>">
        <input type="hidden" name="default_booking_chair_max" value="<?= esc((int) ($settings['default_booking_chair_max'] ?? 3)) ?>">

        <div class="settings-fields">
<div class="form-field">
<label for="setting-default_timezone">
<?= $st('Timezone') ?>
</label>
<select name="default_timezone" id="setting-default_timezone" required>
                            <?php $defaultTimezone = $settings['default_timezone'] ?? 'Asia/Jerusalem'; ?>
                            <option value="UTC" <?= $defaultTimezone === 'UTC' ? 'selected' : '' ?>>UTC</option>
                            <option value="America/New_York" <?= $defaultTimezone === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
                            <option value="America/Chicago" <?= $defaultTimezone === 'America/Chicago' ? 'selected' : '' ?>>America/Chicago</option>
                            <option value="America/Denver" <?= $defaultTimezone === 'America/Denver' ? 'selected' : '' ?>>America/Denver</option>
                            <option value="America/Los_Angeles" <?= $defaultTimezone === 'America/Los_Angeles' ? 'selected' : '' ?>>America/Los_Angeles</option>
                            <option value="America/Anchorage" <?= $defaultTimezone === 'America/Anchorage' ? 'selected' : '' ?>>America/Anchorage</option>
                            <option value="Pacific/Honolulu" <?= $defaultTimezone === 'Pacific/Honolulu' ? 'selected' : '' ?>>Pacific/Honolulu</option>
                            <option value="Europe/London" <?= $defaultTimezone === 'Europe/London' ? 'selected' : '' ?>>Europe/London</option>
                            <option value="Europe/Paris" <?= $defaultTimezone === 'Europe/Paris' ? 'selected' : '' ?>>Europe/Paris</option>
                            <option value="Europe/Berlin" <?= $defaultTimezone === 'Europe/Berlin' ? 'selected' : '' ?>>Europe/Berlin</option>
                            <option value="Europe/Rome" <?= $defaultTimezone === 'Europe/Rome' ? 'selected' : '' ?>>Europe/Rome</option>
                            <option value="Europe/Madrid" <?= $defaultTimezone === 'Europe/Madrid' ? 'selected' : '' ?>>Europe/Madrid</option>
                            <option value="Europe/Amsterdam" <?= $defaultTimezone === 'Europe/Amsterdam' ? 'selected' : '' ?>>Europe/Amsterdam</option>
                            <option value="Europe/Zurich" <?= $defaultTimezone === 'Europe/Zurich' ? 'selected' : '' ?>>Europe/Zurich</option>
                            <option value="Europe/Vienna" <?= $defaultTimezone === 'Europe/Vienna' ? 'selected' : '' ?>>Europe/Vienna</option>
                            <option value="Europe/Stockholm" <?= $defaultTimezone === 'Europe/Stockholm' ? 'selected' : '' ?>>Europe/Stockholm</option>
                            <option value="Europe/Oslo" <?= $defaultTimezone === 'Europe/Oslo' ? 'selected' : '' ?>>Europe/Oslo</option>
                            <option value="Europe/Copenhagen" <?= $defaultTimezone === 'Europe/Copenhagen' ? 'selected' : '' ?>>Europe/Copenhagen</option>
                            <option value="Europe/Helsinki" <?= $defaultTimezone === 'Europe/Helsinki' ? 'selected' : '' ?>>Europe/Helsinki</option>
                            <option value="Europe/Warsaw" <?= $defaultTimezone === 'Europe/Warsaw' ? 'selected' : '' ?>>Europe/Warsaw</option>
                            <option value="Europe/Prague" <?= $defaultTimezone === 'Europe/Prague' ? 'selected' : '' ?>>Europe/Prague</option>
                            <option value="Europe/Budapest" <?= $defaultTimezone === 'Europe/Budapest' ? 'selected' : '' ?>>Europe/Budapest</option>
                            <option value="Europe/Athens" <?= $defaultTimezone === 'Europe/Athens' ? 'selected' : '' ?>>Europe/Athens</option>
                            <option value="Europe/Istanbul" <?= $defaultTimezone === 'Europe/Istanbul' ? 'selected' : '' ?>>Europe/Istanbul</option>
                            <option value="Europe/Moscow" <?= $defaultTimezone === 'Europe/Moscow' ? 'selected' : '' ?>>Europe/Moscow</option>
                            <option value="Asia/Tokyo" <?= $defaultTimezone === 'Asia/Tokyo' ? 'selected' : '' ?>>Asia/Tokyo</option>
                            <option value="Asia/Shanghai" <?= $defaultTimezone === 'Asia/Shanghai' ? 'selected' : '' ?>>Asia/Shanghai</option>
                            <option value="Asia/Hong_Kong" <?= $defaultTimezone === 'Asia/Hong_Kong' ? 'selected' : '' ?>>Asia/Hong_Kong</option>
                            <option value="Asia/Singapore" <?= $defaultTimezone === 'Asia/Singapore' ? 'selected' : '' ?>>Asia/Singapore</option>
                            <option value="Asia/Seoul" <?= $defaultTimezone === 'Asia/Seoul' ? 'selected' : '' ?>>Asia/Seoul</option>
                            <option value="Asia/Taipei" <?= $defaultTimezone === 'Asia/Taipei' ? 'selected' : '' ?>>Asia/Taipei</option>
                            <option value="Asia/Bangkok" <?= $defaultTimezone === 'Asia/Bangkok' ? 'selected' : '' ?>>Asia/Bangkok</option>
                            <option value="Asia/Jakarta" <?= $defaultTimezone === 'Asia/Jakarta' ? 'selected' : '' ?>>Asia/Jakarta</option>
                            <option value="Asia/Kolkata" <?= $defaultTimezone === 'Asia/Kolkata' ? 'selected' : '' ?>>Asia/Kolkata</option>
                            <option value="Asia/Dubai" <?= $defaultTimezone === 'Asia/Dubai' ? 'selected' : '' ?>>Asia/Dubai</option>
                            <option value="Asia/Riyadh" <?= $defaultTimezone === 'Asia/Riyadh' ? 'selected' : '' ?>>Asia/Riyadh</option>
                            <option value="Asia/Jerusalem" <?= $defaultTimezone === 'Asia/Jerusalem' ? 'selected' : '' ?>>Asia/Jerusalem</option>
                            <option value="Asia/Gaza" <?= $defaultTimezone === 'Asia/Gaza' ? 'selected' : '' ?>>Asia/Gaza</option>
                            <option value="Asia/Tehran" <?= $defaultTimezone === 'Asia/Tehran' ? 'selected' : '' ?>>Asia/Tehran</option>
                            <option value="Asia/Karachi" <?= $defaultTimezone === 'Asia/Karachi' ? 'selected' : '' ?>>Asia/Karachi</option>
                            <option value="Asia/Dhaka" <?= $defaultTimezone === 'Asia/Dhaka' ? 'selected' : '' ?>>Asia/Dhaka</option>
                            <option value="Australia/Sydney" <?= $defaultTimezone === 'Australia/Sydney' ? 'selected' : '' ?>>Australia/Sydney</option>
                            <option value="Australia/Melbourne" <?= $defaultTimezone === 'Australia/Melbourne' ? 'selected' : '' ?>>Australia/Melbourne</option>
                            <option value="Australia/Brisbane" <?= $defaultTimezone === 'Australia/Brisbane' ? 'selected' : '' ?>>Australia/Brisbane</option>
                            <option value="Australia/Perth" <?= $defaultTimezone === 'Australia/Perth' ? 'selected' : '' ?>>Australia/Perth</option>
                            <option value="Australia/Adelaide" <?= $defaultTimezone === 'Australia/Adelaide' ? 'selected' : '' ?>>Australia/Adelaide</option>
                            <option value="Australia/Darwin" <?= $defaultTimezone === 'Australia/Darwin' ? 'selected' : '' ?>>Australia/Darwin</option>
                            <option value="Pacific/Auckland" <?= $defaultTimezone === 'Pacific/Auckland' ? 'selected' : '' ?>>Pacific/Auckland</option>
                            <option value="Pacific/Fiji" <?= $defaultTimezone === 'Pacific/Fiji' ? 'selected' : '' ?>>Pacific/Fiji</option>
                            <option value="America/Sao_Paulo" <?= $defaultTimezone === 'America/Sao_Paulo' ? 'selected' : '' ?>>America/Sao_Paulo</option>
                            <option value="America/Buenos_Aires" <?= $defaultTimezone === 'America/Buenos_Aires' ? 'selected' : '' ?>>America/Buenos_Aires</option>
                            <option value="America/Lima" <?= $defaultTimezone === 'America/Lima' ? 'selected' : '' ?>>America/Lima</option>
                            <option value="America/Bogota" <?= $defaultTimezone === 'America/Bogota' ? 'selected' : '' ?>>America/Bogota</option>
                            <option value="America/Mexico_City" <?= $defaultTimezone === 'America/Mexico_City' ? 'selected' : '' ?>>America/Mexico_City</option>
                            <option value="America/Toronto" <?= $defaultTimezone === 'America/Toronto' ? 'selected' : '' ?>>America/Toronto</option>
                            <option value="America/Vancouver" <?= $defaultTimezone === 'America/Vancouver' ? 'selected' : '' ?>>America/Vancouver</option>
                            <option value="America/Montreal" <?= $defaultTimezone === 'America/Montreal' ? 'selected' : '' ?>>America/Montreal</option>
                            <option value="Africa/Cairo" <?= $defaultTimezone === 'Africa/Cairo' ? 'selected' : '' ?>>Africa/Cairo</option>
                            <option value="Africa/Johannesburg" <?= $defaultTimezone === 'Africa/Johannesburg' ? 'selected' : '' ?>>Africa/Johannesburg</option>
                            <option value="Africa/Lagos" <?= $defaultTimezone === 'Africa/Lagos' ? 'selected' : '' ?>>Africa/Lagos</option>
                            <option value="Africa/Casablanca" <?= $defaultTimezone === 'Africa/Casablanca' ? 'selected' : '' ?>>Africa/Casablanca</option>
                        </select>
</div>
<div class="form-field">
<label for="setting-default_date_format">
<?= $st('DateFormat') ?>
</label>
<select name="default_date_format" id="setting-default_date_format" required>
                            <option value="Y-m-d" <?= $defaultDateFormat === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                            <option value="m/d/Y" <?= $defaultDateFormat === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                            <option value="d/m/Y" <?= $defaultDateFormat === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                        </select>
</div>
<div class="form-field">
<label for="setting-default_time_format">
<?= $st('TimeFormat') ?>
</label>
<select name="default_time_format" id="setting-default_time_format" required>
                            <option value="24" <?= $defaultTimeFormat === '24' ? 'selected' : '' ?>><?= $st('Hour24') ?></option>
                            <option value="12" <?= $defaultTimeFormat === '12' ? 'selected' : '' ?>><?= $st('Hour12') ?></option>
                        </select>
</div>
<div class="form-field">
<label for="setting-default_currency">
<?= $st('Currency') ?>
</label>
<select name="default_currency" id="setting-default_currency" required>
                            <option value="ILS" <?= $defaultCurrency == 'ILS' ? 'selected' : '' ?>><?= $st('ILS') ?></option>
                            <option value="USD" <?= $defaultCurrency == 'USD' ? 'selected' : '' ?>><?= $st('USD') ?></option>
                            <option value="JOD" <?= $defaultCurrency == 'JOD' ? 'selected' : '' ?>><?= $st('JOD') ?></option>
                            <option value="EUR" <?= $defaultCurrency == 'EUR' ? 'selected' : '' ?>><?= $st('EUR') ?></option>
                            <option value="GBP" <?= $defaultCurrency == 'GBP' ? 'selected' : '' ?>><?= $st('GBP') ?></option>
                        </select>
</div>
<div class="form-field">
<label for="setting-default_currency_position">
<?= $st('CurrencyPosition') ?>
</label>
<select name="default_currency_position" id="setting-default_currency_position" required>
                            <option value="before" <?= $defaultCurrencyPosition === 'before' ? 'selected' : '' ?>><?= $st('Before') ?></option>
                            <option value="after" <?= $defaultCurrencyPosition === 'after' ? 'selected' : '' ?>><?= $st('After') ?></option>
                        </select>
</div>
</div>
<?php $settingClose(); ?>
</section>
<section id="tab-working-hours" class="settings-content" hidden aria-labelledby="nav-working-hours">
<?php $settingOpen('controlplane/settings/tenant-defaults','Hours'); ?>
<p class="settings-context">
<?= $st('NewClinics') ?>
</p>
<input type="hidden" name="redirect_tab" value="working-hours">
<input type="hidden" name="default_timezone" value="<?= esc($settings['default_timezone']??'Asia/Jerusalem','attr') ?>">
<input type="hidden" name="default_date_format" value="<?= esc($settings['default_date_format']??'d/m/Y','attr') ?>">
<input type="hidden" name="default_time_format" value="<?= esc($settings['default_time_format']??'12','attr') ?>">
<input type="hidden" name="default_currency" value="<?= esc($settings['default_currency']??'ILS','attr') ?>">
<input type="hidden" name="default_currency_position" value="<?= esc($settings['default_currency_position']??'after','attr') ?>">
<div class="settings-fields">
<?php $settingField('default_working_hours_start','Opens','time','required data-clinic-time="1"');$settingField('default_working_hours_end','Closes','time','required data-clinic-time="1"');$settingField('default_appointment_duration','Slot','number','required min="15" max="479"');$settingField('default_booking_chair_max','Chairs','number','required min="1" max="3"'); ?>
</div>
<fieldset class="settings-days">
<legend>
<?= $st('Days') ?>
</legend>
<?php $workingDefaults=$settings['default_working_days']??['monday','tuesday','wednesday','thursday','friday'];foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day): ?>
<label>
<input type="checkbox" name="default_working_days[]" value="<?= esc($day) ?>" <?= in_array($day,$workingDefaults,true)?'checked':'' ?>>
<span>
<?= $st(ucfirst($day)) ?>
</span>
</label>
<?php endforeach; ?>
</fieldset>
<?php $settingClose(); ?>
</section>