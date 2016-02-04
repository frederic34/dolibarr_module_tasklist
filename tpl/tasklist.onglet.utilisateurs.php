<?php
	$conf->global->USER_HIDE_INACTIVE_IN_COMBOBOX = 1;
	//Récupération de la liste utilisateur
	$disabled = ($user->rights->projet->all->lire) ? 0 : 1;

	$form = new Form($db);
	$selectUsers = $form->select_dolusers($user->id,'search_user',1,'',$disabled,'','',$conf->entity,'','','','','','form-control');
	
	//Affichage des filtres
	?>
	<div class="form-group">
			<?php print $selectUsers; ?>
	</div>
	
