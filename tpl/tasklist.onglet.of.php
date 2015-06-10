<?php

	//Récupération de la liste des postes de travail
	$disabled = ($user->rights->asset->of->lire) ? 0 : 1;
	
	$sql = "SELECT rowid, numero FROM ".MAIN_DB_PREFIX."assetOf";
	$PDOdb->Execute($sql);
	$TOFTemp = $PDOdb->Get_All(PDO::FETCH_ASSOC);
	
	$TOF[-1] = 'Tous';
	foreach($TOFTemp as $of){
		$TOF[$workstation['rowid']] = $of['numero'];
	}
	
	//pre($TWorkstation,true);exit;
	$form = new TFormCore;
	$selectOf = $form->combo('','search_of',$TOF,'',1,'','data-native-menu="false"');
	
	//Affichage des filtres
	?>
	<div class="ui-grid-a" style="margin-left: 15px; margin-right: 15px;">
		<div class="ui-block-a">
			<fieldset data-role="controlgroup" data-type="horizontal" data-inline="true">
			<?php print $selectOf; ?>
		</div>
	</div>
	<hr>
