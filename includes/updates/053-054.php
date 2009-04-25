<?php

if(!defined('EQDKP_INC'))
{
	header('HTTP/1.0 404 Not Found');
	exit;
}

$new_version    = '0.5.4.1';
$updateFunction = false;

$updateDESC = array(
	'',
	'Change Config Value of Parser: ctrt to eqdkp',
	'Add Boss: Emalon the Storm Watcher',
	'Fixed Typo for Deconstructor'
);
$reloadSETT = 'settings.php';

$updateSQL = array(
	"UPDATE __raidlogimport_config SET config_value = 'eqdkp' WHERE config_name = 'parser' AND config_value = 'ctrt';",
	"INSERT INTO __raidlogimport_bz (bz_string, bz_note, bz_bonus, bz_type, bz_tozone, bz_sort) VALUES ('Emalon the Storm Watcher', 'Emalon', '2', 'boss', '24', '1');",
	"UPDATE __raidlogimport_bz SET bz_string = 'XT-002 Deconstructor' WHERE bz_string = 'XT-002 Deconstuctor';"
);

?>