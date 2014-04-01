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

if ( isset ( $_POST['daymode_activate'] ) ) {
	$data = '';
	if     ( $the_board  ) { $data = $current_page . '?b=' . $the_board; }
	elseif ( $this_thread ) { $data = $current_page . '?t=' . $this_thread; }
	else   {                  $data = $current_page; }
	$wpdb->query ( "UPDATE $regular_board_users SET user_colormode = 1 WHERE user_id = $profileid" );
	echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $data . '"></p>';
}
if ( isset ( $_POST['nightmode_activate'] ) ) {
	$data = '';
	if     ( $the_board  ) { $data = $current_page . '?b=' . $the_board; }
	elseif ( $this_thread ) { $data = $current_page . '?t=' . $this_thread; }
	else   {                  $data = $current_page; }
	$wpdb->query ( "UPDATE $regular_board_users SET user_colormode = 2 WHERE user_id = $profileid" );
	echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $data . '"></p>';
}


if ( $user_exists ) {
	echo '<form name="user_mode" method="post" action="' . $current_page . '">';
	wp_nonce_field( 'user_mode' );
	if ( $mode == 'night' ) {
		echo '<input type="submit" value="activate day mode" name="daymode_activate" />';
	}
	if ( $mode == 'day' ) {
		echo '<input type="submit" value="activate night mode" name="nightmode_activate" />';
	}
	echo '</form>';
}

echo '</center><hr />';

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
	<span><a href="' . $current_page . '?'; if ( $the_board ) { echo 'b=' . $the_board . '&amp;'; } echo 'a=submit&amp;self">[ <i class="fa fa-pencil"></i> ] Submit a new text post</a></span>
	<span><a href="' . $current_page . '?'; if ( $the_board ) { echo 'b=' . $the_board . '&amp;'; } echo 'a=submit">[ <i class="fa fa-link"></i> ] Submit a new link</a></span>
	<span><a href="' . $current_page . '?a=create">[ <i class="fa fa-book"></i> ] Create a new board</a></span>
	';
}

echo '<hr />';
if ( $board_name ) {
	echo '<span class="frontinfo">/' . $board_short . '/ ' . $board_name . '</span><p><em>' . $board_description . '</em></p>';
}
if ( $board_rules ) {
	echo regular_board_format ( $board_rules );
}
if ( $the_board ) {
	echo '<hr />';
}

if ( get_option ( 'regular_board_frontpage' ) ) {
	echo '<span class="frontinfo">' . $blog_title . '</span>' . regular_board_format ( wpautop ( get_option ( 'regular_board_frontpage' ) ) );
	echo '<hr />';
}

echo '<div class="tag_cloud"><span><a href="#">navigation</a></span>';
if ( $protocol == 'boards' ) {
	foreach ( $getboards as $gotboards ) {
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

echo '<span><a href="' . $current_page . '?a=replies"'; if ( $this_area == 'replies' ) { echo ' class="active"'; } echo '>all replies</a></span>';
echo '</div>';
echo '</div>';