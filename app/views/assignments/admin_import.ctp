<?php
foreach($data as $ct => $c){
	print "<h2>$ct</h2>";
	foreach($c as $qn =>$q){
		print "<h3>Q $qn". (count($q)>1 ? 'a' :'') . " " .  (array_key_exists('description', $q[0]) ? $q[0]['description'] : '')  . "</h3><p>".$q[0]['text'] . "</p>";
		print "<p>debug:</p><pre>";
		var_dump($q);
		print "</pre><br/>";
		foreach($q as $an => $a){
			if($an > 0){
				print "<h3>Q $qn". chr(97+$an) ." ". $a['description'] ." </h3>";
			}
			print "<pre>" . $a['sql'] . "</pre>";
		}
	}
}
?>
