<div class="slide cover"> 
	<p>/////////////////////////////////////////////////////////////////////// Modéliser par l’exemple. Pratique des tableurs et des bases de données</p>
	<h1><?php echo $title_for_layout;?><br />Corrigé</h1> 
	<p>Gabor Maksay<br />
	<a href="mailto:gabor.maksay@heig-vd.ch">gabor.maksay@heig-vd.ch</a></p>
	<p>Yves Pigneur<br />
	<a href="mailto:yves.pigneur@unil.ch">yves.pigneur@unil.ch</a></p>
</div>
<?php
foreach($data['Question'] as $question):
?>
<div class="slide">
	<h1>SQL &gt; <?php echo $question['Chapter']['name'];?> &gt; requête <?php echo $question['order_no'];?><?php echo $question['variant'];?> &gt; solution</h1>
	<span class="edit"><?php echo $html->link('edit', '/admin/questions/edit/' . $question['id']);?></span>
	<p class="question"><?php echo $question['text'];?><?php if(preg_match('/JOIN/ims', $question['sql'])){
		echo " (variante JOIN)";		
	}?></p>
	<p class="hint" style="width:50%">Schéma: <?php echo $question['schema']; ?></p>
	<div class="datagrid incremental">
		<table class="datagrid_table non-incremental">
			<thead class="non-incremental">
				<tr>
					<?php foreach($question['data']->header as $th): ?>
					<th><?php echo $th; ?></th>				
					<?php endforeach;?>
				</tr>
			</thead>
			<tbody class="non-incremental">
				<?php foreach($question['data']->content as $tr): ?>
				<tr>
					<?php foreach($tr as $td): ?>
					<td><?php echo $td; ?></td>
					<?php endforeach;?>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
	<p class="sql incremental"><?php echo nl2br($question['sql']);?>
	
	</p>
	<input type="hidden" name="assignment" value="<?php echo $question['assignment_name'];?>" />
	<span class="view_schema"><a href="nojs.html">schéma</a></span>
	<?php echo $html->image('sql/schema_'.strtolower($question['assignment_name']).'.png', array('class'=>'schema')); ?>
</div>
<?php
endforeach;
?>
<div class="slide">
	<h1>Suivi du document</h1>
	<p>Versions Dates Remarques Auteurs</p>
	<p>Toute remarque concernant la correction d’erreurs constatées et/ou l’amélioration des fonctionnalités de ce support de cours est la bienvenue et à adresser à :</p>
	<p class="float">Haute Ecole d'Ingénierie<br/>
	et de Gestion du Canton de Vaud<br />
	Comem+<br />
	Gabor Maksay<br />
	Centre St-Roch<br />
	CH – 1401 Yverdon-les-Bains<br />
	<a href="mailto:gabor.maksay@heig-vd.ch">gabor.maksay@heig-vd.ch</a></p>
	<p class="float">
	<br />
	Université de Lausanne<br />
	HEC - ISI<br />
	Yves Pigneur<br />
	Internef<br />
	CH – 1015 Lausanne-Dorigny<br />
	<a href="mailto:yves.pigneur@unil.ch">yves.pigneur@unil.ch</a></p>
</div>
<div class="slide cover">
	<h1>Questions...</h1>
	<p style="position:absolute; bottom: 10pt; right:10pt;"><a href="http://www.hec.unil.ch/hec1">http://www.hec.unil.ch/hec1</a><br />
	<a href="http://cours.heig-vd.ch/bdmc">http://cours.heig-vd.ch/bdmc</a></p>
</div>