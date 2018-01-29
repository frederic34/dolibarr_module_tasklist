<?php
	ob_start();

	ini_set('display_errors','On');
	error_reporting(E_ALL);

	require('../config.php');
	dol_include_once('/projet/class/task.class.php');
	dol_include_once('/projet/class/projet.class.php');
	dol_include_once('/user/class/usergroup.class.php');
	dol_include_once('/core/lib/date.lib.php');

	if($conf->of->enabled) $resOF = dol_include_once('/of/class/ordre_fabrication_asset.class.php');
	else if($conf->asset->enabled) $resOF = dol_include_once('/asset/class/ordre_fabrication_asset.class.php');

	ob_clean();
	
	$PDOdb = new TPDOdb;

	$get = isset($_REQUEST['get'])?$_REQUEST['get']:'';
	$put = isset($_REQUEST['put'])?$_REQUEST['put']:'';
	
	_get($PDOdb,$get);
	_put($PDOdb,$put);
	_more($PDOdb, !empty($get) ? $get : $put);

function _get(&$PDOdb,$case) {
	switch ($case) {
		case 'task_liste':
            $TTask = _getTasklist($PDOdb,$_REQUEST['id'],$_REQUEST['type'],$_REQUEST['fk_user']);
			__out($TTask, 'json');
			break;
		
		case 'of_liste':
			if(!class_exists('TAssetOF')) __out(array());
			else __out(_list_of($PDOdb,$_REQUEST['fk_user']));
			break;
        case 'task-product-of':
			
			if(!class_exists('TAssetOF')) __out(array(),'json');
			
            $TProduct = _getProductTaskOF($PDOdb,(int)$_REQUEST['fk_of']);
            
            __out($TProduct, 'json');
            break;    
            
		case 'time_spent':
			__out(_getTimeSpent($PDOdb,$_REQUEST['id'],$_REQUEST['action']));
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
		case 'task-product-of':
			__out(_updateQtyOfLine($PDOdb,$_REQUEST['fk_of'],$_REQUEST['TLine']));
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

function _more(&$PDOdb, $action) {
	
	global $db, $hookmanager;
	
	$object= new Task($db);
	
	$Tid = explode('_', GETPOST('id'));
	$id = array_pop($Tid);
	
	$object->fetch($id);
	
	$hookmanager->initHooks(array('tasklistcard'));
	$reshook = $hookmanager->executeHooks('doActionsInterface', $parameters, $object, $action);
	
}

function _updateQtyOfLine(&$PDOdb,&$fk_of,&$TLine){
	global $db, $conf;
	
	$assetOf = new TAssetOF;
	$assetOf->load($PDOdb, $fk_of);
	
	$TLineUpdated=array('ids'=>array(),'errrors'=>array());
	
	if($assetOf->getId() && !empty($TLine)){
		
		foreach($TLine as $line){
			$lineOF = new TAssetOFLine;
			$lineOF->load($PDOdb, $line['lineid']);
			
			if($lineOF->getId()){
				$lineOF->qty_used = $line['qty_use'];
				$lineOF->save($PDOdb);
				
				$TLineUpdated['ids'][] = $lineOF->getId();
				
				if(!empty($lineOF->errors)) $TLineUpdated['errors'] = array_merge($TLineUpdated['errors'], $lineOF->errors);
				
			}
		}
	}
	
	return $TLineUpdated;
}

function _getProductTaskOF(&$PDOdb, $fk_of) {
    global $db,$conf;
    
    dol_include_once('/product/class/product.class.php');
    
    $Tab = array('productOF'=>array(), 'productTask'=>array());
    
    $of=new TAssetOF;
    $of->load($PDOdb, $fk_of);
    
    foreach($of->TAssetOFLine as &$line) {
        
        //if($line->type!='NEEDED') continue;
        
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
                ,'lineid'=>$line->getId()
				,'type'=>$line->type
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
                    ,'lineid'=>$line->getId()
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
				
				if((float)DOL_VERSION >= 3.7){
					$ttemp = $task->getSummaryOfTimeSpent();
				} else {
					$q = 'SELECT SUM(t.task_duration) as total_duration FROM '.MAIN_DB_PREFIX.'projet_task_time as t WHERE t.fk_task = '.$task->id;
					$resqll = $db->query($q);
					$ress = $db->fetch_object($resqll);
					$ttemp['total_duration'] = $ress->total_duration;
				}
				
				if($task->planned_workload>0) $task->progress = round($ttemp['total_duration'] / $task->planned_workload * 100, 2);
				
				$task->add_contact($user->id, 180, 'internal');
				
				$task->addTimeSpent($user);
				
				$PDOdb->Execute("UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '0000-00-00 00:00:00' WHERE rowid = ".$task->id);
				
				return convertSecondToTime($ttemp['total_duration']+$time);
			}
		}
	}
	
	return 0;
}

function _getTimeSpent(&$PDOdb,$taskId,$action){
	global $db;	
	
	//echo 'coucou';		
	$Tid = explode('_',$taskId);
	$id = array_pop($Tid);

	$task = new Task($db);
	$task->fetch($id);
	//echo "UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '".date('Y-m-d h:i:s')."' WHERE rowid = ".$task->id;
	if($task->id){
		if($action == 'stop')
		{
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
		} else {
			// CAS CLOSE
			
			$PDOdb->Execute("SELECT COUNT(task_duration) FROM ".MAIN_DB_PREFIX."projet_task_time  WHERE fk_task = ".$task->id);
	
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
		
		return array('result'=>'OK', 'tasklist_time_start'=>dol_print_date(time(), 'dayhour'));
	}
	
	return array('result'=>'KO');
}

//Lance le/les OFs en production s'ils ne le sont pas
function _openProdOF(&$PDOdb, &$db, &$task)
{
	global $conf;
	
	if ($task->fk_project > 0)
	{
		dol_include_once('/projet/class/project.class.php');
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

function _list_of(&$PDOdb, $fk_user=0) {
	global $db, $user, $conf, $mc, $langs;
	//echo "1";
	
	if(!class_exists('TAssetOF')) return false;
	
	$TRes = array();
	$static_task = new Task($db);
	$static_user = new User($db);
	$static_user->fetch($fk_user);
	$static_user->getrights('projet');
	
	$sql="SELECT DISTINCT tex.fk_of
	 FROM ".MAIN_DB_PREFIX."projet_task t 
	 	LEFT JOIN ".MAIN_DB_PREFIX."projet_task_extrafields tex ON (tex.fk_object=t.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."projet p ON (t.fk_projet=p.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."assetOf of ON (tex.fk_of=of.rowid)
	 WHERE of.status!='DRAFT' AND of.status!='CLOSE' AND  (t.progress < 100 OR t.progress IS NULL) AND tex.fk_of>0 AND p.entity IN(".getEntity('project',1).")";
	
	//echo $sql;
	if($fk_user>0 && empty($static_user->rights->projet->all->lire)) {
		
		$TRoles = $static_task->getUserRolesForProjectsOrTasks('',$static_user);
		$TTaskIds = implode(',',array_keys($TRoles));
		if(!empty($TTaskIds)) $sql .= " AND t.rowid IN (".$TTaskIds.") "; 
				
	}

	$TOF=array();
	$Tab = $PDOdb->ExecuteAsArray($sql);
	
	$TTransStatus = array_map(array($langs, 'trans'), TAssetOf::$TStatus);
	
	foreach($Tab as &$res) {
		
				$of=new TAssetOF;
				$of->withChild = false;
				$of->load($PDOdb, $res->fk_of);
				
				if($conf->entity != $of->entity) {

					if(empty($TEntity) && !empty($mc->dao)) {

						$mc->dao->getEntities();
						$TEntity=array();
						foreach ($mc->dao->entities as $entity)
						{
							if ($entity->active == 1)
							{
							$TEntity[$entity->id] = $entity->label; 
							}
						}
					}

					$label = $of->numero.' ('.$TEntity[$of->entity].')';
				}
				else {
					$label = $of->numero;

				}
                
				$TOF[$of->getId()] = array(
					'label'=>$label	
						,'statut'=>$TTransStatus[$of->status]
				);
				
		
	}
	
	return $TOF;
	
}

function _getTasklist(&$PDOdb,$id='',$type='', $fk_user = -1){
	global $db, $user, $conf,$mc;
	//echo "1";
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
	require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
	$formfile = new FormFile($db);
	
	$TRes = array();
	$static_task = new Task($db);
	$static_user = new User($db);
	
	$sql = "SELECT t.rowid, t.ref as taskRef, t.label as taskLabel, p.ref as projetRef, p.title as projetLabel, t.planned_workload,p.entity
			, t.progress, t.priority, t.tasklist_time_start";
			
	if (!empty($conf->ordo->enabled)) {
		$sql .= " ,t.date_estimated_start as dateo,t.date_estimated_end as datee";
	}
	else{
		$sql .= " , t.dateo, t.datee";
	}
			
	$sql.=" FROM ".MAIN_DB_PREFIX."projet_task as t 
				LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON (p.rowid = t.fk_projet)
				LEFT JOIN ".MAIN_DB_PREFIX."projet_task_extrafields as te ON (te.fk_object = t.rowid) 
			WHERE 1 AND p.entity IN(".getEntity('project',1).")";
	
	$date_deb = date('Y-m-d H:i',strtotime('+2 day'));
	
	if (!empty($conf->ordo->enabled)) {
		$sql .= " AND t.date_estimated_start < '".$date_deb."'
		";
	}
	else{
		$sql .= " AND t.dateo <= '".$date_deb."'";
	}
	
	$sql.=" AND (t.progress < 100 OR t.progress IS NULL) ";
	
	//echo $sql;
	
	//if(!empty($id)) $id = 0;

	if(!empty($type)){
		switch ($type) {
			case 'user':
				//On ne prends que les tâches assignées à l'utilisateurtask
				$static_user->fetch( $fk_user > 0 ? $fk_user : $id);
				$static_user->getrights('projet');
	
				if(empty($static_user->rights->projet->all->lire)) {
				
					$TRoles = $static_task->getUserRolesForProjectsOrTasks('',$static_user);
					$TTaskIds = implode(',',array_keys($TRoles));
					if(!empty($id) && $id>=0) $sql .= " AND t.rowid IN (".$TTaskIds.") "; // TODO le IN est limité, attention au nombre d'itération testé
					
				}
				break;
			case 'workstation':
				//On ne prends que les tâches liées au poste de travail
				if(!empty($id) && $id>=0) $sql .= " AND te.fk_workstation = ".$id." ";
				
				if($fk_user>0) {
					$static_user->fetch($fk_user);
					$static_user->getrights('projet');
					if(empty($static_user->rights->projet->all->lire)) {
						$TRoles = $static_task->getUserRolesForProjectsOrTasks('',$static_user);
						$TTaskIds = implode(',',array_keys($TRoles));
						if(!empty($TTaskIds)) $sql .= " AND t.rowid IN (".$TTaskIds.") ";
					} 
					
				}
				
				break;
			case 'of':
				//On ne prends que les tâches liées à l'Ordre de Fabrication
				if(!empty($id) && $id>=0) $sql .= " AND te.fk_of = ".$id." ";

				if($fk_user>0) {
					$static_user->fetch($fk_user);
					$static_user->getrights('projet');
					if(empty($static_user->rights->projet->all->lire)) {
						$TRoles = $static_task->getUserRolesForProjectsOrTasks('',$static_user);
						$TTaskIds = implode(',',array_keys($TRoles));
						if(!empty($TTaskIds)) $sql .= " AND t.rowid IN (".$TTaskIds.") "; 
					}
					
				}
				

				break;
		}
	}
	
	if (!empty($conf->ordo->enabled)) {
		$sql .= " ORDER BY t.date_estimated_start ASC";
	}
	else{
		$sql .= " ORDER BY t.dateo ASC";
	}

	$sql.=" LIMIT 20";

	$TOf = array();

	if($PDOdb->Execute($sql)){
		$TRes = $PDOdb->Get_All();
	
		foreach($TRes as &$res){
			$static_task->fetch($res->rowid);
			$static_task->fetch_optionals($static_task->id);
			
			$charset = mb_detect_encoding($res->taskLabel);
			$res->taskLabel=iconv($charset,'UTF-8', $res->taskLabel);
			
			if (!empty($conf->global->TASKLIST_SHOW_DOCPREVIEW))
			{
				$docpreview='';
				$commande_origin = _getCommandeFromProjectId($static_task->fk_project);
				if ($commande_origin)
				{
					$modulepart=$commande_origin->element; // commande
					$modulesubdir=dol_sanitizeFileName($commande_origin->ref);
					$filedir=$conf->commande->dir_output . '/' . $modulesubdir;

					$file_list=dol_dir_list($filedir,'files',0,'','(\.meta|_preview.*.*\.png)$','date',SORT_DESC);
					// Loop on each file found
					if (is_array($file_list))
					{
						foreach($file_list as $file)
						{
							$relativepath = $modulesubdir."/".$file["name"];
							$docpreview.= $formfile->showPreview($file,$modulepart,$relativepath,0,'').'&nbsp;';
						}
					}
				}

				$res->docpreview = json_encode($docpreview);
			}
			
			if(!empty($conf->global->TASKLIST_SHOW_REF_PROJECT)) {
				dol_include_once('/projet/class/project.class.php');
				$project = new Project($db);
				$project->fetch($static_task->fk_project);
				if(!empty($project->ref)) {
					$res->taskRef=$project->ref.'/'.$res->taskRef;
				}
			}

			if($static_task->array_options['options_fk_of']>0) {
				
				$fk_of = $static_task->array_options['options_fk_of'];
				
				if(!isset($TOf[$fk_of])) {
					$TOf[$fk_of]=new TAssetOF;
					$TOf[$fk_of]->withChild = false;
					$TOf[$fk_of]->load($PDOdb, $static_task->array_options['options_fk_of']);
				}
				
				$link_of = 'javascript:openOF('.$TOf[$fk_of]->getId().',\''.$TOf[$fk_of]->numero.'\');';
				
				$res->taskOF=' <a href="'.$link_of.'" class="btn btn-default">'.$TOf[$fk_of]->numero.'</a>';
                
			}
			else {
				$res->taskOF = '';	
			}
			
			$res->taskLabel.=' '.$res->progress.'%';

			if($conf->entity !=  $res->entity) {

                                 if(empty($TEntity) && !empty($mc->dao)) {

                                                $mc->dao->getEntities();
                                                $TEntity=array();
                                                foreach ($mc->dao->entities as $entity)
                                                {
                                                        if ($entity->active == 1)
                                                        {
                                                        $TEntity[$entity->id] = $entity->label; 
                                                        }
                                                }
                                  }

                                 $res->taskLabel.=' ('.$TEntity[$res->entity].')';
                        }


			$res->planned_workload = convertSecondToTime($res->planned_workload,'allhourmin');
			
			// TODO j'ai un peu l'impression que les tableaux $TSummary && $ttemp contiennent la même chose, mais pas sûr et pas le temps de vérif
			if((float)DOL_VERSION >= 3.7){
				// la fonction getSummaryOfTimeSpent existe qu'à partir de doli 3.7 
				$TSummary = $static_task->getSummaryOfTimeSpent($res->rowid);
			} else {
				$q = 'SELECT SUM(t.task_duration) as total_duration FROM '.MAIN_DB_PREFIX.'projet_task_time as t WHERE t.fk_task = '.$res->rowid;
				$resqll = $db->query($q);
				$ress = $db->fetch_object($resqll);
				$TSummary['total_duration'] = $ress->total_duration;
			}
			
			$res->spent_time = convertSecondToTime($TSummary['total_duration'],'allhourmin');

			if((float)DOL_VERSION >= 3.7){
				$ttemp = $static_task->getSummaryOfTimeSpent();
			} else {
				$q = 'SELECT SUM(t.task_duration) as total_duration FROM '.MAIN_DB_PREFIX.'projet_task_time as t WHERE t.fk_task = '.$static_task->id;
				$resqll = $db->query($q);
				$ress = $db->fetch_object($resqll);
				$ttemp['total_duration'] = $ress->total_duration;
			}

			if($static_task->planned_workload>0) $res->progress = round($ttemp['total_duration'] / $static_task->planned_workload * 100, 2);
			
			if($res->dateo === '0000-00-00 00:00:00') $res->dateo_aff = 'N/A';
			else $res->dateo_aff = dol_print_date($res->dateo,'dayhour');
			
			if($res->datee === '0000-00-00 00:00:00') $res->datee_aff = 'N/A';
			else $res->datee_aff = dol_print_date($res->datee,'dayhour');
			
			if($res->tasklist_time_start === '0000-00-00 00:00:00') $res->tasklist_time_start = '';
			else $res->tasklist_time_start = dol_print_date($res->tasklist_time_start,'dayhour');
			
		}
	}

	return $TRes;
}


function _getCommandeFromProjectId($fk_project)
{
	global $db,$conf,$TCommande;
	
	if (empty($TCommande)) $TCommande = array();
	
	if (!empty($TCommande[$fk_project]))
	{
		$commande = $TCommande[$fk_project];
		return $commande;
	}
	else
	{
		$sql = 'SELECT rowid FROM ' .MAIN_DB_PREFIX.'commande WHERE fk_projet = '.$fk_project.' AND entity = '.$conf->entity;
		$resql = $db->query($sql);
		if ($resql)
		{
			if ($obj = $db->fetch_object($resql))
			{
				$commande = new Commande($db);
				if ($commande->fetch($obj->rowid) > 0)
				{
					$TCommande[$fk_project] = $commande;
					return $commande;
				}
				
			}
		}
		else
		{
			dol_print_error($db);
		}
	}
	
	return false;
}
