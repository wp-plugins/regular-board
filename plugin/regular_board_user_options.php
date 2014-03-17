<?php 

/**
 * User Options Page Content
 *
 * (1) Allow the current user to set certain options for their browsing,
 * (1) such as a password and a display name, as well as set subscribed 
 * (1) boards and followed ID(s).
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( isset ( $_POST['options'] ) ) {
	if ( $_REQUEST['password']    ) { $password    = sanitize_text_field ( wp_hash ( $_REQUEST['password'] ) ); }
	if ( $_REQUEST['newpassword'] ) { $newpassword = sanitize_text_field ( wp_hash ( $_REQUEST['newpassword'] ) ); }
	if ( $_REQUEST['oldpassword'] ) { $oldpassword = sanitize_text_field ( wp_hash ( $_REQUEST['oldpassword'] ) ); }
	if ( $_REQUEST['avatar'] )      { 
		$ch   = curl_init();
		$opts = array (
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL            => $_REQUEST['avatar'],
			CURLOPT_NOBODY         => true,
			CURLOPT_TIMEOUT        => 10
		);
		curl_setopt_array ( $ch, $opts );
		curl_exec ( $ch );
		$status = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		curl_close ( $ch );
		$path_info = pathinfo ( $_REQUEST['avatar'] );
		if ( $status == '200' && getimagesize ( $_REQUEST['avatar'] ) !== false ) {
			if ( 
				$path_info['extension'] == 'jpg'  || 
				$path_info['extension'] == 'gif'  || 
				$path_info['extension'] == 'jpeg' || 
				$path_info['extension'] == 'png'
			) {
				$update_avatar = sanitize_text_field ( $_REQUEST['avatar'] );
			}
		}
	} else { 
		$update_avatar = '';
	}
	if ( $_REQUEST['email'] ) {
		$update_email = sanitize_text_field ( wp_hash ( $_REQUEST['email'] ) );
	} else {
		$update_email = $profile_email;
	}
	$update_name                                   = sanitize_text_field ( $_REQUEST['USERNAME'] );
	$update_heaven                                 = intval  ( $_REQUEST['heaven'] );
	$update_boards                                 = sanitize_text_field( $_REQUEST['boards'] );
	$update_follow                                 = sanitize_text_field( $_REQUEST['follow'] );
	$update_slogan                                 = sanitize_text_field( substr ( $_REQUEST['slogan'], 0, $max_text ) );
	if ( $update_name ) {
		$checkname = $wpdb->get_results ( $wpdb->prepare ( "SELECT NAME FROM $regular_board_users WHERE user_name = %s AND user_id != %d", $update_name, $profileid ) );
		if ( count ( $checkname ) == 0 ) {
			$wpdb->query ( "UPDATE $regular_board_friends SET friends_connector = '$update_name' WHERE friends_connector = '$profile_name'" );
			$wpdb->query ( "UPDATE $regular_board_friends SET friends_connectee = '$update_name' WHERE friends_connectee = '$profile_name'" );
			$wpdb->query ( "UPDATE $regular_board_messages SET messages_from = '$update_name' WHERE messages_from = '$profile_name'" );
			$wpdb->query ( "UPDATE $regular_board_messages SET messages_to = '$update_name' WHERE messages_to = '$profile_name'" );			
			$wpdb->query ( "UPDATE $regular_board_users SET user_name = '$update_name' WHERE user_id = $profileid" );
			$wpdb->query ( "UPDATE $regular_board_posts SET post_name = '$update_name' WHERE post_userid = $profileid AND post_name != 'null' " );
			$update_name_to = $update_name;
		} else {
			$update_name_to = $profile_name;
			echo '<p><strong>' . $update_name . '</strong> is already taken.  Please use a different one.</p>';
		}
	}
	if ( !$profilepassword && $password ) {
		$update_password = $password;
		$wpdb->query( "UPDATE $regular_board_posts SET post_password = '$password' WHERE post_userid = $profileid" );
	} elseif ( $profilepassword  && $newpassword && $oldpassword ) {
		$update_password = $password;
		$wpdb->query( "UPDATE $regular_board_posts SET post_password = '$newpassword' WHERE post_userid = $profileid" );
	} else {
		$update_password = $profilepassword;
	}

	$wpdb->update (
		$regular_board_users,
		array ( 
			'user_email'    => $update_email,
			'user_avatar'   => $update_avatar,
			'user_name'     => $update_name_to,
			'user_heaven'   => $update_heaven,
			'user_boards'   => $update_boards,
			'user_follow'   => $update_follow,
			'user_slogan'   => $update_slogan,
			'user_password' => $update_password
		),
		array ( 
			'user_id'    => $profileid
		),
		array ( 
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d'
		)
	);
	
}

echo '<form method="post" name="useroptions" class="user-options" action="' . $current_page . '?a=options">';
	wp_nonce_field( 'useroptions' );


	echo '<p><strong>Account</strong></p>
	<section>
		<label>Internal ID</label>
		<input type="text" value="' . $user_ip . '" />
	</section>';
	
	echo '<section>
		<label for="USERNAME">Username</label>
		<input type="text" name="USERNAME" id="USERNAME" placeholder="Your memorable name" value="';
		if ( $profile_name != 'null' && $profile_name ) {
			echo $profile_name;
		}
		echo '" />
		<span>Usernames are unique, and unless you post anonymously, tied to each post that you make.  You can change 
		it at any time.</span>
	</section>';
	
	if ( !$profilepassword ) {
		echo '<section>
			<label for="password">Password</label>
			<input type="text" name="password" id="password" placeholder="' . $random_password . '" />
			<span>Setting a password will allow you to edit and delete posts that you have made.</span>
		</section>';
	}
	if ( $profilepassword ) {
		echo '<section>
			<label for="oldpassword">Current password</label>
			<input type="text" name="oldpassword" id="oldpassword" placeholder="Enter current password" />
		</section>
		<section>
			<label for="newpassword">New password</label>
			<input type="text" name="newpassword" id="newpassword" placeholder="Enter new password" />
		</section>';
	}
	if ( !$thisboard ) { 
		echo '<section>
			<label for="boards">Boards</label>';
		foreach ( $getboards as $board ) {
			$board->board_shortname . ' &mdash; ';
		}
		echo '<input type="text" name="boards" id="boards" value="' . $boards . '" placeholder="Boards" />
		<span>By putting a comma separated list of boards, you are able to build a customized feed of content 
		tailored to your personal tastes from the boards available.  Simply use the follow format: board,board,board... 
		to customize your viewing preferences.</span>
		</section>';
	}
	echo '<section>
		<label for="follow">Follow</label>
		<input type="text" name="follow" id="follow" value="' . $profilefollow . '" placeholder="Usernames" />
		<span>By putting a comma separated list of usernames, you are able to build a customized feed of content 
		tailored to your personal tastes from the people you like.  Simply use the follow format: username,username,username... 
		or userid,userid,userid... to customize your viewing preferences.</span>
	</section>
	<section>
		<label for="slogan">Slogan</label>
		<input type="text" name="slogan" id="slogan" value="' . $profileslogan . '" />
		<span>A quote or a line of text to appear on your profile.</span>
	</section>
	<section>
		<label for="avatar">Avatar IMG URL</label>
		<input type="text" name="avatar" id="avatar" value="' . $profileavatar . '" />
		<span>An image to appear on your profile.</span>
	</section>
	<section><label>anonymous?</label>
		<select name="heaven" id="heaven">
			<option '; if ( $profileheaven == 0 ){ echo 'selected="selected" '; } echo 'value="0">no</option>
			<option '; if ( $profileheaven == 1 ){ echo 'selected="selected" '; } echo 'value="1">yes</option>
		</select>
	</section>
	<section>
		<label for="options">Save</label>
		<input type="submit" name="options" id="options" value="Save these options" />
	</section>
</form>';

echo '<hr />';

if ( isset ( $_POST['friendrequest'] ) && isset ( $_REQUEST['request_id'] ) ) {
	if ( strtolower ( $_REQUEST['request_id'] ) != strtolower ( $profile_name ) ) {
		$checked_user      = 0;
		$check_user        = sanitize_text_field ( $_REQUEST['request_id'] );
		$checked_user      = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_users WHERE user_name = '$check_user' " );
		if ( $checked_user > 0 ) {
			$check_request = 0;
			$check_request = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_friends WHERE ( friends_connector = '$profile_name' AND friends_connectee = '$check_user' OR friends_connector = '$check_user' AND friends_connectee = '$profile_name')" );
			if ( $check_request == 0 ) {
				$wpdb->query ( 
					$wpdb->prepare ( 
						"INSERT INTO $regular_board_friends 
						( 
							friends_id, 
							friends_connector, 
							friends_connectee, 
							friends_mutual
						) VALUES ( 
							%d,
							%s,
							%s,
							%d
						)", 
						'', 
						$profile_name,
						$check_user,
						0
					) 
				);
			}
		} else {
			echo '<p><em>That user does not exist.</em></p>';
		}
	} else {
		echo '<p><em>You can\'t be friends with yourself - sorry.</em></p>';
	}
}
echo '
<form method="post" name="friend_request" class="user-options" action="' . $current_page . '?a=options">
<p><strong>Friend Connections</strong></p>';
wp_nonce_field( 'friend_request' );
echo '<section><label for="request_id">Enter a username</label><input type="text" id="request_id" name="request_id" placeholder="Enter username to initiate request" /></section>
<section><input type="submit" name="friendrequest" id="friendrequest" value="Initiate connection" /></section>
</form>';

if ( count ( $my_waiting ) > 0 ) {
	foreach ( $my_waiting as $waiting ) {
		
		$this_form = $waiting->friends_id;
		
		if ( isset ( $_POST['accept' . $this_form . ''] ) ) {
			$wpdb->query ( "UPDATE $regular_board_friends SET friends_mutual = 1 WHERE friends_id = $this_form" );
		}
		if ( isset ( $_POST['decline' . $this_form . ''] ) ) {
			$wpdb->delete ( $regular_board_friends, array ( 'friends_id' => $this_form ), array ( '%d' ) );
		}
		
		echo '<form class="friend_request" method="post" action="' . $current_page . '?a=options">
		<section>
			<label>Request from ' . sanitize_text_field ( $waiting->friends_connector ) . '</label>
			<input type="submit" name="decline' . $waiting->friends_id . '" value="Decline" />
			<input type="submit" name="accept' . $waiting->friends_id . '" value="Accept" />
		</section>
		</form>';
	}
}
echo '
<script type="text/javascript">
	document.title = \'Options\';
</script>';