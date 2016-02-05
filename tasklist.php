<?php

	require('config.php');
	dol_include_once('/projet/class/task.class.php');
	dol_include_once('/projet/class/projet.class.php');
	dol_include_once('/user/class/usergroup.class.php');
	dol_include_once('/core/lib/date.lib.php');
	
	if (!($user->admin || $user->rights->tasklist->all->read)) {
    	accessforbidden();
	}
	
	if($conf->of->enabled) dol_include_once('/of/asset/class/ordre_fabrication_asset.class.php');
	if($conf->workstation->enabled) dol_include_once('/workstation/class/workstation.class.php');

    $conf->use_javascript_ajax = false; // 3.7 compatibility
    
    if($conf->workstation->enabled) $langs->load('workstation@workstation');
	if($conf->asset->enabled) $langs->load('asset@asset');
	
	$accessOF = ($conf->asset->enabled && $user->rights->asset->of->lire)||($conf->of->enabled && $user->rights->of->of->lire); 
	
	$PDOdb = new TPDOdb;
?>
<!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> -->
<!DOCTYPE html>
<html>
	<head>
		<title>Dolibarr - <?php echo $langs->trans('Tasklist'); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		
		<link rel="stylesheet" href="css/style.css"/>
		<link rel="stylesheet" href="lib/normalize.css"/>
		<link rel="stylesheet" href="lib/bootstrap/css/bootstrap.min.css" />
		
		<script src="js/jquery-1.9.1.min.js" type="text/javascript"></script>
		<script src="js/jquery-ui-1.10.2.custom.min.js" type="text/javascript"></script>
		<script src="lib/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
		
	</head>
	<body>		
	    <div class="container-fluid">
			<?php require('./tpl/tasklist.onglet.php'); ?>
			<!-- Tab panes -->
			<div class="tab-content">
			  <div class="tab-pane active" id="list-task-user">
			  		<div class="row">
			  				<div id="liste_tache_user" class="list-group">
								
							</div>
					</div>
			  	
			  </div>
			  <div class="tab-pane" id="list-task-workstation">
			  		<div class="row">
			  		<?php 
	                    if($conf->workstation->enabled && $user->rights->workstation->all->read){
	                        ?>
	                            <!-- Affichage de l'onglet "Postes de travail" -->
	                            <div class="col-md-4">
	                            	<?php require('./tpl/tasklist.onglet.workstations.php'); ?>
	                            </div>
	                            <div class="col-md-8">
	                            	<div id="liste_tache_workstation" class="list-group"></div>
	                            </div>
	                        <?php
	                    }
	
	                ?>
	               </div>
			  </div>
			  <div class="tab-pane" id="list-of">
			  		<?php 
	                   if($accessOF){
	                   	
						  ?> <div class="col-md-4">
                            	<?php require('./tpl/tasklist.onglet.of.php'); ?>
                            </div>
                            <div class="col-md-8">
                            	<div id="list-task-of" class="">
                            		<div id="liste_tache_of" class="list-group"></div>
                            	</div>
                            </div>
	                      <?php
	                         
	                    }
	
	                ?>
	           </div>
			 
			</div>
		    
	        
	        <?php require('./tpl/tasklist.listeTache.php'); ?>
	        
			<?php require('./tpl/tasklist.popup.php'); ?>
		</div>
		<script src="js/fonctions.js" type="text/javascript"></script>
	</body>
</html>