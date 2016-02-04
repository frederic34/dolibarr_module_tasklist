		<!--
			DIV caché clonée pour afficher la liste : sert de template de base
		-->
								  

		<div id="task_list_clone" class="list-group-item" style="display:none">
			<a data-toggle="collapse" href="#"><h4 class="md-col-12" rel="taskRef"></h4></a>
			<div class="collapse">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-6">
							<?php echo $langs->trans('DateStart'); ?> : <span rel="dateo"></span>
						</div>
						<div class="col-md-6">
							<?php echo $langs->trans('DateEnd'); ?> : <span rel="datee"></span>
						</div>
						<div class="col-md-6">
							<?php echo $langs->trans('ExpectedTime'); ?> : <span rel="planned_workload"></span>
						</div>
						<div class="col-md-6">
							<?php echo $langs->trans('PastTime'); ?> : <span rel="spent_time"></span>
						</div>
						<div class="col-md-6">
							<?php echo $langs->trans('Progress'); ?> : <span rel="progress"></span> %
						</div>
						<div class="col-md-6">
							<?php echo $langs->trans('Priority'); ?> : <span rel="priority"></span>
						</div>
					</div>
					<div class="row">
							<label rel="compteur"></label>
						
					</div>
					<div class="row">
							 <a href="#" class="btn btn-primary col-md-3 start"><?php echo $langs->trans('Start'); ?></a>
							 <a href="#" class="btn btn-info col-md-3 pause" style="display:none;"><?php echo $langs->trans('Pause'); ?></a>
							 <a href="#" class="btn btn-warning col-md-3 close" style="display:none;"><?php echo $langs->trans('Close'); ?></a>
						
					</div>
				</div>	
			</div>
		</div>
								
