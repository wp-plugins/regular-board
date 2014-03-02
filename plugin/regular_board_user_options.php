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
	if ( $_REQUEST['email'] )       { $email       = sanitize_text_field ( wp_hash ( $_REQUEST['email'] ) ); }
	$name                                          = sanitize_text_field ( $_REQUEST['USERNAME'] );
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

echo '<form method="post" name="useroptions" class="user-options" action="' . $current_page . '?a=options">';
	wp_nonce_field( 'useroptions' );


	echo '<h3>Account</h3>
	<section>
		<label>Internal ID</label>
		<input type="text" value="' . $user_ip . '" />
	</section>';
	
	if ( $profile_email ) {
		echo '<p>E-mail has been set (What we have: ' . $profile_email . ' )  For your own security, we do not store emails as plain text.</p>';
	} else {
		echo '<section>
			<label for="email">Email</label>
			<input type="text" name="email" id="email" placeholder="you@there.com" value="' . $profile_email . '" />
			<span>Your email address is <strong>not</strong> stored as plain text, and never shared.  Setting an 
			email address will ensure that, should you need to, you will be able to regain control of this account 
			if you ever get a new IP address.  Once set, it can not be changed.</span>
		</section>';
	}
	
	echo '<section>
		<label for="USERNAME">Username</label>
		<input type="text" name="USERNAME" id="USERNAME" placeholder="Your memorable name" value="' . $profile_name . '" />
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
		<input type="text" name="follow" id="follow" value="' . $profilefollow . '" placeholder="User IDs" />
		<span>By putting a comma separated list of usernames or IDs, you are able to build a customized feed of content 
		tailored to your personal tastes from the people you like.  Simply use the follow format: username,username,username... 
		or userid,userid,userid... to customize your viewing preferences.</span>
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
</form>

<script type="text/javascript">
	document.title = \'Options\';
</script>';