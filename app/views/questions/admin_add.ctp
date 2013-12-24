<?php echo $html->script('codemirror/codemirror', array('inline' => false)); ?>
<?php $html->scriptStart(array('inline' => false));?>
function lookup() {
	$.post('<?php echo $html->url('/admin/questions/minnb');?>',
		{'chapter_id':$("#QuestionChapterId").val(), 'assignment_name':$("#QuestionAssignmentId").val()},
		function(data) {
			$('#QuestionOrderNo').val(data);
		}
	);
}

$(document).ready(
	function() {
		$("#QuestionAssignmentId, #QuestionChapterId").change(lookup);
		lookup();
		cm = CodeMirror.fromTextArea('QuestionSql',
			{parserfile: "parsesql.js",
			 stylesheet: "<?php echo $html->url('/');?>css/codemirror/sqlcolors.css",
			 path: "<?php echo $html->url('/');?>js/codemirror/",
			 textWrapping: false,
			 height: 'dynamic',
			 indentUnit: 0,
			 content: '\n'
			 });
	}
);
<?php
$html->scriptEnd();?>
<div class="questions form">
<?php echo $this->Form->create('Question');?>
	<fieldset>
 		<legend><?php __('Admin Add Question'); ?></legend>
	<?php
		echo $this->Form->input('assignment_id', array('default'=> isset($this->params['pass'][0]) ? $this->params['pass'][0] : null));	
		echo $this->Form->input('chapter_id', array('default'=> isset($this->params['pass'][1]) ? $this->params['pass'][1] : null));
		echo $this->Form->input('text');
		echo $this->Form->input('sql');
		echo $this->Form->input('order_no');		
		echo $this->Form->input('variant');		
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Questions', true), array('action' => 'index'));?></li>
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