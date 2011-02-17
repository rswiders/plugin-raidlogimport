<?php
 /*
 * Project:     EQdkp-Plus Raidlogimport
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2009
 * Date:        $Date: 2009-06-09 17:20:27 +0200 (Di, 09 Jun 2009) $
 * -----------------------------------------------------------------------
 * @author      $Author: hoofy_leon $
 * @copyright   2008-2009 hoofy_leon
 * @link        http://eqdkp-plus.com
 * @package     raidlogimport
 * @version     $Rev: 5040 $
 *
 * $Id: rli.class.php 5040 2009-06-09 15:20:27Z hoofy_leon $
 */

if(!defined('EQDKP_INC')) {
	header('HTTP/1.0 Not Found');
	exit;
}
if(!class_exists('pdh_w_rli_item')) {
class pdh_w_rli_item extends pdh_w_generic {
	public function __construct() {
		parent::pdh_w_generic();
	}
	
	public function add($item_id, $event_id, $itempool_id) {
		global $db, $pdh;
		if($item_id <= 0 || $event_id <= 0 || $itempool_id <= 0) return false;
		if($pdh->get('rli_item', 'itempool', array($item_id, $event_id))) $this->delete($item_id, $event_id);
		if($db->query("INSERT INTO __raidlogimport_item2itempool :params;", array('item_id' => $item_id, 'event_id' => $event_id, 'itempool_id' => $itempool_id))) {
			$pdh->enqueue_hook('rli_item_update');
			return true;
		}
		return false;
	}
	
	public function delete($item_id, $event_id) {
		global $db, $pdh;
		if($db->query("DELETE FROM __raidlogimport_item2itempool WHERE event_id = '".$event_id."' AND item_id = '".$item_id."';")) {
			$pdh->enqueue_hook('rli_item_update');
			return true;
		}
		return false;
	}
}
}
?>