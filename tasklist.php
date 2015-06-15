<?php

	require('config.php');
	dol_include_once('/projet/class/task.class.php');
	dol_include_once('/projet/class/projet.class.php');
	dol_include_once('/user/class/usergroup.class.php');
	dol_include_once('/core/lib/date.lib.php');
	
	if($conf->asset->enabled) dol_include_once('/asset/class/ordre_fabrication_asset.class.php');
	if($conf->workstation->enabled) dol_include_once('/workstation/class/workstation.class.php');

    $conf->use_javascript_ajax = false; // 3.7 compatibility
    
    if($conf->workstation->enabled) $langs->load('workstation@workstation');
	if($conf->asset->enabled) $langs->load('asset@asset');
	
	$PDOdb = new TPDOdb;
?>
<!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> -->
<!DOCTYPE html>
<html>
	<head>
		<title>Dolibarr - <?php echo $langs->trans('Tasklist'); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="js/jquery.mobile-1.3.0.min.css" />
		<link rel="stylesheet" href="js/jquery.mobile.popup.css" />
		
		<link rel="stylesheet" href="css/style.css"/>
		
		<script src="js/jquery-1.9.1.min.js" type="text/javascript"></script>
		<script src="js/jquery.mobile-1.3.0.min.js" type="text/javascript"></script>
		<script src="js/jquery-ui-1.10.2.custom.min.js" type="text/javascript"></script>
		<script src="js/fonctions.js" type="text/javascript"></script>
	</head>
	<body>		
		<div id="page" data-role="page">
			<div class="ui-content contenu" data-role="content" role="main">
				<div id="main" class="ui-grid-a contenu">
					<!-- Affichage des onglets -->
					<?php require('./tpl/tasklist.onglet.php'); ?>

					<!-- Corps de la page -->
					<div id="corps-1" class="ui-content ui-bar-a corps" style="width: 100%">
						
						<!-- Affichage de l'onglet "Utilisateur" --> 
						<?php require('./tpl/tasklist.onglet.utilisateurs.php'); ?>
						
						<div id='liste_tache_user' style="width:100%;"></div>
						
						<?php require('./tpl/tasklist.listeTache.php'); ?>
					</div>
					<?php	
					if($conf->workstation->enabled && $user->rights->workstation->all->read){
						?>
						<div id="corps-2" class="ui-content ui-bar-a corps" style="width: 100%">
							<!-- Affichage de l'onglet "Postes de travail" -->
							<?php require('./tpl/tasklist.onglet.workstations.php'); ?>
							
							<div id='liste_tache_workstation' style="width:100%;"></div>
							
							<?php require('./tpl/tasklist.listeTache.php'); ?>

						</div>
						<?php
					}
					if($conf->asset->enabled && $user->rights->asset->of->lire){
						?>
						<div id="corps-3" class="ui-content ui-bar-a corps" style="width: 100%">
							<!-- Affichage de l'onglet "Ordre de fabrication" -->
							<?php require('./tpl/tasklist.onglet.of.php'); ?>
							
							<div id='liste_tache_of' style="width:100%;"></div>
							
							<?php require('./tpl/tasklist.listeTache.php'); ?>
							
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		
		<?php require('./tpl/tasklist.popup.php'); ?>
	</body>
</html>