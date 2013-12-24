<?php
App::import('Vendor', 'PHPMarkdown', array('file' => 'PHPMarkdown/markdown.php'));
?>
<div class="assignments view">
<h2><?php echo $assignment['Assignment']['name']; ?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo Markdown($assignment['Assignment']['description']); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Chapter'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($assignment['Chapter']['name'], array('controller' => 'chapters', 'action' => 'view', $assignment['Chapter']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Order No'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $assignment['Assignment']['order_no']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $assignment['Assignment']['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Schéma'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $html->image('sql/schema_'.strtolower($assignment['Assignment']['name']).'.png', array('id'=>'schema')); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Tables'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
				$fix_permission = false;
				foreach($table_list as $table_name => $access){
					if($access == 't'){
						 $access = 'icon-check';
					}else{
						$access = 'icon-cross';
						$fix_permission = true;
					}
					echo "<div><div class='icon $access' style='float:left;'></div> $table_name</div>";
				}
				if($fix_permission){
					echo $this->Html->link('fix permission', array('action' => 'fix_permission', $assignment['Assignment']['name']));
				}
			?>
			&nbsp;
		</dd>
		
	</dl>		
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Assignment', true), array('action' => 'edit', $assignment['Assignment']['name'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Assignments', true), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Assignment', true), array('action' => 'delete', $assignment['Assignment']['name']), null, sprintf(__('Are you sure you want to delete # %s?', true), $assignment['Assignment']['name'])); ?> </li>
		<li><?php echo $this->Html->link(__('New Assignment', true), array('action' => 'add')); ?> </li>		
		<li><?php echo $this->Html->link(__('New Question', true), array('controller' => 'questions', 'action' => 'add', $assignment['Assignment']['name']));?></li>
		<li><?php echo $this->Html->link(__('Export Questions', true), array('action' => 'export', $assignment['Assignment']['name'])); ?> </li>
		<li><?php echo $this->Html->link(__('Test', true), array('action' => 'test', $assignment['Assignment']['name'])); ?> </li>
		<li><?php echo $this->Html->link(__('SCORM package', true), array('action' => 'imsmanifest', $assignment['Assignment']['name'])); ?> </li>
		<li><?php echo $this->Html->link(__('SCORM package JSONP', true), array('action' => 'imsmanifest', $assignment['Assignment']['name'], 'jsonp')); ?> </li>
		<li><?php echo $this->Html->link(__('Slides', true), array('action' => 'slide', $assignment['Assignment']['name'])); ?> </li>
		<li><?php echo $this->Html->link(__('Slides2 answers hidden', true), array('action' => 'slide2', $assignment['Assignment']['name'])); ?></li>
		<li><?php echo $this->Html->link(__('PDF', true), array('action' => 'pdf', $assignment['Assignment']['name'])); ?> </li>
		<li><?php echo $this->Html->link(__('PDF nodata', true), array('action' => 'pdf', $assignment['Assignment']['name'],'nodata')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php __('Related Questions');?></h3>
	<?php if (!empty($assignment['Question'])):?>
	<?php echo $this->Form->create('Question', array('url' => array('controller' => 'tps', 'action' => 'link')));?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th>&nbsp;</th>
		<th><?php __('Text'); ?></th>
		<th><?php __('Sql'); ?></th>
		<th><?php __('Order No'); ?></th>
		<th><?php __('Variant'); ?></th>
		<th><?php __('Modified'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		$chapter_ref = '';
		foreach ($assignment['Question'] as $question):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			if($chapter_ref != $question['Chapter']['name']){
			?>
				<tr><td colspan="7"><h4><?php echo $question['Chapter']['name'];?></h4></td></tr>
			<?php
				$chapter_ref = $question['Chapter']['name'];
			}
			?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->Form->checkbox($question['id']);?></td>
			<td><?php echo nl2br($question['text']);?></td>
			<td>schéma: <?php echo $question['schema'];?>
			<pre style="overflow:auto;width:400px;"><?php echo $question['sql'];?></pre></td>
			<td><?php echo $question['order_no'];?></td>
			<td><?php echo $question['variant'];?></td>
			<td><?php echo $question['modified'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('controller' => 'questions', 'action' => 'view', $question['id'])); ?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'questions', 'action' => 'edit', $question['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php echo $this->Form->input('Tp', array('type'=>'select'));?>	
<?php echo $this->Form->end('Add Selected To TP');?>
<?php endif; ?>
</div>
