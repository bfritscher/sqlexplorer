<?php echo $html->script('codemirror/codemirror', array('inline' => false)); ?>
<?php $html->scriptStart(array('inline' => false));?>
$(document).ready(
	function() {
		cm = CodeMirror.fromTextArea('QuestionSql',
			{parserfile: "parsesql.js",
			 stylesheet: "<?php echo $html->url('/');?>css/codemirror/sqlcolors.css",
			 path: "<?php echo $html->url('/');?>js/codemirror/",
			 textWrapping: false,
			 height: 'dynamic',
			 indentUnit: 0
			 });
	}
);
<?php
$html->scriptEnd();?>
<div class="questions form">
<?php echo $this->Form->create('Question');?>
	<fieldset>
 		<legend><?php __('Admin Edit Question'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('chapter_id');
		echo $this->Form->input('assignment_id');
		echo $this->Form->input('text');
		echo $this->Form->input('sql');
		echo $this->Form->input('order_no');
		echo $this->Form->input('variant');
		echo $this->Form->input('Tp');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('New Question Test', true), array('controller' => 'question_tests', 'action' => 'add', $this->data['Question']['id'])); ?> </li>
        <li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $this->Form->value('Question.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Question.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Questions', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Assignments', true), array('controller' => 'assignments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Question Tests', true), array('controller' => 'question_tests', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Questions', true), array('controller' => 'questions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Tps', true), array('controller' => 'tps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Tp', true), array('controller' => 'tps', 'action' => 'add')); ?> </li>
	</ul>
</div>