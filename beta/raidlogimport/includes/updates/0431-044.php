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

$new_version    = '0.4.4';
$updateFunction = false;

$updateDESC = array();

global $eqdkp;
$updateDESC = array(
	'',
	'Added Config Values: use_timedkp, use_bossdkp',
);
$reloadSETT = 'settings.php';
$updateSQL = array(
	"INSERT INTO __raidlogimport_config (config_name, config_value) VALUES ('use_timedkp', '1');",
	"INSERT INTO __raidlogimport_config (config_name, config_value) VALUES ('use_bossdkp', '1');"
);

?>