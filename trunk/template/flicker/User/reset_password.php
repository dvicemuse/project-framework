
	<h1>Reset Your Account Password</h1>


	<? $this->show_error(); ?>
	<? $this->show_flash(); ?>

	<form method="post">

		<div class="field_row_wrapper">
			<h2>Email Address</h2>
			<?= $this->Validate->print_field('email', 'Email', 'text', 'title="Enter your account email address."'); ?>
		</div>

		<input type="submit" name="reset_submit" value="Send Password Reset Email" class="submit_button" title="Send a password reset link to your email address." />

		<div class="clear"></div>
		
		<div class="cancel_reset">Don't need to reset you password? <a href="<?= $this->config['web_path']?>/<?= $this->config['log_in_module'] ?>/">Log in now</a>.</div>
		
		<div class="clear"></div>

	</form>