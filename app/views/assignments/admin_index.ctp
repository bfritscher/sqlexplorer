<?php
$this->Html->script('jquery-ui-1.8.5.custom.sortable.min',array('inline' => false));
?>
<?php $html->scriptStart(array('inline' => false));?>
$(document).ready(function() {
	var sortable = $('tbody').sortable({
		axis:'y',
		containment:'parent',
		update: function(event, ui) {
			fixBandedRows();
			$.post('<?php echo $html->url('/admin/assignments/changeorder');?>', $(this).sortable("serialize"));
		}
	});
	fixBandedRows();
});

function fixBandedRows(){
	$('tbody > tr').each(function(index, el){
				index % 2 == 0 ? $(el).removeClass('altrow') : $(el).addClass('altrow');
		});
}

<?php $html->scriptEnd();?>
<div class="assignments index">
	<h2><?php __('Assignments');?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('DB');?></th>
			<th><?php echo $this->Paginator->sort('IMG');?></th>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('chapter_id');?></th>
			<th><?php echo $this->Paginator->sort('modified');?></th>
			<th class="actions"><?php __('Actions');?></th>
	</tr>
	</thead>
	<?php
	$i = 0;
	foreach ($assignments as $assignment):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
		$db_exists = in_array(strtolower($assignment['Assignment']['name']), $db_list);
		if($db_exists){
			unset($db_list[strtolower($assignment['Assignment']['name'])]);
		}
	?>
	<tr<?php echo $class;?> id="assignment_<?php echo $assignment['Assignment']['name']?>">
		<td><div class="icon icon-<?php echo  $db_exists ? 'check' : 'cross'?>"></div></td>
		<td><div class="icon icon-<?php echo file_exists(IMAGES . 'sql' . DS . 'schema_'.strtolower($assignment['Assignment']['name']) . '.png') ? 'check' : 'cross'?>"></div></td>
		<td><?php echo $this->Html->link($assignment['Assignment']['name'], array('action' => 'view', $assignment['Assignment']['name'])); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($assignment['Chapter']['name'], array('controller' => 'chapters', 'action' => 'view', $assignment['Chapter']['id'])); ?>
		</td>
		<td><?php echo $assignment['Assignment']['modified']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Slides', true), array('action' => 'slide', $assignment['Assignment']['name'])); ?>
			<?php echo $this->Html->link(__('View', true), array('action' => 'view', $assignment['Assignment']['name'])); ?>
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $assignment['Assignment']['name'])); ?>
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
	<p>Databases with no assignment:</p>
	<?php 
		foreach($db_list as $db){
			echo "<p>$db</p>";
		}
	
	?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Assignment', true), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Chapters', true), array('controller' => 'chapters', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Chapter', true), array('controller' => 'chapters', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Questions', true), array('controller' => 'questions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Question', true), array('controller' => 'questions', 'action' => 'add')); ?> </li>
	</ul>
</div>