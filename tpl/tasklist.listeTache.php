		<!--
			DIV caché clonée pour afficher la liste : sert de template de base
		-->
		<?php 

		$langs->load('deliveries');

		$TFields = array (
			"client" => array('label' => $langs->trans('Customer'), 'class' => "col-xs-6 col-md-6"),
			"date_prevue_livraison_la_plus_proche" => array('label' => $langs->trans('DeliveryDate'), 'class' => "col-xs-6 col-md-6"),
			"dateo" => array('label' => $langs->trans('DateStart'), 'class' => "col-xs-6 col-md-6"),
			"datee" => array('label' => $langs->trans('DateEnd'), 'class' => "col-xs-6 col-md-6"),
			"planned_workload" => array('label' => $langs->trans('ExpectedTime'), 'class' => "col-xs-6 col-md-6"),
			"spent_time" => array('label' => $langs->trans('PastTime'), 'class' => "col-xs-6 col-md-6"),
			"progress" => array('label' => $langs->trans('Progress'), 'class' => "col-xs-6 col-md-6", 'moreHTML' => '%'),
			"priority" => array('label' => $langs->trans('Priority'), 'class' => "col-xs-6 col-md-6"),
			"desc" => array('label' => $langs->trans('Description'), 'class' => "col-xs-12 col-md-12")
		);

		$TFieldsToHide = unserialize($conf->global->TASKLIST_HIDE_TASKS_FIELDS);

		?>
		<div id="task_list_clone" class="list-group-item" style="display:none">
			<div class="container-fluid">
				<?php if (!empty($conf->global->TASKLIST_SHOW_DOCPREVIEW)) { ?>
				<div class="col-md-1 col-sm-1 col-xs-1" rel="docpreview"></div>
				<a data-toggle="collapse" href="#" class="col-md-8 col-sm-8 col-xs-8"><h4 class="md-col-9" rel="taskRef"></h4></a>
				<?php } else { ?>
				<a data-toggle="collapse" href="#" class="col-md-9 col-sm-9 col-xs-9"><h4 class="md-col-9" rel="taskRef"></h4></a>
				<?php } ?>

				<span class="col-sm-3 col-md-3 col-xs-3" rel="link-of"></span>
			</div>
			<div class="collapse">
				<div class="container-fluid">
					<div class="row">
						<?php

						foreach ($TFields as $rel => $infos)
						{
							$display = $moreHTML = '';
							if (!empty($infos['moreHTML'])) $moreHTML = $infos['moreHTML'];

							if (in_array($rel, $TFieldsToHide))
							{
								$display = ' style="display:none"';
							}

							print '<div class="' . $infos['class'] . '" ' . $display . '>';
							print $infos['label'] . ' : <span rel="' . $rel . '"></span> ' . $moreHTML;
							print '</div>';
						}

						?>

					</div>
					<div class="row">
							<label rel="compteur"></label>

					</div>
					<div class="row">
							 <a href="#" class="btn btn-primary col-md-3 start"><?php echo $langs->trans('Start'); ?></a>
							 <a href="#" class="btn btn-info col-md-3 pause" style="display:none;"><?php echo $langs->trans('Pause'); ?> <span rel="start-time" class="badge"></span></a>
							 <a href="#" class="btn btn-success col-md-3 close" style="display:none;"><?php echo $langs->trans('Close'); ?></a>

					</div>
				</div>
			</div>
		</div>

