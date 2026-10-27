<?php if (! empty($messages)) : ?>
	<div class="access-message" role="status">
		<ul>
		<?php foreach ($messages as $message) : ?>
			<li><?= esc($message) ?></li>
		<?php endforeach ?>
		</ul>
	</div>
<?php endif ?>
