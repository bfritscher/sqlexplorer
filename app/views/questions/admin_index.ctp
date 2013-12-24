<div class="questions index">
	<h2><?php __('Questions');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('chapter_id');?></th>
			<th><?php echo $this->Paginator->sort('assignment_name');?></th>
			<th><?php echo $this->Paginator->sort('text');?></th>
			<th><?php echo $this->Paginator->sort('schema');?></th>
			<th><?php echo $this->Paginator->sort('sql');?></th>
			<th><?php echo $this->Paginator->sort('order_no');?></th>
			<th><?php echo $this->Paginator->sort('variant');?></th>
			<th><?php echo $this->Paginator->sort('modified');?></th>
			<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($questions as $question):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $question['Question']['id']; ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($question['Chapter']['name'], array('controller' => 'chapters', 'action' => 'view', $question['Chapter']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($question['Assignment']['name'], array('controller' => 'assignments', 'action' => 'view', $question['Assignment']['name'])); ?>
		</td>
		<td><?php echo $question['Question']['text']; ?>&nbsp;</td>
		<td><?php echo $question['Question']['schema']; ?>&nbsp;</td>
		<td><pre><?php echo $question['Question']['sql']; ?>&nbsp;</pre></td>
		<td><?php echo $question['Question']['order_no']; ?>&nbsp;</td>
		<td><?php echo $question['Question']['variant']; ?>&nbsp;</td>
		<td><?php echo $question['Question']['modified']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View', true), array('action' => 'view', $question['Question']['id'])); ?>
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $question['Question']['id'])); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $question['Question']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $question['Question']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Question', true), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Chapters', true), array('controller' => 'chapters', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Chapter', true), array('controller' => 'chapters', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Assignments', true), array('controller' => 'assignments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Assignment', true), array('controller' => 'assignments', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Question Tests', true), array('controller' => 'question_tests', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Question Test', true), array('controller' => 'question_tests', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Questions', true), array('controller' => 'questions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Tps', true), array('controller' => 'tps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Tp', true), array('controller' => 'tps', 'action' => 'add')); ?> </li>
	</ul>
</div>