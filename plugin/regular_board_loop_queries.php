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
$getuser     = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_bans_select FROM $regular_board_bans WHERE banned_ip = %s LIMIT 1", $user_ip  ) );

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
	if ( $this_area == 'videos' ) {
		$use_this++;
		if ( $the_board ) {
			$where_by = "WHERE post_type = 'youtube' AND post_board = '$the_board'";
		} else {
			$where_by = "WHERE post_type = 'youtube'";
		}
		
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
	if ( $this_area == 'topics' || $this_area == 'topics' && $the_board || !$this_area && !$this_user && !$the_tag ) {
		$use_this++;
		if ( $the_board ) {
			$where_by = "WHERE post_parent = 0 AND post_board = '$the_board' AND post_public = 1";
			$order_by = "post_sticky DESC, post_last DESC";
		} else {
			$where_by = "WHERE post_parent = 0 AND post_public = 1";
			$order_by = "post_sticky DESC, post_last DESC";		
		}
	}
	if ( !$the_board && $this_area == 'replies' && !$this_thread && !$this_user ){
		$use_this++;
		$where_by = "WHERE post_parent != 0 AND post_public = 1";
		$order_by = "post_sticky DESC, post_last DESC";
	}

	if ( $nothing_is_here ) {
		$use_this++;
		if ( $profileboards ) {
			$profileboards = " post_board IN ( " . join (',', $profileboards ) . ") AND post_public = 1";
		} else {
			$profileboards = '';
		}
		if ( $following  ) {
			$following = " ( post_userid IN (" . join (',', $following ) . ") OR post_name IN (" . join (',', $following ) . ") ) AND post_public = 1";
		} else {
			$following = '';
		}

		if ( $following || $profileboards ) {
			$where_by = "WHERE post_parent = 0 AND $following $profileboards ";
		} else {
			if ( $these_boards ) {
				$where_by = "WHERE post_parent = 0 AND post_board IN ( " . join (',', $these_boards ) . ")";
			} elseif ( !$these_boards ) {
				$where_by = "WHERE post_parent = 0 ";
			}
		}
		$order_by = "post_date DESC";
	}

	if ( $this_area == 'all' ) {
		$use_this++;
		$where_by = "WHERE post_parent = 0 AND post_public = 1";
		$order_by = "post_date DESC";
	}	
	
	
	if ( !$the_board && $this_area == 'gallery' && !$this_thread && !$this_user ) {
		$use_this++;
		if ( !$the_board ) {
			$where_by = "WHERE post_url != '' AND post_type = 'image'";
		} 
		if ( $the_board ) {
			$where_by = "WHERE post_url != '' AND post_type = 'image' AND post_board = '$the_board'";
		}
	}		
	if ( $this_thread && !$this_user ) {
		$use_this++;
		if ( $is_moderator ) {
			$where_by = "WHERE post_id = $this_thread";
		} else {
			$where_by = "WHERE post_id = $this_thread AND post_public = 1";
		}
		if ( $search_enabled && $search ) {
			$countParentReplies = "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' ) AND post_parent = $this_thread";
		} else {
			$countParentReplies = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_parent = %d", $this_thread ) );
		}
		$this_title = $wpdb->get_var ( "SELECT post_title FROM $regular_board_posts WHERE post_id = $this_thread LIMIT 1" );
		if ( $this_title ) {
			$this_title = htmlentities ( $this_title );
		} else {
			$this_title = '(Untitled)';
		}
	}
	if ( $this_area == 'history' ) {
		$use_this++;
		$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_users_select FROM $regular_board_users WHERE user_id = %d LIMIT 1", $profileid, $this_user ) );
		$where_by = "WHERE post_userid = $profileid";
		$order_by = "post_date DESC";		
	}
	if ( $this_area == 'mod' ) {
		$mod_logs = $wpdb->get_results ( "SELECT * FROM $regular_board_logs ORDER BY logs_id DESC LIMIT 50 " );
	}
	if ( $this_user ) {
		$my_friends  = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_friends_select FROM $regular_board_friends WHERE ( friends_connector = %s OR friends_connectee = %s ) AND friends_mutual = %d", $this_user, $this_user, 1 ) );
		$use_this++;
		$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_users_select FROM $regular_board_users WHERE user_name = %s LIMIT 1", $profileid, $this_user ) );
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
		if ( isset ( $_GET['n'] ) ) {
			$results    = intval ( $_GET['n'] );
		}
		if ( $results ) {
			$start = ( $results - 1 ) * $posts_per_page;
		} else {
			$start = 0;
		}
		$getposts = $wpdb->get_results( "SELECT $regular_board_posts_select FROM $regular_board_posts $where_by ORDER BY $order_by LIMIT $start,$posts_per_page" );
	}
}
