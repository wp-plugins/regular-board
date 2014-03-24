<?php 

/**
 * Post History for Current User
 *
 * (1) Get the user's post history
 * (2) Posts will show up in history if they weren't posted as anonymous ( by option, not name )
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}
if ( $this_area == 'history' ) {
	$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_id = %d LIMIT 1", $profileid ) );
}
if ( $this_user ) {
	$usprofile = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_users WHERE user_name = %s LIMIT 1", $this_user ) );
}

$the_profile_name    = '';
$the_profile_avatar  = '';
$the_profile_slogan  = '';
$the_profile_details = '';
$connect_with        = '';
if ( count ( $usprofile ) > 0 ) {
	foreach ( $usprofile as $theprofile ) {
			
			if ( $theprofile->user_name ) {
				$the_profile_name = sanitize_text_field ( $theprofile->user_name );
			}
			if ( $theprofile->user_avatar ) {
				if ( $theprofile->user_avatar != 'NULL' ) {
					$the_profile_avatar = '<img src="' . $theprofile->user_avatar . '" class="imageFULL" />';
				}
			}
			if ( $theprofile->user_slogan ) {
				if ( $theprofile->user_slogan != 'NULL' ) {
					$the_profile_slogan = '<p><em>' . str_replace ( '\\', '', $theprofile->user_slogan ) . '</em></p>';
				}
			}
			$the_profile_details = '<p>level ' . $theprofile->user_level . '<br />active posts: ' . $totalpages . ' /
			total posts: ' . $theprofile->user_posts . ' <br />
			 member for ' . str_replace ( 'ago', '', regular_board_timesince ( $theprofile->user_date ) ) . ' </p>';
			
			
			echo '<div class="profile_deets">';


			if ( count ( $my_friends ) > 0 ) {
				echo '<hr />Connections: ';
				foreach ( $my_friends as $friends ) {
					if ( $friends->friends_connector != $the_profile_name ) {
						$friend_name = sanitize_text_field ( $friends->friends_connector );
					}
					if ( $friends->friends_connectee != $the_profile_name ) {
						$friend_name = sanitize_text_field ( $friends->friends_connectee );
					}
					echo ' [ <a href="' . $this_page . '?u=' . $friend_name . '">' . $friend_name . '</a> ] ';
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
		if ( $totalpages ) {
			foreach ( $getposts as $posts ) {
				if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
					include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
				} else {
					include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
				}
			}
		} else {
			echo '<div class="thread"><center><em>nothing to see here.</em></center></div>';
		}
	}
}

include ( plugin_dir_path(__FILE__) . '/regular_board_paging.php' );