<?php 

/**
 * Queries
 *
 * (1) Queries for different areas
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

$total_posts = $wpdb->get_var ( "SELECT SUM(board_postcount) FROM $regular_board_boards" );
$getuser     = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_bans WHERE banned_ip = %s AND banned_banned = %d LIMIT 1", $user_ip, 0  ) );
if ( count ( $getuser ) > 0 ) {
	$userisbanned = 1;
}
if ( $search_enabled && isset ( $_POST['regular_board_search_submit'] ) && $_REQUEST['regular_board_search'] ) {
	$search = sanitize_text_field ( str_replace ( '\'', '\\\'', $_REQUEST['regular_board_search'] ) );
}
$use_this      = 0;
$order_by = "post_id DESC";
if ( $search_enabled && $search ) {
	$use_this++;
	$where_by = "WHERE ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' )";
} else {
	if ( $the_tag ) {
		$use_this++;
		$where_by = "WHERE post_comment LIKE '%#$the_tag%'";
	}
	if ( $the_board && !$the_tag ) {
		$use_this++;
		if ( $protocol == 'boards' ) {
			$where_by = "WHERE post_parent = 0 AND post_board = '$the_board'";
		}
		if ( $protocol == 'tags' ) {
			$where_by = "WHERE post_comment LIKE '%#$the_board%'";
		}
		$order_by = "post_sticky DESC, post_last DESC";
	}		
	if ( $this_area == 'topics' || !$the_board && !$this_area && !$this_user && !$the_tag ) {
		$use_this++;
		$where_by = "WHERE post_parent = 0";
		$order_by = "post_sticky DESC, post_last DESC";
	}
	if( !$the_board && $this_area == 'replies' && !$this_thread && !$this_user ){
		$use_this++;
		$where_by = "WHERE post_parent != 0";
		$order_by = "post_sticky DESC, post_last DESC";
	}
	if( $nothing_is_here ) {
		$use_this++;
		if ( $these_boards ) {
			$where_by = "WHERE post_parent = 0 AND post_board IN ( " . join (',', $these_boards ) . ") ";
		} else {
			$where_by = "WHERE post_parent = 0 ";
		}
		$order_by = "post_date DESC";
	}
	if( !$the_board && $this_area == 'gallery' && !$this_thread && !$this_user ) {
		$use_this++;
		$where_by = "WHERE post_url != ''";
	}		
	if( !$the_board && $this_area == 'subscribed' && $profileboards && !$this_user ) {
		$use_this++;
		$where_by = "WHERE post_board IN ( " . join (',', $profileboards ) . ")";
	}
	if( !$the_board && $this_area == 'following' && $following && !$this_user ) {
		$use_this++;
		$where_by = "WHERE ( post_userid IN (" . join (',', $following ) . ") OR post_name IN (" . join (',', $following ) . ") )";
	}
	if ( $this_thread && !$this_user ) {
		$use_this++;
		$where_by = "WHERE post_id = $this_thread AND post_parent = 0";
		if ( $search_enabled && $search ) {
			$countParentReplies = "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' ) AND post_parent = $this_thread";
		} else {					
			$countParentReplies = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_parent = %d", $this_thread ) );
		}
	}
	if ( $this_area == 'history' ) {
		$use_this++;
		$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_id = %d LIMIT 1", $profileid, $this_user ) );
		$where_by = "WHERE post_userid = $profileid";
		$order_by = "post_date DESC";		
	}
	if ( $this_area == 'mod' ) {
		$mod_logs = $wpdb->get_results ( "SELECT * FROM $regular_board_logs ORDER BY logs_id DESC LIMIT 50 " );
	}
	if ( $this_user ) {
		$my_friends  = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_friends_select FROM $regular_board_friends WHERE ( friends_connector = %s OR friends_connectee = %s ) AND friends_mutual = %d", $this_user, $this_user, 1 ) );
		$use_this++;
		$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_name = %s LIMIT 1", $profileid, $this_user ) );
		$where_by = "WHERE post_name = '$this_user'";
		$order_by = "post_date DESC";
	}
}
if ( $use_this > 0 ) {
	if ( $search_enabled && $search ) {
		$totalpages = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts $where_by AND ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' )" );
	} else {
		$totalpages = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts $where_by" );
	}
	if ( $totalpages > 0 ) {
		if ( strpos ( strtolower ( $query ), 'n=' ) ) {
			$results    = intval ( $_GET['n'] );
		}
		if( $results ) {
			$start = ( $results - 1 ) * $posts_per_page;
		} else {
			$start = 0;
		}
		$getposts = $wpdb->get_results( "SELECT $regular_board_posts_select FROM $regular_board_posts $where_by ORDER BY $order_by LIMIT $start,$posts_per_page" );
	}
}
