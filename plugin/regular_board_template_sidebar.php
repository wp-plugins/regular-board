<?php 

/**
 * Template: Sidebar
 *
 * (1) Sidebar content
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

echo '<div class="left-half">';
if ( $search_enabled ) {
	$search_action = $current_page;
	echo '<form name="regular_board_search" method="post" action="' . $search_action . '">';
		wp_nonce_field('regular_board_search');
		echo '
		<input type="text" name="regular_board_search" id="regular_board_search" placeholder="Search" />
		<input type="submit" class="hidden" id="regular_board_search_submit" name="regular_board_search_submit" value="Search" />
	</form>';
}		

if ( !$user_exists && !$userisbanned ) {
	include ( plugin_dir_path(__FILE__) . '/regular_board_loginorregister.php' );
} else {
	if ( $this_area != 'post' ) {
		if ( !$this_thread ) {
			if ( $the_board || $correct == 0 && $this_thread && count($getposts) > 0 || $nothing_is_here || $this_thread ) {
				if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
					include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
				} else {
					include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
				}
			}
		}
	}
}

echo '<small class="clear smallstats">
<i class="fa fa-user" title="You are using ' . $check_ammount . ' of ' . $accounts_per_ip . ' user slots available to you."> ' . $check_ammount . ' / ' . $accounts_per_ip . '</i>
 &mdash; 
<i class="fa fa-users" title="Accounts total"> ' . $count_users_total;
if ( $user_total_allowed ) {
	echo ' / ' . $user_total_allowed . ' / ' . $count_logged_total; 
}
echo '</i>
&mdash; 
<i class="fa fa-pencil" title="Active posts / total posts created (overall)"> ' . $posts_active_total . ' / ' . $posts_users_total . '</i>
</small>';

echo '<div class="tag_cloud"><span><a href="#">navigation</a></span>';

if ( $protocol == 'boards' ) {
	foreach ( $getboards as $gotboards ) {
		
		$board_post_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_board = '$gotboards->board_shortname' ");
		if ( !$board_post_count ) {
			$wpdb->query ( "UPDATE $regular_board_boards SET board_postcount = 0 WHERE board_shortname = '$gotboards->board_shortname'" );
		}
		
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
		if ( $gotboards->board_postcount > 0 ) {
			$percent = regular_board_percent ( $gotboards->board_postcount, $total_posts );
		} else {
			$percent = 0;
		}
		if ( $percent == 0 ) { $percent = 10; }
		elseif ( $percent >= 1 && $percent <= 10 )   { $percent = 11; }
		elseif ( $percent >= 11 && $percent <= 20 )  { $percent = 12; }
		elseif ( $percent >= 21 && $percent <= 30 )  { $percent = 13; }
		elseif ( $percent >= 31 && $percent <= 40 )  { $percent = 14; }
		elseif ( $percent >= 41 && $percent <= 50 )  { $percent = 15; }
		elseif ( $percent >= 51 && $percent <= 60 )  { $percent = 16; }
		elseif ( $percent >= 61 && $percent <= 70 )  { $percent = 17; }
		elseif ( $percent >= 71 && $percent <= 80 )  { $percent = 18; }
		elseif ( $percent >= 81 && $percent <= 90 )  { $percent = 19; }
		elseif ( $percent >= 91 && $percent <= 100 ) { $percent = 20; }
		echo '<span '; if ( $percent == 10 ) { echo 'class="nothing" '; } echo 'style="font-size:' . $percent . 'px;"><a href="' . $current_page . '?b=' . $gotboards->board_shortname . '"'; if ( $the_board && $the_board == $gotboards->board_shortname ) { echo ' class="active"'; } echo '>';
		echo $gotboards->board_name . '</a></span>';
	}
}

echo '<span><a href="' . $this_page . '?a=replies"'; if ( $this_area == 'replies' ) { echo ' class="active"'; } echo '>all replies</a></span>
<span><a href="' . $this_page . '?a=subscribed"'; if ( $this_area == 'subscribed' ) { echo ' class="active"'; } echo '>all subscribed</a></span>
<span><a href="' . $this_page . '?a=following"'; if ( $this_area == 'following' ) { echo ' class="active"'; } echo '>all followed</a></span>';
echo '</div>';
echo '</div>';