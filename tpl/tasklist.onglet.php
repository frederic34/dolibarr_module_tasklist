<div data-role="footer" data-position="fixed" data-theme="b">
<div data-role="navbar">
	<ul>
		<li><a href="#list-task-user" id="onglet1" class="ui-btn-active" style="display: "><?php echo $langs->trans('Users'); ?></a></li>
		<?php if($conf->workstation->enabled && $user->rights->workstation->all->read){ ?><li><a  href="#list-task-workstation" id="onglet2"><?php echo $langs->trans('WorkStations'); ?></a></li><?php } ?>
		<?php if($conf->asset->enabled && $user->rights->asset->of->lire){ ?><li><a  href="#list-task-of" id="onglet3"><?php echo $langs->trans('OFAsset'); ?></a></li><?php } ?>
	</ul>
</div>
</div>