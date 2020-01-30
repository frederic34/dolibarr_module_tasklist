<nav class="navbar navbar-fixed-top navbar-default">
	<div class="container-fluid">
		<div class="navbar-header">
	      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu-tasklist" aria-expanded="false">
	        <span class="sr-only">Toggle navigation</span>
	        <span class="icon-bar"></span>
	        <span class="icon-bar"></span>
	        <span class="icon-bar"></span>
	      </button>
	      <a class="navbar-brand hidden-sm hidden-md hidden-lg" href="#">Menu</a>
	    </div>
			
		<div class="collapse navbar-collapse " id="menu-tasklist">
		    <ul class="nav navbar-nav" role="tablist">
			  <li class="active"><a href="#list-task-user" role="tab" data-toggle="tab"><?php echo $langs->trans('Tasks'); ?></a></li>
			  <?php if($conf->workstation->enabled && $user->rights->workstation->all->read){ ?><li><a href="#list-task-workstation" id="onglet2"  role="tab" data-toggle="tab"><?php echo $langs->trans('WorkStations'); ?></a></li><?php } ?>
		      <?php if($accessOF) { ?><li><a href="#list-of" id="onglet3" role="tab" data-toggle="tab"><?php echo $langs->trans('OFAsset'); ?></a></li><?php } ?>
			</ul>
			
			<ul class="nav navbar-nav navbar-right">	
					<li>
		<?php
		if($user->rights->tasklist->user->read && (empty($conf->global->TASKLIST_ONLY_ADMIN_CAN_CHANGE_USER) OR $user->admin == 1)) {
		?>
						<div class="button-group">
					        <a type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <span id="user-name"><?php echo $user->login ?></span> <span class="caret"></span></a>
							<input id="search_user" type="hidden" value="<?php echo $user->id ?>" />
								<ul class="dropdown-menu" id="select-user-list">
								<li class="filter">
									<input id="search_user_text" type="text" value=""  placeholder="<?php echo $langs->trans('Filter'); ?>"/>
								</li>
		<?php
			global $conf;
			
			$sql = "SELECT DISTINCT u.rowid,u.login
					FROM ".MAIN_DB_PREFIX."user as u
					LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as uu ON (uu.fk_user = u.rowid)
					WHERE u.statut = 1 AND u.entity IN (0,".$conf->entity.")
					ORDER BY login";
			
			$resUser = $db->query($sql);
			while($obj = $db->fetch_object($resUser)) {
				echo '<li class="btn" login="'.$obj->login.'" user-id="'.$obj->rowid.'" onclick="changeUser('.$obj->rowid.')">'. $obj->login .'</li>';	
			}
		
		?>
								</ul>
<script type="text/javascript">
$(document).ready(function(){			
	$("#search_user_text").on("keyup", function() {
    	var value = $(this).val().toLowerCase();
    	$("#select-user-list li:not(.filter)").filter(function() {
      		$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    	});
  	});
});
</script>
						
						</div>
		<?php
		}
		else {
			echo '<p class="navbar-text navbar-right"><span class="glyphicon glyphicon-user"></span> <span id="user-name">'.$user->login.'&nbsp;</span></p>';
		}
		?>				
		
					</li>
			</ul>
		</div>
	</div>
</nav>
