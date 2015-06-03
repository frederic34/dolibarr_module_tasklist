<?php

function _getOfList(&$PDOdb){
	$sql = "SELECT of.rowid
			FROM ".MAIN_DB_PREFIX."";
}
	
function _getTasklist(&$PDOdb){
	
	$TRes = array();

	$sql = "SELECT t.rowid, t.ref as taskRef, t.label, p.ref as projetRef, t.planned_workload, t.progress, t.priority, t.dateo, t.datee
			FROM ".MAIN_DB_PREFIX."projet_task as t 
				LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON (p.rowid = t.fk_projet)
			ORDER BY t.dateo ASC";
	//echo $sql;
	$PDOdb->Execute($sql);
	$TRes = $PDOdb->Get_All();

	return $TRes;
}