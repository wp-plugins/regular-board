<?php 

/**
 * Flood Function
 *
 * (1) Get the user's last post
 * (2) Check the time of that post against the current time
 * (3) Check the current time against any flood gates set in Options
 * (4) Determine if the amount of time specified in flood gate has passed relative to
 * (4) the current time
 * (5) Based on this determination, activate the flood gate
 * (6) If the user is allowed flood gate bypassing or is the WordPress admin, 
 * (7) always set flood gate to be off
 *
 * @package regular_board
 */

if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

$check_user_last_post = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_userid = %d ORDER BY post_date DESC LIMIT 1", $profileid ) );
if ( count ( $check_user_last_post ) > 0 ) {
	if ( $user_flood ) {
		$user_flood = array ( $user_flood );
		$current_user_check = $current_user->user_login;
	}
	foreach( $check_user_last_post as $user_last_post ) {
		if ( $user_flood && in_array ( $current_user_check, $user_flood ) || current_user_can ( 'manage_options' ) ) {
			$timegateactive = false;
		} else {
			$time = $user_last_post->post_date;
			$posted_on = strtotime ( $time );
			$currently = strtotime ( $current_timestamp );
			$timegate = $currently - $posted_on;
			if ( $timegate < $flood_gate ) {
				$timegateactive = true;
			}
		}
	}
}
