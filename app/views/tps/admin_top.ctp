<h2>Top</h2>
<?php
$total = 0;
foreach($stats as $stat){
	echo $stat[0]['type'] . " " . $stat[0]['count'] . " " .  $stat[0]['percent'] . "%<br />";
}
?>
<h3>Correct</h3>
<table>
	<tr>
		<th>#</th>
		<th>User</th>
		<th>Total</th>
		<th>Nb questions</th>
	</tr>
<?php
$result = true;
$i=1;
$total = 0;
foreach($data as $user){
	$total += $user[0]['total'];
	if(!$user[0]['result'] && $result){
		$result = false;
		$i=1;
		?>
</table>

<h3>Wrong</h3>
<table>
	<tr>
		<th>#</th>
		<th>User</th>
		<th>Total</th>
		<th>Nb questions</th>
	</tr>	
<?php
	}
?>
	<tr>
		<td><?php echo $i;$i++;?></td>
		<td><?php echo $this->Html->link($user[0]['user'], array('controller'=>'profile', 'action'=>'view',$user[0]['user']));?></td>
		<td><?php echo $user[0]['total'];?></td>
		<td><?php echo $user[0]['count'];?></td>
	</tr>
<?php
}
?>
</table>
<p>Total queries: <?php echo number_format($total, 0, '.', "'"); ?> for <?php echo count($data);?> users</p>