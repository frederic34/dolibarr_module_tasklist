<?php
	ob_start();

	ini_set('display_errors','On');
	error_reporting(E_ALL);

	require('../config.php');
	dol_include_once('/tasklist/fonction.php');
	dol_include_once('/projet/class/task.class.php');
	dol_include_once('/projet/class/projet.class.php');
	dol_include_once('/user/class/usergroup.class.php');
	dol_include_once('/core/lib/date.lib.php');

	ob_clean();
	
	$PDOdb = new TPDOdb;

	$get = isset($_REQUEST['get'])?$_REQUEST['get']:'';
	$put = isset($_REQUEST['put'])?$_REQUEST['put']:'';
	
	_get($PDOdb,$get);
	_put($PDOdb,$put);

function _get(&$PDOdb,$case) {
	switch ($case) {
		case 'task_liste':
			__out(_getTasklist($PDOdb,$_REQUEST['id'],$_REQUEST['type']));
			break;
		default:
			
			break;
	}
	
}

function _put(&$PDOdb,$case) {
	
	switch ($case) {
		
		case 'start_task':
			__out(_startTask($PDOdb,$_REQUEST['id']));
			break;
		
		case 'stop_task':
			__out(_stopTask($PDOdb,$_REQUEST['id']));
			break;
		
		case 'close_task':
			__out(_closeTask($PDOdb,$_REQUEST['id']));
			break;
		
		/*case 'logout':
			/* Déloggue ! 
			$prefix=dol_getprefix();
			$sessionname='DOLSESSID_'.$prefix;
			$sessiontimeout='DOLSESSTIMEOUT_'.$prefix;
			if (! empty($_COOKIE[$sessiontimeout])) ini_set('session.gc_maxlifetime',$_COOKIE[$sessiontimeout]);
			session_name($sessionname);
			session_destroy();
			dol_syslog("End of session ".$sessionname);

			break;*/

		default:
			
			break;
	}
}

function _closeTask(&$PDOdb,$taskId){
	global $db;
}

function _stopTask(&$PDOdb,$taskId){
	global $db,$user;

	$Tid = explode('_',$taskId);
	$id = array_pop($Tid);

	$task = new Task($db);
	$task->fetch($id);
	//echo "UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '".date('Y-m-d h:i:s')."' WHERE rowid = ".$task->id;
	if($task->id){
		
		$PDOdb->Execute("SELECT tasklist_time_start FROM ".MAIN_DB_PREFIX."projet_task  WHERE rowid = ".$task->id);

		if($PDOdb->Get_line()){
			$time_start = strtotime($PDOdb->Get_field("tasklist_time_start"));
			$time_end = strtotime(date('Y-m-d H:i:s'));
			
			$time = $time_end - $time_start;
			
			if($time > 0){
				
				$task->timespent_date = date('Y-m-d');
		        $task->timespent_datehour = date('Y-m-d H:i:s');;
		        $task->timespent_duration = $time;
		        $task->timespent_fk_user = $user->id;
				$ttemp = $task->getSummaryOfTimeSpent();
				$task->progress = round($ttemp['total_duration'] / $task->planned_workload * 100, 2);
				
				$task->addTimeSpent($user);
				
				$PDOdb->Execute("UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '0000-00-00 00:00:00' WHERE rowid = ".$task->id);
				
				return 1;
			}
		}
	}
	
	return 0;
}

function _startTask(&$PDOdb,$taskId){
	global $db,$user;

	$Tid = explode('_',$taskId);
	$id = array_pop($Tid);

	$task = new Task($db);
	$task->fetch($id);
	//echo "UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '".date('Y-m-d h:i:s')."' WHERE rowid = ".$task->id;
	if($task->id){
		$sql  = "UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '".date('Y-m-d H:i:s')."' WHERE rowid = ".$task->id;
		
		if($task->progress == 0){
			$task->date_start = date('Y-m-d H:i:s');
			$task->update($user);
		}
		
		$PDOdb->Execute($sql);
		
		return 1;
	}
	
	return 0;
}

function _getTasklist(&$PDOdb,$id='',$type=''){
	global $db, $user;
	//echo "1";
	$TRes = array();
	$static_tack = new Task($db);
	$static_user = new User($db);
	
	$sql = "SELECT t.rowid, t.ref as taskRef, t.label as taskLabel, p.ref as projetRef, p.title as projetLabel, t.planned_workload, t.progress, t.priority, t.dateo, t.datee
			FROM ".MAIN_DB_PREFIX."projet_task as t 
				LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON (p.rowid = t.fk_projet)
				LEFT JOIN ".MAIN_DB_PREFIX."projet_task_extrafields as te ON (te.fk_object = t.rowid) ";
				
	//if(!empty($id)) $id = 0;

	if(!empty($type)){
		switch ($type) {
			case 'user':
				//On ne prends que les tâches assignées à l'utilisateur
				$static_user->fetch($id);
				$TRoles = $static_tack->getUserRolesForProjectsOrTasks('',$static_user);
				$TTaskIds = implode(',',array_keys($TRoles));
				if(!empty($id) && $id>=0) $sql .= " WHERE t.rowid IN (".$TTaskIds.") ";
				break;
			case 'workstation':
				//On ne prends que les tâches liées au poste de travail
				if(!empty($id) && $id>=0) $sql .= " WHERE te.fk_workstation = ".$id." ";
				break;
			case 'of':
				//On ne prends que les tâches liées à l'Ordre de Fabrication
				if(!empty($id) && $id>=0) $sql .= " WHERE te.fk_of = ".$id." ";
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

			$static_tack->fetch($res->rowid);
			$ttemp = $static_tack->getSummaryOfTimeSpent();
			$res->progress = round($ttemp['total_duration'] / $static_tack->planned_workload * 100, 2);
		}
	}

	return $TRes;
}
