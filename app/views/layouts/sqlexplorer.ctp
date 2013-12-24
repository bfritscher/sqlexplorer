<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title><?php echo $title_for_layout; ?></title>
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('sqlexplorer');
		echo $this->Html->css('cupertino/jquery-ui-1.8.4.custom');
	?>
	<?php 
		echo $this->Html->script('jquery-1.4.2.min');
		echo $this->Html->script('jquery-ui-1.8.4.custom.min');
		echo $scripts_for_layout;
	?>
</head>
<body>
	<?php echo $this->Session->flash(); ?>
	<?php echo $this->Session->flash('auth'); ?>
	<?php echo $content_for_layout; ?>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>