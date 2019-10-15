<?php

	//Récupération de la liste des postes de travail
	$disabled = ($user->rights->workstation->all->lire) ? 0 : 1;
	
	$PDOdb=new TPDOdb;
	$TWorkstation = array(-1=>$langs->trans('All')) + TWorkstation::getWorstations($PDOdb);
    /*
	//pre($TWorkstation,true);exit;
	$form = new TFormCore;
	$selectWorkstation = $form->combo('','search_workstation',$TWorkstation,'',1,'','','form-control');
	
	//Affichage des filtres
	?>
		
			<?php print $selectWorkstation; ?>
		
	
*/
?><input type="hidden" id="search_workstation" value="" />
<ul class="list-group" id="list-workstation"><?php

	foreach($TWorkstation as $idWS=>$label) {
		
		echo '<li class="list-group-item" ws-id="'.$idWS.'" onclick="javascript:setWorkstation('.$idWS.')"><a href="#">'.$label.'</a></li>';
		
	}

?></ul><?php

	