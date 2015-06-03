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

function _put($case) {
	
	switch ($case) {
		case 'logout':
			/* Déloggue ! */
			$prefix=dol_getprefix();
			$sessionname='DOLSESSID_'.$prefix;
			$sessiontimeout='DOLSESSTIMEOUT_'.$prefix;
			if (! empty($_COOKIE[$sessiontimeout])) ini_set('session.gc_maxlifetime',$_COOKIE[$sessiontimeout]);
			session_name($sessionname);
			session_destroy();
			dol_syslog("End of session ".$sessionname);

			break;

		default:
			
			break;
	}
}
