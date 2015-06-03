<?php
	if($conf->asset->enabled && $user->rights->asset->of->lire){
		//Récupération de la liste des OF
		$TOf = _getOfList($PDOdb);
	}