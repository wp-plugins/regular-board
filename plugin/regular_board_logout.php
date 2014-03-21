<?php 

/**
 * Logout
 *
 * (1) Log the user out
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

$wpdb->update (
	$regular_board_users,
	array ( 
		'user_logged_in'      => 0,
		'user_logged_in_from' => ''
	),
	array ( 
		'user_id'  => $profileid
	),
	array ( 
		'%d', 
		'%s', 
		'%d'
	)
);
echo '<meta http-equiv="refresh" content="0;' . $current_page . '">';