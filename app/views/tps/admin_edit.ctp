<div class="tps form">
<?php echo $this->Form->create('Tp');?>
	<fieldset>
 		<legend><?php __('Admin Edit Tp'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('when');
		echo $this->Form->input('name');
		echo $this->Form->input('comment');
		echo $this->Form->input('type');
		//echo $this->Form->input('Question');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $this->Form->value('Tp.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Tp.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Tps', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Questions', true), array('controller' => 'questions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Question', true), array('controller' => 'questions', 'action' => 'add')); ?> </li>
	</ul>
</div>