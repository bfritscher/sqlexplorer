<?php echo $html->script('codemirror/codemirror', array('inline' => false)); ?>
<?php $html->scriptStart(array('inline' => false));?>
$(document).ready(
	function() {
		cm = CodeMirror.fromTextArea('QuestionSql',
			{parserfile: "parsesql.js",
			 stylesheet: "<?php echo $html->url('/');?>css/codemirror/sqlcolors.css",
			 path: "<?php echo $html->url('/');?>js/codemirror/",
			 textWrapping: false,
			 readOnly: true,
			 height: 'dynamic',
			 indentUnit: 0
			 });
	}
);
<?php
$html->scriptEnd();?>
<div class="questions view">
<h2><?php  __('Question');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $question['Question']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Chapter'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($question['Chapter']['name'], array('controller' => 'chapters', 'action' => 'view', $question['Chapter']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Text'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $question['Question']['text']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Schema'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $question['Question']['schema']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Sql'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<textarea id="QuestionSql"><?php echo $question['Question']['sql']; ?></textarea>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Order No'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $question['Question']['order_no']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Variant'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $question['Question']['variant']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Assignment'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($question['Assignment']['name'], array('controller' => 'assignments', 'action' => 'view', $question['Assignment']['name'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $question['Question']['modified']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Question', true), array('action' => 'edit', $question['Question']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Question', true), array('action' => 'delete', $question['Question']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $question['Question']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Questions', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Question', true), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Chapters', true), array('controller' => 'chapters', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Chapter', true), array('controller' => 'chapters', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Assignments', true), array('controller' => 'assignments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Assignment', true), array('controller' => 'assignments', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Question Tests', true), array('controller' => 'question_tests', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Question Test', true), array('controller' => 'question_tests', 'action' => 'add',  $question['Question']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Questions', true), array('controller' => 'questions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Tps', true), array('controller' => 'tps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Tp', true), array('controller' => 'tps', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php __('Related Question Tests');?></h3>
	<?php if (!empty($question['QuestionTest'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Id'); ?></th>
		<th><?php __('Sql'); ?></th>
		<th><?php __('Question Id'); ?></th>
		<th><?php __('Last Result'); ?></th>
		<th><?php __('Modified'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($question['QuestionTest'] as $questionTest):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $questionTest['id'];?></td>
			<td><?php echo $questionTest['sql'];?></td>
			<td><?php echo $questionTest['question_id'];?></td>
			<td><?php echo $questionTest['last_result'];?></td>
			<td><?php echo $questionTest['modified'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'question_tests', 'action' => 'view', $questionTest['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'question_tests', 'action' => 'edit', $questionTest['id'])); ?>
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'question_tests', 'action' => 'delete', $questionTest['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $questionTest['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
<div class="related">
	<h3><?php __('Related Tps');?></h3>
	<?php if (!empty($question['Tp'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Id'); ?></th>
		<th><?php __('When'); ?></th>
		<th><?php __('Name'); ?></th>
		<th><?php __('Comment'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($question['Tp'] as $tp):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $tp['id'];?></td>
			<td><?php echo $tp['when'];?></td>
			<td><?php echo $tp['name'];?></td>
			<td><?php echo $tp['comment'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'tps', 'action' => 'view', $tp['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'tps', 'action' => 'edit', $tp['id'])); ?>
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'tps', 'action' => 'delete', $tp['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $tp['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
