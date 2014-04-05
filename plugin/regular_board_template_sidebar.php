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

echo '<div class="piece_form"><div class="form_form">';
if ( $this_area == 'editpost' && $user_exists && $this_thread ) { 
	include ( plugin_dir_path(__FILE__) . '/regular_board_post_edit.php'    ); 
} else {
	if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_post_form.php' ) ) {
		include ( ABSPATH . '/regular_board_child/regular_board_post_form.php' );
	} else {
		include ( plugin_dir_path(__FILE__) . '/regular_board_post_form.php' );
	}	
}
echo '</div>';
						if ( $this_thread && $threadexists == 1 ) {
							echo '<p class="nav_tools">';
							if ( $thisboard ) {
								echo '<a class="load_link" href="' . $current_page . '">Return</a>';
							} elseif ( $the_board ) {
								echo '<a class="load_link" href="' . $current_page . '?b=' . $the_board . '">Return</a>';
							} elseif ( $thread_board ) {
								echo '<a class="load_link" href="' . $current_page . '?b=' . $thread_board . '">Return</a>';
							} else {
								echo '<a class="load_link" href="' . $current_page . '">Return</a>';
							}								
							echo '<a href="#top">Top</a><a class="reload" xdata="' . $this_thread .'" data="' . $current_page . '?t=' . $this_thread . '">Refresh thread</a>
							</p>';
						}
echo '</div>';


if ( $this_area == 'history' && $user_exists || $this_user ) {
	echo '<div class="piece">';
	echo '<strong>';
		if ( $the_profile_name ) { 
			echo $the_profile_name;
		} else {
			echo 'anonymous';
		}
	echo '</strong>';
	echo $the_profile_avatar . $the_profile_slogan . $the_profile_details . $connect_with;
	echo '<hr />';
	if ( count ( $my_friends ) > 0 ) {
		echo 'Connections: ';
		foreach ( $my_friends as $friends ) {
			if ( $friends->friends_connector != $the_profile_name ) {
				$friend_name = sanitize_text_field ( $friends->friends_connector );
			}
			if ( $friends->friends_connectee != $the_profile_name ) {
				$friend_name = sanitize_text_field ( $friends->friends_connectee );
			}
			echo ' <a class="load_link" href="' . $this_page . '?u=' . $friend_name . '">' . $friend_name . '</a> ';
		}
		echo '<hr />';
	}
	$check_friend = 0;
	$check_friend = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_friends WHERE ( friends_connector = '$profile_name' AND friends_connectee = '$the_profile_name' OR friends_connector = '$the_profile_name' AND friends_connectee = '$profile_name')" );
	if ( $user_exists) {
		if ( $the_profile_name ) {
			if ( $profile_name != $the_profile_name ) {
				if ( $check_friend == 0 ) {
					if ( strtolower ( $_REQUEST['request_id'] ) != strtolower ( $profile_name ) ) {
						if ( isset ( $_POST['request_friendship'] ) ) {
							$wpdb->query ( 
								$wpdb->prepare ( 
									"INSERT INTO $regular_board_friends 
									( 
										friends_id, 
										friends_connector, 
										friends_connectee, 
										friends_mutual
									) VALUES ( 
										%d,
										%s,
										%s,
										%d
									)", 
									'', 
									$profile_name,
									$the_profile_name,
									0
								) 
							);
						}
					}
					if ( $the_profile_name ) {
						$connect_with = '
						<form method="post" name="friend_request" class="friendship" action="' . $current_page . '?u=' . $the_profile_name . '">'
						. wp_nonce_field( 'friend_request' ) . 
						'<section><input type="submit" name="request_friendship" id="request_friendship" value="Connect with this user" /></section>
						</form>';
					}
				}
			}
		}
	}
	echo '</div>';
}

$url_data = '';
if     ( $the_board && !$this_thread  ) { $url_data = $current_page . '?b=' . $the_board; }
elseif ( $this_thread ) { $url_data = $current_page . '?t=' . $this_thread; }
elseif ( $this_area ) { $url_data = $current_page . '?a=' . $this_area; }
else   {                  $url_data = $current_page; }
if ( $user_exists ) {
	if ( isset ( $_POST['daymode_activate'] ) ) {
		$wpdb->query ( "UPDATE $regular_board_users SET user_colormode = 1 WHERE user_id = $profileid" );
		echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $url_data . '"></p>';
	}
	if ( isset ( $_POST['nightmode_activate'] ) ) {
		$wpdb->query ( "UPDATE $regular_board_users SET user_colormode = 2 WHERE user_id = $profileid" );
		echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $url_data . '"></p>';
	}
	if ( isset ( $_POST['tinymode_activate'] ) ) {
		$wpdb->query ( "UPDATE $regular_board_users SET user_chanmode = 1 WHERE user_id = $profileid" );
		echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $url_data . '"></p>';
	}
	if ( isset ( $_POST['expandedmode_activate'] ) ) {
		$wpdb->query ( "UPDATE $regular_board_users SET user_chanmode = 2 WHERE user_id = $profileid" );
		echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $url_data . '"></p>';
	}
	echo '<div class="piece"><form class="modes" name="user_mode" method="post" action="' . $current_page . '">';
	wp_nonce_field( 'user_mode' );
	if ( $mode == 'night' ) {
		echo '<input type="submit" value="activate day mode" name="daymode_activate" />';
	}
	if ( $mode == 'day' ) {
		echo '<input type="submit" value="activate night mode" name="nightmode_activate" />';
	}
	if ( $style == 'tiny' ) {
		echo '<input type="submit" value="activate expanded mode" name="expandedmode_activate" />';
	}
	if ( $style == 'expanded' ) {
		echo '<input type="submit" value="activate tiny mode" name="tinymode_activate" />';
	}
	echo '</form></div>';
}
if ( $search_enabled ) {
	$search_action = $current_page;
	echo '<div class="piece"><form name="regular_board_search" class="modes" method="post" action="' . $search_action . '">';
		wp_nonce_field('regular_board_search');
		echo '
		<input type="text" name="regular_board_search" id="regular_board_search" placeholder="Search" />
		<input type="submit" class="hidden" id="regular_board_search_submit" name="regular_board_search_submit" value="Search" />
	</form></div>';
}

if ( !$user_exists && !$userisbanned ) {
	include ( plugin_dir_path(__FILE__) . '/regular_board_loginorregister.php' );
} else { 
	if ( $user_create == 1 ) {
		echo '<span><a href="' . $current_page . '?a=create">[ <i class="fa fa-book"></i> ] Create a new board</a></span>';
	}
}
if ( $board_name ) {
	echo '<div class="piece"><span class="frontinfo">/' . $board_short . '/ ' . $board_name . '</span><p><em>' . $board_description . '</em></p>';
}
if ( $board_rules ) {
	echo regular_board_format ( $board_rules );
}
if ( $board_name ) {
	echo '</div>';
}
if ( get_option ( 'regular_board_frontpage' ) ) {
	echo '<div class="piece"><span class="frontinfo">' . $blog_title . '</span>' . regular_board_format ( wpautop ( get_option ( 'regular_board_frontpage' ) ) ) . '</div>';
}

echo '<div class="piece"><div class="tag_cloud"><span><a href="#">navigation</a></span>';
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
echo '</div></div>';
echo '</div>';