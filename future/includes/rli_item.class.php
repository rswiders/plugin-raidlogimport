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

if(!class_exists('rli_item')) {
class rli_item extends gen_class {
	public static $dependencies = array('rli', 'in', 'pdh', 'user', 'tpl', 'html', 'jquery', 'core',
		'member'	=> 'rli_member',
		'raid'		=> 'rli_raid',
	);

	private $items = array();

	public function __construct() {
		$this->items = $this->get_cache_data('item');
	}
	
	public function reset() {
		$this->items = array();
	}

	private function config($name) {
		return $this->config($name);
	}
	
	public function add($name, $member, $value, $id=0, $time=0, $raid=0, $itempool=0) {
		if(!empty($this->toignore)) {
			foreach($this->toignore as $toignore) {
				if(strcasecmp($toignore, $member) === 0) return false;
			}
		} elseif(!isset($this->toignore) && strlen($this->config('ignore_dissed')) > 2) {
			$this->toignore = explode(',', $this->config('ignore_dissed'));
		} else {
			$this->toignore = array();
		}
		$this->items[] = array('name' => $name, 'member' => $member, 'value' => $value, 'game_id' => $id, 'time' => $time, 'raid' => $raid, 'itempool' => $itempool);
		return true;
	}
	
	public function add_new($num) {
		while($num > 0) {
			$this->items[] = array('name' => '');
			$num--;
		}
	}
	
	public function load_items() {
		foreach($_POST['loots'] as $k => $loot) {
			if(is_array($this->items) AND in_array($k, array_keys($this->items))) {
				foreach($this->items as $key => $item) {
					if($k == $key) {
						if(isset($loot['delete']) AND $loot['delete']) {
							unset($this->items[$key]);
							continue;
						}
						$this->items[$key] = $this->in->getArray('loots:'.$key, '');
						$this->items[$key]['value'] = floatvalue($this->in->get('loots:'.$key.':value', '0.0'));
						$this->items[$key]['time'] = $item['time'];
					}
				}
			} elseif(!isset($loot['delete'])) {
				$this->items[$k] = $this->in->getArray('loots:'.$k, '');
				$this->items[$k]['value'] = floatvalue($this->in->get('loots:'.$k.':value', '0.0'));
				if(!isset($begin_end)) $begin_end = $this->raid->get_start_end();
				$this->items[$k]['time'] = $begin_end['begin'];
			}
		}
		unset($begin_end);
	}
	
	public function display($with_form=false) {
		if(is_array($this->items)) {
			$p = count($this->items);
			$start = 0;
			$end = $p+1;
			if($vars = ini_get('suhosin.post.max_vars')) {
				$vars = $vars - 5;
				$dic = $vars/7;
				settype($dic, 'int');
				$page = 1;

				if(!(strpos($this->in->get('checkitem'), $this->user->lang('rli_itempage')) === false)) {
					$page = str_replace($this->user->lang('rli_itempage'), '', $this->in->get('checkitem'));
				}
				if($page >= 1) {
					$start = ($page-1)*$dic;
					$page++;
				}
				$end = $start+$dic;
			}
			$members = $this->member->get_for_dropdown(2);
			//add disenchanted and bank
			$members['disenchanted'] = 'disenchanted';
			$members['bank'] = 'bank';
			ksort($members);
			$members = array_merge(array($this->user->lang('rli_choose_mem')), $members);
			$itempools = $this->pdh->aget('itempool', 'name', 0, array($this->pdh->get('itempool', 'id_list')));
			//maybe add "saving" for itempool
			foreach($this->items as $key => $item) {
				if(($start <= $key AND $key < $end) OR !$with_form) {
					if($with_form) {
						$mem_sel = (in_array($item['member'], $members)) ? $item['member'] : 0;
						$raid_select = "<select size='1' name='loots[".$key."][raid]'>";
						$att_raids = $this->raid->get_attendance_raids();
						$this->raid->raidlist(true);
						foreach($this->raid->raidlist as $i => $note) {
							if(!(in_array($i, $att_raids) AND $this->config('attendence_raid'))) {
								$raid_select .= "<option value='".$i."'";
								if((!$item['raid'] && $this->raid->item_in_raid($i, $item['time'])) || ($item['raid'] && $item['raid'] == $i)) {
									$raid_select .= ' selected="selected"';
									if(!$item['itempool'] && $this->config('itempool_save')) {
										$item['itempool'] = $this->pdh->get('rli_item', 'itempool', array($item['game_id'], $this->raid->raidevents[$i]));
									}
									if(!$item['itempool']) {
										$mdkps = $this->pdh->get('multidkp', 'mdkpids4eventid', array($this->raid->raidevents[$i]));
										$itmpls = $this->pdh->get('multidkp', 'itempool_ids', array($mdkps[0]));
										$item['itempool'] = $itmpls[0];
									}
								}
								$raid_select .= ">".$note."</option>";
							}
						}
					}
					$this->tpl->assign_block_vars('loots', array(
						'LOOTNAME'  => $item['name'],
						'ITEMID'    => (isset($item['game_id'])) ? $item['game_id'] : '',
						'LOOTER'    => ($with_form) ? $this->html->widget(array('type' => 'dropdown', 'name' => "loots[".$key."][member]", 'options' => $members, 'selected' => $mem_sel, 'id' => 'loots_'.$key.'_member', 'no_lang' => true)) : $item['member'],
						'RAID'      => ($with_form) ? $raid_select."</select>" : $item['raid'],
						'ITEMPOOL'	=> ($with_form) ? $this->html->widget(array('type' => 'dropdown', 'name' => "loots[".$key."][itempool]", 'options' => $itempools, 'selected' => $item['itempool'], 'id' => 'loots_'.$key.'_itempool', 'no_lang' => true)) : $this->pdh->get('itempool', 'name', array($item['itempool'])),
						'LOOTDKP'   => runden($item['value']),
						'KEY'       => $key,
						'DELDIS'	=> 'disabled="disabled"')
					);
				}
			}
			if($with_form) {
				//js deletion
				$options = array(
					'custom_js' => "$('#'+del_id).css('display', 'none'); $('#'+del_id+'submit').removeAttr('disabled');",
					'withid' => 'del_id',
					'message' => $this->user->lang('rli_delete_items_warning')
				);
				$this->jquery->Dialog('delete_warning', $this->user->lang('confirm_deletion'), $options, 'confirm');
				
				//js addition
				$this->tpl->assign_block_vars('loots', array(
					'KEY'		=> 999,
					'ITEMPOOL'	=> $this->html->widget(array('type' => 'dropdown', 'name' => 'loots[999][itempool]', 'options' => $itempools, 'selected' => 0, 'id' => 'loots_999_itempool', 'no_lang' => true)),
					'ITEMPOOL'	=> $this->html->widget(array('type' => 'dropdown', 'name' => 'loots[999][member]', 'options' => $members, 'selected' => 0, 'id' => 'loots_999_member', 'no_lang' => true)),
					'ITEMPOOL'	=> $this->html->widget(array('type' => 'dropdown', 'name' => 'loots[999][raid]', 'options' => $this->raid->raidlist, 'selected' => 0, 'id' => 'loots_999_raid', 'no_lang' => true)),
					'DISPLAY'	=> 'style="display: none;"',
					'S_IP_SAVE' => $this->config('itempool_save')
				));
				$this->tpl->add_js(
"var rli_key = ".($key+1).";
$('.del_item').click(function() {
	$(this).removeClass('del_item');
	delete_warning($(this).attr('class'));
});
$('#add_item_button').click(function() {
	var item = $('#item_999').clone(true);
	item.find('#item_999submit').attr('disabled', 'disabled');
	item.html(item.html().replace(/999/g, rli_key));
	item.attr('id', 'item_'+rli_key);
	item.removeAttr('style');
	$('#item_'+(rli_key-1)).after(item);
	$('#item_'+rli_key+'submit').prev().click(function() {
		$(this).removeClass('del_item');
		delete_warning($(this).attr('class'));
	});
	rli_key++;
});", 'docready');
			}
		}
		if($end && $end <= $p) {
			$next_button = '<input type="submit" name="checkitem" value="'.$this->user->lang('rli_itempage').(($page) ? $page : 2).'" class="mainoption" />';
		} elseif($this->config('deactivate_adj')) {
			$next_button = '<input type="submit" name="insert" value="'.$this->user->lang('rli_go_on').' ('.$this->user->lang('rli_insert').')" class="mainoption" />';
		} else {
			$next_button = '<input type="submit" name="checkadj" value="'.$this->user->lang('rli_go_on').' ('.$this->user->lang('rli_checkadj').')" class="mainoption" />';
		}
		if($end >= $p && !empty($dic) && $p+$dic >= $end) $next_button .= ' <input type="submit" name="checkitem" value="'.$this->user->lang('rli_itempage').(($page) ? $page : 2).'" class="mainoption" />';
		$this->tpl->assign_var('NEXT_BUTTON', $next_button);
	}
	
	public function save_itempools() {
		$to_save = $this->in->getArray('itempool_save', 'int');
		$this->raid->raidlist(true);
		$saves = array();
		foreach($to_save as $id) {
			$event = $this->raid->raidevents[$this->in->get('loots:'.$id.':raid', 0)];
			$game_id = $this->in->get('loots:'.$id.':game_id', 0);
			$saves[$id] = $this->pdh->put('rli_item', 'add', array($game_id, $event, $this->in->get('loots:'.$id.':itempool', 0)));
		}
		if(!in_array(false, $saves, true)) {
			$this->core->message($this->user->lang('rli_itempool_saved'), $this->user->lang('success'), 'green');
		} else {
			$message = $this->user->lang('rli_itempool_nosave').': <br />';
			$fails = array();
			foreach($saves as $id => $res) {
				if(!$res) $fails[] = $this->in->get('loots:'.$id.':name');
			}
			$this->core->message($message.implode(', ', $fails), $this->user->lang('rli_itempool_partial_save'), 'red');
		}
		$this->pdh->process_hook_queue();
	}

	public function check($bools) {
		if(is_array($this->items)) {
			foreach($this->items as $key => $item) {
				if(!$item['name'] OR !$item['raid'] OR !$item['itempool']) {
					$bools['false']['item'] = false;
				}
			}
		} else {
			$bools['false']['item'] = 'miss';
		}
	}
	
	public function insert() {
		foreach($this->items as $item) {
			if(!$this->pdh->put('item', 'add_item', array($item['name'], array($this->member->name_ids[$item['member']]), $this->raid->real_ids[$item['raid']], $item['game_id'], $item['value'], $item['itempool'], $item['time']))) {
				return false;
			}
		}
		return true;
	}
	
	public function __destruct() {
		$this->rli->add_cache_data('item', $this->items);
	}
}
}
if(version_compare(PHP_VERSION, '5.3.0', '<')) registry::add_const('dep_rli_item', rli_item::$dependencies);
?>