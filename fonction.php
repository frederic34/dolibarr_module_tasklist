<?php

function _getOfList(&$PDOdb){
	$sql = "SELECT of.rowid
			FROM ".MAIN_DB_PREFIX."";
}
	
function _getTasklist(&$PDOdb){
	global $db;
	
	$TRes = array();

	$sql = "SELECT t.rowid, t.ref as taskRef, t.label as taskLabel, p.ref as projetRef, p.title as projetLabel, t.planned_workload, t.progress, t.priority, t.dateo, t.datee
			FROM ".MAIN_DB_PREFIX."projet_task as t 
				LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON (p.rowid = t.fk_projet)
			ORDER BY t.dateo ASC";
	//echo $sql;
	if($PDOdb->Execute($sql)){
		$TRes = $PDOdb->Get_All();
		
		$static_tack = new Task($db);
		
		foreach($TRes as &$res){
			$res->planned_workload = convertSecondToTime($res->planned_workload,'allhourmin');
			$TSummary = $static_tack->getSummaryOfTimeSpent($res->rowid);
			$res->spent_time = convertSecondToTime($TSummary['total_duration'],'allhourmin');
		}
	}

	return $TRes;
}