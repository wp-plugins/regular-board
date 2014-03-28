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

$banner             = '';
if ( $board_banner != '' ) {
	$banner  = '<div class="banner"><img src="' . $board_banner . '" alt="Banner" /></div>';
}
echo $banner;

if ( $this_area == 'history' && $user_exists || $this_user ) {
	echo '<strong>';
		if ( $the_profile_name ) { 
			echo $the_profile_name;
		} else {
			echo 'anonymous';
		}
	echo '</strong>';
	echo $the_profile_avatar . $the_profile_slogan . $the_profile_details . $connect_with;
	echo '<hr />';
}

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
	echo '<hr />
	<div class="tag_cloud">
		<span><a href="' . $this_page . '?'; if ( $the_board ) { echo 'b=' . $the_board . '&amp;'; } echo 'a=submit&amp;self">Submit a new text post</a></span>
		<span><a href="' . $this_page . '?'; if ( $the_board ) { echo 'b=' . $the_board . '&amp;'; } echo 'a=submit">Submit a new link</a></span>
		<span><a href="' . $this_page . '?a=create">Create a new board</a></span>
	</div>';
}

echo '<hr />';
if ( !$the_board ) {
	if ( get_option ( 'regular_board_frontpage' ) ) {
		echo '<span class="frontinfo">Welcome</span>' . regular_board_format ( wpautop ( get_option ( 'regular_board_frontpage' ) ) );
		echo '<hr />';
	}
}
if ( $board_name ) {
	echo '<span class="frontinfo">/' . $board_short . '/ ' . $board_name . '</span><em>' . $board_description . '</em>';
}
if ( $board_rules ) {
	echo regular_board_format ( $board_rules );
}
if ( $the_board ) {
	echo '<hr />';
}
echo '<div class="tag_cloud"><span><a href="#">navigation</a></span>';
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