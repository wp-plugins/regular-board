<?php 

/**
 * Post History for Current User
 *
 * (1) Get the user's post history
 * (2) Posts will show up in history if they weren't posted as anonymous ( by option, not name )
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}
if ( $this_area == 'history' ) {
	$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_users_select FROM $regular_board_users WHERE user_id = %d LIMIT 1", $profileid ) );
}
if ( $this_user ) {
	$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_users_select FROM $regular_board_users WHERE user_name = %s LIMIT 1", $this_user ) );
}

$the_profile_name    = '';
$the_profile_avatar  = '';
$the_profile_slogan  = '';
$the_profile_details = '';
$connect_with        = '';
if ( count ( $usprofile ) > 0 ) {
	foreach ( $usprofile as $theprofile ) {
			
			if ( $theprofile->user_name ) {
				$the_profile_name = sanitize_text_field ( $theprofile->user_name );
			}
			if ( $theprofile->user_avatar ) {
				if ( $theprofile->user_avatar != 'NULL' ) {
					$the_profile_avatar = '<img src="' . $theprofile->user_avatar . '" class="imageFULL" />';
				}
			}
			if ( $theprofile->user_slogan ) {
				if ( $theprofile->user_slogan != 'NULL' ) {
					$the_profile_slogan = '<p><em>' . str_replace ( '\\', '', $theprofile->user_slogan ) . '</em></p>';
				}
			}
			$the_profile_details = '<p>level ' . $theprofile->user_level . '<br />active posts: ' . $totalpages . ' /
			total posts: ' . $theprofile->user_posts . ' <br />
			 member for ' . str_replace ( 'ago', '', regular_board_timesince ( $theprofile->user_date ) ) . ' </p>';
			
			
		if ( $totalpages ) {
			foreach ( $getposts as $posts ) {
				if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
					include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
				} else {
					include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
				}
			}
		} else {
			echo '<div class="thread"><center><em>nothing to see here.</em></center></div>';
		}
	}
}

include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );