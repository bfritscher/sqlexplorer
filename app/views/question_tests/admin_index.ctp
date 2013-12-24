<div class="questionTests index">
	<h2><?php __('Question Tests');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('sql');?></th>
			<th><?php echo $this->Paginator->sort('last_result');?></th>
			<th><?php echo $this->Paginator->sort('modified');?></th>
			<th><?php echo $this->Paginator->sort('assignment_name');?></th>
			<th><?php echo $this->Paginator->sort('chapter_id');?></th>
			<th><?php echo $this->Paginator->sort('question_order_no');?></th>
			<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($questionTests as $questionTest):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $questionTest['QuestionTest']['id']; ?>&nbsp;</td>
		<td><?php echo $questionTest['QuestionTest']['sql']; ?>&nbsp;</td>
		<td><?php echo $questionTest['QuestionTest']['last_result']; ?>&nbsp;</td>
		<td><?php echo $questionTest['QuestionTest']['modified']; ?>&nbsp;</td>
		<td><?php echo $questionTest['QuestionTest']['assignment_name']; ?>&nbsp;</td>
		<td><?php echo $questionTest['QuestionTest']['chapter_id']; ?>&nbsp;</td>
		<td><?php echo $questionTest['QuestionTest']['question_order_no']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $questionTest['QuestionTest']['id'])); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $questionTest['QuestionTest']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $questionTest['QuestionTest']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Question Test', true), array('action' => 'add')); ?></li>
	</ul>
</div>