<?php 

/**
 * Banned Functions
 *
 * (1) Determine whether or not the current user is currently banned.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}
echo '<div class="thread">';
foreach ( $getuser as $banneddetails ) {
	$LENGTH = $banneddetails->banned_length;
	$FILED = $banneddetails->banned_date;
	if ( $LENGTH != 0 ) {
		$DATEFILED   = strtotime ( $banneddetails->banned_date );
		$CURRENTDATE = strtotime ( $current_timestamp );

		if ( strpos ( strtolower ( $LENGTH ), 'minute' ) ) {
			$bantime = intval ( $LENGTH ) * 60;
		} elseif (strpos ( strtolower ( $LENGTH ), 'hour' ) ) {
			$bantime = intval ( $LENGTH ) * 3600;
		} elseif (strpos ( strtolower ( $LENGTH ), 'day' ) ) {
			$bantime = intval ( $LENGTH ) * 86400;
		} elseif (strpos ( strtolower ( $LENGTH ), 'week' ) ) {
			$bantime = intval ( $LENGTH ) * 604800;
		} elseif (strpos ( strtolower ( $LENGTH ), 'month' ) ) {
			$bantime = intval ( $LENGTH ) * 2628000;
		} elseif (strpos ( strtolower ( $LENGTH ), 'year' ) ) {
			$bantime = intval ( $LENGTH ) * 31536000;
		} else {
			$bantime = intval ( $LENGTH ) * 60;
		}
		$banIsActiveFor = ( $DATEFILED + $bantime );
	}
	if ( !$LENGTH ) {
		$LENGTH = 'Permanent';
	}
	if ( $LENGTH ) {
		if ( $CURRENTDATE > $banIsActiveFor ) { 
			$banLifted = 1;
		} else {
			$banLifted = 0;
		}
	} else {
		$banLifted = 0;
	}
	
	echo '<div class="profile_deets">';
	if ( $banned_image ) {
		echo '<img src="' . $banned_image . '" alt="Banned" class="imageFULL" />';
	}
	echo '<h1>BANNED</h1>';
	foreach ( $getuser as $gotUser ) {
		$BANID   = intval ( $gotUser->banned_id );
		$BANNED  = intval ( $gotUser->banned_banned );
		$IP      = $gotUser->banned_ip;
		$MESSAGE = $gotUser->banned_message;
		$MESSAGE = regular_board_format ( $MESSAGE );
		if ( !$MESSAGE ) {
			$MESSAGE = '<em>No reason given</em>';
		}
		$filed_on = strtotime ( $FILED );
		$today_is = strtotime ( $current_timestamp );
		$unbanned = ( intval ( $bantime ) - ( intval ( $today_is ) - intval ( $filed_on ) ) );
		
		
		if ( $LENGTH ) {
			echo '<h3>Ban length: ' . $LENGTH . '</h3> ' . $unbanned . ' seconds until unbanned.<hr />';
		} else {
			echo '<h3>Ban length: PERMANENT</h3>';
		}

		echo '<p>Reason: ' . $MESSAGE . '</p>
		</div>';
	}
	if ( $LENGTH ) {
		if ( $unbanned <= 0 ) {
			$wpdb->delete ( $regular_board_bans, array('banned_id' => $BANID ), array ( '%d' ) );
		}
	}
}
echo '</div>';