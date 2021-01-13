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
	
?><!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> -->
<!DOCTYPE html>
<html>
	<head>
		<title>Dolibarr - <?php echo $langs->trans('Tasklist'); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<link rel="stylesheet" href="css/style.css"/>
		<link rel="stylesheet" href="lib/normalize.css"/>
		<link rel="stylesheet" href="lib/bootstrap/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.10.4.custom.min.css" />

		<!-- Includes CSS for font awesome -->
		<link rel="stylesheet" type="text/css" href="/client/srv54/eprolor/dolibarr/htdocs/theme/common/fontawesome-5/css/all.min.css?layout=classic&amp;version=12.0.4">
		<link rel="stylesheet" type="text/css" href="/client/srv54/eprolor/dolibarr/htdocs/theme/common/fontawesome-5/css/v4-shims.min.css?layout=classic&amp;version=12.0.4">
		
		<script src="js/jquery-1.9.1.min.js" type="text/javascript"></script>
		<script type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>
		<script src="lib/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
		
		
		<script src="<?php echo DOL_URL_ROOT; ?>/core/js/lib_head.js.php" type="text/javascript"></script>
		<script type="text/javascript">
			TASKLIST_SHOW_DOCPREVIEW = <?php echo (int) $conf->global->TASKLIST_SHOW_DOCPREVIEW; ?>;
			initPreview = function() {
				$(".documentpreview").click(function () {
					console.log("We click on preview for element with href="+$(this).attr('href')+" mime="+$(this).attr('mime'));
					document_preview($(this).attr('href'), $(this).attr('mime'), '<?php echo dol_escape_js($langs->transnoentities("Preview")); ?>');
					return false;
				});
			};
		</script>
		
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
                            		<div id="liste_tache_of" class="list-group table-responsive"></div>
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
		<div id="dialogforpopup" style="display: none;"></div>
		<script src="js/fonctions.js" type="text/javascript"></script>
		
		<?php
		
			$hookmanager->initHooks(array('tasklistcard'));
			$reshook = $hookmanager->executeHooks('formObjectOptionsEnd', $parameters, $object, $action);
		
		?>
		
	</body>
</html>
