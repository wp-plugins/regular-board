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
	if ( $_REQUEST['password']    ) { $password    = wp_hash ( $_REQUEST['password'] ); }
	if ( $_REQUEST['newpassword'] ) { $newpassword = wp_hash ( $_REQUEST['newpassword'] ); }
	if ( $_REQUEST['oldpassword'] ) { $oldpassword = wp_hash ( $_REQUEST['oldpassword'] ); }
	$email                                         = wp_hash ( $_REQUEST['email'] );
	$name                                          = sanitize_text_field( $_REQUEST['USERNAME'] );
	$heaven                                        = intval  ( $_REQUEST['heaven'] );
	$boards                                        = sanitize_text_field( $_REQUEST['boards'] );
	$follow                                        = sanitize_text_field( $_REQUEST['follow'] );
	if ( $name ) {
		$checkname = $wpdb->get_results ( $wpdb->prepare ( "SELECT NAME FROM $regular_board_users WHERE user_name = %s AND user_id != %d", $name, $profileid ) );
		if ( count ( $checkname ) == 0 ) {
			$wpdb->query ( "UPDATE $regular_board_users SET user_name = '$name' WHERE user_id = $profileid" );
			$wpdb->query ( "UPDATE $regular_board_posts SET post_name = '$name' WHERE post_userid = $profileid" );
		} else {
			echo '<p><i class="fa fa-warning"></i> <strong>' . $name . '</strong> is already taken.  Please use a different one.</p>';
		}
	}
	
	$wpdb->query ( "UPDATE $regular_board_users SET user_email = '$email' WHERE user_id = $profileid" );
	
	$wpdb->query ( "UPDATE $regular_board_users SET user_heaven = $heaven WHERE user_id = $profileid" );
	if ( !$profilepassword && $password ) {
		$wpdb->query( "UPDATE $regular_board_users SET user_password = '$password' WHERE user_id = $profileid" );
		$wpdb->query( "UPDATE $regular_board_posts SET post_password = '$password' WHERE post_userid = $profileid" );
	}
	if ( $profilepassword  && $newpassword && $oldpassword ) {
		$wpdb->query( "UPDATE $regular_board_users SET user_password = '$newpassword' WHERE user_id = $profileid AND user_password = '$oldpassword'" );
		$wpdb->query( "UPDATE $regular_board_posts SET post_password = '$newpassword' WHERE post_userid = $profileid" );
	}
	$wpdb->query( "UPDATE $regular_board_users SET user_boards = '$boards' WHERE user_id = $profileid" );
	$wpdb->query( "UPDATE $regular_board_users SET user_follow = '$follow' WHERE user_id = $profileid" );
}

echo '<form method="post" name="useroptions" action="' . $current_page . '?a=options">';
	wp_nonce_field( 'useroptions' );
	
	echo '<p>Your internal ID is: <strong>' . $user_ip . '</strong>.  Keep this information safe somewhere 
	in case you ever need to restore <strong>this</strong> ID to your <strong>new</strong> IP address.</p>';
	
	if ( $profile_email ) {
		echo '<p>E-mail has been set (What we have: ' . $profile_email . ' )  For your own security, we do not store emails as plain text.</p>';
	} else {
		echo '<p>Set your email address here (can also be blank) (this installation does not make publicly accessible this information):<br />
			<input type="text" name="email" id="email" placeholder="you@there.com" value="' . $profile_email . '" />';
	}
	echo '<p>Set a memorable name:<br />
	<input type="text" name="USERNAME" id="USERNAME" placeholder="Your memorable name" value="' . $profile_name . '" /></p>
	';
	if ( !$profilepassword ) {
		echo '<p>Set a password to use on every post you make (default password is always random):
			<input type="text" name="password" id="password" placeholder="' . $random_password . '" /></p>';
	}
	if ( $profilepassword ) {
		echo '<p>To change your current password, enter it in the first box and your new password in the second.<br />
			<input type="text" name="oldpassword" id="oldpassword" placeholder="Enter current password" />
			<input type="text" name="newpassword" id="newpassword" placeholder="Enter new password" /></p>';
	}
		if ( !$thisboard ) { 
		echo '<p>Comma-separated list of boards you wish to subscribe to (available boards below) (example: board, board, board):<br />';
				foreach ( $getboards as $board ) {
					$board->board_shortname . ' &mdash; ';
				}
				echo '<input type="text" name="boards" id="boards" value="' . $boards . '" placeholder="Boards" /></p>
		';
	}
		echo '<p>Comma-separated list of user IDs to follow:<br />
			<input type="text" name="follow" id="follow" value="' . $profilefollow . '" placeholder="User IDs" /></p>
		<p><select name="heaven" id="heaven">
				<option '; if ( $profileheaven == 0 ){ echo 'selected="selected" '; } echo 'value="0">Give me the choice of posting anonymously</option>
				<option '; if ( $profileheaven == 1 ){ echo 'selected="selected" '; } echo 'value="1">Always post anonymously</option>
			</select></p>
		<p><input type="submit" name="options" id="options" value="Save these options" /></p>
</form>

<script type="text/javascript">
	document.title = \'Options\';
</script>';