		<!--
			DIV caché clonée pour afficher la liste : sert de template de base
		-->
		<div id="task_list_clone" class='task_list' style="display: none;">
			<div class="ui-grid-b" style="margin-left: 15px; margin-right: 15px;">
				<div class="ui-block-a" style="font-size: 20px;">
					<?php echo $langs->trans('Task'); ?> : <label rel="taskRef"></label>
				</div>
				<!--<div class="ui-block-b" style="font-size: 20px;">
					Projet : <label id="task[<?php echo $task->rowid; ?>]['projetRef']"><?php echo $task->projetRef." - ".$task->projetLabel; ?></label>
				</div>-->
				<div class="ui-block-a" style="margin-left: 35px; margin-right: 15px;">
					<?php echo $langs->trans('DateStart'); ?> : <label rel="dateo"></label>
				</div>
				<div class="ui-block-b" style="margin-left: 35px; margin-right: 15px;">
					<?php echo $langs->trans('DateEnd'); ?> : <label rel="datee"></label>
				</div>
				<div class="ui-block-a" style="margin-left: 35px; margin-right: 15px;">
					<?php echo $langs->trans('ExpectedTime'); ?> : <label rel="planned_workload"></label>
				</div>
				<div class="ui-block-b" style="margin-left: 35px; margin-right: 15px;">
					<?php echo $langs->trans('PastTime'); ?> : <label rel="spent_time"></label>
				</div>
				<div class="ui-block-a" style="margin-left: 35px; margin-right: 15px;">
					<?php echo $langs->trans('Progress'); ?> : <label rel="progress"></label> %
				</div>
				<div class="ui-block-b" style="margin-left: 35px; margin-right: 15px;">
					<?php echo $langs->trans('Priority'); ?> : <label rel="priority"></label>
				</div>
			</div>
			<div class="ui-grid-a" style="margin-left: 15px; margin-right: 15px;">
				<p style="text-align: center; width: 100%;">
					 <a href="#" data-role="button" data-theme="b" data-inline="true" class="start"><?php echo $langs->trans('Start'); ?></a>
					 <a href="#" data-role="button" data-theme="b" data-inline="true" class="pause" style="display:none;"><?php echo $langs->trans('Pause'); ?></a>
					 <a href="#" data-role="button" data-theme="b" data-inline="true" class="close" style="display:none;"><?php echo $langs->trans('Close'); ?></a>
				</p>
			</div>
			<hr>
		</div>