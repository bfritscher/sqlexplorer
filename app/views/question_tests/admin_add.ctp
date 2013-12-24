<?php echo $html->script('codemirror/codemirror', array('inline' => false)); ?>
<?php $html->scriptStart(array('inline' => false));?>
$(document).ready(
	function() {
		cm = CodeMirror.fromTextArea('QuestionTestSql',
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
<div class="questionTests form">
<?php echo $this->Form->create('QuestionTest', array('url'=>array('action'=>'add', $question_id)));?>
	<fieldset>
 		<legend><?php __('Admin Add Question Test'); ?></legend>
	<?php
		echo $this->Form->input('sql');
		echo $this->Form->input('comment');
		echo $this->Form->input('assignment_name', array('type'=>'select', 'options' => $assignments));
		echo $this->Form->input('chapter_id');
		echo $this->Form->input('question_order_no');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Question Tests', true), array('action' => 'index'));?></li>
	</ul>
</div>