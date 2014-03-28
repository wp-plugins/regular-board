<?php 

/**
 * User mods and janitors
 *
 * (1) Determine if user is a moderator or a janitor
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

$current_user        = wp_get_current_user();
$current_user_login  = $current_user->user_login;

if ( current_user_can ( 'manage_options' ) ) {
	$is_moderator = true;
}

if ( $board_mods ) {
	$usermods = explode ( ',', $board_mods );
	if ( in_array ( $current_user_login, $usermods ) || in_array ( $profileid, $usermods ) ) {
		$is_user_mod    = true;
		$user_logged_in = 1;
	}
}

if ( $board_jans ) {
	$userjanitors = explode ( ',', $board_jans );
	if ( in_array ( $current_user_login, $userjanitors ) || in_array ( $profileid, $userjanitors ) ) {
		$is_user_janitor = true;
		$user_logged_in  = 1;
	}
}

if ( $usermod ) {
	$usermod = array ( $usermod );
}

if ( $is_moderator ) {
	$is_user = false;
}

if ( $is_user_mod ) {
	$is_user = false;
}

if ( $is_user_janitor ) {
	$is_user = false;
}

if ( $is_moderator || $is_user_mod ) {
	if ( $this_area == 'queue'   ) { $get_queue   = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE ( post_reportcount > %d OR post_public > %d )", 0, 1 ) ); }
}

if ( $lock == 1 ) {
	if ( $is_user ) {
		$posting = 0;
	}
	if ( $is_user !== true ) {
		$posting = 1;
	}
}