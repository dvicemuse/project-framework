
	<h1>Update Password (<?= $this->user->email() ?>)</h1>

	<? $this->show_error(); ?>
	<? $this->show_flash(); ?>

	<form action="" method="post">

		<div class="field_row_wrapper">
			<h2>New Password:</h2>
			<?= $this->load_helper('Validate')->hide_errors(TRUE)->print_password('user_password', '', array('autocomplete' => 'off')); ?>
		</div>

		<div class="field_row_wrapper">
			<h2>Confirm Password:</h2>
			<?= $this->load_helper('Validate')->hide_errors(TRUE)->print_password('user_password_confirm', '', array('autocomplete' => 'off')); ?>
		</div>

		<input type="hidden" name="hash" value="<?= $this->info['raw_route'][2] ?>" />
		<input type="submit" name="update_submit" value="Update Account Password" class="submit_button" />

		<div class="clear"></div>

		<div class="cancel_reset">Don't need to update you password? <a href="<?= $this->page_link($this->config->path->log_in_controller) ?>">Cancel update</a>.</div>
		
		<div class="clear"></div>

	</form>

