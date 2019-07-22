		<!--
			DIV caché clonée pour afficher la liste : sert de template de base
		-->


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
						<div class="col-xs-6 col-md-6">
							<?php echo $langs->trans('DateStart'); ?> : <span rel="dateo"></span>
						</div>
						<div class="col-xs-6 col-md-6">
							<?php echo $langs->trans('DateEnd'); ?> : <span rel="datee"></span>
						</div>
						<div class="col-xs-6 col-md-6">
							<?php echo $langs->trans('ExpectedTime'); ?> : <span rel="planned_workload"></span>
						</div>
						<div class="col-xs-6 col-md-6">
							<?php echo $langs->trans('PastTime'); ?> : <span rel="spent_time"></span>
						</div>
						<div class="col-xs-6 col-md-6">
							<?php echo $langs->trans('ProgressCalculated'); ?> : <span rel="calculate_progress"></span> %
						</div>
						<div class="col-xs-6 col-md-6">
							<?php echo $langs->trans('Priority'); ?> : <span rel="priority"></span>
						</div>
						<?php
						if(!empty($user->rights->tasklist->all->AllowToChangeTaskPercent)) {

						?>
						<div class="col-xs-6 col-md-6">
							<?php echo $langs->trans('ProgressDeclared'); ?> : <span rel="select-progress"></span>
						</div>
						<?php

						}
						if(!empty($conf->global->TASKLIST_SHOW_DESCRIPTION_TASK)) {
						    
						?>
						<div class="col-xs-12 col-md-12">
							<?php echo $langs->trans('Description'); ?> : <p rel="description"></p>
						</div>
						<?php
						
						}
						?>
					</div>
					<div class="row">
						<div class="col-xs-12 col-md-12" rel="extrafields">

						</div>
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

