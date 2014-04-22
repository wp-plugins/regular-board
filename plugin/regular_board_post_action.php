<?php

/**
 * Post form action
 *
 * (1) Handle post and reply creation/editing
 *
 * @package regular_board
 */
 
// die() if referer non-existent or calling file directly
if ( !defined ( 'regular_board_plugin' ) || isset ( $_POST['FORMSUBMIT']) && !$_REQUEST['_wp_http_referer'] ) {
	die();
}

$check_user_last_post = $wpdb->get_results ( 
	$wpdb->prepare ( 
		"SELECT $regular_board_posts_select FROM $regular_board_posts WHERE 
			( 
				post_userid = %d OR post_guestip = %s 
			) 
			ORDER BY post_date DESC LIMIT 1", 
			$profileid, 
			$user_ip 
		) 
	);

if ( count ( $check_user_last_post ) > 0 ) {

	if ( $user_flood ) {
		$user_flood = array ( $user_flood );
		$current_user_check = $current_user->user_login;
	}
	
	foreach( $check_user_last_post as $user_last_post ) {
	
		if ( $user_flood && in_array ( $current_user_check, $user_flood ) || current_user_can ( 'manage_options' ) ) {
			$timegateactive = false;
		} else {
			$time = $user_last_post->post_date;
			$posted_on = strtotime ( $time );
			$currently = strtotime ( $current_timestamp );
			$timegate = $currently - $posted_on;
			if ( $timegate < $flood_gate ) {
				$timegateactive = true;
			}
		}
		
	}
}

if ($user_exists ) {
	if ( $is_moderator ) {
		$mod_code = 1;
	} elseif ( $is_user_mod ) {
		$mod_code = 2;
	} elseif ( $is_user ) {
		$mod_code = 0;
	} else {
		$mod_code = 0;
	}
	$poster_ip = '';
} else {
	$mod_code     = 0;
	$post_public  = 666;
	$post_email   = 'heaven';
	$profile_name = 'null';
	$poster_ip    = $user_ip;
}

if ( $userisbanned ) {

} else {

	if ( isset ( $_REQUEST['user_id'] ) || isset ( $_REQUEST['message_to'] ) ) {
		$check_friends = 0;
		if ( $_REQUEST['user_id'] || $_REQUEST['message_to'] ) {
			if ( $_REQUEST['user_id'] ) {
				$check_to   = sanitize_text_field ( $_REQUEST['user_id'] );
			}
			if ( $_REQUEST['message_to'] ) {
				$check_to = sanitize_text_field ( $_REQUEST['message_to'] );
			}
			$check_friends = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_friends WHERE friends_connector = '$profile_name' AND friends_connectee = '$check_to' AND friends_mutual = 1 LIMIT 1" );
		}
		if ( $check_to == $profile_name ) {
			echo '<div class="thread"><em>You can\'t send a message to yourself.  Go <a href="' . $this_page . '?a=messages">back</a> and enter a new recipient.</em></div>';
		} else {
			if ( $check_friends > 0 ) {
				$message_subject = sanitize_text_field ( $_REQUEST['SUBJECT'] );
				$message_content = esc_sql ( wp_strip_all_tags ( $_REQUEST['COMMENT'] ) );
				$wpdb->query (
					$wpdb->prepare (
						"INSERT INTO $regular_board_messages 
						(
							messages_id,
							messages_date,
							messages_subject,
							messages_content,
							messages_to,
							messages_from,
							messages_read
						)
						VALUES ( 
							%d, 
							%s, 
							%s, 
							%s, 
							%s, 
							%s, 
							%d
						)",
						'', 
						$current_timestamp,
						$message_subject,
						$message_content,
						$check_to,
						$profile_name,
						0
					)
				);
				echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $this_page . '?a=messages"></p>';
			} else {
				echo '<div class="thread"><em>You aren\'t connected with that user, and may not send them messages.  Go <a href="' . $this_page . '?a=messages">back</a> and enter a new recipient.</em></div>';
			}
		}
	
	} else {
		
		$archived      = 0;
		$edited        = 0;
		$check_comment = $_REQUEST['COMMENT'] = esc_sql ( str_replace ( array ( '\\n', '\\r', '\\r\\n' ), '||', $_REQUEST['COMMENT'] ) );

		if ( isset ( $_REQUEST['password'] ) ) {
			if ( $_REQUEST['password'] ) {
				// Most likely editing a post - we don't need flood protection.
				$timegateactive     = false;
			}
		}

		if ( isset ( $_REQUEST['PARENT'] ) ) {
			
			/* Check existence of parent (for reply) */
			if ( $_REQUEST['PARENT'] ) {
				$post_parent        = $_REQUEST['PARENT'];
				$check_parent       = $wpdb->get_results (
										$wpdb->prepare ( 
											"SELECT $regular_board_posts_select FROM $regular_board_posts WHERE 
											post_id = %d AND post_public != %d AND post_public = %d LIMIT 1",
											$post_parent,
											2,
											1
										)
									);
				foreach ( $check_parent as $checked ) {
					$check_time   = strtotime ( $checked->post_date );
					$current_time = strtotime ( $current_timestamp );
					$final_time   = $current_time - $check_time;
					if ( $final_time > $archive_gate ) {
						$archived = 1;
					}
					if ( $checked->post_locked ) {
						$archived = 1;
					}
					$dontbumpthis = '';
					$check_last = $wpdb->get_var ( "SELECT post_userid FROM $regular_board_posts WHERE post_parent = $post_parent ORDER BY post_last DESC LIMIT 1" );
					if ( $check_last == $profileid ) {
						// $tlast = 1;
						$dontbumpthis = 1;
					}
				}
			}
			/* */
			
		} else {
			//** No parent exists, create a new thread. **//
			$post_parent   = 0;
		}

		if ( count ( $getuser ) == 0 ) {
			if ( $archived == 0 ) {
				if ( $timegateactive !== true ) {
					if ( $this_area == 'post' ) {
						$IS_IT_SPAM = 0;
						
					
						$empty      = 0;
						
						if ( !$_REQUEST['COMMENT'] && !$URL ) {
							$empty  = 1;
						} elseif ( $_REQUEST['LINK'] || $_REQUEST['PAGE'] || $_REQUEST['LOGIN'] || $_REQUEST['USERNAME'] ) {
							
							/** Check hidden fields.
							 **	Hidden fields have been filled out; probably by a bot. 
							 ** Ban the sucker.
							 */
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
										%d
									)",
									'',
									$current_timestamp,
									$user_ip,
									1,
									'filling out hidden fields (likely you are a bot)',
									0
								)
							);
							/** What did the bot fill out?
							 ** Take the content of the hidden fields and insert that content 
							 ** (sanitized) into the database.
							 ** And of course permanently ban the bot.
							 */
							$_REQUEST['LINK']     = sanitize_text_field( $_REQUEST['LINK'] );
							$_REQUEST['PAGE']     = sanitize_text_field( $_REQUEST['PAGE'] );
							$_REQUEST['LOGIN']    = sanitize_text_field( $_REQUEST['LOGIN'] );
							$_REQUEST['USERNAME'] = sanitize_text_field( $_REQUEST['USERNAME'] );
							$bot_content          = $_REQUEST['LINK'] . $_REQUEST['PAGE'] . $_REQUEST['LOGIN'] . $_REQUEST['USERNAME'];
							$wpdb->query (
								$wpdb->prepare (
									"INSERT INTO $regular_board_logs 
									( 
										logs_id, logs_date, logs_ip, logs_thread, logs_parent, logs_board, logs_message, logs_content
									) 
									VALUES ( 
										%d, %s, %s, %d, %d, %s, %s, %s
									)",
								'', $current_timestamp, $user_ip, 0, 0, '', 'Permanently banned under suspicion of being a bot.', $bot_content
								)
							);
							
						} elseif ( $IS_IT_SPAM == 1 ) {
							
							/**
							 ** Akismet spam detection
							 ** Permanently ban the IP.
							 */
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
										%d
									)",
									'',
									$current_timestamp,
									$user_ip,
									1,
									'You have been flagged as a spammer',
									0
								)
							);
							
						} else {
						
							if ( !$IS_IT_SPAM && !$tlast ) {
								
								/**
								 * Nothing suspicious detected so far,
								 * proceed with creating the comment.
								 */
								
								// If URLs are enabled, prepare the entered URL
								if ( preg_match ( '/\+\+(.*?)\+\+/', $check_comment, $match ) ) {
									if ( $match[1] ) {
										$match[1] = sanitize_text_field ( $match[1] );
										$clean_url = $match[1];
									}
								} elseif ( $URL ) {
									$clean_url = $URL;
								}
								$ch   = curl_init();
								$opts = array (
									CURLOPT_RETURNTRANSFER => true,
									CURLOPT_URL            => $clean_url,
									CURLOPT_NOBODY         => true,
									CURLOPT_TIMEOUT        => 10
								);
								curl_setopt_array ( $ch, $opts );
								curl_exec ( $ch );
								$status = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
								curl_close ( $ch );
								if ( $clean_url ) {
									$path_info = pathinfo ( $clean_url );
									if ( preg_match ('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $clean_url, $match ) ) {
										// Youtube
										$match[1] = sanitize_text_field ( $match[1] );
										$video_id  = $match[1];
										$post_type = 'youtube';
										$post_url  = $video_id;
									} elseif ( $status == '200' && getimagesize ( $clean_url ) !== false ) {
										// image
										if ( in_array ( $path_info [ 'extension' ], $allowed_types, true ) ) {
											$post_type = 'image';
											$post_url  = $clean_url;
										}
									} else {
										// link
										$post_type = 'URL';
										if ( false === strpos ( $clean_url, '://' ) ) {
											$post_url = '//' . $clean_url;
										} else {
											$post_url = esc_url ( $clean_url );
										}
									}
								} else {
									// none of the above link types
									$post_type = 'post';
									$post_url  = '';
								}
							
								if ( !$enable_url && !$post_parent || !$enable_rep && $post_parent ) {
									$post_type = 'post';
									$post_url  = '';
								}
								
								
								// Comment
								if ( $_REQUEST['COMMENT'] ) {
									$post_comment = substr ( $_REQUEST['COMMENT'], 0, $max_body );
									$test_comment       = str_replace ( array('!heaven','!sage'), '', $post_comment );
									if ( preg_match ( '#\+\+(.*)\+\+#', $post_comment, $match ) ) {
										$test_comment    = str_replace ( '++' . $match[1] . '++', '', $post_comment );
									}
									if ( preg_match ( '#\[\[title:(.*)\]\]#', $post_comment, $match ) ) {
										$test_comment    = str_replace ( '[[title:' . $match[1] . ']]', '', $post_comment );
									}
									if ( preg_match ( '#\[\[(.*?)\]\]#', $post_comment, $match ) ) {
										$test_comment    = str_replace ( '[[' . $match[1] . ']]', '', $post_comment );
									}
									if ( preg_match ( '#\^(.*?)\^#', $post_comment, $match ) ) {
										$test_comment    = str_replace ( '^' . $match[1] . '^', '', $post_comment );
									}
									if ( !$test_comment ) {
										$post_comment = '';
									}
								} else {
									$post_comment = '';
								}

								// Password (if profile password is not present, a random password will be generated for this post
								if ( $profilepassword ) {
									$post_password = $profilepassword;
								} else {
									$post_password = wp_hash ( $random_password );
								}
								
								// Previously, we WERE dismissing duplicates if editing
								// but if we are editing, then it shouldn't be duplicate content, anyway
								// and disabling duplicate check just allowed for abuse
								if ( !$post_comment && $post_url ) {
									$get_duplicate = $wpdb->get_row( $wpdb->prepare( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_url = %s", $post_url ) );
								}
								if ( $post_comment && !$post_url ) {
									$get_duplicate = $wpdb->get_row( $wpdb->prepare( "SELECT $regular_board_posts_select WHERE post_comment = %s", $post_comment ) );
								}
								if ( $post_comment && $post_url ) {
									$get_duplicate = $wpdb->get_row( $wpdb->prepare( "SELECT $regular_board_posts_select FROM $regular_board_posts WHERE (post_comment = %s AND post_url = %s)", $post_comment, $post_url ) );
								}
								$duplicate_count = 0;
								if ( !isset ( $_REQUEST['password'] ) ) {
									if ( count ( $get_duplicate ) > 0 ) {
										$duplicate_count++;
									}
								}
								if ( $duplicate_count == 0 ) {
									// If posting options are enabled
									if ( $profileheaven ) {
										$post_email   = 'heaven';
										$profile_name = 'null';
									}
									$sage_this        = '';
									if ( strpos ( $check_comment, '!sage' ) !== false ) {
										$sage_this    = 1;
									}
									if ( strpos ( $check_comment, '!heaven' ) !== false ) {
										$post_email   = 'heaven';
										$profile_name = 'null';
									}
									if ( preg_match ( '/\[\[title\:(.*?)\]\]/', $check_comment, $match ) ) {
										$match_count++;
										if ( $match[1] ) {
											$match[1] = sanitize_text_field ( $match[1] );
											$post_subject = $match[1];
										} else {
											$match[$match_count] = '';
										}
									}
									if ( $post_parent == 0 ) {
										if ( preg_match ( '/\[\[(.*?)\]\]/', $check_comment, $match ) ) {
											if ( $match[1] ) {
												$match[1] = sanitize_text_field ( $match[1] );
												$checkboard = $wpdb->get_results ( "SELECT board_shortname FROM $regular_board_boards WHERE board_shortname = '$match[1]' " );
												if ( count ( $checkboard ) > 0 ) {
													$the_board    = esc_sql ( $match[1] );
												} else {
													$the_board    = $the_board;
													$post_comment = $post_comment;
												}
											}
										}
									} else {
										$post_parent = intval ( $post_parent );
										$the_board = $wpdb->get_var ( "SELECT post_board FROM $regular_board_posts WHERE post_id = $post_parent ");
									}
									if ( $_REQUEST['COMMENTPARENT'] ) {
										$post_comment_parent = sanitize_text_field ( intval ( $_REQUEST['COMMENTPARENT'] ) );
									} else {
										if ( preg_match ( '/\^(.*?)\^/', $check_comment, $parent_comment ) ) {
											if ( $parent_comment[1] ) {
												$post_comment_parent = intval ( $parent_comment[1] );
											} 
										}									
									}
									if ( $post_parent == $post_comment_parent ) {
										$post_comment_parent = 0;
									}									
									
									// Password was sent with form, we're editing something
									if ( isset ( $_REQUEST['password'] ) ) {
										if ( $_REQUEST['password'] ) {
											$check_password = $_REQUEST['password'];
											$check_id       = $_REQUEST['editthisthread'];
											$check_pass     = $wpdb->get_results ( 
																$wpdb->prepare (
																	"SELECT $regular_board_posts_select FROM $regular_board_posts WHERE 
																	post_password = %s AND post_id = %d", 
																	$check_password, 
																	$check_id 
																) 
															  );
											if ( count ( $check_pass ) > 0 ) {
												foreach ( $check_pass as $pass ) {
													if ( $pass->post_parent > 0 ) {
														$return_to = $pass->post_parent;
													}
													if ( !$pass->post_parent ) {
														$return_to = $check_id;
													}
													$last = $pass->post_last;
													if ( preg_match ( '#\+\+(.*)\+\+#', $post_comment, $match ) ) {
														$post_comment = str_replace ( '++' . $match[1] . '++', '', $post_comment );
													}
													if ( preg_match ( '#\[\[title:(.*)\]\]#', $post_comment, $match ) ) {
														$post_comment = str_replace ( '[[title:' . $match[1] . ']]', '', $post_comment );
													}
													if ( preg_match ( '#\[\[(.*?)\]\]#', $post_comment, $match ) ) {
														$post_comment = str_replace ( '[[' . $match[1] . ']]', '', $post_comment );
													}													
													$wpdb->update (
														$regular_board_posts,
														array ( 
															'post_board'          => $the_board,
															'post_name'           => $profile_name,
															'post_title'          => $post_subject,
															'post_comment'        => $post_comment,
															'post_url'            => $post_url,
															'post_type'           => $post_type,
															'post_comment_parent' => $post_comment_parent,
														),
														array ( 
															'post_id'      => $check_id
														),
														array ( 
															'%s',
															'%s',
															'%s', 
															'%s', 
															'%s', 
															'%s', 
															'%d',
															'%d'
														)
													);
													$edited        = 1;
													$update_post   = $wpdb->get_row ( 
																		$wpdb->prepare ( 
																			"SELECT $regular_board_posts_select FROM $regular_board_posts 
																			WHERE ( post_id = %d OR post_parent = %d ) AND post_public != %d AND post_public = %d 
																			ORDER BY post_id DESC", 
																			$check_id, 
																			$check_id, 
																			2, 
																			1 
																		) 
																	 );
													// Delete posts that somehow got through with no data
													$wpdb->delete (
														$regular_board_posts, 
														array(
															'post_comment' => ''
														),
														array(
															'%s'
														)
													);
												echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '?t=' . $check_id . '"></p>';
												}
											} else {
												$edited = 3;
											}
										}
									} elseif ( $timegateactive !== true ) {
										if ( !$profile_posts ) {
											$post_public = 666;
											$first_post  = 1;
										} else {
											$post_public = 1;
											$first_post  = 0;
										}
										if ( preg_match ( '#\+\+(.*)\+\+#', $post_comment, $match ) ) {
											$post_comment = str_replace ( '++' . $match[1] . '++', '', $post_comment );
										}
										if ( preg_match ( '#\[\[title:(.*)\]\]#', $post_comment, $match ) ) {
											$post_comment = str_replace ( '[[title:' . $match[1] . ']]', '', $post_comment );
										}
										if ( preg_match ( '#\[\[(.*?)\]\]#', $post_comment, $match ) ) {
											$post_comment = str_replace ( '[[' . $match[1] . ']]', '', $post_comment );
										}
										$wpdb->query (
											$wpdb->prepare (
												"INSERT INTO $regular_board_posts 
												(
													post_id, 
													post_parent, 
													post_name, 
													post_date, 
													post_email, 
													post_title, 
													post_comment, 
													post_comment_parent,
													post_type, 
													post_url, 
													post_board, 
													post_moderator, 
													post_last, 
													post_sticky, 
													post_locked, 
													post_password, 
													post_userid, 
													post_public, 
													post_report, 
													post_reportcount,
													post_reply_count,
													post_guestip
												)
												VALUES ( 
													%d, 
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
													%s, 
													%d, 
													%d, 
													%s, 
													%d, 
													%d, 
													%s, 
													%d,
													%d,
													%s
												)",
												'', 
												$post_parent, 
												$profile_name, 
												$current_timestamp, 
												$post_email, 
												$post_subject, 
												$post_comment, 
												$post_comment_parent,
												$post_type, 
												$post_url, 
												$the_board, 
												$mod_code, 
												$current_timestamp, 
												0, 
												0, 
												$post_password, 
												$profileid, 
												$post_public, 
												'', 
												0,
												0,
												$poster_ip
											)
										);
										
										if ( !$first_post ) {
											$wpdb->update (
												$regular_board_users,
												array( 
													'user_posts' => $profile_posts_up
												),
												array( 
													'user_id' => $profileid
												),
												array( 
													'%d'
												)
											);
											if ( strpos ( $profile_posts_check, '.' ) === false  ) {
												$wpdb->update (
													$regular_board_users,
													array( 
														'user_level' => $profile_level_up
													),
													array( 
														'user_id' => $profileid
													),
													array( 
														'%d'
													)
												);								
											}
										}
										
										if ( $post_parent ) {
											$wpdb->query ( 
												"UPDATE $regular_board_posts SET 
												post_reply_count = post_reply_count + 1 
												WHERE post_id = $post_parent" 
											);
										}
										if ( $the_board ) {
											$wpdb->query ( 
												"UPDATE $regular_board_boards SET 
												board_postcount = board_postcount + 1 
												WHERE board_shortname = '$the_board'"
											);										
										}
										
										if ( $first_post ) {
											echo '<div class="thread"><p>Since this is your first time posting, a moderator will need to approve it before it appears.  Once you have had 
											a post approved by a moderator, you will be able to post without moderation approval.</p></div>';
										} else {
											if ( isset ( $_REQUEST['PARENT'] ) ) {
												echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '?t=' . intval( $_REQUEST['PARENT']) . '"></p>';
											} elseif ( !isset ( $_REUEST['PARENT'] ) ) {
												echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '"></p>';
											} elseif ( $the_board ) {
												echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '?b=' . $the_board . '"></p>';
											} elseif ( $thread_board ) {
												echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '?b=' . $thread_board . '"></p>';
											} else {
												echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '"></p>';
											}
										}
									}
									
									if ( !$dontbumpthis ) {
										if ( $post_parent && !$LOCKED && strtolower ( $post_email ) != 'sage' || $sage_this ) {
											$wpdb->update (
												$regular_board_posts,
												array ( 
													'post_last'   => $current_timestamp
												),
												array ( 
													'post_id'      => $post_parent
												),
												array ( 
													'%s', 
													'%d'
												)
											);
										}
									}
									// Delete posts that somehow got through with no data
									$wpdb->delete (
										$regular_board_posts, 
										array(
											'post_comment' => ''
										),
										array(
											'%s'
										)
									);

								} else {
									if ( $duplicate_count > 0 ) {
										$auto_mute = $wpdb->get_results ( "SELECT $regular_board_bans_select FROM $regular_board_bans WHERE banned_ip = '$user_ip' AND banned_message = 'unoriginal' LIMIT 1 " );

										if ( count ( $auto_mute ) == 0 ) {
											$mute_count = 5;
											$wpdb->update (
												$regular_board_users,
												array( 
													'user_strikes' => $profile_strikes_up
												),
												array( 
													'user_id' => $profileid
												),
												array( 
													'%d'
												)
											);
											
											$duplicate_content = '[ ' . $post_url . ' ] [ ' . $post_comment . ' ]';
											
											$wpdb->query (
												$wpdb->prepare (
													"INSERT INTO $regular_board_logs 
													( 
														logs_id, logs_date, logs_ip, logs_thread, logs_parent, logs_board, logs_message, logs_content
													) 
													VALUES ( 
														%d, %s, %s, %d, %d, %s, %s, %s
													)",
												'', $current_timestamp, $user_ip, 0, 0, '', 'Auto-muted for attempting to submit duplicate content.', $duplicate_content
												)
											);
											
											$wpdb->query (
												$wpdb->prepare (
													"INSERT INTO $regular_board_bans 
													( 
														banned_id, banned_date, banned_ip, banned_banned, banned_message, banned_length 
													) 
													VALUES ( 
														%d, %s, %s, %d, %s, %s 
													)",
												'', $current_timestamp, $user_ip, 5, 'unoriginal', $ban_length_minutes 
												)
											);
										}
										if ( count ( $auto_mute ) == 1 ) {
												foreach ( $auto_mute as $mute ) {
													if ( $mute->banned_banned == 5 ) { $banned_count = 4; }
													if ( $mute->banned_banned == 4 ) { $banned_count = 3; }
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
										echo '<div class="thread"><p>' . $mute_count . ' more attempts at submitting unoriginal content before you are auto-muted for ' . $ban_length_minutes . '.</p></div>';
									}
								}
							} else {
								echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '"></p>';
							}
						}
						if ( $empty > 0 ) {
							echo '<p>Nothing submitted.</p>';
						}
					} else {
						echo '<p>Nothing submitted.</p>';
					}
				}
			}
		} elseif ( isset ( $_POST['FORMSUBMIT'] ) && $timegateactive === true ) { 
			echo 'You can\'t do that yet.'; 
		}
	}
}