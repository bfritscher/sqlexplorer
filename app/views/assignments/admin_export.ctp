<?php
define('MAX_LENGHT', 80);

separator(2);
comment("Script " . $assignment['Assignment']['name'] . ".sql");
nl();
//$assignment['Assignment']['description'];
//$assignment['Chapter']['name'];
$chapter = 0;
foreach ($assignment['Question'] as $question){
	//if change chapter
	if($chapter != $question['chapter_id']){
		chapter($chapters[$question['chapter_id']]);
		$chapter = $question['chapter_id'];
	}
	question($question);
}

function chapter($name){
	separator(2);
	comment("Requetes chapitre $name"); 
	nl();
}

function question($question){
	separator();
	//TODO: how to do multiline?
	$words = preg_split ('/ /', $question['text']);
	$line = $question['order_no'] . "." .  $question['variant'] . " ";
	while($word = array_shift($words)){
		if(strlen($line . " $word") > MAX_LENGHT){
			comment($line);	
			$line = $word;
		}else{
			$line .= " $word";
		}
	}
	comment($line);
	//TODO: how to handle variante/siblings?
	//TODO: do a max lenght test?
	echo $question['sql'] . ";";
	nl(2);
}

function separator($count=1){
	$separator = "-- ";
	while(strlen($separator) < MAX_LENGHT){
		$separator .= "-";	
	} 
	for($i=0;$i<$count;$i++){
		echo $separator;
		nl();
	}	
}

function comment($text){
	echo "-- $text";
	nl();
}

function nl($count=1){
	for($i=0;$i<$count;$i++){
		echo "\n";
	}
}

?>