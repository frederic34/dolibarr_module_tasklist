<?php

function _getOfList(&$PDOdb){
	$sql = "SELECT of.rowid
			FROM ".MAIN_DB_PREFIX."";
}
	
function _getTasklist(&$PDOdb,$id='',$type=''){
	global $db, $user;
	
	$TRes = array();
	$static_tack = new Task($db);
	$static_user = new User($db);
	
	$sql = "SELECT t.rowid, t.ref as taskRef, t.label as taskLabel, p.ref as projetRef, p.title as projetLabel, t.planned_workload, t.progress, t.priority, t.dateo, t.datee
			FROM ".MAIN_DB_PREFIX."projet_task as t 
				LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON (p.rowid = t.fk_projet)
				LEFT JOIN ".MAIN_DB_PREFIX."projet_task_extrafield as te ON (te.fk_object = t.rowid)";
	
	if($id && $type){
		switch ($type) {
			case 'user':
				//On ne prends que les tâches assignées à l'utilisateur
				$static_user->fetch($id);
				$TRoles = $static_tack->getUserRolesForProjectsOrTasks('',$static_user);
				$TTaskIds = implode(',',array_keys($TRoles));
				$sql .= " WHERE t.rowid IN (".$TTaskIds.") ";
				break;
			case 'workstation':
				//On ne prends que les tâches liées au poste de travail
				$sql .= " WHERE te.fk_workstation = ".$id;
				break;
			case 'of':
				$sql .= " WHERE te.fk_of = ".$id;
				break;
		}
	}

	$sql .= "ORDER BY t.dateo ASC";
	
	//echo $sql;
	if($PDOdb->Execute($sql)){
		$TRes = $PDOdb->Get_All();

		foreach($TRes as &$res){
			$res->planned_workload = convertSecondToTime($res->planned_workload,'allhourmin');
			$TSummary = $static_tack->getSummaryOfTimeSpent($res->rowid);
			$res->spent_time = convertSecondToTime($TSummary['total_duration'],'allhourmin');
		}
	}

	return $TRes;
}