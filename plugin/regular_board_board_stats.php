<?php 

/**
 * Board Statistics
 *
 * (1) Display information for all boards on the installation, on a board-by-board basis:
 * (1)  - any posts made within a (10 minute/2 hour/12 hour/1 day) time span
 * (1)  - all posts made 
 * (1)  - all posts made by mods and usermods
 * (1)  - all posts by users
 * (1)  - all posts by current user
 * (1)  - all posts marked as spam
 * (1)  - all posts marked for deletion
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

$count_all  = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts" );
foreach($getboards as $gotboards){
	$created_on       = $gotboards->board_date;
	$count_all_posts  = $wpdb->get_var( $wpdb->prepare ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_board = %s AND post_public = %d", $gotboards->board_shortname, 1 ) );
	$count_mod_posts  = $wpdb->get_var( $wpdb->prepare ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_board = %s AND ( post_moderator = %d OR post_moderator = %d) AND post_public = %d", $gotboards->board_shortname, 1, 2, 1 ) );
	$count_my_posts   = $wpdb->get_var( $wpdb->prepare ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_board = %s AND post_public = %d AND post_userid = %d", $gotboards->board_shortname, 1, $profileid ) );
	$count_user_posts = $wpdb->get_var( $wpdb->prepare ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_board = %s AND post_moderator = %d AND post_public = %d", $gotboards->board_shortname, 0, 1 ) );
	$count_spam       = $wpdb->get_var( $wpdb->prepare ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_public = %s", 2 ) );  
	$count_deleted    = $wpdb->get_var( $wpdb->prepare ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_public = %s", 3 ) );  
	$count_posts      = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_posts WHERE post_board = %s AND post_public = %d", $gotboards->board_shortname, 1 ) );
	$min10_t          = 0;
	$hou02_t          = 0;
	$hou12_t          = 0;
	$hou24_t          = 0;
	$currently        = strtotime ( $current_timestamp );
	foreach ( $count_posts as $posts ) {
		$timedif   = strtotime ( $posts->post_date );
		$moderator = $posts->post_moderator;
		$type      = $posts->post_type;
		if ( $currently - 600 <= $timedif && $timedif + 600 >= $currently ) {
			$min10_t++;
		}
		if ( $currently - 7200 <= $timedif && $timedif + 7200 >= $currently ) {
			$hou02_t++;
		}
		if ( $currently - 43200 <= $timedif && $timedif + 43200 >= $currently ) {
			$hou12_t++;
		}
		if ( $currently - 86400 <= $timedif && $timedif + 86400 >= $currently ) {
			$hou24_t++;
		}
	}
	echo '<div class="stats"><h1><a href="' . $current_page . '?b=' . $gotboards->board_shortname . '">' . $gotboards->board_name . '</a> ( ' . $gotboards->board_shortname . ' )</h1>
	<p>
		<em>Total posts: ' . $count_all_posts . '</em>
		&mdash; 
		<em>My posts: ' . $count_my_posts . '</em>
	</p>

	<h3>Posts made within the last...</h3>
	<p>
		<code>10 minutes: ' . $min10_t . '</code> ::
		<code>2 hours: ' . $hou02_t . '</code> ::
		<code>12 hours: ' . $hou12_t . '</code> ::
		<code>24 hours: ' . $hou24_t . '</code>
	</p>

	<h3>Moderator vs User Activity</h3>
	<p>
		The <strong>moderators</strong> have made ' . $count_mod_posts . ' posts, while <strong>users</strong> 
		have made ' . $count_user_posts . ' posts.  <br />
		On top of these ' . ( $count_mod_posts + $count_user_posts ) . ' posts, 
		' . $count_deleted . ' were deleted while ' . $count_spam . ' were marked as spam.
	</p></div>';
}