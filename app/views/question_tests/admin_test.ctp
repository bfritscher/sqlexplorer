<h2>Test <?php echo $question['Question']['assignment_name'];?> <?php echo $question['Chapter']['name'];?> <?php echo $question['Question']['order_no'];?></h2>
<h3 style="background-color: <?php echo $test_answer->result_bool ? 'red' : 'green'?>;">Result: <?php echo $test_answer->result_bool ? 'ERREUR même résultat' : 'OK'?></h3>
<div><?php echo $test['QuestionTest']['comment'];?></div>
<pre><?php echo $test['QuestionTest']['sql'];?></pre>
<table class="datagrid_table non-incremental">
	<thead class="non-incremental">
		<tr>
			<?php foreach($test_answer->data->header as $th): ?>
			<th><?php echo $th; ?></th>				
			<?php endforeach;?>
		</tr>
	</thead>
	<tbody class="non-incremental">
		<?php foreach($test_answer->data->content as $tr): ?>
		<tr>
			<?php foreach($tr as $td): ?>
			<td><?php echo $td; ?></td>
			<?php endforeach;?>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>

<h2>Question Answer</h2>
<div><?php echo $question['Question']['text'];?><br />
<?php echo $question['Question']['schema'];?></div>
<pre><?php echo $question['Question']['sql'];?></pre>
<table class="datagrid_table non-incremental">
	<thead class="non-incremental">
		<tr>
			<?php foreach($question_answer->header as $th): ?>
			<th><?php echo $th; ?></th>				
			<?php endforeach;?>
		</tr>
	</thead>
	<tbody class="non-incremental">
		<?php foreach($question_answer->content as $tr): ?>
		<tr>
			<?php foreach($tr as $td): ?>
			<td><?php echo $td; ?></td>
			<?php endforeach;?>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>