<div class="view">
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>>SQL</dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link('Assignments', '/admin/assignments'); ?><br />
			<?php echo $this->Html->link('Tp SQL', '/admin/tps'); ?><br />
			&nbsp;
		</dd>
	</dl>
</div>