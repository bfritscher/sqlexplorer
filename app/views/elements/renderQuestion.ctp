	<div id="question"><?php echo nl2br($question['Question']['text'])?>
	<?php if(preg_match('/JOIN/ims', $question['Question']['sql'])){
		echo " (variante JOIN)";		
	}?>
	<br />
	Schéma: <?php echo $question['Question']['schema'];?></div>