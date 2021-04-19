<?php


	if(!defined('INC_FROM_DOLIBARR') && defined('INC_FROM_CRON_SCRIPT')) {
		if(is_file('../master.inc.php')) include("../master.inc.php");
		else  if(is_file('../../../master.inc.php')) include("../../../master.inc.php");
		else include("../../master.inc.php");

	}
	elseif(!defined('INC_FROM_DOLIBARR')) {
		if(is_file('../main.inc.php')) include("../main.inc.php");
		else  if(is_file('../../../main.inc.php')) include("../../../main.inc.php");
		else include("../../main.inc.php");
	} else {
		global $dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
	}

	if(!defined('DB_HOST')) {
		define('DB_HOST',$dolibarr_main_db_host);
		define('DB_NAME',$dolibarr_main_db_name);
		define('DB_USER',$dolibarr_main_db_user);
		define('DB_PASS',$dolibarr_main_db_pass);
		define('DB_DRIVER',$dolibarr_main_db_type);
	}

	if(!dol_include_once('/abricot/inc.core.php')) {
		print $langs->trans('AbricotNotFound'). ' : <a href="http://wiki.atm-consulting.fr/index.php/Accueil#Abricot" target="_blank">Abricot</a>';
		exit;
	}

	dol_include_once('/core/lib/files.lib.php');
	if(! defined('ATM_ASSET_NAME')) define('ATM_ASSET_NAME', (float) DOL_VERSION >= 8.0 || dol_is_dir(dol_buildpath('/assetatm')) ? 'assetatm' : 'asset');

