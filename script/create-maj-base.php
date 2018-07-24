<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}

$ATMdb = new TPDOdb;

//Obligatoire pour que la fonctionnalité d'import standard fonctionne
$ATMdb->Execute("ALTER TABLE ".MAIN_DB_PREFIX."projet_task ADD tasklist_time_start TIMESTAMP  NULL DEFAULT NULL");

$ATMdb->close();
/* uncomment


dol_include_once('/mymodule/class/xxx.class.php');

$PDOdb=new TPDOdb;

$o=new TXXX($db);
$o->init_db_by_vars($PDOdb);
*/
