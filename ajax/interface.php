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
            $TTask = _getTasklist($PDOdb,$_REQUEST['id'],$_REQUEST['type']);
            __out($TTask, 'json');
			break;
			
        case 'task-product-of':
            $TProduct = _getProductTaskOF($PDOdb,(int)$_REQUEST['fk_of']);
            
            __out($TProduct, 'json');
            break;    
            
		case 'time_spent':
			__out(_getTimeSpent($PDOdb,$_REQUEST['id']));
			break;
		
		case 'logged-status':
			print 'ok';
			
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
			__out(_stopTask($PDOdb,$_REQUEST['id'],$_REQUEST['hour'],$_REQUEST['minutes'],$_REQUEST['id_user_selected']));
			break;
		case 'close_task':
			__out(_closeTask($PDOdb,$_REQUEST['id'],$_REQUEST['hour'],$_REQUEST['minutes'],$_REQUEST['id_user_selected']));
			break;
		default:
			
			break;
	}
}

function _getProductTaskOF(&$PDOdb, $fk_of) {
    global $db;
    
    dol_include_once('/asset/class/ordre_fabrication_asset.class.php');
    dol_include_once('/product/class/product.class.php');
    
    $Tab = array('productOF'=>array(), 'productTask'=>array());
    
    $of=new TAssetOF;
    $of->load($PDOdb, $fk_of);
    
    foreach($of->TAssetOFLine as &$line) {
        
        if($line->type!='NEEDED') continue;
        
        $fk_product = $line->fk_product;
        
        $p=new Product($db);
        $p->fetch($fk_product);
        
        
        if(empty($line->TWorkstation)) {
            $Tab['productOF'][] = array(
                'fk_product'=>$fk_product
                ,'label'=>$p->label
                ,'qty_needed'=>$line->qty_needed
                ,'qty'=>$line->qty
                ,'qty_used'=>$line->qty_used
            );
        }
        else{
            foreach($line->TWorkstation as &$ws) {
                
                $Tab['productTask'][$ws->getId()][]= array(
                    'fk_product'=>$fk_product
                    ,'label'=>$p->label
                    ,'qty_needed'=>$line->qty_needed
                    ,'qty'=>$line->qty
                    ,'qty_used'=>$line->qty_used
                );
            }
            
        }
        
        
    }
    
    
    return $Tab;
    
    
}

function _closeTask(&$PDOdb,$taskId,$hour,$minutes,$id_user_selected){
	global $db, $user;
	
	$Tid = explode('_',$taskId);
	$id = array_pop($Tid);
	
	_stopTask($PDOdb,$taskId,$hour,$minutes,$id_user_selected);
	
	$task = new Task($db);
	$task->fetch($id);
	
	$task->progress = 100;
	
	if( $task->update($user) ) return 1;
	
	return 0;
}

function _stopTask(&$PDOdb,$taskId,$hour,$minutes,$id_user_selected=0){
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
		        //$task->timespent_fk_user = $user->id;
		        $task->timespent_fk_user = $id_user_selected;
				$ttemp = $task->getSummaryOfTimeSpent();
				if($task->planned_workload>0) $task->progress = round($ttemp['total_duration'] / $task->planned_workload * 100, 2);
				
				$task->addTimeSpent($user);
				
				$PDOdb->Execute("UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '0000-00-00 00:00:00' WHERE rowid = ".$task->id);
				
				return convertSecondToTime($ttemp['total_duration']+$time);
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
	return '00:00';

}

function _startTask(&$PDOdb,$taskId){
	global $db,$user,$conf;

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
		
		if ($conf->asset->enabled) _openProdOF($PDOdb, $db, $task);
		
		return 1;
	}
	
	return 0;
}

//Lance le/les OFs en production s'ils ne le sont pas
function _openProdOF(&$PDOdb, &$db, &$task)
{
	if ($task->fk_project > 0)
	{
		dol_include_once('/projet/class/project.class.php');
		dol_include_once('/asset/class/ordre_fabrication_asset.class.php');
		dol_include_once('/asset/class/asset.class.php');
		
		$project = new Project($db);
		$project->fetch($task->fk_project);

		if ($project->id > 0)
		{
			$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'assetOf WHERE fk_project = '.$project->id.' AND status = "VALID"';
			$PDOdb->Execute($sql);
			
			while ($res = $PDOdb->Get_line())
			{
				$assetOf = new TAssetOF;
				$assetOf->load($PDOdb, $res->rowid);
				
				$assetOf->openOF($PDOdb);
			}
			
		}
		
	}
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
				if(!empty($id) && $id>=0) $sql .= " AND t.rowid IN (".$TTaskIds.") "; // TODO le IN est limité, attention au nombre d'itération testé
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
				
				$link_of = 'javascript:openOF('.$of->getId().');';
				
				$res->taskLabel.=' <a href="'.$link_of.'">'.$of->numero.'</a>';
                
                $res->taskLabel.=' '.$res->progress.'%';
			}

			$res->planned_workload = convertSecondToTime($res->planned_workload,'allhourmin');
			$TSummary = $static_task->getSummaryOfTimeSpent($res->rowid);
			$res->spent_time = convertSecondToTime($TSummary['total_duration'],'allhourmin');

			$ttemp = $static_task->getSummaryOfTimeSpent();
			if($static_task->planned_workload>0) $res->progress = round($ttemp['total_duration'] / $static_task->planned_workload * 100, 2);
			
			if($res->dateo === '0000-00-00 00:00:00') $res->dateo_aff = '00-00-0000 00:00:00';
			else $res->dateo_aff = dol_print_date($res->dateo,'dayhour');
			
			if($res->datee === '0000-00-00 00:00:00') $res->datee_aff = '00-00-0000 00:00:00';
			else $res->datee_aff = dol_print_date($res->datee,'dayhour');
			
		}
	}

	return $TRes;
}
