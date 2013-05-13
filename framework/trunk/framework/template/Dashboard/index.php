You are now logged in as <b><?= $this->Auth->first_name() ?> <?= $this->Auth->last_name() ?></b>.

<a href="<?= $this->page_link('user', 'logout') ?>">Log out</a>.

<p>PHP Time</p>

<? pr(date('r')); ?>

<p>MySQL Time</p>

<?php $mysql_time = $this->load_helper('Db')->get_row("SELECT NOW() AS `time`"); pr($mysql_time['time']); ?>

<? pr($this->request); ?>