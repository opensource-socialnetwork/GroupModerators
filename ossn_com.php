<?php
/**
 * Open Source Social Network
 *
 * @package   Open Source Social Network
 * @author    Open Social Website Core Team <info@openteknik.com>
 * @copyright (C) OpenTeknik LLC
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */
define('GroupModerators', ossn_route()->com . 'GroupModerators/');
function group_moderators_init() {
		if(ossn_isLoggedin()) {
				ossn_add_hook('group', 'is:moderator', 'group_moderator_hook');
				ossn_add_hook('group', 'subpage', 'group_moderators_page');
				ossn_group_subpage('moderators');

				ossn_register_action('group/member/moderator/create', GroupModerators . 'actions/add.php');
				ossn_register_action('group/member/moderator/remove', GroupModerators . 'actions/remove.php');

				ossn_register_callback('group', 'delete', 'group_moderator_delete_relations');
				ossn_register_callback('user', 'delete', 'group_moderator_user_posts_delete');
				ossn_register_callback('page', 'load:group', 'group_moderator_link');
				ossn_register_callback('group', 'delete:member', 'group_moderator_member_delete');
		}
}
/**
 * Group moderators list
 *
 * @param integer $group_guid Group guid
 * @param integer $count Count total moderators
 *
 * @return boolean|array
 */
function group_moderators_list($group_guid, $count = false) {
		if(!isset($group_guid)) {
				return false;
		}
		$members = ossn_get_relationships(array(
				'from'  => $group_guid,
				'type'  => 'group:moderator',
				'count' => $count,
		));
		if($count) {
				return $members;
		}
		if($members) {
				foreach($members as $member) {
						$users[] = ossn_user_by_guid($member->relation_to);
				}
		}
		if(isset($users)) {
				return $users;
		}
		return false;
}
/**
 * Group moderators menu link
 *
 * @return void
 */
function group_moderator_link($event, $type, $params) {
		$owner = ossn_get_page_owner_guid();
		$url   = ossn_site_url();
		ossn_register_menu_link('groupmoderators', 'groupmoderators', ossn_group_url($owner) . 'moderators', 'groupheader');
}

/**
 * Delete relationships from system upon group deletation
 *
 * @param string $callback
 * @param string $type
 * @param array $params option values
 *
 * @return void
 */
function group_moderator_delete_relations($callback, $type, $params) {
		if(isset($params['entity']->guid) && $params['entity'] instanceof OssnGroups) {
				$list = ossn_get_relationships(array(
						'from'       => $params['entity']->guid,
						'type'       => 'group:moderator',
						'page_limit' => false,
				));
				if($list) {
						foreach($list as $relation) {
								ossn_delete_group_relations($relation->relation_id);
						}
				}
		}
}
/**
 * Delete relationships from system upon user deletation
 *
 * @param string $callback
 * @param string $type
 * @param array $params option values
 *
 * @return void
 */
function group_moderator_user_posts_delete($callback, $type, $params) {
		if(isset($params['entity']->guid) && $params['entity'] instanceof OssnUser) {
				$list = ossn_get_relationships(array(
						'to'         => $params['entity']->guid,
						'type'       => 'group:moderator',
						'page_limit' => false,
				));
				if($list) {
						foreach($list as $relation) {
								ossn_delete_group_relations($relation->relation_id);
						}
				}
		}
}
/**
 * Check if user is moderator of group
 *
 * @param integer $group_guid Group guid
 * @param integet $user_guid User guid
 *
 * @return boolean
 */
function group_moderator_is_moderator(int $group_guid, int $user_guid) {
		if(!empty($group_guid) && !empty($user_guid)) {
				return ossn_relation_exists($group_guid, $user_guid, 'group:moderator');
		}
		return false;
}
/**
 * Group moderator hook
 *
 * @param string $hook
 * @param string $type
 * @param boolean $return boolean
 * @param array $params option values
 *
 * @return boolean
 */
function group_moderator_hook($hook, $type, $return, $params) {
		global $Ossn;
		if(!isset($Ossn->group_moderators)) {
				$Ossn->group_moderators = array();
		}
		if(!isset($Ossn->group_moderators[$params['group']->guid])) {
				$Ossn->group_moderators[$params['group']->guid] = array();
		}
		if(ossn_isLoggedin()) {
				if(!in_array($params['user_guid'], $Ossn->group_moderators[$params['group']->guid])) {
						if(group_moderator_is_moderator($params['group']->guid, $params['user_guid'])) {
								$Ossn->group_moderators[$params['group']->guid][] = $params['user_guid'];
								return true;
						}
				} else {
						return true;
				}
		}
		return false;
}
/**
 * Group moderator page
 *
 * @param string $hook
 * @param string $type
 * @param boolean $return boolean
 * @param array $params option values
 *
 * @return boolean
 */
function group_moderators_page($hook, $type, $return, $params) {
		$page  = $params['subpage'];
		$group = ossn_get_group_by_guid(ossn_get_page_owner_guid());
		if($page == 'moderators') {
				$content = ossn_plugin_view('groupmoderators/list', array(
						'group' => $group,
				));
				echo ossn_set_page_layout('module', array(
						'title'   => ossn_print('groupmoderators'),
						'content' => $content,
				));
		}
}
/**
 * Delete relationships from system if member is removed from group
 *
 * @param string $callback
 * @param string $type
 * @param array  $params option values
 *
 * @return void
 */
function group_moderator_member_delete($callback, $type, $params) {
		if(isset($params['user_guid']) && isset($params['group_guid'])) {
				ossn_delete_relationship(array(
						'from' => $params['group_guid'],
						'to'   => $params['user_guid'],
						'type' => 'group:moderator',
				));
		}
}
ossn_register_callback('ossn', 'init', 'group_moderators_init');