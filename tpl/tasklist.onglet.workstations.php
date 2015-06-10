<?php

	//Récupération de la liste des postes de travail
	$disabled = ($user->rights->projet->all->lire) ? 0 : 1;
	
	$sql = "SELECT rowid, name FROM ".MAIN_DB_PREFIX."workstation";
	$PDOdb->Execute($sql);
	$TWorkstationTemp = $PDOdb->Get_All(PDO::FETCH_ASSOC);
	
	$TWorkstation[-1] = 'Tous';
	foreach($TWorkstationTemp as $workstation){
		$TWorkstation[$workstation['rowid']] = $workstation['name'];
	}
	
	//pre($TWorkstation,true);exit;
	$form = new TFormCore;
	$selectWorkstation = $form->combo('','search_workstation',$TWorkstation,'',1,'','data-native-menu="false"');
	
	//Affichage des filtres
	?>
	<div class="ui-grid-a" style="margin-left: 15px; margin-right: 15px;">
		<div class="ui-block-a">
			<fieldset data-role="controlgroup" data-type="horizontal" data-inline="true">
			<?php print $selectWorkstation; ?>
		</div>
	</div>
	<hr>
