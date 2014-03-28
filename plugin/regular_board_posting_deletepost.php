<?php 

/**
 * Post actions
 *
 * (1) Handle actions generated by post buttons
 * (1) such as delete, spam, and report.
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}


/**
 * Post deletion
 * Compares the hashed profile password of current user to the password for the post
 * moderators and janitors are allowed to bypass this check
 */

if ( $this_area == 'ban' ) {
	echo '<div id="post_action">';
		if ( $is_moderator ) {
		echo '<form class="regularboard_form" method="post" name="form" action="' . $current_page . '?a=ban&t=' . $this_thread . '">';
		wp_nonce_field('form');
		echo '<label>Reason for ban</label><input type="text" name="reason" placeholder="Reason for ban">';
		echo '</select><input type="submit" name="confirm" value="Reason" /></form>';

		if ( isset ( $_POST['confirm'] ) ) {
			$get_id      = $wpdb->get_var( "SELECT post_userid FROM $regular_board_posts WHERE post_id = $this_thread" );
			$get_id      = intval ( $get_id );
			$get_ip      = $wpdb->get_var( "SELECT user_ip FROM $regular_board_users WHERE user_id = $get_id" );
			$get_ip      = sanitize_text_field ( $get_ip );
			
			$get_content = $wpdb->get_results( "SELECT post_url, post_comment FROM $regular_board_posts WHERE post_id = $this_thread LIMIT 1" );
			foreach ( $get_content as $posts ) {
				$banned_content = '[ ' . $posts->post_url . ' ] ' . ' [ ' . $posts->post_comment . ' ] ';
			}
			
			if ( isset ( $_REQUEST['reason'] ) && $_REQUEST['reason'] ) {
				$banned_message = sanitize_text_field ( $_REQUEST['reason'] );
			} else {
				$banned_message = 'No reason given.';
			}
			
			$wpdb->query (
				$wpdb->prepare (
					"INSERT INTO $regular_board_bans
					(
						banned_id,
						banned_date,
						banned_ip,
						banned_banned,
						banned_message,
						banned_length
					) 
					VALUES (
						%d,
						%s,
						%s,
						%d,
						%s,
						%s
					)",
					'',
					$current_timestamp,
					$get_ip,
					1,
					$banned_message,
					$ban_length_minutes
				)
			);
			$wpdb->query (
				$wpdb->prepare (
					"INSERT INTO $regular_board_logs 
					( 
						logs_id, logs_date, logs_ip, logs_thread, logs_parent, logs_board, logs_message, logs_content
					) 
					VALUES ( 
						%d, %s, %s, %d, %d, %s, %s, %s
					)",
				'', $current_timestamp, $user_ip, 0, 0, '', $banned_message, $banned_content
				)
			);	
		}
	} else {
		echo 'You can\'t access that.';
	}
	echo '</div>';
	
}

if ( $this_area == 'approve' ) {
	if ( $is_moderator || $is_user_mod || $is_user_janitor ) {
		echo '<div id="post_action">';
			$post_status         = 0;
			$post_status         = $wpdb->get_var ( "SELECT post_public FROM $regular_board_posts WHERE post_id = $this_thread AND post_public = 666" );
			$post_userid         = $wpdb->get_var ( "SELECT post_userid FROM $regular_board_posts WHERE post_id = $this_thread AND post_public = 666" );
			$user_posts          = $wpdb->get_var ( "SELECT user_postcount FROM $regular_board_users WHERE user_id = $post_userid" );
			$user_level          = $wpdb->get_var ( "SELECT user_level FROM $regular_board_users WHERE user_id = $post_userid" );
			$user_posts_plus_one = ( $user_posts + 1 );
			if ( !$user_level ) {
				$user_level_plus_one = 1;
			} elseif ( $user_level == 1 ) {
				$user_level_plus_one = $user_level;
			}
			if ( $post_status ) {
				$wpdb->update (
					$regular_board_posts,
					array( 
						'post_public' => 1,
						'post_last'   => $current_timestamp,
						'post_date'   => $current_timestamp
					),
					array( 
						'post_id' => $this_thread
					),
					array( 
						'%d',
						'%s',
						'%s',
						'%d'
					)
				);
				$wpdb->update (
					$regular_board_users,
					array( 
						'user_posts' => $user_posts_plus_one,
						'user_level' => $user_level_plus_one
					),
					array( 
						'user_id' => $post_userid
					),
					array( 
						'%d',
						'%d',
						'%d'
					)
				);
				echo '<p>Post approved.</p>';
			}
		echo '</div>';
	}
}
 
if ( $this_area == 'delete' ) {
	echo '<div id="post_action">';
	$comparedpass = $profilepassword;
	$comparepassword = $wpdb->get_var( "SELECT post_password FROM $regular_board_posts WHERE post_id = $this_thread" );
	if ( $comparepassword == $comparedpass || $is_moderator || $is_user_mod || $is_user_janitor ) {
		echo '<p>Are you sure? <a data="' . $posts->post_id . '" href="' . $current_page . '?a=destroy&amp;t=' . $this_thread . '"> Yes </a> / <a href="' . $current_page . '?t=' . $this_thread . '"> No </a></p>';
	} else {
		echo '<p>You can\'t do that.</p>';
	}
	echo '</div>';
}

/**
 * Post (permanent) deletion
 * Compares the hashed profile password of current user to the password for the post
 * moderators and janitors are allowed to bypass this check
 */
 
if ( $this_area == 'destroy' ) {
	echo '<div id="post_action">';
	$comparedpass = $profilepassword;
	$comparepassword = $wpdb->get_var( "SELECT post_password FROM $regular_board_posts WHERE post_id = $this_thread" );
	if ( $comparepassword == $comparedpass || $is_moderator || $is_user_mod || $is_user_janitor ) {
		
		$grab_board = $wpdb->get_var ( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread" );
		$grab_board = sanitize_text_field ( $grab_board );
		if ( $grab_board ) {
			$postcount  = $wpdb->get_var ( "SELECT board_postcount FROM $regular_board_boards WHERE board_shortname = '$grab_board'" );
			$postcount  = $postcount - 1;
			$wpdb->update (
				$regular_board_boards,
				array( 
					'board_postcount' => $postcount
				),
				array( 
					'board_shortname' => $grab_board
				),
				array( 
					'%d',
					'%s'
				)
			);			
		}
		
		$wpdb->update (
			$regular_board_posts,
			array( 
				'post_title' => '[deleted]',
				'post_name' => 'null',
				'post_comment' => '[deleted]',
				'post_userid' => 0,
				'post_public' => 1,
				'post_type'   => 'post',
				'post_url'    => '',
			),
			array( 
				'post_id' => $this_thread
			),
			array( 
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%d'
			)
		);		
		
		echo '<p>Post deleted.</p>';
		
	} else {
		echo '<p>You can\'t do that.</p>';
	}
	echo '</div>';
}

/**
 * Post undeletion
 * Compares the hashed profile password of current user to the password for the post
 * moderators and janitors are allowed to bypass this check
 */
 
if ( $this_area == 'undelete' ) {
	echo '<div id="post_action">';
	$comparedpass = $profilepassword;
	$comparepassword = $wpdb->get_var( "SELECT post_password FROM $regular_board_posts WHERE post_id = $this_thread" );
	if ( $comparepassword == $comparedpass || $is_moderator || $is_user_mod || $is_user_janitor ) {
		
		$wpdb->update (
			$regular_board_posts,
			array( 
				'post_public' => 1
			),
			array( 
				'post_id' => $this_thread
			),
			array( 
				'%d',
				'%d'
			)
		);
		echo '<p>Post restored.</p>';
		
	} else {
		echo '<p>You can\'t do that.</p>';
	}
	echo '</div>';
}


/**
 * Post move
 * If there is more than one board, this option is "activated" to allow moderators and usermods to move 
 * posts to other boards.
 */
 
elseif ( $this_area == 'move' && $protocol == 'boards' ) {
	echo '<div id="post_action">';
	if ( $is_moderator || $is_user_mod ) {
		
		if ( !isset ( $_POST['confirm'] ) ) {
			if ( count ( $getboards ) > 1 ) {
				echo '<form class="regularboard_form" method="post" name="form" action="' . $current_page . '?a=move&t=' . $this_thread . '">';
				wp_nonce_field('form');
				echo '<select name="move_to" id="move_to">';
				foreach($getboards as $gotBoard){
					if ( $gotBoard->board_shortname ) {
						$board = esc_sql($gotBoard->board_shortname);
						$name  = esc_sql($gotBoard->board_name);
						echo '<option value="'.$board.'">/'.$board.'/ - '.$name.'</option>';
					}
				}
				echo '</select><input type="submit" name="confirm" value="Move" /></form>';
				
			}
		}
		if ( isset ( $_POST['confirm'] ) ) {
			$get_board           = $wpdb->get_var( "SELECT post_board FROM $regular_board_posts WHERE post_id = $this_thread" );
			if ( $get_board ) {
				
				$new_board = sanitize_text_field ( $_REQUEST['move_to'] );
				
				$get_board_count = $wpdb->get_var( "SELECT board_postcount FROM $regular_board_boards WHERE board_shortname = '$get_board'" );
				$get_new_count   = $wpdb->get_var( "SELECT board_postcount FROM $regular_board_boards WHERE board_shortname = '$new_board'" );
				$get_new_count   = ( $get_new_count + 1 );

				$wpdb->update (
					$regular_board_boards,
					array( 
						'board_postcount' => $get_new_count
					),
					array( 
						'board_shortname' => $new_board
					),
					array( 
						'%d',
						'%s'
					)
				);
				
				if ( $get_board_count > 0 ) {
					$new_postcount = ( $get_board_count - 1 );
					$wpdb->update (
						$regular_board_boards,
						array( 
							'board_postcount' => $new_postcount
						),
						array( 
							'board_shortname' => $get_board
						),
						array( 
							'%d',
							'%s'
						)
					);
				}
			}		
		
			$wpdb->update (
				$regular_board_posts,
				array( 
					'post_board' => $_REQUEST['move_to']
				),
				array( 
					'post_id' => $this_thread
				),
				array( 
					'%s',
					'%d'
				)
			);
			$wpdb->update (
				$regular_board_posts,
				array( 
					'post_board' => $_REQUEST['move_to']
				),
				array( 
					'post_parent' => $this_thread
				),
				array( 
					'%s',
					'%d'
				)
			);
			echo '<p>Post moved.</p>';
		}
		
	} else {
		echo '<p>You can\'t access that.</p>';
	}
	echo '</div>';
}

/**
 * Post spam
 * Mark this post as spam.
 */
 
elseif ( $this_area == 'spam' ) {
	echo '<div id="post_action">';
	if ( $is_moderator || $is_user_mod ) {
		$wpdb->update (
			$regular_board_posts,
			array( 
				'post_public' => 2
			),
			array( 
				'post_id' => $this_thread
			),
			array( 
				'%d',
				'%d'
			)
		);
		echo '<p>Post marked as spam.</p>';
	} else {
		echo '<p>You can\'t access that.</p>';
	}
	echo '</div>';
}

/**
 * Post (un)spam
 * (un)spam this post.
 */
 
elseif ( $this_area == 'unspam' ) {
	echo '<div id="post_action">';
	if ( $is_moderator || $is_user_mod ) {
		$wpdb->update (
			$regular_board_posts,
			array( 
				'post_public' => 1
			),
			array( 
				'post_id' => $this_thread
			),
			array( 
				'%d',
				'%d'
			)
		);
		echo '<p>Post no longer marked as spam.</p>';
	} else {
		echo '<p>You can\'t access that.</p>';
	}
	echo '</div>';
}

/**
 * Post lock
 * Lock this post
 */
 
elseif ( $this_area == 'lock' ) {
	echo '<div id="post_action">';
	if ( $is_moderator || $is_user_mod ) {
			$wpdb->update (
				$regular_board_posts,
				array( 
					'post_locked' => 1
				),
				array( 
					'post_id' => $this_thread
				),
				array( 
					'%d',
					'%d'
				)
			);
			echo '<p>Post locked.</p>';

	} else {
		echo '<p>You can\'t access that.</p>';
	}
	echo '</div>';
}

/**
 * Post unlock
 * Unlock this post
 */
 
elseif ( $this_area == 'unlock' ) {
	echo '<div id="post_action">';
	if ( $is_moderator || $is_user_mod ) {
		
			$wpdb->update (
				$regular_board_posts,
				array( 
					'post_locked' => 0
				),
				array( 
					'post_id' => $this_thread
				),
				array( 
					'%d',
					'%d'
				)
			);
			echo '<p>Post unlocked.</p>';

	} else {
		echo '<p>You can\'t access that.</p>';
	}
	echo '</div>';
}

/**
 * Post sticky
 * Make this post sticky
 */
 
elseif ( $this_area == 'sticky' ) {
	echo '<div id="post_action">';
	if ( $is_moderator || $is_user_mod ) {
		
			$wpdb->update (
				$regular_board_posts,
				array( 
					'post_sticky' => 1
				),
				array( 
					'post_id' => $this_thread
				),
				array( 
					'%d',
					'%d'
				)
			);
			echo '<p>Post stickied.</p>';

	} else {
		echo '<p>You can\'t access that.</p>';
	}
	echo '</div>';
}

/**
 * Post unsticky
 * Unstick this post
 */
 
elseif ( $this_area == 'unsticky' ) {
	echo '<div id="post_action">';
	if ( $is_moderator || $is_user_mod ) {
		
			$wpdb->update (
				$regular_board_posts,
				array( 
					'post_sticky' => 0
				),
				array( 
					'post_id' => $this_thread
				),
				array( 
					'%d',
					'%d'
				)
			);
			echo '<p>Post unstickied.</p>';

	} else {
		echo '<p>You can\'t access that.</p>';
	}
	echo '</div>';
}

/**
 * Post reporting
 * Checks if a report already exists in the database before submission to ignore duplicates
 */
 
elseif ( $this_area == 'report' ) {
	echo '<div id="post_action">
	<form class="regularboard_form" name="delete" name="form" method="post" action="' . $current_page . '?a=report&t=' . $this_thread . '" >';
		wp_nonce_field('form');
	echo '<input type="text" name="reason" placeholder="Reason for reporting..." />
	<input type="submit" name="report" value="Report thread" />
	</form>';
	if ( isset ( $_POST['report'] ) && $_REQUEST['reason'] ) {
		$REPORTMESSAGE = esc_sql( $_REQUEST['reason'] );
		$reportthread = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_id = %d", $this_thread ) );
		foreach ( $reportthread as $reported ) {
			$grabexistingreport = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_id = %d", $this_thread ) );
			if ( count ( $grabexistingreport ) > 0 ) {
				foreach ( $grabexistingreport as $existing ) {
					$wpdb->update (
						$regular_board_posts,
						array( 
							'post_report' => $REPORTMESSAGE,
							'post_reportcount' => $existing->post_reportcount + 1
						),
						array( 
							'post_id' => $this_thread
						),
						array( 
							'%s',
							'%d',
							'%d'
						)
					);
					echo '<p>Post reported.</p>';
				}
			}
		}
	}
	echo '</div>';
}

/**
 * Post report dimissing
 * Dismisses a report (completely).
 */
 
elseif ( $this_area == 'dismiss' ) {
	if ( $is_moderator || $is_user_mod ) {
		echo '<div id="post_action">';
		$grabexistingreport = $wpdb->get_results ( $wpdb->prepare ( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_id = %d", $this_thread ) );
		if ( count ( $grabexistingreport ) > 0 ) {
			foreach ( $grabexistingreport as $existing ) {
				$wpdb->update (
					$regular_board_posts,
					array( 
						'post_report' => '',
						'post_reportcount' => 0
					),
					array( 
						'post_id' => $this_thread
					),
					array( 
						'%s',
						'%d',
						'%d'
					)
				);
				echo '<p>Report dimissed.</p>';
			}
		}
	}
	echo '</div>';
}