<?php 

/**
 * Board information
 *
 * (1) Determine board information to show
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( $protocol == 'boards' ) {
	$getboards = $wpdb->get_results ( "SELECT * FROM $regular_board_boards WHERE board_shortname != '' ORDER BY board_postcount DESC, board_name ASC" );
}
if ( $the_board ) {
	$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $the_board ) ) ;
}
if ( isset ( $_REQUEST['board'] ) ) {
	$the_board = sanitize_text_field ( strtolower ( $_REQUEST['board'] ) );
	$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $the_board ) ) ;
}
if ( !$the_board && $thisboard ) {
	$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $thisboard ) );
}
if ( $protocol == 'boards' ) {
	if ( count ( $getboards ) == 1 ) {
		foreach ( $getboards as $board ) {
			$thisboard = $board->board_shortname;
		}
	}
}
if ( $this_thread ) {
	$thread_board      = $wpdb->get_var ( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread LIMIT 1" );
	if ( $thread_board ) {
		$get_current_board = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s LIMIT 1", $thread_board ) );
	}
}		
if ( count ( $get_current_board ) > 0 && $protocol == 'boards' ) {
	foreach ( $get_current_board as $current_board_information ) {
		$lock              = intval ( $current_board_information->board_locked );
		$board_id          = intval ( $current_board_information->board_id );
		$board_name        = $current_board_information->board_name;
		$board_short       = $current_board_information->board_shortname;
		$board_description = $current_board_information->board_description;
		$board_mods        = $current_board_information->board_mods;
		$board_jans        = $current_board_information->board_janitors;
		$board_posts       = intval ( $current_board_information->board_postcount );
		$require_logged    = intval ( $current_board_information->board_logged );
		$boardwipe         = $current_board_information->board_wipe;
		$boarddate         = $current_board_information->board_date;
		if ( !$board_wipe_every ) {
			if( $boardwipe && $boardwipe != strtolower ( 'never' ) ) {
				$board_date = strtotime ( $boarddate );
				$today_is   = strtotime ( $current_timestamp );
				if ( strpos ( strtolower ( $boardwipe ), 'minute' ) ) {
					$uptime   = intval ( $boardwipe ) * 60;
					$interval = ' every minute';
				} elseif ( strpos ( strtolower ( $boardwipe ), 'hour' ) ) {
					$uptime   = intval ( $boardwipe ) * 3600;
					$interval = ' hourly';
				} elseif ( strpos ( strtolower ( $boardwipe ), 'day' ) ) {
					$uptime   = intval ( $boardwipe ) * 86400;
					$interval = ' daily';
				} elseif ( strpos ( strtolower ( $boardwipe ), 'week' ) ) {
					$uptime   = intval ( $boardwipe ) * 604800;
					$interval = ' weekly';
				} elseif ( strpos ( strtolower ( $boardwipe ), 'month' ) ) {
					$uptime   = intval ( $boardwipe ) * 2628000;
					$interval = ' monthly';
				} elseif ( strpos ( strtolower ( $boardwipe ), 'year' ) ) {
					$uptime   = intval ( $boardwipe ) * 31536000;
					$interval = ' yearly';
				} else {
					$uptime   = intval ( $boardwipe ) * 60;
					$interval = ' every minute';
				}
				$board_life = ( intval ( $board_date ) + intval ( $uptime ) );
				$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_wipe_date ) ) );
				$wipe = number_format ( intval ( $today_is ) - intval ( $board_date ) ) / intval ( $uptime ) * 100;
				$next_clean = date($boarddate, time() + $next_wipe);
				if ( strpos( $next_wipe, '-' ) !== true && $display_wipe && $display_wipe == 1 ) { 
					$wipe_on_this_date = date ( "M d, Y - h:i:s A T", $board_life );
					$wipe_countdown = $wipe_on_this_date;
				}
			}
		}
		if ( $board_wipe_every ) {
			$wipe_countdown = '';
		}
		if( $board_description ) {
			$boardheader      = '<li><a href="' . $current_page . '?b=' . $board_short . '">' . $board_name . ' - ' . $board_description . ' <i class="fa fa-caret-square-o-down"></i></a>';
		}
		if( !$board_description ) {
			$boardheader      = '<li><a href="' . $current_page . '?b=' . $board_short . '">' . $board_short .  ' - ' . $board_name . ' <i class="fa fa-caret-square-o-down"></i></a>';
		}
		echo '<script type="text/javascript">document.title = \'' . $board_name . ' / ' . $board_short . '\';</script>';
	}
} else {
	$boardheader = '';
}