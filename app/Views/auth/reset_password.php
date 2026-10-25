<?= view('auth/partials/access_head',['title'=>lang('Auth.reset_password_heading'),'locale'=>$locale??service('request')->getLocale()]) ?><header class="v4-access-heading"><h2><?= esc(lang('Auth.reset_password_heading')) ?></h2></header><div id="infoMessage" class="v4-access-alert" role="status"><?= $message ?></div><?php echo form_open('auth/reset_password/' . $code,['class'=>'v4-form v4-access-form']);?>

	<p>
		<label for="new_password"><?php echo sprintf(lang('Auth.reset_password_new_password_label'), $minPasswordLength);?></label> <br />
		<?php echo form_input($new_password);?>
	</p>

	<p>
		<?php echo form_label(lang('Auth.reset_password_new_password_confirm_label'), 'new_password_confirm');?> <br />
		<?php echo form_input($new_password_confirm);?>
	</p>

	<?php echo form_input($user_id);?>

	<p><?php echo form_submit('submit', lang('Auth.reset_password_submit_btn'));?></p>

<?php echo form_close();?>
<?= view('auth/partials/access_footer') ?>