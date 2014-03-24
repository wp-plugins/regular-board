<?php 

/**
 * Registration/login
 *
 * (1) Logs in the user or registers them if password/username combination do not exist.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}



if ( isset ( $_REQUEST['password'] ) && $_REQUEST['password'] ) { $password       = sanitize_text_field ( wp_hash ( $_REQUEST['password'] ) ); }
if ( isset ( $_REQUEST['email'] ) && $_REQUEST['email'] )       { $username       = sanitize_text_field ( wp_hash ( $_REQUEST['email'] ) ); }
echo '<div id="reply" class="reply">';

	
	echo '<form enctype="multipart/form-data" name="i_want_to_log_in" method="post" action="' . $current_page . '">';
	wp_nonce_field('i_want_to_log_in');
	echo '<label for="email">username (will not be displayed)</label><input type="text" id="email" name="email" />';
	echo '<label for="password">password</label><input type="password" id="password" name="password"  />';
	if ( $registration_open ) {
		echo '<input type="submit" name="i_want_to_log_in" value="Sign-in" />
		<input type="submit" name="i_dont_want_to_sign_up" value="Click to start posting" />';
	} else {
		echo '<input type="submit" name="i_want_to_log_in" value="Sign-in" />';
	}

	
	echo '</form>';
	if ( isset ( $_POST['i_dont_want_to_sign_up'] ) ) {
			if ( $check_ammount < $accounts_per_ip ) {
			$username = '';
			$password = '';
			if ( $registration_open ) {
				$wpdb->query ( 
					$wpdb->prepare ( 
						"INSERT INTO $regular_board_users 
							( 
								user_id, 
								user_date, 
								user_ip, 
								user_name, 
								user_email, 
								user_password, 
								user_heaven, 
								user_boards, 
								user_follow, 
								user_avatar,
								user_posts,
								user_level,
								user_strikes,
								user_logged_in,
								user_logged_in_from
							) VALUES ( 
								%d, 
								%s, 
								%s, 
								%s, 
								%s, 
								%s, 
								%d, 
								%s, 
								%s,
								%s,
								%d,
								%d,
								%d,
								%d,
								%s
							)", 
						'', 
						$current_timestamp, 
						$user_ip, 
						'', 
						$username, 
						$password, 
						0, 
						'', 
						'', 
						'',
						0,
						0,
						0,
						1,
						$user_ip
					) 
				);
				echo '<meta http-equiv="refresh" content="0;' . $this_page . '?a=options">';
			}
		} else {
			echo '<hr /><p class="information">You have too many accounts associated with this IP address.</p>';
		}
	}
	if ( isset ( $_POST['i_want_to_log_in'] ) && isset ( $_REQUEST['password'] ) && $_REQUEST['email'] && isset ( $_REQUEST['email'] ) && $_REQUEST['password'] ) {
		$check_username = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_users WHERE user_email = '$username' " );
		if ( $check_username ) {
			$check_password = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_users WHERE user_email = '$username' AND user_password = '$password' " );
		}
		if ( $check_username ) {
			if ( $check_password ) {
				$wpdb->update (
					$regular_board_users,
					array ( 
						'user_logged_in'      => 1,
						'user_logged_in_from' => $user_ip
					),
					array ( 
						'user_email'      => $username,
						'user_password'  => $password
					),
					array ( 
						'%d', 
						'%s', 
						'%s', 
						'%s'
					)
				);
				echo '<meta http-equiv="refresh" content="0">';
			} else {
				echo '<p><center><small>Bad password attempt.  This has been recorded.</small></center></p>';
				$login_limit = $wpdb->get_results ( "SELECT * FROM $regular_board_bans WHERE banned_ip = '$user_ip' AND banned_message = 'bad password' LIMIT 1 " );
				if ( count ( $login_limit ) == 0 ) {
					$mute_count = 3;
					$wpdb->query (
						$wpdb->prepare (
							"INSERT INTO $regular_board_bans 
							( 
								banned_id, banned_date, banned_ip, banned_banned, banned_message, banned_length 
							) 
							VALUES ( 
								%d, %s, %s, %d, %s, %s 
							)",
						'', $current_timestamp, $user_ip, 3, 'bad password', '10 minutes' 
						)
					);
				}
				if ( count ( $login_limit ) > 0 ) {
					foreach ( $login_limit as $mute ) {
						if ( $mute->banned_banned == 3 ) { $banned_count = 2; }
						if ( $mute->banned_banned == 2 ) { $banned_count = 1; }
						$mute_count = $banned_count - 1;
						$wpdb->update (
							$regular_board_bans,
							array( 
								'banned_banned' => $banned_count
							),
							array( 
								'banned_ip' => $user_ip
							),
							array( 
								'%s'
							)
						);
					}
				}
			}
		}
	} 
echo '</div>';