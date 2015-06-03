<?php

	//Récupération de la liste utilisateur
	$form = new Form($db);
	$selectUsers = $form->select_dolusers($user->id,'search_user" data-native-menu="false',1);
	
	if($conf->asset->enabled && $user->rights->asset->of->lire){
		//Récupération de la liste des OF
		$TOf = _getOfList($PDOdb);
	}
	
	//Affichage des filtres
	?>
	<div class="ui-grid-a" style="margin-left: 15px; margin-right: 15px;">
		<div class="ui-block-a">
			<fieldset data-role="controlgroup" data-type="horizontal" data-inline="true">
			<?php print $selectUsers; ?>
		</div>
	</div>
	<hr>
	<?php
	
	//Récupération de la liste des tâches à réaliser
	$TTasks = _getTaskList($PDOdb);
	
	//Affichage des tâches
	if(count($TTasks)){
		
		foreach($TTasks as $task){
			?>
			
			<hr>
			<?php
		}
		
	}
	
	
