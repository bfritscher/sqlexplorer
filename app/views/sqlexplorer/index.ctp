<?php echo $html->script('jquery.dataTables.min', array('inline' => false)); ?>
<?php echo $html->script('jquery.flydom-3.1.1', array('inline' => false)); ?>
<?php echo $html->script('codemirror/codemirror', array('inline' => false)); ?>
<?php $html->scriptStart(array('inline' => false));?>
var baseurl = '<?php echo $html->url('/');?>';
<?php $html->scriptEnd();?>
<?php echo $html->script('sqlexplorer', array('inline' => false)); ?>
<?php $html->scriptStart(array('inline' => false));?>
function sendQuery(){
	var query = sql.getCode();
	var assignment = '<?php echo strtolower($assignment['Assignment']['name']);?>';
	
	var ajax = jQuery.ajax({
		url: '<?php echo $this->Html->url(array('controller' => 'questions', 'action' => 'evaluate', (isset($question['Question']['id']) ? $question['Question']['id'] : '0')));?>',
		type: 'POST',
		data: 'sql=' + encodeURIComponent(query) + '&assignment=' + assignment  <?php if(isset($sco)) echo "+ '&student_mail=' + doLMSGetValue('cmi.core.student_id')"?>,
		dataType: 'json',
		timeout: 5000,
		error: function(){
			alert('Erreur de communication avec le serveur!');
		},
		success: function(eval){
			addSqlHistory(query);	
			showDataGrid(eval.data);
			<?php if(isset($sco)): ?>
			//save interaction only has a range from 0-255
			doLMSSetValue('cmi.interactions.'+ 0 +'.student_response', removeNL(query).substring(0,255));
			doLMSSetValue('cmi.interactions.'+ 0 +'.result', eval.result);

			if(eval.result == 'correct'){
				doLMSSetValue('cmi.core.score.raw', 1);
				doLMSSetValue('cmi.core.lesson_status','passed');
				questionPassed(query, eval.sql_answer);
			}else{
				doLMSSetValue('cmi.core.score.raw', 0);
				doLMSSetValue('cmi.core.lesson_status','failed');
			}
			
			<?php else: ?>
			if(eval.result == 'correct'){
				questionPassed(query, eval.sql_answer);				
			}
			<?php endif;?>
	    }
	});
	return false;
}
<?php $html->scriptEnd();?>

<?php if(isset($sco)): ?>
<h1><span><?php echo $question['Question']['assignment_name'];?></span> <?php echo $chapters[$question['Question']['chapter_id']]. ' <span>question ' . $question['Question']['order_no'] . $question['Question']['variant']; ?></span></h1>
<?php echo $this->element('renderQuestion', $question);?>

<?php else:?>
<h1><span>SQL</span> Explorer <select class="menu">
<option value="none">Cours</option>
<?php
$chapter = "";
foreach($selection_list as $a){
	$new_chapter = $chapter != $a['Chapter']['name'];
	if($new_chapter){
		$chapter = $a['Chapter']['name'];
		if($a['Chapter']['id'] == ADDITIONAL_ASSIGNEMENTS_CHAPTER_ID){
			echo '</select><select class="menu">';
			echo '<option value="none">' . $chapter .'</option>';
		}
			echo '<option disabled="disabled">--------------------------</option>';
	}
	echo '<option '. ($assignment['Assignment']['name'] == $a['Assignment']['name'] ? 'selected="selected"' : '')  .'>';
	echo $a['Assignment']['name'];
	echo '</option>';
}
?>
</select></h1>
<?php 
$isExample = strtolower($assignment['Assignment']['name']) == strtolower(preg_replace('/\s/', '', $chapters[$assignment['Assignment']['chapter_id']]));
?>
<?php if($assignment['Question'] && !$isExample): ?>
	<h2 class="clear"><a href="#" onclick="toggleSection(this)" class="<?php echo isset($this->params['pass'][1]) ? 'on' : 'off'; ?>">Questions</a></h2>
	<div id="questions" style="display:<?php echo isset($this->params['pass'][1]) ? 'block' : 'none'; ?>;">
	<?php
	$chapter_id = 0;
	foreach($assignment['Question'] as $q){
		$active = '';
		if(isset($this->params['pass'][1]) && $q['id'] == $this->params['pass'][1]){
			$active = 'selected'; 
		}
		if($chapter_id != $q['chapter_id']){
			$chapter_id = $q['chapter_id'];
			echo '<span>' . $chapters[$chapter_id] . '</span>';
		}
		echo $html->link($q['order_no'] . $q['variant'],
								array('controller'=>'sqlexplorer', 'action' => $assignment['Assignment']['name'], $q['id']),
								array('class'=>$active));
	}
	if($question){
		echo $this->element('renderQuestion', $question);
	}
	?>
	</div>
<?php endif; //questions ?>
<?php if($assignment['Question'] && $isExample): ?>
	<h2><a href="#" onclick="toggleSection(this)" class="on">Exemples</a></h2>
	<div id="scrapbook">
		<div class="clear"></div>
		<?php foreach($assignment['Question'] as $q): ?>
			<a href="#" onclick="sql.setCode($(this).children().text());return false;"
		   		title="<?php echo htmlentities($q['sql'])?>"><?php echo $q['text']?>
	<?php if(preg_match('/JOIN/ims', $q['sql'])){
		echo " (variante JOIN)";		
	}?>
		   		<pre><?php echo $q['sql']?></pre></a>
		<?php endforeach;?>
		<div class="clear"></div>
	</div>
<?php endif; //examples?>
<?php endif; //not sco?>
<div class="clear"></div>
<div id="error" class="error"></div>  
 <form id="queryForm" name="queryForm" method="post" action="<?php echo $this->Html->url('/questions/csv');?>">
  <div id="toolbar">
  	<div class="clear"></div>
  	<a id="buttonSendQuery" onclick="sendQuery();" title="<?php echo __('Exécuter la requête SQL'); ?>"><?php echo __('Exécuter');?></a>
  	<a id="contentLoading" title="<?php echo __('Résultat en cours de chargement, veuillez patienter'); ?>"></a>
  	<div class="separator">&nbsp;</div>
  	<a id="buttonCSV" title="<?php echo __('Résultat sous forme de fichier csv/excel'); ?>"></a>
  	<div class="separator">&nbsp;</div>
  	<a id="buttonUndo" class="buttonOn" title="<?php echo __('Annuler');?>"></a>
  	<a id="buttonRedo" class="buttonOn" title="<?php echo __('Refaire');?>" ></a>
  	<div class="clear"></div>
  </div>
  <div id="sql-div"><textarea id="sql" name="sql" style="width: 100%; height: 174px;"></textarea></div>
  <input type="hidden" name="assignment_name" value="<?php echo $assignment['Assignment']['name'] ?>"/>
 </form>
<h2><a href="javascript:return false;"  onclick="toggleSection(this)" class="on"><?php echo __('Résultat'); ?></a></h2>
<div id="datagrid"></div>
<h2><a href="javascript:return false;"  onclick="toggleSection(this)" class="on"><?php echo __('Historique'); ?></a></h2>
<div id="history-div">
 <table class="display"><thead><tr>
 <th><?php echo __('No'); ?></th><th><?php echo __('Requête'); ?></th>
 </tr></thead><tbody id="history"></tbody>
 <tfoot><tr><th></th><th><a href="javascript:return false;"  onclick="toggleHistory(this); return false;" class="off"><?php echo __('voir plus') ?></a></th></tr></tfoot>
 </table>
</div> 
<h2><a href="javascript:return false;" onclick="toggleSection(this)" class="on"><?php echo __('Schéma'); ?></a></h2>
<?php echo $html->image('sql/schema_'.strtolower($assignment['Assignment']['name']).'.png', array('id'=>'schema')); ?>
<div id="logo">
</div>
<div id="dialog-confirm" class="hide" title="Question R&eacute;ussie !">
	<p>Votre réponse:</p>
	<pre id="answer_local"> </pre>
	<p>Réponse du corrigé:</p>
	<pre id="answer_server"> </pre>
	<textarea id="answer_error_msg" style="display:none;width:100%" rows="5">Ma réponse est marqué comme correct, mais ne correspond pas au corrigé

votre e-mail: </textarea>
	<textarea id="answer_error_msg_debug" style="display:none;">
	<?php if(isset($assignment['Question']) && !$isExample): ?>
	<?php echo $question['Question']['assignment_name'];?> <?php echo $chapters[$question['Question']['chapter_id']]. ' question ' . $question['Question']['order_no'] . $question['Question']['variant']; ?> 
id:<?php echo $question['Question']['id'];?>
<?php endif; ?></textarea>
	<button>Signaler une erreur</button>
	
</div>