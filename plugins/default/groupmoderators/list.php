<?php
if(ossn_isLoggedin() && $params['group']->owner_guid == ossn_loggedin_user()->guid){
	$members = $params['group']->getMembers();
	$count = $params['group']->getMembers(true);
} else {
	$members = group_moderators_list($params['group']->guid);	
	$count = group_moderators_list($params['group']->guid, true);
}
//add group owner on top
$user = ossn_user_by_guid($params['group']->owner_guid);
?>
<div class="ossn-group-members">
			<div class="row">
            		<div class="col-md-2 col-2 col-xs-12">
    	        		<img src="<?php echo $user->iconURL()->large; ?>" class="img-responsive"/>
					</div>
                   <div class="col-md-10 col-10 col-xs-12">
    	    	        <div class="uinfo">
                          <?php
	    						echo ossn_plugin_view('output/url', array(
	    								'text' => $user->fullname,
	    								'href' =>  $user->profileURL(),
	    								'class' => 'userlink',
	    						));						
	    					?>
             	   		</div>
            		</div>           
       			</div>
</div>
<?php
if ($members) {
    foreach ($members as $user) {
      ?>
	     <div class="ossn-group-members">
			<div class="row">
            		<div class="col-md-2 col-2 col-xs-12">
    	        		<img src="<?php echo $user->iconURL()->large; ?>" class="img-responsive"/>
					</div>
                   <div class="col-md-10 col-10 col-xs-12">
    	    	        <div class="uinfo">
                          <?php
	    						echo ossn_plugin_view('output/url', array(
	    								'text' => $user->fullname,
	    								'href' =>  $user->profileURL(),
	    								'class' => 'userlink',
	    						));						
	    					?>
             	   		</div>
                    	 <div class="right request-controls">
				<?php
					if(ossn_isLoggedin()){
						if ((ossn_isAdminLoggedin() && $user->guid !== $params['group']->owner_guid) || (ossn_loggedin_user()->guid == $params['group']->owner_guid && $user->guid !== $params['group']->owner_guid)) {
								if(!group_moderator_is_moderator($params['group']->guid, $user->guid)){
	    								echo ossn_plugin_view('output/url', array(
	    									'text' => ossn_print('groupmoderators:create'),
	    									'href' =>  ossn_site_url("action/group/member/moderator/create?group={$params['group']->guid}&user={$user->guid}", true),
		    								'class' => 'btn btn-warning btn-responsive ossn-make-sure'
		    							));
								} else {
	    								echo ossn_plugin_view('output/url', array(
	    									'text' => ossn_print('groupmoderators:remove'),
	    									'href' =>  ossn_site_url("action/group/member/moderator/remove?group={$params['group']->guid}&user={$user->guid}", true),
		    								'class' => 'btn btn-danger btn-responsive ossn-make-sure'
		    							));									
								}
		    			}
					}
				?>		
                    	</div>
            		</div>           
       			</div>
          </div>
    <?php
    }
	echo ossn_view_pagination($count);
}