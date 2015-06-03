<?php

	require('config.php');
	dol_include_once('/compta/facture/class/facture.class.php');
	dol_include_once('/societe/class/societe.class.php');
	dol_include_once('/user/class/usergroup.class.php');

    $conf->use_javascript_ajax = false; // 3.7 compatibility
    
    $langs->load('workstation@workstation');
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
		<script src="js/ajax.js" type="text/javascript"></script>
		<script src="js/fonctions.js" type="text/javascript"></script>
	</head>
	<body>		
		<div id="page" data-role="page">
			<div class="ui-content contenu" data-role="content" role="main">
				<div id="main" class="ui-grid-a contenu">
					<!-- Affichage des onglets -->
					<?php require('./tpl/tasklist.onglet.php'); ?>
					
					<!-- Affichage de l'onglet "Utilisateur" --> 
					<?php require('./tpl/tasklist.onglet.utilisateurs.php'); ?>
					
					<!-- Affichage de l'onglet "Postes de travail" -->
					<?php require('./tpl/tasklist.onglet.workstations.php'); ?>
				</div>
			</div>
		</div>
	</body>
</html>