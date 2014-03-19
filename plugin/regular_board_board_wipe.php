<?php 

/**
 * Board wipe mechanism (non-single)
 *
 * (1) Wipe boards and posts 
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}
if ( $board_wipe_every && $board_wipe_every != strtolower ( 'never' ) && $board_wipe_per == strtolower ( 'board' ) ) {
	$today_is   = strtotime ( $current_timestamp );
	if ( strpos ( strtolower ( $board_wipe_every ), 'minute' ) ) {
		$uptime = intval ( $board_wipe_every ) * 60;
	} elseif ( strpos ( strtolower ( $board_wipe_every ), 'hour' ) ) {
		$uptime = intval ( $board_wipe_every ) * 3600;
	} elseif ( strpos ( strtolower ( $board_wipe_every ), 'day' ) ) {
		$uptime = intval ( $board_wipe_every ) * 86400;
	} elseif ( strpos ( strtolower ( $board_wipe_every ), 'week' ) ) {
		$uptime = intval ( $board_wipe_every ) * 604800;
	} elseif ( strpos ( strtolower ( $board_wipe_every ), 'month' ) ) {
		$uptime = intval ( $board_wipe_every ) * 2628000;
	} elseif ( strpos ( strtolower ( $board_wipe_every ), 'year' ) ) {
		$uptime = intval ( $board_wipe_every ) * 31536000;
	} elseif ( strpos ( strtolower ( $board_wipe_every ), 'second' ) ) {
		$uptime = intval ( $board_wipe_every ) * 1;
	} else {
		$uptime = intval ( $board_wipe_every ) * 60;
	}
	$board_life = ( intval ( $board_wipe_date ) + intval ( $uptime ) );
	$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_wipe_date ) ) );
	$wipe = number_format ( intval ( $today_is ) - intval ( $board_wipe_date ) ) / intval ( $uptime ) * 100;
	if ( strpos( $next_wipe, '-' ) !== true && $display_wipe && $display_wipe == 1 ) { 
		$wipe_on_this_date = date ( "M d, Y - h:i:s A T", $board_life );
		$wipe_countdown = $wipe_on_this_date;
	}
	if($today_is > $board_life){
		$wpdb->query ( "UPDATE $regular_board_boards SET board_postcount = 0 WHERE board_id > 0" );
		
		if ( $protectedboards ) {
			$wpdb->query ( "DELETE FROM $regular_board_posts WHERE post_board NOT IN ( " . join (',', $protected_boards ) . ")");
			
		} else{				
			$wpdb->delete ( $regular_board_posts, array ( 'post_moderator' => 0 ), array ( '%d' ) );
			$wpdb->delete ( $regular_board_posts, array ( 'post_moderator' => 1 ), array ( '%d' ) );
			$wpdb->delete ( $regular_board_posts, array ( 'post_moderator' => 2 ), array ( '%d' ) );
			$wpdb->delete ( $regular_board_posts, array ( 'post_moderator' => 3 ), array ( '%d' ) );
		}
		
		update_option ( 'regular_board_wipealldate', str_replace ( '\\', '', $current_timestamp ) );
	}
}

if ( $protocol == 'boards' ) {
	foreach ( $getboards as $gotboards ) {
		if ( !$board_wipe_every ) {
			if( $gotboards->board_wipe && $gotboards->board_wipe != strtolower ( 'never' ) ) {
				$board_date = strtotime($gotboards->board_date);
				$today_is = strtotime($current_timestamp);
				if ( strpos ( strtolower ( $gotboards->board_wipe ), 'minute' ) ) {
					$uptime = intval ( $gotboards->board_wipe ) * 60;
				} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'hour' ) ) {
					$uptime = intval ( $gotboards->board_wipe ) * 3600;
				} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'day' ) ) {
					$uptime = intval ( $gotboards->board_wipe ) * 86400;
				} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'week' ) ) {
					$uptime = intval ( $gotboards->board_wipe ) * 604800;
				} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'month' ) ) {
					$uptime = intval ( $gotboards->board_wipe ) * 2628000;
				} elseif ( strpos ( strtolower ( $gotboards->board_wipe ), 'year' ) ) {
					$uptime = intval ( $gotboards->board_wipe ) * 31536000;
				} else {
					$uptime = intval ( $gotboards->board_wipe ) * 60;
				}
				$board_life = ( intval ( $board_date ) + intval ( $uptime ) );
				$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_date ) ) );
				$wipe = number_format ( intval ( $today_is ) - intval ( $board_date ) ) / intval ( $uptime ) * 100;
				
				if($today_is > $board_life){
					$wpdb->delete ( $regular_board_posts, array ( 'post_board' => $gotboards->board_shortname ), array ( '%s' ) );
					$wpdb->query ( "UPDATE $regular_board_boards SET board_date = '$current_timestamp' WHERE board_shortname = '$gotboards->board_shortname'" );
				}
			}
		}
	}
}