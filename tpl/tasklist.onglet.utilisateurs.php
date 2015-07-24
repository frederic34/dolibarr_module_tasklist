<?php
	$conf->global->USER_HIDE_INACTIVE_IN_COMBOBOX = 1;
	//Récupération de la liste utilisateur
	$disabled = ($user->rights->projet->all->lire) ? 0 : 1;

	$form = new Form($db);
	$selectUsers = $form->select_dolusers($user->id,'search_user" data-native-menu="false',1,'',$disabled);
	
	//Affichage des filtres
	?>
	
			<?php print $selectUsers; ?>
	
