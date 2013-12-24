<div class="assignments form">
<?php echo $this->Form->create('Assignment', array('type' => 'file'));?>
	<fieldset>
 		<legend><?php __('Admin Add Assignment'); ?></legend>
	<?php
		echo $this->Form->input('name', array( 'type'=>'text'));
		echo $this->Form->input('description');
		echo $this->Form->input('chapter_id');
		echo $this->Form->input('order_no');
		echo $this->Form->input('schema', array( 'type'=>'file'));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Assignments', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Chapters', true), array('controller' => 'chapters', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Chapter', true), array('controller' => 'chapters', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Questions', true), array('controller' => 'questions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Question', true), array('controller' => 'questions', 'action' => 'add')); ?> </li>
	</ul>
</div>