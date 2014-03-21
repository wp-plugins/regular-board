<?php 

/**
 * Area: mod
 *
 * (1) Moderation action queue.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( count ( $mod_logs ) > 0 ) {
	echo '<div class="thread"><div class="right">Age</div><div class="left">Message</div></div>';
	foreach ( $mod_logs as $logs ) {
		echo '<div class="thread">';
		
		echo '<div class="right">' . regular_board_timesince( $logs->logs_date ) . '</div><div class="left">' . $logs->logs_message . '</div>';
		
		echo '</div>';
	}
} else { 
	echo '<div class="thread"><p><strong>Nothing to see here.</strong></p></div>';
}