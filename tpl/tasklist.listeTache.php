		<!--
			DIV caché clonée pour afficher la liste : sert de template de base
		-->
								  

		<div id="task_list_clone" style="display:none">
			<h3 rel="taskRef"></h3>
			<p>
				<div class="ui-grid-a ui-responsive">
					<div class="ui-block-a">
						<?php echo $langs->trans('DateStart'); ?> : <span rel="dateo"></span>
					</div>
					<div class="ui-block-b">
						<?php echo $langs->trans('DateEnd'); ?> : <span rel="datee"></span>
					</div>
					<div class="ui-block-a">
						<?php echo $langs->trans('ExpectedTime'); ?> : <span rel="planned_workload"></span>
					</div>
					<div class="ui-block-b">
						<?php echo $langs->trans('PastTime'); ?> : <span rel="spent_time"></span>
					</div>
					<div class="ui-block-a">
						<?php echo $langs->trans('Progress'); ?> : <span rel="progress"></span> %
					</div>
					<div class="ui-block-b">
						<?php echo $langs->trans('Priority'); ?> : <span rel="priority"></span>
					</div>
				</div>
				<div>
						<label rel="compteur"></label>
					
				</div>
				<div>
						 <a href="#" class="ui-btn ui-icon-arrow-r ui-btn-icon-right ui-btn-inline ui-btn-b start"><?php echo $langs->trans('Start'); ?></a>
						 <a href="#" class="ui-btn ui-icon-arrow-l ui-btn-icon-right ui-btn-inline ui-btn-b pause" style="display:none;"><?php echo $langs->trans('Pause'); ?></a>
						 <a href="#" class="ui-btn ui-icon-check ui-btn-icon-right ui-btn-inline ui-btn-b close" style="display:none;"><?php echo $langs->trans('Close'); ?></a>
					
				</div>
							
			</p>
		</div>
								
