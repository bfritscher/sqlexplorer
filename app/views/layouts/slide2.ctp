<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"> 
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>
	<meta name="copyright" content="Copyright &#169; Author" /> 
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('slidy', null, array('media'=>'screen, projection, print'));
		echo $html->script('slidy');
		echo $html->script('jquery-1.4.2.min');
		echo $this->Html->script('jquery-ui-1.8.4.custom.min');
		echo $html->script('jquery.bar');
		echo $html->script('codemirror/codemirror');
		echo $scripts_for_layout;
	?> 
	<style type="text/css">
	body{
		overflow: hidden;
	}
	h1, h2, h3{
		color: rgb(162,41,50);
	}
	
	table{
		border-collapse:collapse;
	}
	
	th{
		background-color: #ccc;
	}
	td {
		background-color: #fff;
	}
	td, th{
		border: 1px solid #000;
		margin: 0;
	}
	div.slide{
		background: url('<?php echo $this->Html->url('/');?>img/slides/background.png') repeat-x top left;
	}
	
	div.slide.cover{
		text-align: right;		
		background-image: none;
		margin-top: 200px;
	}
	
	div.slide.cover h1{
		margin: 60pt 0 60pt 0;
	}
	
	#logo_unil{
		float: left;
		margin: 1em 0 0 1em;
	}
	
	#logo_heigvd{
		float: right;
		margin: 1em 1em 0 0;
	}
	
	.float{
		float:left;
	}
	
	div.slide.img img{

	}
	.datagrid{
		max-width: 50%;
		height: 450px;
		overflow: auto;
		position: absolute;
		right: 1em;
		top: 5em;
		z-index: 2;
		opacity: 0.8;
	}
	
	.datagrid_table{
		margin: 0;
	}

	.sql{
		font-family: monospace;
		background-color:#E4E5E7;
		border-color:#95ABD0;
		border-style:solid;
		border-width:thin thin thin 1em;
		color:#00428C;
		font-size:80%;
		font-weight:bold;
		line-height:120%;
		padding:0.2em 1em;
	}
	
	.schema{
		opacity: 0.8;
		z-index:9999999;
		display: none;
	}
	
	.CodeMirror-wrapping{
		margin-left: 0.5em;
	}
	
	.edit{
		position:fixed;
		top:10px;
		right:10px;
	}
	
	.edit a{
		visibility:hidden;	
	}
	.edit:hover a{
		visibility:visible;
	}
	
	.view_schema{
		position:fixed;
		bottom:20px;
		left:10px;
	}
	
	.view_schema a{
		visibility:hidden;	
	}
	.view_schema:hover a{
		visibility:visible;
	}
	.datagrid_toggle{
		font-size: 8pt;
		cursor: pointer;
	}
	
	
.jbar{
	min-height:50px;
	width:100%;
	background-color: #fff;
	position:fixed;
	filter:progid:DXImageTransform.Microsoft.Alpha(opacity=95); 
	opacity: 0.95;
	-moz-opacity: 0.95;
	text-align:center;
	left:0px;
	z-index:9999999;
	margin:0px;
	padding:0px;
}
.jbar-top{
	top:0px;
	border-top:2px solid #f00;
	border-bottom: 1px solid #f00;
}
.jbar-bottom{
	bottom:0px;
	border-bottom:2px solid #f00;
	border-top: 1px solid f00;
	color:#777;
}
.jbar-content{
	line-height:46px;
	font-size: 18px;
	font-family:'Lucida Grande',sans-serif;
}
a.jbar-cross{
	position:absolute;
	width:31px;
	height:31px;
	background:transparent url(../images/cross.png) no-repeat top left;	
	cursor:pointer;
	right:10px;
}
a.jbar-cross:hover{
	background-image: url(../images/cross_hover.png)
}
.jbar-top a.jbar-cross{
	top:8px;	
}
.jbar-bottom a.jbar-cross{
	bottom:8px;
}

/* resizable */

.ui-wrapper { border: 1px solid #006AC3; }
.ui-wrapper input, .ui-wrapper textarea { border: 0; }


.ui-resizable-s { cursor: s-resize; height: 6px; width: 100%; bottom: 0px; left: 0px; background: transparent url('../img/ui/resizable-s.gif') repeat scroll center top; }
.ui-resizable-n { cursor: n-resize;}
.ui-resizable-e { cursor: e-resize;}
.ui-resizable-w { cursor: w-resize;}
.ui-resizable-se { cursor: se-resize;}
.ui-resizable-sw { cursor: sw-resize;}
.ui-resizable-nw { cursor: nw-resize;}
.ui-resizable-ne { cursor: ne-resize;}

.ui-resizable { position: relative;}
.ui-resizable-handle { position: absolute;font-size: 0.1px; display: block;}
.ui-resizable-disabled .ui-resizable-handle, .ui-resizable-autohide .ui-resizable-handle { display: none; }
/* knobHandles using CSS */

.ui-wrapper .ui-resizable-handle { width: 8px; height: 8px; border: 1px solid rgb(128, 128, 128); background: rgb(242, 242, 242); background-image: none; }
.ui-wrapper .ui-resizable-n, .ui-wrapper .ui-resizable-s { left: 50%; margin-left: -5px;}
.ui-wrapper .ui-resizable-e, .ui-wrapper .ui-resizable-w { top: 50%; margin-top: -5px;}
.ui-wrapper .ui-resizable-ne, .ui-wrapper .ui-resizable-n, .ui-wrapper .ui-resizable-nw { top: 0;}
.ui-wrapper .ui-resizable-se, .ui-wrapper .ui-resizable-s, .ui-wrapper .ui-resizable-sw { bottom: 0;}
.ui-wrapper .ui-resizable-ne, .ui-wrapper .ui-resizable-e, .ui-wrapper .ui-resizable-se { right: 0; }


  </style> 
</head>
<body>
	<div class="background">
	</div>
	<div class="background cover">
		
	</div>

	<?php $this->Session->flash(); ?>
	
	<?php echo $content_for_layout; ?>

	<?php //echo $this->element('sql_dump'); ?>
	
	<script type="text/javascript">	
	var timeout;
	$(document).ready(
			function() {
		$('.schema').draggable({
		 			cursor: "move"
		});

		$('.view_schema a.schema_link').click(function(){
			$(this).parent().next().toggle();
			return false;
		});
		
		$('.view_schema a.solution_link').click(function(){
			id = $(this).attr('href');
			window[id].setCode($('#' + id + '_sol').text());
			return false;
		});
		
	});
	
	function removebar(){
		if($('.jbar').length){
			clearTimeout(timeout);
			$('.jbar').fadeOut('fast',function(){
				$(this).remove();
			});
		}	
	};
	
	function showMsg(anchor, text){
		if(!$('.jbar').length){
			timeout = setTimeout(removebar,10000);
			var _message_span = $(document.createElement('span')).addClass('jbar-content').html(text);
			_message_span.css({"color" : '#fff'});
			var _wrap_bar;
			_wrap_bar	  = $(document.createElement('div')).addClass('jbar jbar-top') ;
			
			_wrap_bar.css({"background-color" 	: '#f00'});
			_wrap_bar.css({"cursor"	: "pointer"});
			_wrap_bar.click(function(e){removebar();});
			_wrap_bar.append(_message_span);
			anchor.append(_wrap_bar).fadeIn('fast');
		}
	
	}
	
	function sendQuery(editor){
		var sql = editor.getCode(),
		assignment = jQuery(editor.wrapping).parent('.slide').children('input[name=assignment]').attr('value'),
		datagrid = jQuery(editor.wrapping).parent('.slide').find('table.datagrid_table');
		
		var ajax = jQuery.ajax({
			url: '<?php echo $this->Html->url('/questions/evaluate/0')?>',
			type: 'POST',
			data: 'sql=' + encodeURIComponent(sql) + '&assignment=' + assignment,
			dataType: 'json',
			timeout: 5000,
			error: function(){
				alert('TODO ERROR MSG CONSTANT');
			},
			success: function(eval){
				if(eval.data.error){
					showMsg(jQuery(editor.wrapping).parent('.slide'), eval.data.error);
				}else{
					updateDatagrid(datagrid, eval.data);
				}
			}
		});
	}
	
	function updateDatagrid(datagrid, data){
		datagrid.slideUp('fast', function(){
			header = datagrid.find('thead tr');
			header.children().remove();
			body = datagrid.find('tbody')
			body.children().remove();
			$.each(data.header,
				function(index, value){
					header.append('<th>'+ value +'</th>');
				});
			$.each(data.content,
				function(index, row){
					tr = $('<tr></tr>').appendTo(body);
					jQuery.each(row, function(col, value){
						tr.append('<td>'+ value +'</td>');
					});
				});
			datagrid.slideDown('fast');
			
		});
	}
	
	/* todo add incremental to outer frame */
	jQuery('p.sql').each(function(index, el){
		window[el.id] = new CodeMirror(CodeMirror.replace(this),
			{parserfile: "parsesql.js",
			 stylesheet: "<?php echo $html->url('/');?>css/codemirror/sqlcolorsslide.css",
			 path: "<?php echo $html->url('/');?>js/codemirror/",
			 content: jQuery(this).text(),
			 textWrapping: false,
			 height: 'dynamic',
			 minHeight: 400,
			 indentUnit: 0,
			 iframeClass: 'incremental',
			 initCallback: function(editor){
				editor.grabKeys(function(e){
					sendQuery(editor);
				}, function(keycode, e){
					if(e.ctrlKey && keycode == 13){
						return true;
					}else{
						return false;
					}
				});
			 }});

	});
	jQuery('.datagrid').each(function(){
		$('<a class="non-incremental datagrid_toggle">[>>>]</a>').toggle(function(){
				$(this).text('[<<<]').next().hide();
				
			},
			function(){
				$(this).text('[>>>]').next().show();
		}).prependTo(this);
		
	});
	
	</script>
</body>
</html>

