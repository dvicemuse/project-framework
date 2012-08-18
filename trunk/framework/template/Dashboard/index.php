You are now logged in as <b><?= $this->Auth->first_name() ?> <?= $this->Auth->last_name() ?></b>.

<a href="<?= $this->page_link('user', 'logout') ?>">Log out</a>.

<? pr($this->request); ?>