
<a href="<?= $this->page_link(strtolower($this->request->controller_name), 'manage') ?>">Add New</a>

<table>
	<?php $c = 0; ?>
	<?php if(is_array($this->records)){ ?>
		<?php foreach($this->records as $record){ ?>
			<?php if($c == 0){ ?>
				<thead>
					<tr>
						<?php foreach($record as $k => $r){ ?>
							<th><?= $k ?></th>
						<?php } ?>
						<th>&nbsp;</th>
					</tr>
				</thead>
			<?php $c++; } ?>
		
			<tbody>
				<tr>
					<?php foreach($record as $r){ ?>
						<td><?= $r ?></td>
					<?php } ?>
					<td>
						<a href="<?= $this->page_link(strtolower($this->request->controller_name), 'manage', current($record)) ?>">Edit</a>
						<a href="<?= $this->page_link(strtolower($this->request->controller_name), 'delete', current($record)) ?>">Delete</a>
					</td>
				</tr>
			</tbody>
		<?php } ?>
	<?php } ?>
</table>