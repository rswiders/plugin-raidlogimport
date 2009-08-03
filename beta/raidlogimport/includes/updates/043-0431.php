<?php
 /*
 * Project:     EQdkp-Plus Raidlogimport
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2008-2009 hoofy_leon
 * @link        http://eqdkp-plus.com
 * @package     raidlogimport
 * @version     $Rev$
 *
 * $Id$
 */

if(!defined('EQDKP_INC'))
{
	header('HTTP/1.0 404 Not Found');
	exit;
}

$new_version    = '0.4.3.1';
$updateFunction = false;

$updateDESC = array();

global $eqdkp;
$updateSQL = array();
if($eqdkp->config['default_game'] == 'WoW')
{
	$updateDESC = array(
		'',
		'Deleted Config Value: conf_adjustment',
	);
	$reloadSETT = 'settings.php';
	$updateSQL = array(
		"DELETE FROM __raidlogimport_config WHERE config_name = 'conf_adjustment';"
	);
}

?>
