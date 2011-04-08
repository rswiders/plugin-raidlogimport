<?php/** Project:     EQdkp-Plus Raidlogimport* License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported* Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/* -----------------------------------------------------------------------* Began:       2009* Date:        $Date: 2009-06-09 17:20:27 +0200 (Di, 09 Jun 2009) $* -----------------------------------------------------------------------* @author      $Author: hoofy_leon $* @copyright   2008-2009 hoofy_leon* @link        http://eqdkp-plus.com* @package     raidlogimport* @version     $Rev: 5040 $** $Id: rli.class.php 5040 2009-06-09 15:20:27Z hoofy_leon $*/if(!defined('EQDKP_INC')) {	header('HTTP/1.0 Not Found');	exit;}include_once($eqdkp_root_path.'maintenance/includes/sql_update_task.class.php');if (!class_exists('update_raidlogimport_0600')) {class update_raidlogimport_0600 extends sql_update_task {	public $author      = 'Hoofy';	public $version     = '0.6.0.0';	public $name        = 'Raidlogimport 0.6.0.0 Update';	public $type        = 'plugin_update';	public $plugin_path = 'raidlogimport';		private $data		= array();	public function __construct() {		parent::__construct();		// init language		$this->langs = array(			'english' => array(				'update_raidlogimport_0600' => 'Raidlogimport 0.6.0.0 Update Package',				'before_update_function' => 'Copy config',				'update_function' => 'Copy bosses and zones',				1 => 'Drop old boss/zone table',				2 => 'Drop old config table',				3 => 'Truncate new zone table',				3 => 'Truncate new boss table',			),			'german' => array(				'update_raidlogimport_0600' => 'Raidlogimport 0.6.0.0 Update Package',				'before_update_function' => 'Kopiere Einstellungen',				'update_function' => 'Kopiere Bosse und Zonen',				1 => 'Lösche alte Boss/Zonen Tabelle',				2 => 'Lösche alte Einstellungs-Tabelle',				3 => 'Leere neue Zonen Tabelle',				4 => 'Leere neue Boss Tabelle',			),		);		// init SQL querys		$this->sqls = array(			1 => "DROP TABLE IF EXISTS __raidlogimport_bz;",			2 => "DROP TABLE IF EXISTS __raidlogimport_config;",			3 => "TRUNCATE TABLE __raidlogimport_zone;",			4 => "TRUNCATE TABLE __raidlogimport_boss;",		);	}		public function before_update_function() {		global $db, $core;		$result = $db->query("SELECT config_name, config_value FROM __raidlogimport_config;");		$toignore = array('rli_inst_version', 'rlic_data', 'rlic_lastcheck', 'rli_inst_build', 'auto_minus', 'am_value_raids', 'am_allxraids', 'am_raidnum', 'am_value', 'item_save_lang', 'member_start', 'member_start_event', 'timezone_offset', 'null_sum');		while ( $row = $db->fetch_record($result) ) {			if(in_array($row['config_name'], $toignore)) continue;			$core->config_set($row['config_name'], $row['config_value']);		}		$db->free_result($result);		$result = $db->query("SELECT * FROM __raidlogimport_bz;");		while ( $row = $db->fetch_record($result) ) {			$this->data[$row['bz_type']][$row['bz_id']] = array(				'string'	=> $row['bz_string'],				'note'		=> $row['bz_note'],				'bonus'		=> $row['bz_bonus'],				'timebonus' => $row['bz_bonusph'],				'diff'		=> $row['bz_diff']			);			if($row['bz_type'] == 'boss') {				$sort = explode('.', $row['bz_sort']);				$this->data['boss'][$row['bz_id']]['tozone'] = $sort[0];				$this->data['boss'][$row['bz_id']]['sort'] = $sort[1];			} else {				$this->data['zone'][$row['bz_id']]['sort'] = $row['bz_sort'];			}		}		return true;	}	public function update_function() {		global $db, $core, $pdl;		//fetch events		$ev_res = $db->query("SELECT event_name, event_id FROM __events;");		$events = array();		while ( $row = $db->fetch_record($ev_res) ) {			$events[$row['event_id']] = $row['event_name'];		}		$db->free_result($ev_res);		unset($ev_res);		//insert zones		$zone_conv = array();		$i = 1;		$sqls = array();		foreach($this->data['zone'] as $oid => $zone) {			$note1 = $zone['note'].$core->config('diff_'.$zone['diff'], 'raidlogimport');			if(!($key = array_search($zone['note'], $events) || $key = array_search($note1, $events))) {				foreach($events as $id => $name) {					if(strpos($name, $zone['note']) !== false) $key = $id;				}			}			if(!$key) $key = 1;			$zone_conv[$oid] = $i;			$i++;			$sqls[] = "('".$db->escape($zone['string'])."', '".$key."', '".$zone['timebonus']."', '".$zone['diff']."', '".$zone['sort']."')";		}		if(!empty($sqls)) {			$db->query("INSERT INTO __raidlogimport_zone (`zone_string`, `zone_event`, `zone_timebonus`, `zone_diff`, `zone_sort`) VALUES ".implode(', ', $sqls).";");		}				//insert bosses		$sqls = array();		foreach($this->data['boss'] as $boss) {			$tozone = (isset($zone_conv[$boss['tozone']])) ? $zone_conv[$boss['tozone']] : 0;			$sqls[] = "('".$db->escape($boss['string'])."', '".$db->escape($boss['note'])."', '".$boss['bonus']."', '".$boss['timebonus']."', '".$boss['diff']."', '".$tozone."', '".$boss['sort']."')";		}		if(!empty($sqls)) {			$db->query("INSERT INTO __raidlogimport_boss (`boss_string`, `boss_note`, `boss_bonus`, `boss_timebonus`, `boss_diff`, `boss_tozone`, `boss_sort`) VALUES ".implode(', ', $sqls).";");		}		return true;	}}}?>