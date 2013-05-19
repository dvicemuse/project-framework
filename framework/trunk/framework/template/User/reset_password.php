
	<h1>Reset Your Account Password</h1>

	<? $this->show_error(); ?>
	<? $this->show_flash(); ?>

	<form action="" method="post">

		<div class="field_row_wrapper">
			<h2>Email Address</h2>
			<?= $this->load_helper('Validate')->print_text('user_email', '', array('title' => 'Enter your account email address.')); ?>
		</div>

		<input type="submit" name="reset_submit" value="Send Password Reset Email" class="submit_button" title="Send a password reset link to your email address." />

		<div class="clear"></div>

		<div class="cancel_reset">Don't need to reset you password? <a href="<?= $this->page_link('') ?>">Go back</a>.</div>

		<div class="clear"></div>
		
		<input type="hidden" name="action" value="reset" />

	</form>