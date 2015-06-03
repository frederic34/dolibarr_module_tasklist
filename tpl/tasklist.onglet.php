<div data-role="navbar" style="width: 100%">
	<ul>
		<li><a onclick="switch_onglet(this.id);" id="onglet1" class="ui-btn-active" style="display: "><?php echo $langs->trans('Users'); ?></a></li>
		<?php if($conf->workstation->enabled){ ?><li><a onclick="switch_onglet(this.id);" id="onglet2"><?php echo $langs->trans('WorkStations'); ?></a></li><?php } ?>
		<?php if($conf->asset->enabled){ ?><li><a onclick="switch_onglet(this.id);" id="onglet3"><?php echo $langs->trans('OFAsset'); ?></a></li><?php } ?>
	</ul>
</div>