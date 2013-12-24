	<script type="text/javascript">
//<![CDATA[
function sendQuery(){
	var query = sql.getCode();
	var assignment = 'policlinique';
	
	var ajax = jQuery.ajax({
		url: 'http://hec.unil.ch/info1ere/questions/evaluate/<?php echo (isset($question['Question']['id']) ? $question['Question']['id'] : '0'); ?>',
		type: 'GET',
		data: 'sql=' + encodeURIComponent(query) + '&assignment=' + assignment  + '&student_mail=' + doLMSGetValue('cmi.core.student_id'),
		dataType: 'jsonp',
		timeout: 5000,
		error: function(){
			alert('Erreur de communication avec le serveur!');
		},
		success: function(eval){
			addSqlHistory(query);	
			showDataGrid(eval.data);
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
			
				    }
	});
	return false;
}

//]]>
</script>
<h1><span><?php echo $question['Question']['assignment_name'];?></span> <?php echo $chapters[$question['Question']['chapter_id']]. ' <span>question ' . $question['Question']['order_no'] . $question['Question']['variant']; ?></span></h1>
	<?php echo $this->element('renderQuestion', $question);?>
<div class="clear"></div>
<div id="error" class="error"></div>  
 <form id="queryForm" name="queryForm" method="post" action="<?php echo $this->Html->url('/questions/csv', true);?>">
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
<img alt="" id="schema" src="http://hec.unil.ch/info1ere/img/sql/schema_<?php echo strtolower($assignment['Assignment']['name']);?>.png" />
<div id="logo">
</div>
<div id="dialog-confirm" class="hide" title="Question R&eacute;ussie !">
	<p>Votre réponse:</p>
	<pre id="answer_local"> </pre>
	<p>Réponse du corrigé:</p>
	<pre id="answer_server"> </pre>
	<textarea id="answer_error_msg" style="display:none;width:100%" rows="5">Ma réponse est marqué comme correct, mais ne correspond pas au corrigé</textarea>
	<textarea id="answer_error_msg_debug" style="display:none;"><?php echo $question['Question']['assignment_name'];?> <?php echo $chapters[$question['Question']['chapter_id']]. ' question ' . $question['Question']['order_no'] . $question['Question']['variant']; ?> 
id:<?php echo $question['Question']['id'];?></textarea>
	<?php //<button>Signaler une erreur</button>?>
</div>