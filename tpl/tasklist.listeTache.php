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
						 <a href="#" data-role="button" data-theme="b" data-inline="true" class="start"><?php echo $langs->trans('Start'); ?></a>
						 <a href="#" data-role="button" data-theme="c" data-inline="true" class="pause" style="display:none;"><?php echo $langs->trans('Pause'); ?></a>
						 <a href="#" data-role="button" data-theme="e" data-inline="true" class="close" style="display:none;"><?php echo $langs->trans('Close'); ?></a>
					
				</div>
							
			</p>
		</div>
								
