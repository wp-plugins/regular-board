<?php 

/**
 * User Options Form Handling
 *
 * (1) Handle option saving for user options form
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

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
	$checkuser = $wpdb->get_results ( $wpdb->prepare ( "SELECT user_email FROM $regular_board_users WHERE user_email = %s", $update_email) );
	if ( count ( $checkuser ) == 0 ) {
		$wpdb->query ( "UPDATE $regular_board_users SET user_email = '$update_email' WHERE user_id = $profileid" );
	} else {
		echo '<p><strong>This username is already taken.  Please use a different one.</p>';
	}
}
$update_name                                   = sanitize_text_field ( $_REQUEST['USERNAME'] );
$update_heaven                                 = intval  ( $_REQUEST['heaven'] );
$update_boards                                 = sanitize_text_field( $_REQUEST['boards'] );
$update_follow                                 = sanitize_text_field( $_REQUEST['follow'] );
$update_slogan                                 = sanitize_text_field( substr ( $_REQUEST['slogan'], 0, $max_text ) );
if ( $update_name ) {
	$checkname = $wpdb->get_results ( $wpdb->prepare ( "SELECT user_name FROM $regular_board_users WHERE user_name = %s AND user_id != %d", $update_name, $profileid ) );
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