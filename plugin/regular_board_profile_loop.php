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
$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE ( user_id = %d OR user_name = %s ) LIMIT 1", $profileid, $this_user ) );
if ( count ( $usprofile ) > 0 ) {
	foreach ( $usprofile as $theprofile ) {
		if(count( $getposts ) > 0 ) {
			if ( $theprofile->user_name ) {
				echo '<h1>' . $theprofile->user_name . '</h1> ';
			} else {
				echo '<h1> Anonymous </h1>';
			}
			echo '<i class="fa fa-laptop"></i> active posts: ' . $totalpages . ' &mdash; 
			<i class="fa fa-clock-o"></i> first seen ' . regular_board_timesince ( $theprofile->user_date ) . ' </p>
			<h3>Post history</h3>';
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