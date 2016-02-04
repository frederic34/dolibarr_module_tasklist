<div class="collapse navbar-collapse" id="menu-tasklist">
    <ul class="nav nav-tabs navbar-nav" role="tablist">
	  <li class="active"><a href="#list-task-user" role="tab" data-toggle="tab"><?php echo $langs->trans('Users'); ?></a></li>
	  <?php if($conf->workstation->enabled && $user->rights->workstation->all->read){ ?><li><a href="#list-task-workstation" id="onglet2"  role="tab" data-toggle="tab"><?php echo $langs->trans('WorkStations'); ?></a></li><?php } ?>
      <?php if($conf->asset->enabled && $user->rights->asset->of->lire){ ?><li><a href="#list-of" id="onglet3" role="tab" data-toggle="tab"><?php echo $langs->trans('OFAsset'); ?></a></li><?php } ?>
	</ul>
	
	<ul class="nav navbar-nav navbar-right">	
			<li>
				<div class="button-group">
			        <a type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <span class="caret"></span></a>
					<input id="search_user" type="hidden" value="<?php echo $user->id ?>" />
						<ul class="dropdown-menu" id="select-user-list" style="height: 500px; overflow-y: scroll; ">
<?php
	global $conf;
	
	$sql = "SELECT DISTINCT u.rowid,u.login
			FROM ".MAIN_DB_PREFIX."user as u
			LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as uu ON (uu.fk_user = u.rowid)
			WHERE u.statut = 1 AND u.entity IN (0,".$conf->entity.")";
	
	$TUser = $PDOdb->ExecuteAsArray($sql);
	foreach($TUser as $obj) {
		echo '<li class="btn"><a href="javascript:changeUser('.$obj->rowid.')">'. $obj->login .'</a></li>';	
	}

?>
						</ul>
				</div>
			</li>
	</ul>
</div>