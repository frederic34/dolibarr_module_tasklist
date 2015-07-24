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
		<link rel="stylesheet" href="js/jquery.mobile-1.4.5.min.css" />
		<link rel="stylesheet" href="js/jquery.mobile.popup.css" />
		
		<link rel="stylesheet" href="css/style.css"/>
		
		<script src="js/jquery-1.9.1.min.js" type="text/javascript"></script>
		<script src="js/jquery.mobile-1.4.5.min.js" type="text/javascript"></script>
		<script src="js/jquery-ui-1.10.2.custom.min.js" type="text/javascript"></script>
		<script src="js/fonctions.js" type="text/javascript"></script>
		<script>
            $(function() {
                $( "[data-role='navbar']" ).navbar();
                $( "[data-role='header'], [data-role='footer']" ).toolbar();
            });
            // Update the contents of the toolbars
            /*$( document ).on( "pagecontainerchange", function() {
                // Each of the four pages in this demo has a data-title attribute
                // which value is equal to the text of the nav button
                // For example, on first page: <div data-role="page" data-title="Info">
                var current = $( ".ui-page-active" ).jqmData( "title" );
                // Change the heading
                $( "[data-role='header'] h1" ).text( current );
                // Remove active class from nav buttons
                $( "[data-role='navbar'] a.ui-btn-active" ).removeClass( "ui-btn-active" );
                // Add active class to current nav button
                $( "[data-role='navbar'] a" ).each(function() {
                    if ( $( this ).text() === current ) {
                        $( this ).addClass( "ui-btn-active" );
                    }
                });
            });*/
    </script>
	</head>
	<body>		
	    
		<div id="list-task-user" data-role="page">
		    <div data-role="header">
                <h1>Tâches par utilisateur</h1>
            </div><!-- /header -->
			<div role="main" class="ui-content">
			    	<!-- Affichage de l'onglet "Utilisateur" --> 
						<?php require('./tpl/tasklist.onglet.utilisateurs.php'); ?>
						<div id='liste_tache_user' style="width:100%;" data-role="collapsibleset" data-theme="a" data-content-theme="a">
							
						</div>
					
					
			</div>
		</div>
		
		
        <div id="list-task-workstation" data-role="page">
            <div data-role="header">
                <h1>Tâches par poste de travail</h1>
            </div><!-- /header -->
            <div class="ui-content contenu" data-role="content" role="main">
                <?php 
                    if($conf->workstation->enabled && $user->rights->workstation->all->read){
                        ?>
                            <!-- Affichage de l'onglet "Postes de travail" -->
                            <?php require('./tpl/tasklist.onglet.workstations.php'); ?>
                            
                            <div id='liste_tache_workstation' style="width:100%;" data-role="collapsibleset" data-theme="a" data-content-theme="a"></div>
                        <?php
                    }

                ?>
                
                
            </div>
        </div>
        
        <div id="list-task-of" data-role="page">
            <div data-role="header">
                <h1>Tâches par of</h1>
            </div><!-- /header -->
            <div class="ui-content contenu" data-role="content" role="main">
                <?php 
                   if($conf->asset->enabled && $user->rights->asset->of->lire){
                        ?>
                            <!-- Affichage de l'onglet "Ordre de fabrication" -->
                            <?php require('./tpl/tasklist.onglet.of.php'); ?>
                            
                            <div id='liste_tache_of' style="width:100%;" data-role="collapsibleset" data-theme="a" data-content-theme="a"></div>
                            
                           <?php 
                    }

                ?>
            </div>
        </div>
        <?php require('./tpl/tasklist.listeTache.php'); ?>
		<?php require('./tpl/tasklist.popup.php'); ?>
		<?php require('./tpl/tasklist.onglet.php'); ?>
	</body>
</html>