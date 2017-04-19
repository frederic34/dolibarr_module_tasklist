<?php

	//Récupération de la liste des postes de travail
	$disabled = ($user->rights->of->of->lire) ? 0 : 1;
	/*
	$sql = "SELECT rowid, numero, statut 
	   FROM ".MAIN_DB_PREFIX."assetOf 
	   WHERE status IN ('VALID','OPEN')
	ORDER BY numero ASC";
	$PDOdb->Execute($sql);
	$TOFTemp = $PDOdb->Get_All(PDO::FETCH_ASSOC);
	//$TOF[-1] = 'Tous';
	foreach($TOFTemp as $of){
		$TOF[$of['rowid']] = $of['numero'].' '.$of['statut'];
	}
	*/
	
	?><ul id="liste-of" class="list-group" data-filter-placeholder="Numéro OF"><?php
	
	  /* foreach($TOF as $idOf=>$numero) {
	       
           print '<li class="list-group-item"><a href="javascript:openOF('.$idOf.',\''.$numero.'\');">'.$numero.'</a></li>';
           
	   }
	*/
	?></ul>