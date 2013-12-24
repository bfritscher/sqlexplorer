<style type="text/css">
.false{
	background-color: red;
}

.true{
	background-color: green;
}
.notest{
	background-color: orange;
}
</style>
<script type="text/javascript">
	var total = new Object();
	total.correct = 0;
	total.wrong = 0;
	total.notest = 0;
	function updateTotal(result){
		if(result){
			total.correct++;
		}else{
			total.wrong++;
		}
		$('#total').html('correct: ' + total.correct + ' wrong: ' + total.wrong);
	}
</script>
<h2><?php echo $assignment['Assignment']['name']; ?></h2>
<table>
<tr>
<th>Question</th>
<th>Tests</th>
</tr>
<?php
$i = 0;
foreach ($assignment['Question'] as $question){
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
	//only on question of each variant
	//if($question['variant'] == '' || $question['variant'] == 'a'){
?>	
	<tr <?php echo $class;?>>
		<td><?php echo $this->Html->link($question['Chapter']['name'] . " " . $question['order_no'] . $question['variant'],
		array('controller'=>'questions', 'action'=>'edit', $question['id']));?>
		<pre><?php echo $question['sql'];?></pre></td>
		<td id="test_<?php echo $question['id'];?>">
<script type="text/javascript">
$.post('<?php echo $this->Html->url('/admin/question_tests/test_json/' . $question['id']);?>', function(data){
	var td = $("#test_<?php echo $question['id'];?>");
	if(data.length > 0){
		$.each(data, function(index, test){
			td.append('<p class="' + test.result +'"><a href="<?php echo $this->Html->url('/admin/question_tests/test/');?>' + test.id + '">Test '
					+ test.id + ': ' + (test.result ? 'correct' : 'wrong') + '</a></p>');
			updateTotal(test.result);
		});
	}else{
		td.append('<p class="notest"><a href="<?php echo $this->Html->url('/admin/question_tests/add/' . $question['id']);?>">NO TEST!</a></p>');
		updateTotal(false);
	}
	
}, "json");
</script></td>
	</tr>
<?php
	//} //if variant
}
?>
<tr>
	<th>Total</th>
	<th id="total"></th>
</tr>
</table>