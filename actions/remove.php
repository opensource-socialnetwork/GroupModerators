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
$add   = new OssnGroup();
$group = input('group');
$guid  = input('user');
$group = ossn_get_group_by_guid($group);
if($group->owner_guid !== ossn_loggedin_user()->guid && !ossn_isAdminLoggedin()) {
		ossn_trigger_message(ossn_print('groupmoderators:removed:failed'), 'error');
		redirect(REF);
}
$user = ossn_user_by_guid($guid);
if(ossn_relation_exists($group->guid, $user->guid, 'group:moderator')) {
		$options = array(
				'from' => $group->guid,
				'to'   => $user->guid,
				'type' => 'group:moderator',
		);
		if(ossn_delete_relationship($options)) {
				ossn_trigger_message(ossn_print('groupmoderators:removed'));
				redirect(REF);
		}
}
ossn_trigger_message(ossn_print('groupmoderators:removed:failed'), 'error');
redirect(REF);