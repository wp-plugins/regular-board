<?php 

/**
 * Board loops
 *
 * (1) Display when viewing a board
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if( $lock == 1 ) {
	echo '<p>This board is currently locked.</p>';
}

if ( $the_board && !$this_thread ) {
	echo '<div id="thread' . htmlentities($the_board) . '">';
}
foreach ( $getposts as $posts ) {
	echo '<div class="';
	if ( $comment_parent ) {
		echo 'thread' . $this_thread;
	} else {
		echo 'thread';
		if ( $posts->post_comment_parent ) {
			echo ' child';
		}
	}
	echo '" id="thread' . $posts->post_id . '">';
	if ( $search_enabled && $search && $this_thread ) {
		$gotReplies = $wpdb->get_results( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE ( post_email = '$search' OR post_comment LIKE '%$search%' OR post_title LIKE '%$search%' OR post_url LIKE '%$search%' ) AND post_parent = $posts->post_id ORDER BY post_last ASC" );
	} 
	if ( !$search && $this_thread ) { 
		$gotReplies = $wpdb->get_results ( $wpdb->prepare ("SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_parent = %d AND post_comment_parent = 0 ORDER BY post_last ASC", $posts->post_id ) );
	}
	if ( $search_enabled && $search && $this_thread ) { 
		echo '<p><em>&nbsp;&nbsp;&nbsp;&nbsp; Searching this thread for ' . $search . '.  Returned ' . count ( $gotReplies ) . ' results.</em></p>';
	}
	
	if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
		include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
	} else {
		include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
	}
	if ( !$posts->post_parent ) { 
		$parent_present = 1;
		echo '<div class="omitted' . $posts->post_id . '"'; if ( $this_thread ) { echo ' id="omitted"'; } echo '>';
	}

	
	// Thread replies 	
	if ( $gotReplies ) {
		if ( count ( $gotReplies ) > 0 && $this_thread ) {
			foreach ( $gotReplies as $posts ) {
				if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
					include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
				} else {
					include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
				}
			}
		}
	}

	if ( $parent_present ) { 
		echo '</div>';
	}
	echo '</div>';
}
if ( $the_board && !$this_thread ) {
	echo '</div>';
}

if( $the_board && !$this_thread ) {
	include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );
}

$threadexists = 1;

