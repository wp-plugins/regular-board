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

$thread_count    = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_parent = 0" );
$reply_count     = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_parent > 0" );
$ten_minutes     = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$ten_minutes_ago' AND '$current_timestamp'" );
$two_hours       = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$two_hours_ago' AND '$current_timestamp'" );
$twelve_hours    = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$twelve_hours_ago' AND '$current_timestamp'" );
$month           = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$one_month_ago' AND '$current_timestamp'" );
$day             = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_date BETWEEN '$one_day_ago' AND '$current_timestamp'" );
$count_users     = ( $wpdb->get_var( "SELECT COUNT(Distinct user_id) FROM $regular_board_users WHERE user_posts > 0 " ) + $wpdb->get_var( "SELECT COUNT(Distinct post_guestip) FROM $regular_board_posts" ) );
$count_boards    = $wpdb->get_var( "SELECT COUNT(Distinct post_board) FROM $regular_board_posts" );


echo '<div class="stats"><h1>Installation statistics</h1>

<p>
	Statistics are based on <strong>active</strong> content.<br />
	This page does not take into account posts that have been deleted or marked as spam.
</p>

<p>
	<strong>Post statistics</strong>:<br />
	There are <strong>' . $thread_count . '</strong> active threads with <strong>' . $reply_count . '</strong> active comments.<br />
	Within the last ten minutes, <strong>' . $ten_minutes . '</strong> posts were made.<br />
	<strong>' . $two_hours . '</strong> within the last two hours, and <strong>' . $twelve_hours . '</strong> within the last 12 hours.<br />
	<strong>' . $month . '</strong> within the last month.<br />
	Within the last day, there have been <strong>' . $day . '</strong> posts created.
</p>

<p>
	<strong>User statistics</strong>:<br />
	There have been ' . $count_users . ' unique posters.
</p>

<p>
	<strong>Board statistics</strong>:<br />
	There are currently ' . $count_boards . ' active boards.
<?p>

</div>';
