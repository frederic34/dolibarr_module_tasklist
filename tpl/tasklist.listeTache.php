<?php
	//Récupération de la liste des tâches à réaliser
	$TTasks = _getTaskList($PDOdb);
	//pre($TTasks,true);
	//Affichage des tâches
	if(count($TTasks)){
		
		foreach($TTasks as $task){
			?>
			<div class="ui-grid-b" style="margin-left: 15px; margin-right: 15px;">
				<div class="ui-block-a" style="font-size: 20px;">
					Tâche : <label id="task[<?php echo $task->rowid; ?>]['taskRef']"><?php echo $task->taskRef." - ".$task->taskLabel; ?></label>
				</div>
				<!--<div class="ui-block-b" style="font-size: 20px;">
					Projet : <label id="task[<?php echo $task->rowid; ?>]['projetRef']"><?php echo $task->projetRef." - ".$task->projetLabel; ?></label>
				</div>-->
				<div class="ui-block-a" style="margin-left: 35px; margin-right: 15px;">
					Date début : <label id="task[<?php echo $task->rowid; ?>]['dateo']"><?php echo date('d/m/Y H:i',strtotime($task->dateo)); ?></label>
				</div>
				<div class="ui-block-b" style="margin-left: 35px; margin-right: 15px;">
					Date fin : <label id="task[<?php echo $task->rowid; ?>]['datee']"><?php echo date('d/m/Y H:i',strtotime($task->datee)); ?></label>
				</div>
				<div class="ui-block-a" style="margin-left: 35px; margin-right: 15px;">
					Temps prévu : <label id="task[<?php echo $task->rowid; ?>]['planned_workload']"><?php echo $task->planned_workload; ?></label>
				</div>
				<div class="ui-block-b" style="margin-left: 35px; margin-right: 15px;">
					Temps passé : <label id="task[<?php echo $task->rowid; ?>]['spent_time']"><?php echo $task->spent_time; ?></label>
				</div>
				<div class="ui-block-a" style="margin-left: 35px; margin-right: 15px;">
					Progression : <label id="task[<?php echo $task->rowid; ?>]['progress']"><?php echo $task->progress; ?></label>
				</div>
				<div class="ui-block-b" style="margin-left: 35px; margin-right: 15px;">
					Priorité : <label id="task[<?php echo $task->rowid; ?>]['priority']"><?php echo $task->priority; ?></label>
				</div>
			</div>
			<div class="ui-grid-a" style="margin-left: 15px; margin-right: 15px;">
				<p style="text-align: center; width: 100%;">
					 <a href="#" data-role="button" data-theme="b" data-inline="true">Démarrer</a>
					 <a href="#" data-role="button" data-theme="b" data-inline="true">Mettre en pause</a>
					 <a href="#" data-role="button" data-theme="b" data-inline="true">Terminer</a>
				</p>
			</div>
			<hr>
			<?php
		}
		
	}