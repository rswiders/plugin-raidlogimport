<?php
/*
* Project:     EQdkp-Plus Raidlogimport
* License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
* Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
* -----------------------------------------------------------------------
* Began:       2008
* Date:        $Date: 2009-07-04 16:06:06 +0200 (Sa, 04 Jul 2009) $
* -----------------------------------------------------------------------
* @author      $Author: hoofy_leon $
* @copyright   2008-2009 hoofy_leon
* @link        http://eqdkp-plus.com
* @package     raidlogimport
* @version     $Rev: 5166 $
*
* $Id: dkp.php 5166 2009-07-04 14:06:06Z hoofy_leon $
*/

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);

$eqdkp_root_path = './../../../';
include_once('./../includes/common.php');

class rli_import extends page_generic {
	public static function __dependencies() {
		$dependencies = array('user', 'rli', 'in', 'tpl', 'core', 'pm', 'config',
			'adj'		=> 'rli_adjustment',
			'item'		=> 'rli_item',
			'member'	=> 'rli_member',
			'parser'	=> 'rli_parse',
			'raid'		=> 'rli_raid',
		);
		return array_merge(parent::$dependencies, $dependencies);
	}

	public function __construct() {
		$this->user->check_auth('a_raidlogimport_dkp');
		
		$handler = array(
			'checkraid'	=> array('process' => 'process_raids'),
			'checkmem'	=> array('process' => 'process_members'),
			'checkitem'	=> array('process' => 'process_items'),
			'save_itempools' => array('process' => 'itempool_save'),
			'checkadj'	=> array('process' => 'process_adjustments'),
			'viewall'	=> array('process' => 'process_views'),
			'insert'	=> array('process' => 'insert_log')
		);
		parent::__construct(false, $handler);
		$this->process();
	}

	public function process_raids() {
		if($this->in->exists('log')) {
			$this->rli->flush_cache();
			$log = simplexml_load_string(utf8_encode(trim(str_replace("&", "and", html_entity_decode($_POST['log'])))));
			if ($log === false) {
				message_die($this->user->lang('xml_error'));
			} else {
				$this->parser->parse_string($log);
			}
		}
		$this->raid->add_new($this->in->get('raid_add', 0));
		if($this->in->get('checkraid') == $this->user->lang('rli_calc_note_value')) {
			$this->raid->recalc();
		}

		$this->raid->display(true);

		$this->tpl->assign_vars(array(
			'USE_TIMEDKP' => ($this->rli->config('use_dkp') & 2),
			'USE_BOSSDKP' => ($this->rli->config('use_dkp') & 1))
		);
		//language
		$this->tpl->assign_vars(lang2tpl());

		$this->core->set_vars(array(
			'page_title'        => sprintf($this->user->lang('admin_title_prefix'), $core->config('guildtag'), $this->config->get('dkp_name')).': '.$this->user->lang('rli_check_data'),
			'template_path'     => $this->pm->get_data('raidlogimport', 'template_path'),
			'template_file'     => 'raids.html',
			'display'           => true)
		);
	}

	public function process_members() {
		$this->member->add_new($this->in->get('members_add', 0));

		//display members
		$this->member->display(true);

		//show raids
		$this->raid->display();

		//language
		$this->tpl->assign_vars(lang2tpl());

		$this->tpl->assign_vars(array(
			'S_ATT_BEGIN'	 => ($this->rli->config('attendence_begin') > 0 AND !$this->rli->config('attendence_raid')) ? TRUE : FALSE,
			'S_ATT_END'		 => ($this->rli->config('attendence_end') > 0 AND !$this->rli->config('attendence_raid')) ? TRUE : FALSE,
			'MEMBER_DISPLAY' => ($this->rli->config('member_display') == 1) ? $this->raid->th_raidlist : false,
			'RAIDCOUNT'		 => ($this->rli->config('member_display') == 1) ? $this->raid->count() : 1,
			'RAIDCOUNT3'	 => ($this->rli->config('member_display') == 1) ? $this->raid->count()+2 : 3,
			'DETAIL_RAIDLIST' =>($this->rli->config('member_display') == 2) ? true : false)
		);

		$this->core->set_vars(array(
			'page_title'        => sprintf($this->user->lang('admin_title_prefix'), $this->config->get('guildtag'), $this->config->get('dkp_name')).': '.$this->user->lang('rli_check_data'),
			'template_path'     => $this->pm->get_data('raidlogimport', 'template_path'),
			'template_file'     => 'members.html',
			'display'           => true)
		);
	}

	public function process_items() {
		$this->item->add_new($this->in->get('items_add', 0));
		$this->member->display();
		$this->raid->display();
		$this->item->display(true);
		
		$this->tpl->assign_vars(array(
			'S_ATT_BEGIN'	=> ($this->rli->config('attendence_begin') > 0 AND !$this->rli->config('attendence_raid')) ? true : false,
			'S_ATT_END'		=> ($this->rli->config('attendence_end') > 0 AND !$this->rli->config('attendence_raid')) ? true : false)
		);

		//language
		$this->tpl->assign_vars(lang2tpl());
		
		$this->core->set_vars(array(
			'page_title'        => sprintf($this->user->lang('admin_title_prefix'), $this->config->get('guildtag'), $this->config->get('dkp_name')).': '.$this->user->lang('rli_check_data'),
			'template_path'     => $this->pm->get_data('raidlogimport', 'template_path'),
			'template_file'     => 'items.html',
			'display'           => true)
		);
	}
	
	public function itempool_save() {
		$this->item->save_itempools();
		$this->process_items();
	}

	public function process_adjustments() {
		$this->adj->add_new($this->in->get('adjs_add', 0));

		$this->member->display();
		$this->raid->display();
		$this->item->display();
		$this->adj->display(true);

		$this->tpl->assign_vars(array(
			'S_ATT_BEGIN'	=> ($this->rli->config('attendence_begin') > 0 AND !$this->rli->config('attendence_raid')) ? true : false,
			'S_ATT_END'		=> ($this->rli->config('attendence_end') > 0 AND !$this->rli->config('attendence_raid')) ? true : false)
		);

		//language
		$this->tpl->assign_vars(lang2tpl());
		
		$this->core->set_vars(array(
			'page_title'        => sprintf($this->user->lang('admin_title_prefix'), $this->config->get('guildtag'), $this->config->get('dkp_name')).': '.$this->user->lang('rli_check_data'),
			'template_path'     => $this->pm->get_data('raidlogimport', 'template_path'),
			'template_file'     => 'adjustments.html',
			'display'           => true)
		);
	}

	public function insert_log() {
		global $db, $core, $user, $tpl, $pm, $rli, $pdh;
		
		$message = array();
		$bools = $this->rli->check_data();
		if(!in_array('miss', $bools) AND !in_array(false, $bools)) {
			#$this->db->query("START TRANSACTION");
			$isok = $this->member->insert();
			if($isok) $isok = $this->raid->insert();
			if($isok) $isok = $this->item->insert();
			if($isok && !$this->rli->config('deactivate_adj')) $isok = $this->adj->insert();
			if($isok) {
				#$this->db->query("COMMIT;");
				$this->pm->do_hooks('/plugins/raidlogimport/admin/dkp.php');
				$pdh->process_hook_queue();
				$this->rli->flush_cache();
				$message[] = $this->user->lang('bz_save_suc');
			} else {
				#$this->db->query("ROLLBACK;");
				$message[] = $this->user->lang('rli_error');
			}
			foreach($message as $answer) {
				$this->tpl->assign_block_vars('sucs', array(
					'PART1'	=> $answer)
				);
			}
			$this->tpl->assign_vars(array(
				'L_SUCCESS' => $this->user->lang('rli_success'),
				'L_LINKS'	=> $this->user->lang('links'))
			);
	
			$this->core->set_vars(array(
				'page_title'        => sprintf($this->user->lang('admin_title_prefix'), $this->config->get('guildtag'), $this->config->get('dkp_name')).': '.$this->user->lang('rli_imp_suc'),
				'template_path'     => $this->pm->get_data('raidlogimport', 'template_path'),
				'template_file'     => 'success.html',
				'display'           => true)
			);
		} else {
			unset($_POST);
			$check = $this->user->lang('rli_missing_values').'<br />';
			foreach($bools['false'] as $loc => $la) {
				if($la == 'miss') {
					$check .= $this->user->lang('rli_'.$loc.'_needed');
				}
				$check .= '<input type="submit" name="check'.$loc.'" value="'.$this->user->lang('rli_check'.$loc).'" class="mainoption" /><br />';
			}
			$this->tpl->assign_vars(array(
				'L_NO_IMP_SUC'	=> $this->user->lang('rli_imp_no_suc'),
				'CHECK'			=> $check)
			);
			$this->core->set_vars(array(
				'page_title'		=> sprintf($this->user->lang('admin_title_prefix'), $this->config->get('guildtag'), $this->config->get('dkp_name')).': '.$this->user->lang('rli_imp_no_suc'),
				'template_path'		=> $this->pm->get_data('raidlogimport', 'template_path'),
				'template_file'		=> 'check_input.html',
				'display'			=> true,
				)
			);
		}
	}

	public function display($messages=array()) {
		if($messages) {
			foreach($messages as $title => $message) {
				$type = ($title == 'rli_error' or $title == 'rli_no_mem_create') ? 'red' : 'green';
				if(is_array($message)) {
					$message = implode(',<br />', $message);
				}
				$this->core->message($message, $this->user->lang($title).':', $type);
			}
		}
		$this->tpl->assign_vars(array(
			'L_INSERT'		 => $this->user->lang('rli_dkp_insert'),
			'L_SEND'		 => $this->user->lang('rli_send'),
			'S_STEP1'        => true)
		);

		$this->core->set_vars(array(
			'page_title'        => sprintf($this->user->lang('admin_title_prefix'), $this->config->get('guildtag'), $this->config->get('dkp_name')).': '."DKP String",
			'template_path'     => $this->pm->get_data('raidlogimport', 'template_path'),
			'template_file'     => 'log_insert.html',
			'display'           => true,
			)
		);
	}
}
if(version_compare(PHP_VERSION, '5.3.0', '<')) registry::add_const('dep_rli_import', rli_import::__dependencies());
registry::register('rli_import');
?>