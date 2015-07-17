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
			
		case 'time_spent':
			__out(_getTimeSpent($PDOdb,$_REQUEST['id']));
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
			__out(_stopTask($PDOdb,$_REQUEST['id'],$_REQUEST['hour'],$_REQUEST['minutes']));
			break;
		case 'close_task':
			__out(_closeTask($PDOdb,$_REQUEST['id'],$_REQUEST['hour'],$_REQUEST['minutes']));
			break;
		default:
			
			break;
	}
}

function _closeTask(&$PDOdb,$taskId,$hour,$minutes){
	global $db, $user;
	
	$Tid = explode('_',$taskId);
	$id = array_pop($Tid);
	
	_stopTask($PDOdb,$taskId,$hour,$minutes);
	
	$task = new Task($db);
	$task->fetch($id);
	
	$task->progress = 100;
	
	if( $task->update($user) ) return 1;
	
	return 0;
}

function _stopTask(&$PDOdb,$taskId,$hour,$minutes){
	global $db,$user;

	$Tid = explode('_',$taskId);
	$id = array_pop($Tid);

	$task = new Task($db);
	$task->fetch($id);
	//echo "UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '".date('Y-m-d h:i:s')."' WHERE rowid = ".$task->id;
	if($task->id){
		
		$PDOdb->Execute("SELECT tasklist_time_start FROM ".MAIN_DB_PREFIX."projet_task  WHERE rowid = ".$task->id);

		if($PDOdb->Get_line()){
			/*$time_start = strtotime($PDOdb->Get_field("tasklist_time_start"));
			$time_end = strtotime($time);*/
			//ime = $time_end - $time_start;
			$time = ($hour * 60 * 60) + ($minutes * 60 );
			
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

function _getTimeSpent(&$PDOdb,$taskId){
	global $db;	
	
	//echo 'coucou';		
	$Tid = explode('_',$taskId);
	$id = array_pop($Tid);

	$task = new Task($db);
	$task->fetch($id);
	//echo "UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '".date('Y-m-d h:i:s')."' WHERE rowid = ".$task->id;
	if($task->id){
		
		$PDOdb->Execute("SELECT tasklist_time_start FROM ".MAIN_DB_PREFIX."projet_task  WHERE rowid = ".$task->id);

		if($PDOdb->Get_line()){
			
			$t_start = new DateTime($PDOdb->Get_field("tasklist_time_start"));
			$t_end = new DateTime(date('Y-m-d H:i:s'));
			
			$interval = $t_start->diff($t_end);
			
			$heures = $interval->h;
			$minutes = ($interval->i > 0) ? $interval->i : 1;
			
			$heures = str_pad($heures, 2, '0', STR_PAD_LEFT);
			$minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
			
			return $heures.':'.$minutes;
			
		}
	}
	return -1;

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
	global $db, $user, $conf;
	//echo "1";
	$TRes = array();
	$static_task = new Task($db);
	$static_user = new User($db);
	
	$sql = "SELECT t.rowid, t.ref as taskRef, t.label as taskLabel, p.ref as projetRef, p.title as projetLabel, t.planned_workload
			, t.progress, t.priority";
			
	if($conf->scrumboard->enabled) {
		$sql .= " ,t.date_estimated_start as dateo,t.date_estimated_end as datee";
	}
	else{
		$sql .= " , t.dateo, t.datee";
	}
			
	$sql.=" FROM ".MAIN_DB_PREFIX."projet_task as t 
				LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON (p.rowid = t.fk_projet)
				LEFT JOIN ".MAIN_DB_PREFIX."projet_task_extrafields as te ON (te.fk_object = t.rowid) 
			WHERE t.progress != 100";
				
	//if(!empty($id)) $id = 0;

	if(!empty($type)){
		switch ($type) {
			case 'user':
				//On ne prends que les tâches assignées à l'utilisateur
				$static_user->fetch($id);
				$TRoles = $static_task->getUserRolesForProjectsOrTasks('',$static_user);
				$TTaskIds = implode(',',array_keys($TRoles));
				if(!empty($id) && $id>=0) $sql .= " AND t.rowid IN (".$TTaskIds.") ";
				break;
			case 'workstation':
				//On ne prends que les tâches liées au poste de travail
				if(!empty($id) && $id>=0) $sql .= " AND te.fk_workstation = ".$id." ";
				break;
			case 'of':
				//On ne prends que les tâches liées à l'Ordre de Fabrication
				if(!empty($id) && $id>=0) $sql .= " AND te.fk_of = ".$id." ";
				break;
		}
	}
	
	if($conf->scrumboard->enabled) {
		$sql .= " ORDER BY t.date_estimated_start ASC";
	}
	else{
		$sql .= " ORDER BY t.dateo ASC";
	}

	if($PDOdb->Execute($sql)){
		$TRes = $PDOdb->Get_All();
	
		foreach($TRes as &$res){
			$static_task->fetch($res->rowid);
			$static_task->fetch_optionals();
			$res->taskLabel=$res->taskLabel;

			if($static_task->array_options['options_fk_of']>0) {
				
				dol_include_once('/asset/class/ordre_fabrication_asset.class.php');
				
				$of=new TAssetOF;
				$of->withChild = false;
				$of->load($PDOdb, $static_task->array_options['options_fk_of']);
				
				$link_of = 'javascript:switch_onglet(\'onglet3\'); reload_liste_tache(\'onglet3\', '.$of->getId().');';
				
				$res->taskLabel.=' <a data-role="button" data-mini="true" data-shadow="false" data-inline="true" href="'.$link_of.'">'.$of->numero.'</a>';
			}

			$res->planned_workload = convertSecondToTime($res->planned_workload,'allhourmin');
			$TSummary = $static_task->getSummaryOfTimeSpent($res->rowid);
			$res->spent_time = convertSecondToTime($TSummary['total_duration'],'allhourmin');

			$ttemp = $static_task->getSummaryOfTimeSpent();
			$res->progress = round($ttemp['total_duration'] / $static_task->planned_workload * 100, 2);
			
			if($res->dateo === '0000-00-00 00:00:00') $res->dateo_aff = '00-00-0000 00:00:00';
			else $res->dateo_aff = dol_print_date($res->dateo,'dayhour');
			
			if($res->datee === '0000-00-00 00:00:00') $res->datee_aff = '00-00-0000 00:00:00';
			else $res->datee_aff = dol_print_date($res->datee,'dayhour');
			
		}
	}

	return $TRes;
}
