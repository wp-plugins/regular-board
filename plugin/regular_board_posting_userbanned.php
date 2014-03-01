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
	if ( $LENGTH == 0 ) {
		$LENGTH = 'Permanent';
	}
	if ( $LENGTH != 0 ) {
		if ( $CURRENTDATE > $banIsActiveFor ) { 
			$banLifted = 1;
		} else {
			$banLifted = 0;
		}
	} else {
		$banLifted = 0;
	}
	
	echo '<div class="thread"><p>You are currently banned.</p>';
	foreach ( $getuser as $gotUser ) {
		$BANID   = intval ( $gotUser->banned_id );
		$BANNED  = intval ( $gotUser->banned_banned );
		$IP      = $gotUser->banned_ip;
		$MESSAGE = $gotUser->banned_message;
		$MESSAGE = regular_board_format ( $MESSAGE );
		if ( !$MESSAGE ) {
			$MESSAGE = '<em>No reason given</em>';
		}
		echo '<p><i class="fa fa-user"> Your IP: ' . $ipaddress . '</i> &mdash; <i class="fa fa-clock-o"> Length: ' . $LENGTH . '</i></p>';
		echo '<p>You have been banned from using these boards';
		if ( $LENGTH === 'Permanent' ) {
			echo ' permanently';
		}
		if ( $LENGTH !== 'Permanent' ) {
			echo ' for ' . $LENGTH;
		}
		echo '.  Your ban was filed on '.$FILED.'.  The reason given for your ban was:</p><p>' . $MESSAGE . '</p><p>If you wish to appeal this ban, please e-mail the moderators of this board with the following ID: '.$BANID.', with the subject line <em>Ban Appeal</em>, and someone will get back to you shortly.  If there is no moderation e-mail on file, there is nothing more for you to do here.</p><p>Have a nice day.</p>';
		echo '</p>';
	}
	if ( $LENGTH != 0 ) {
		if ( $banLifted == 1 ) {
			$wpdb->delete ( $regular_board_bans, array('banned_id' => $BANID, 'banned_banned' => 1), array ( '%d','%d' ) );
		}
	}
}