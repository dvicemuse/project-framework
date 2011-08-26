<?php

	// User submitted login
	if(!empty($_POST['login_submit']))
	{
		// Pass in login info to login class
		if($this->User->log_user_in($_POST))
		{
			// Logged in, so send the user to the page they requested
			header("Location: {$this->page_link($this->info['current_module'], $this->info['currennt_page'])}");
			exit;
		}else{
			$this->add_flash('Incorrect username or password.');
		}
		// Redirect to keep the back button working
		header("Location: {$this->page_link($this->info['current_module'], $this->info['currennt_page'])}");
		exit;
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Please Log In</title>
	<link rel="stylesheet" type="text/css" href="<?= $this->css_url('log_in.css') ?>" />
	<script type="text/javascript" src="<?= $this->javascript_url('jquery.min.js') ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			// Focus on first text element
			$(":text:visible:enabled:first").focus();
		});
	</script>
</head>
<body>

<div class="width_container2">

	<div id="login_container">

		<h1>Please Log In</h1>

		<? $this->show_error(); ?>
		<? $this->show_flash(); ?>

		<form action="" method="post">

			<div class="field_row_wrapper">
				<h2>Email Address:</h2>
				<?= $this->User->Validate->print_field('user_email', 'Email', 'text', 'title="Enter your account email address." tabindex = "1" autocomplete="off" '); ?>
			</div>

			<div class="clear"></div>

			<div class="field_row_wrapper">
				<h2>Password:</h2>
				<a class="password_reset" href="<?= $this->page_link('user', 'reset_password') ?>" title="Reset your account password.">Forgot your password?</a>
				<?= $this->User->Validate->print_field('user_password', 'Password', 'password', 'style="width: 150px;" title="Enter your account password." tabindex = "2" autocomplete="off" '); ?>
			</div>

			<input type="submit" name="login_submit" value="Enter" class="submit_button" title="Submit you login information and go to the administration dashboard." tabindex = "3" />
			<div class="clear"></div>

		</form>
	</div>

</div>

</body>
</html>