var sql;
$(document).ready(
	function() {
		sql = CodeMirror.fromTextArea('sql',
				{parserfile: "parsesql.js",
				 stylesheet: baseurl +"css/codemirror/sqlcolors.css",
				 path: baseurl + "js/codemirror/",
				 textWrapping: false,
				 minHeight: 250,
				 height: 'dynamic',
				 indentUnit: 0,
				 content: '\n',
				 initCallback: function(){
					sql.grabKeys(function(e){
						sendQuery();
					}, function(keycode, e){
						if(e.ctrlKey && keycode == 13){
							return true;
						}else{
							return false;
						}
					});
				 }});
		//resize and move schema		
		$('#schema').one('load', function(){
			$(this).resizable({
 				handles: "all",
	 			aspectRatio: true, 
	 			autoHide: true,
				minHeight: 50,
				minWidth: 100
 			}).parent().draggable({
	 			cursor: "move"
	 		});
		}).each(function(){
			if(this.complete) $(this).triggerHandler("load");
		});

	 	
		 	
	 	$("#contentLoading").bind("ajaxSend", function(){  
			$("#buttonSendQuery").hide();
			$(this).show();  
		});  
 
		$("#contentLoading").bind("ajaxStop", function(){  
			$(this).hide();  
			$("#buttonSendQuery").show();
		});
		
		$('#buttonCSV').click(function(){
			$('#sql').text(sql.getCode());
			$('#queryForm').submit();
		});  		  
		
		$('#buttonUndo').click(function(){
			sql.undo();
		});  		  
		$('#buttonRedo').click(function(){
			sql.redo();
		});
		$('select.menu').change(function(){
			exo =  $(this).val();
			if(exo != 'none'){
				window.location =  baseurl + 'sqlexplorer/' + exo;
			}
		});
			 
	}
);

var interactionNb = 0;
var historyShowAll = false;
var sql;

function showDataGrid(data){
	$("#datagrid").empty();
	$("#error").hide();
	if(data.error){
		$("#error").text(data.error);
		$("#error").effect("highlight", { 
        			color: "white" 
    				}, 2000); 
	}
	if(data.header){
		//create table from json
		$("#datagrid").tplAppend(data, json2DataGridTpl());
		//paginate table
		$("#datagrid_table").dataTable({
			"bLengthChange": false,
			"bFilter": false,
			"bSort": false,
			"sZeroRecords": "Résultat vide"
		});
		$("#datagrid").effect("highlight", { 
        			color: "#FFFF00" 
    				}, 2000); 
	}
}

function json2DataGridTpl(){
	return function() {
	    return [
	        'table', {id: 'datagrid_table', className: 'display'},
	        		['thead',{},
		        		['tr', {},
		        			function(header){
		        				//header
	       						var array = [];
	       						for(x in header){
	       							array = array.concat(['th', {}, header[x]]);
								}
	       						return array;
	       					}(this.header)],
	       			'tbody',{},
	       				function(content){
		        			var trArray = [];
		       					 
			        		//add rows
			        		for(r in content){
			        			trArray = trArray.concat(['tr', {},
			        				function(row, index){
			        					var array = [];        					
			       						for(x in row){
			       							array = array.concat(['td', {}, row[x]]);
										}
			       						return array;
			        				}(content[r], r)]);	
			        		}
			        		
			        		return trArray;
			        	}(this.content)
		        	]
        	];
	};
}

function addSqlHistory(query){
	var rowClass = (interactionNb % 2==0) ? 'odd' : 'even';
	$("#history").prepend('<tr class="' + rowClass +  '"><td>' + (interactionNb+1) + '</td><td><a href="#" onclick="sql.setCode($(this).children().text());return false;" title="Copier la requête vers l\'éditeur"><pre>' + query + '</pre></a></td></tr>');
	interactionNb++;
	showHistory();
}

function showHistory(){
	if(historyShowAll){
		$('#history > tr').show();
	}else{
		for(var i=0;i< $('#history').children().length;i++){
			if(i <3){
				$($('#history').children()[i]).show();
			}else{
				$($('#history').children()[i]).hide();
			}
		}
	}
}

function toggleHistory(link){
	if(historyShowAll){
		historyShowAll = false;
		$(link).text('voir plus');
	}else{
		historyShowAll = true;
		$(link).text('voir moins');
	}
	$(link).toggleClass('on');
	$(link).toggleClass('off');
	showHistory();
}

function toggleSection(a){
	$(a).toggleClass('on');
	$(a).toggleClass('off');
	$(a).parent().next().toggle();
}

function toggleButton(a){
	$(a).toggleClass('buttonOn');
	$(a).toggleClass('buttonOff');
	return false;	
}

function questionPassed(answer_local, answer_server){
		$("#queryForm").toggle();
		$("#question").append("<h2>Question R&eacute;ussie !</h2>");
		if(answer_local && answer_server){		
			$("#answer_local").text($.trim(answer_local));
			$("#answer_server").text(answer_server);
			$("#dialog-confirm button").button().one('click', function(){
				$(this).button("option", "label", "Envoyer" )
					   .click(function(){
						   msg = $('#answer_error_msg').val()
						   + '\n-------------------------------------\n'
						   + $('#answer_error_msg_debug').val()
						   + '\n\nuser query:\n'+ answer_local
						   + '\n\nsystem query:\n' + answer_server;    						   
						   $.post(baseurl + 'sqlexplorer/senderror', {msg:msg}, function(data){ 
						   	   $("#dialog-confirm button").button("destroy").remove();
						   	   $('#answer_error_msg').replaceWith('<p>Merci pour votre message!</p>');				   
						   },'json');
					   })
				$('#answer_error_msg').slideDown();
			});
			$("#dialog-confirm").dialog({
				resizable: false,
				width: 'auto',
				modal: true,
				position: 'top',
				buttons: {
					'Fermer': function() {
						$(this).dialog('close');
					}
				}
			});
		}
}

function noAPIConnectionError(){
	$("#error").before("<div class=\"error\" id=\"error2\">ATTENTION! La connection avec moodle n'a pas pu être faites, vos résultats ne seront pas enregistrés!<br />Rechargez la page, si l'erreur persiste contactez un assistant.</div>");
	$("#error2").effect("highlight", { 
        			color: "white" 
    				}, 2000); 
	
}

function removeNL(s) {
  /*
  ** Remove NewLine, CarriageReturn and Tab characters from a String
  **   s  string to be processed
  ** returns new string
  */
  r = "";
  for (i=0; i < s.length; i++) {
    if (s.charAt(i) != '\n' &&
        s.charAt(i) != '\r' &&
        s.charAt(i) != '\t') {
      r += s.charAt(i);
      }
    }
  return r;
}