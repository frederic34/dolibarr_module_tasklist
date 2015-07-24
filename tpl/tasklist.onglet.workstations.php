<?php

	//Récupération de la liste des postes de travail
	$disabled = ($user->rights->workstation->all->lire) ? 0 : 1;
	
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
		
			<?php print $selectWorkstation; ?>
		
	
