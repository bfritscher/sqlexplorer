<div class="tps view">
<h2><?php  __('Tp');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $tp['Tp']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('When'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $tp['Tp']['when']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $tp['Tp']['name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Comment'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $tp['Tp']['comment']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Type'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $tp['Tp']['type']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Tp', true), array('action' => 'edit', $tp['Tp']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Tp', true), array('action' => 'delete', $tp['Tp']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $tp['Tp']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Tps', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Tp', true), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Questions', true), array('controller' => 'questions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Question', true), array('controller' => 'questions', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('Test', true), array('action' => 'test', $tp['Tp']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('SCORM package', true), array('action' => 'imsmanifest', $tp['Tp']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('SCORM package JSONP', true), array('action' => 'imsmanifest', $tp['Tp']['id'], 'jsonp')); ?> </li>
		<li><?php echo $this->Html->link(__('Slides', true), array('action' => 'slide', $tp['Tp']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Slides2 answers hidden', true), array('action' => 'slide2',  $tp['Tp']['id'])); ?></li>
		<li><?php echo $this->Html->link(__('PDF', true), array('action' => 'pdf', $tp['Tp']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('PDF no data', true), array('action' => 'pdf', $tp['Tp']['id'], 'nodata')); ?> </li>
		<li><?php echo $this->Html->link(__('Top', true), array('action' => 'top', $tp['Tp']['id'])); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php __('Related Questions');?> (<?php echo count($tp['Question'])?>)</h3>
	<?php if (!empty($tp['Question'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Id'); ?></th>
		<th><?php __('Chapter'); ?></th>
		<th><?php __('Text'); ?></th>
		<th><?php __('Schema'); ?></th>
		<th><?php __('Sql'); ?></th>
		<th><?php __('Assignment Name'); ?></th>
		<th><?php __('Order No'); ?></th>
		<th><?php __('Variant'); ?></th>
		<th><?php __('Modified'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($tp['Question'] as $question):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $i;?></td>
			<td><?php echo $question['id'];?></td>
			<td><?php echo $this->Html->link($question['Chapter']['name'], array('controller' => 'chapters', 'action' => 'view', $question['chapter_id']));?></td>
			<td><?php echo $question['text'];?></td>
			<td><?php echo $question['schema'];?></td>
			<td>schéma: <?php echo $question['schema'];?>
			<pre style="overflow:auto;width:400px;"><?php echo $question['sql'];?></pre></td>
			<td><?php echo $this->Html->link($question['assignment_name'], array('controller' => 'assignments', 'action' => 'view', $question['assignment_name']));?></td>
			<td><?php echo $question['order_no'];?></td>
			<td><?php echo $question['variant'];?></td>
			<td><?php echo $question['modified'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'questions', 'action' => 'view', $question['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'questions', 'action' => 'edit', $question['id'])); ?>
				<?php echo $this->Html->link(__('Remove', true), array('controller' => 'tps', 'action' => 'unlink', $tp['Tp']['id'], $question['id']), null, sprintf(__('Are you sure you want to remove # %s from this TP?', true), $question['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
