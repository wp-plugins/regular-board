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
if ( $area == 'history' ) {
	$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_id = %d LIMIT 1", $profileid ) );
}
if ( $this_user ) {
	$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_name = %s LIMIT 1", $this_user ) );
}
if ( count ( $usprofile ) > 0 ) {
	foreach ( $usprofile as $theprofile ) {
			echo '<div class="profile_deets">';
			if ( $theprofile->user_avatar ) {
				if ( $theprofile->user_avatar != 'NULL' ) {
					echo '<img src="' . $theprofile->user_avatar . '" class="imageFULL" />';
				}
			}
			if ( $theprofile->user_name ) {
				echo '<h1>' . $theprofile->user_name . '</h1> ';
			} else {
				echo '<h1> Anonymous </h1>';
			}
			if ( $theprofile->user_slogan ) {
				if ( $theprofile->user_slogan != 'NULL' ) {
					echo '<p><em>' . str_replace ( '\\', '', $theprofile->user_slogan ) . '</em></p>';
				}
			}
			echo '<i class="fa fa-laptop"></i> active posts: ' . $totalpages . ' &mdash; 
			<i class="fa fa-clock-o"></i> first seen ' . regular_board_timesince ( $theprofile->user_date ) . ' </p>
			<h3>Post history</h3>';
			echo '</div>';
		if(count( $getposts ) > 0 ) {
			foreach ( $getposts as $posts ) {
				if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
					include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
				} else {
					include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
				}
			}
		}
	}
}

include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );