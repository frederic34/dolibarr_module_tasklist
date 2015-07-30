<?php

	//Récupération de la liste des postes de travail
	$disabled = ($user->rights->asset->of->lire) ? 0 : 1;
	
	$sql = "SELECT rowid, numero 
	   FROM ".MAIN_DB_PREFIX."assetOf 
	   WHERE status IN ('VALID','OPEN')
	ORDER BY numero ASC";
	$PDOdb->Execute($sql);
	$TOFTemp = $PDOdb->Get_All(PDO::FETCH_ASSOC);
	
	$TOF[-1] = 'Tous';
	foreach($TOFTemp as $of){
		$TOF[$of['rowid']] = $of['numero'];
	}
	
	
	?><ul id="list-of" data-role="listview"  data-inset="true" data-filter="true" data-filter-placeholder="Numéro OF"><?php
	
	   foreach($TOF as $idOf=>$numero) {
	       
           print '<li><a href="javascript:openOF('.$idOf.',\''.$numero.'\');">'.$numero.'</a></li>';
           
	   }
	
	?></ul>