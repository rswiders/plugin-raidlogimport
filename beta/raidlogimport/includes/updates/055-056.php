<?php
 /*
 * Project:     EQdkp-Plus Raidlogimport
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2009-05-07 17:52:03 +0200 (Do, 07 Mai 2009) $
 * -----------------------------------------------------------------------
 * @author      $Author: hoofy_leon $
 * @copyright   2008-2009 hoofy_leon
 * @link        http://eqdkp-plus.com
 * @package     raidlogimport
 * @version     $Rev: 4786 $
 *
 * $Id: 053-054.php 4786 2009-05-07 15:52:03Z hoofy_leon $
 */

if(!defined('EQDKP_INC'))
{
	header('HTTP/1.0 404 Not Found');
	exit;
}

$new_version    = '0.5.6';
$updateFunction = false;

$updateDESC = array(
	'',
	'Add Config Value: Standby_Raid',
	'Add Config Value: Standby_Absolute',
	'Add Config Value: Standby_Value',
	'Add Config Value: Standby_att',
	'Add Config Value: Standby_dkptype',
	'Add Config Value: Standby_Raid_Note',
	'Add Config Value: member_raid',
);
$reloadSETT = 'settings.php';

global $user;
$updateSQL = array(
	"INSERT INTO __raidlogimport_config (`config_name`, `config_value`) VALUES ('standby_raid', 0);",
	"INSERT INTO __raidlogimport_config (`config_name`, `config_value`) VALUES ('standby_absolute', 0);",
	"INSERT INTO __raidlogimport_config (`config_name`, `config_value`) VALUES ('standby_value', 0);",
	"INSERT INTO __raidlogimport_config (`config_name`, `config_value`) VALUES ('standby_att', 0);",
	"INSERT INTO __raidlogimport_config (`config_name`, `config_value`) VALUES ('standby_dkptype', 0);",
	"INSERT INTO __raidlogimport_config (`config_name`, `config_value`) VALUES ('standby_raidnote', '".$user->lang['standby_raid_note']."');",
	"INSERT INTO __raidlogimport_config (`config_name`, `config_value`) VALUES ('member_raid', '50');",
);
?>