<?php
ob_start();
	if (!defined("NOCSRFCHECK")) define('NOCSRFCHECK', 1);
	if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);

	ini_set('display_errors','On');
	error_reporting(E_ALL);

	require('../config.php');
	dol_include_once('/projet/class/task.class.php');
	dol_include_once('/projet/class/projet.class.php');
	dol_include_once('/user/class/usergroup.class.php');
	dol_include_once('/core/lib/date.lib.php');
	dol_include_once('/core/lib/files.lib.php');

	if($conf->of->enabled) $resOF = dol_include_once('/of/class/ordre_fabrication_asset.class.php');
	else if($conf->{ ATM_ASSET_NAME }->enabled) $resOF = dol_include_once('/' . ATM_ASSET_NAME . '/class/ordre_fabrication_asset.class.php');

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

		case 'of-documents':
			print _showDocuments($PDOdb,$_REQUEST['id']);
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
		case 'progress_task':
		    __out(_progressTask($_REQUEST['id'],$_REQUEST['progress']));
		    break;
		case 'set-of-rank':
		    __out(_setOfRank($PDOdb, $_REQUEST['fk_of'],$_REQUEST['new_rank']));
		    break;
		default:

			break;
	}
}

function _progressTask($fk_task, $progress) {
    global $db,$user;
    $t=new Task($db);
    $t->fetch($fk_task);

    $t->progress = (int)$progress;

    $res=$t->update($user);
    if($res<=0) {
        var_dump($res,$t);
        exit;
    }

    return 'OK';

}
function _setOfRank($PDOdb, $fk_of, $new_rank) {

   $of = new TAssetOF;
   $of->load($PDOdb,$fk_of);
   $of->rank = $new_rank;
   return $of->save($PDOdb);


}

function _more(&$PDOdb, $action) {

	global $db, $hookmanager;

	$object= new Task($db);

	$Tid = explode('_', GETPOST('id', 'alphanohtml'));
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
                if($conf->global->OF_MANAGE_NON_COMPLIANT && ($assetOf->status=='OPEN' || $assetOf->status == 'CLOSE')){
                    $lineOF->qty_non_compliant=$line['qty_non_compliant'];
                }
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
                ,'qty_non_compliant'=> $line->qty_non_compliant
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
    $Tab['conf'] = $conf;
    $Tab['of'] = $of;
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

				$ttemp['total_duration']+= $task->timespent_duration;

				if($task->planned_workload>0) {
				    if ($task->progress === null) $task->progress = 0;
				}

				$task->add_contact($user->id, 180, 'internal');

				$task->addTimeSpent($user);

				$PDOdb->Execute("UPDATE ".MAIN_DB_PREFIX."projet_task SET tasklist_time_start = '0000-00-00 00:00:00' WHERE rowid = ".$task->id);
				if(!empty($task->planned_workload)) $progress_calculated = round($ttemp['total_duration'] / $task->planned_workload * 100,2);
				else $progress_calculated = 0;
				return array('time'=> convertSecondToTime($ttemp['total_duration']),'progress_calculated'=>$progress_calculated, 'progress'=>$task->progress);
			}
		}
	}

	return array('time'=>0, 'progress'=>0);
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

		if ($conf->{ ATM_ASSET_NAME }->enabled) _openProdOF($PDOdb, $db, $task);

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
		dol_include_once('/' . ATM_ASSET_NAME . '/class/asset.class.php');

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

	if(!empty($conf->global->ASSET_CUMULATE_PROJECT_TASK)){
        $sql = "SELECT DISTINCT ee.fk_source as fk_of
         FROM " . MAIN_DB_PREFIX . "projet_task t
            LEFT JOIN " . MAIN_DB_PREFIX . "element_element ee ON (ee.fk_target=t.rowid AND targettype='project_task' AND sourcetype='tassetof')
            LEFT JOIN " . MAIN_DB_PREFIX . "projet p ON (t.fk_projet=p.rowid)
            LEFT JOIN " . MAIN_DB_PREFIX . "assetOf of ON (ee.fk_source=of.rowid)
         WHERE of.status!='DRAFT' AND of.status!='CLOSE' AND  (t.progress < 100 OR t.progress IS NULL) AND ee.fk_source>0 AND p.entity IN(" . getEntity('project', 1) . ")";


    }else {

        $sql = "SELECT DISTINCT tex.fk_of
         FROM " . MAIN_DB_PREFIX . "projet_task t
            LEFT JOIN " . MAIN_DB_PREFIX . "projet_task_extrafields tex ON (tex.fk_object=t.rowid)
            LEFT JOIN " . MAIN_DB_PREFIX . "projet p ON (t.fk_projet=p.rowid)
            LEFT JOIN " . MAIN_DB_PREFIX . "assetOf of ON (tex.fk_of=of.rowid)
         WHERE of.status!='DRAFT' AND of.status!='CLOSE' AND  (t.progress < 100 OR t.progress IS NULL) AND tex.fk_of>0 AND p.entity IN(" . getEntity('project', 1) . ")";
    }

	//echo $sql;
	if($fk_user>0 && empty($static_user->rights->projet->all->lire)) {

		$TRoles = $static_task->getUserRolesForProjectsOrTasks('',$static_user);
		$TTaskIds = implode(',',array_keys($TRoles));
		if(!empty($TTaskIds)) $sql .= " AND t.rowid IN (".$TTaskIds.") ";

	}

	$TOF=array();
    if(!empty($conf->global->OF_RANK_PRIOR_BY_LAUNCHING_DATE))$sql .= ' ORDER BY of.date_lancement ASC, of.rank ASC, of.rowid DESC';
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

				$TOF[] = array(
				    'label'=>$label
					,'statut'=>$TTransStatus[$of->status]
					,'date_lancement'=>$of->date_lancement
					,'rank'=>$of->rank
					,'fk_of'=>$of->getId()
				);


	}
    $TOF['conf'] = $conf;
	return $TOF;

}

function _showDocuments($PDOdb, $fk_of) {
    global $conf, $langs, $db, $user;
    $out =  '';
    if(!empty($fk_of)) {
        $object = new TAssetOF;
        $object->load($PDOdb, $fk_of);
        $upload_dir = $conf->of->multidir_output[$object->entity] . '/' . get_exdir(0, 0, 0, 0, $object, 'tassetof') . dol_sanitizeFileName($object->ref);
        $out .= _printTableFiles($upload_dir, $object, $langs->trans('OFAsset') . ' : <strong>' . $object->ref . '</strong>', 'of', 'flat-table flat-table-1', true);

        //commande
        if(!empty($conf->global->OF_SHOW_ORDER_DOCUMENTS)) {
            dol_include_once('/commande/class/commande.class.php');
            $langs->load('orders');
            $TCommandes = array();
            if(!empty($conf->global->OF_MANAGE_ORDER_LINK_BY_LINE)) {
                $displayOrders = '';
                $TLine_to_make = $object->getLinesProductToMake();

                foreach($TLine_to_make as $line) {
                    if(!empty($line->fk_commandedet)) {
                        $commande = new Commande($db);
                        $orderLine = new OrderLine($db);
                        $orderLine->fetch($line->fk_commandedet);
                        $commande->fetch($orderLine->fk_commande);
                        $TCommandes[$orderLine->fk_commande] = $commande;
                    }
                }
                if(!empty($TCommandes) && !empty($user->rights->commande->lire)) {
                    foreach($TCommandes as $commande) {
                        $upload_dir = $conf->commande->dir_output . "/" . dol_sanitizeFileName($commande->ref);
                        $out .= _printTableFiles($upload_dir, $commande, $langs->trans('Order'), 'commande', 'flat-table flat-table-2');
                    }
                }
            }
            else if(!empty($object->fk_commande)) {
                $commande = new Commande($db);
                $commande->fetch($object->fk_commande);
                $upload_dir = $conf->commande->dir_output . "/" . dol_sanitizeFileName($commande->ref);
                $out .= _printTableFiles($upload_dir, $commande, $langs->trans('Order') . ' : <strong>' . $commande->ref . '</strong>', 'commande', $class = 'flat-table flat-table-2');
            }
        }

        //Product
        if(!empty($conf->global->OF_SHOW_PRODUCT_DOCUMENTS) && !empty($object->TAssetOFLine)) {
            foreach($object->TAssetOFLine as $line) {
                if(!empty($line->fk_product)) {
                    $product = new Product($db);
                    $product->fetch($line->fk_product);
                    if(!empty($conf->product->enabled)) $upload_dir = $conf->product->multidir_output[$product->entity] . '/' . get_exdir(0, 0, 0, 0, $product, 'product') . dol_sanitizeFileName($product->ref);
                    else if(!empty($conf->service->enabled)) $upload_dir = $conf->service->multidir_output[$product->entity] . '/' . get_exdir(0, 0, 0, 0, $product, 'product') . dol_sanitizeFileName($product->ref);

                    $out .= _printTableFiles($upload_dir, $product, $langs->trans('Product') . ' : <strong>' . $product->ref . '</strong> ' . $product->label, 'product', $class = 'flat-table flat-table-3');
                }
            }
        }
    }
    return $out;
}

function _printTableFiles($upload_dir, $object, $title, $modulepart, $class = 'flat-table flat-table-1', $display_if_empty=false) {
    global $langs;
    $langs->load('mails');
    $TFiles = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', 0, 0, 1);
    $out = '';

    if(!empty($TFiles)) {
		$out .= '<table width="100%" class="list-doc-of ' . $class . '">';
		$out .= '<thead><th nowrap="nowrap">' . $title . '</th></thead>';
        foreach($TFiles as $file) {
            $previewurl = getAdvancedPreviewUrl($modulepart, $object->ref . '/' . $file['name'], 0, '');
            $preview = '';
            if(!empty($previewurl)) {
                $preview = '&nbsp;&nbsp;&nbsp;<a href="' . $previewurl . '"><i class="fa fa-search" aria-hidden="true"></i></a>';
            }
            $out .= '<tr><td nowrap="nowrap"><a href="' . dol_buildpath("/document.php?modulepart=" . $modulepart . "&entity=" . $object->entity . "&file=" . urlencode($object->ref . '/' . $file['name']), 1) . '">' . $file['name'] . '</a>' . $preview . '</td></tr>';
        }
		$out .= '</table>';
    }
    else if ($display_if_empty) {
		$out .= '<table width="100%" class="list-doc-of ' . $class . '">';
		$out .= '<thead><th nowrap="nowrap">' . $title . '</th></thead>';
        $out .= '<tr><td nowrap="nowrap">' . $langs->trans('NoAttachedFiles') . '</td></tr>';
		$out .= '</table>';
    }

    return $out;
}

function _printShowDocumentsIcon($PDOdb, $fk_of){
    return '<span class="hover-cursor" onclick="showDocuments('.$fk_of.')"><i class="fa fa-download" aria-hidden="true"></i></span><div id="doc-of-'.$fk_of.'" style="display: none;">'._showDocuments($PDOdb, $fk_of).'</div>';
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

	$sql = "SELECT t.rowid, t.ref as taskRef, t.label as taskLabel, t.description as taskDescription, p.ref as projetRef, p.title as projetLabel, t.planned_workload,p.entity
			, t.progress, t.priority, t.tasklist_time_start";

	if (!empty($conf->ordo->enabled)) {
		$sql .= " ,t.date_estimated_start as dateo,t.date_estimated_end as datee";
	}
	else{
		$sql .= " , t.dateo, t.datee";
	}

	$sql.=" FROM ".MAIN_DB_PREFIX."projet_task as t
				LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON (p.rowid = t.fk_projet)
				LEFT JOIN ".MAIN_DB_PREFIX."projet_task_extrafields as te ON (te.fk_object = t.rowid)";
	if(!empty($conf->global->ASSET_CUMULATE_PROJECT_TASK)){
        $sql.= "LEFT JOIN ".MAIN_DB_PREFIX."element_element as ee ON (ee.fk_target=t.rowid AND targettype='project_task' AND sourcetype='tassetof')";
    }
	$sql.= " WHERE 1 AND p.entity IN(".getEntity('project',1).")";

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
			    $id_user =  $fk_user > 0 ? $fk_user : $id;
			    if(empty($id_user))$id_user=$user->id;

				$static_user->fetch($id_user);
				$static_user->getrights('projet');

				if(empty($static_user->rights->projet->all->lire) && empty($user->admin)) {

					$TRoles = $static_task->getUserRolesForProjectsOrTasks('',$static_user);
					$TTaskIds = implode(',',array_keys($TRoles));
					$sql .= " AND t.rowid IN (".$TTaskIds.") "; // TODO le IN est limité, attention au nombre d'itération testé

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
				if(!empty($id) && $id>=0){
				    if(empty($conf->global->ASSET_CUMULATE_PROJECT_TASK))$sql .= " AND te.fk_of = ".$id." ";
				    else $sql .= " AND ee.fk_source = ".$id." ";
                }

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

    if(!empty($conf->global->ASSET_CUMULATE_PROJECT_TASK)){
        $sql .= " GROUP BY ee.fk_target ";
    }

    if (!empty($conf->ordo->enabled)) {
		$sql .= " ORDER BY t.progress DESC, t.date_estimated_start ASC,t.rowid ASC";
	}
	else{
		$sql .= " ORDER BY t.progress DESC, t.dateo ASC,t.rowid ASC";
	}

	$sql.=" LIMIT 20";

	$TOf = array();

	$extrafields= new ExtraFields($db);
	$extrafields->fetch_name_optionals_label($static_task->table_element);
	if(!empty($conf->global->TASKLIST_SHOW_LINE_ORDER_EXTRAFIELD_JUST_THEM)) {
	    $TIn = explode(',', $conf->global->TASKLIST_SHOW_LINE_ORDER_EXTRAFIELD_JUST_THEM);

	    foreach($extrafields->attribute_label as $field=>$data) {

	        if(!in_array($field, $TIn)) {
	            unset($extrafields->attribute_label[$field]);

	        }

	    }

	}

	if($PDOdb->Execute($sql)){
		$TRes = $PDOdb->Get_All();

		foreach($TRes as &$res){
			$static_task->fetch($res->rowid);
			$static_task->fetch_optionals($static_task->id);
            if( !empty($conf->global->ASSET_CUMULATE_PROJECT_TASK) ) {
                if (!isset($conf->tassetof))$conf->tassetof = new \stdClass(); // for warning
                $conf->tassetof->enabled = 1; // pour fetchobjectlinked
                $static_task->fetchObjectLinked(0,'tassetof',$static_task->id,$static_task->element,'OR',1,'sourcetype',0);
            }
			$charset = mb_detect_encoding($res->taskLabel);
			$res->taskLabel=iconv($charset,'UTF-8', $res->taskLabel);

			if(!empty($conf->global->TASKLIST_SHOW_EXTRAFIELDS)) {
			     $res->extrafields = '<table class="table table-hover" >'.$static_task->showOptionals($extrafields,'view',array('style'=>'style="background-color:rgb(91, 192, 222); color:#000; font-size:15px;"','colspan'=>1)).'</table>';
			}
			else {
			    $res->extrafields='';
			}

			if (!empty($conf->global->TASKLIST_SHOW_DOCPREVIEW))
			{
				$docpreview='';
				$commande_origin = _getCommandeFromProjectId($static_task->fk_project);
				if ($commande_origin && !empty($user->rights->commande->lire))
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

			if(!empty($conf->global->TASKLIST_SHOW_DESCRIPTION_TASK)) {
			    $res->taskDescription=nl2br($res->taskDescription);
			}


			if((!empty($conf->global->ASSET_CUMULATE_PROJECT_TASK) && !empty($static_task->linkedObjectsIds['tassetof'])) || $static_task->array_options['options_fk_of']>0) {
                $res->taskOF = '';
                if(empty($conf->global->ASSET_CUMULATE_PROJECT_TASK))_btOF( $PDOdb, $TOf, $static_task->array_options['options_fk_of'], $res);
                else {
                    if(!empty($static_task->linkedObjectsIds['tassetof'])) {
                        foreach($static_task->linkedObjectsIds['tassetof'] as $fk_of) {
                            _btOF($PDOdb, $TOf, $fk_of, $res);
                        }
                    }
                }
			}
			else {
				$res->taskOF = '';
			}

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

			if((float)DOL_VERSION >= 3.7){
				$ttemp = $static_task->getSummaryOfTimeSpent();
			} else {
				$q = 'SELECT SUM(t.task_duration) as total_duration FROM '.MAIN_DB_PREFIX.'projet_task_time as t WHERE t.fk_task = '.$static_task->id;
				$resqll = $db->query($q);
				$ress = $db->fetch_object($resqll);
				$ttemp['total_duration'] = $ress->total_duration;
			}

			$res->spent_time = convertSecondToTime($ttemp['total_duration'],'allhourmin');

			if($static_task->planned_workload>0) $res->calculate_progress = round($ttemp['total_duration'] / $static_task->planned_workload * 100, 2);

			if($res->dateo === '0000-00-00 00:00:00') $res->dateo_aff = 'N/A';
			else $res->dateo_aff = dol_print_date($res->dateo,'dayhour');

			if($res->datee === '0000-00-00 00:00:00') $res->datee_aff = 'N/A';
			else $res->datee_aff = dol_print_date($res->datee,'dayhour');

			if($res->tasklist_time_start === '0000-00-00 00:00:00') $res->tasklist_time_start = '';
			else $res->tasklist_time_start = dol_print_date($res->tasklist_time_start,'dayhour');

			if(!empty($user->rights->tasklist->all->AllowToChangeTaskPercent)) {
			     dol_include_once('/core/class/html.formother.class.php');
			     $formother = new FormOther($db);
			     $res->select_progress = $formother->select_percent($res->progress,'progress_declared_'.$res->rowid,0,5,0,100,0);
			}

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

function _btOF(&$PDOdb, &$TOf, $fk_of, &$res){

    global $user;

    if(!isset($TOf[$fk_of])) {
        $TOf[$fk_of]=new TAssetOF;
        $TOf[$fk_of]->withChild = false;
        $TOf[$fk_of]->load($PDOdb, $fk_of);
    }

    if(!empty($user->rights->tasklist->user->AllowToUseDolibarrOFRedirection)) { //!empty($conf->global->TASKLIST_OF_LINK_TO_DOLIBARR) &&
        $link_of = dol_buildpath('/of/fiche_of.php',1).'?id='.$TOf[$fk_of]->id;
    }
    else {
        $link_of = 'javascript:openOF('.$TOf[$fk_of]->getId().',\''.$TOf[$fk_of]->numero.'\');';
    }


    $res->taskOF.=' <div class="btn btn-default" ><a href="'.$link_of.'" >'.$TOf[$fk_of]->numero.'</a>&nbsp;&nbsp;&nbsp;'._printShowDocumentsIcon($PDOdb, $fk_of).'</div> ';
}
