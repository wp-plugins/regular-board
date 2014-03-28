<?php 

/**
 * User information
 *
 * (1) Grab user information based on currently logged in IP.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

/**
 * USER INFORMATION
 * Get all information for current IP (user)
 */

/** 
 * Get all information from the database associated with the currently connected 
 * IP address for use throughout the plugin.
 */
 
$myinformation = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_users_select FROM $regular_board_users WHERE user_logged_in_from = %s AND user_logged_in = 1 LIMIT 1", $user_ip ) );
if ( count ( $myinformation ) > 0 ) {
	foreach ( $myinformation as $myinfo ) {
		$profileavatar       = sanitize_text_field ( $myinfo->user_avatar );
		$profileslogan       = sanitize_text_field ( str_replace ( '\\', '', $myinfo->user_slogan ) );
		$profileid           = intval ( $myinfo->user_id );
		$profileheaven       = intval ( $myinfo->user_heaven );
		$profile_email       = sanitize_text_field ( $myinfo->user_email );
		$profile_name        = sanitize_text_field ( $myinfo->user_name );
		if ( !$profile_name ) {
			$profile_name    = 'null';
		}
		$profilepassword     = sanitize_text_field ( $myinfo->user_password );
		$profilefollow       = sanitize_text_field ( $myinfo->user_follow );
		$following           = sanitize_text_field ( $myinfo->user_follow );
		$boards              = sanitize_text_field ( $myinfo->user_boards );
		$profileboards       = sanitize_text_field ( $myinfo->user_boards );
		$following           = sanitize_text_field ( $profilefollow );
		if ( !$myinfo->user_logged_in ) {
			$user_exists         = 0;
		}
		if ( $myinfo->user_logged_in ) {
			$user_exists         = 1;
		}
		if ( $profileboards ) {
			$profileboards       = explode   ( ',', $profileboards );
			$profileboards       = array_map ( 'regular_board_apply_quotes', $profileboards );
		}
		if( $following ) {
			$following       = explode   ( ',', $following );
			$following       = array_map ( 'regular_board_apply_quotes', $following );
		}
		$profile_strikes     = intval ( $myinfo->user_strikes );
		$profile_strikes_up  = intval ( $myinfo->user_strikes + 1 );
		$profile_level       = intval ( $myinfo->user_level );
		$profile_level_up    = intval ( $myinfo->user_level + 1 );
		$profile_posts       = intval ( $myinfo->user_posts );
		$profile_posts_up    = intval ( $myinfo->user_posts + 1 );
		$i_am_logged_in      = intval ( $myinfo->user_logged_in );
		if ( $profile_strikes == 0 ) {
			$ban_length_minutes = '10 minutes';
		} else {
			$ban_length_minutes = $profile_strikes . '0 minutes';
		}				
		if ( $profile_level <= 50 ) {
			$profile_posts_check = $profile_posts / 10;
		}
		if ( $profile_level <= 100 && $profile_level > 50  ) {
			$profile_posts_check = $profile_posts / 20;
		}
		if ( $profile_level <= 150 && $profile_level > 100  ) {
			$profile_posts_check = $profile_posts / 30;
		}
		if ( $profile_level <= 200 && $profile_level > 150  ) {
			$profile_posts_check = $profile_posts / 40;
		}
		if ( $profile_level <= 250 && $profile_level > 200  ) {
			$profile_posts_check = $profile_posts / 50;
		}
		if ( $profile_level <= 300  && $profile_level > 250 ) {
			$profile_posts_check = $profile_posts / 60;
		}
		if ( $profile_level <= 350  && $profile_level > 300 ) {
			$profile_posts_check = $profile_posts / 70;
		}
		if ( $profile_level <= 400  && $profile_level > 350 ) {
			$profile_posts_check = $profile_posts / 80;
		}
		if ( $profile_level <= 450  && $profile_level > 400 ) {
			$profile_posts_check = $profile_posts / 90;
		}
		if ( $profile_level <= 500  && $profile_level > 450 ) {
			$profile_posts_check = $profile_posts / 100;
		}
		if ( $this_area == 'messages' ) {
			if ( !isset ( $_GET['message'] ) ) {
				$my_messages = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_messages_select FROM $regular_board_messages WHERE ( messages_to = %s OR messages_from = %s ) ORDER BY messages_id DESC", $profile_name, $profile_name ) );
			}
			if ( isset ( $_GET['message'] ) ) {
				$message_id = intval ( $_GET['message'] );
				$my_messages = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_messages_select FROM $regular_board_messages WHERE ( messages_to = %s OR messages_from = %s ) AND messages_id = %d LIMIT 1", $profile_name, $profile_name, $message_id ) );
			}
		}
		if ( $this_area == 'stuff' ) {
			$my_unread   = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_messages WHERE messages_read = 0 AND messages_to = '$profile_name'" );
			$my_unread   = intval ( $my_unread );
			$my_waiting  = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_friends_select FROM $regular_board_friends WHERE friends_connectee = %s AND friends_mutual = %d", $profile_name, 0 ) );
			$my_waitings = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_friends WHERE friends_connectee = '$profile_name' AND friends_mutual = 0" );
		}
		if ( $this_area == 'history' || $this_user ) {
			$my_friends  = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_friends_select FROM $regular_board_friends WHERE ( friends_connector = %s OR friends_connectee = %s ) AND friends_mutual = %d", $profile_name, $profile_name, 1 ) );
		}
	}
}