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

$archived       = 0;
$edited         = 0;

$_REQUEST['board']   = sanitize_text_field ( $_REQUEST['board'] );
$_REQUEST['SUBJECT'] = sanitize_text_field ( $_REQUEST['SUBJECT'] );
$_REQUEST['URL']     = sanitize_text_field ( $_REQUEST['URL'] );
$_REQUEST['EMAIL']   = sanitize_text_field ( $_REQUEST['EMAIL'] );
$_REQUEST['COMMENT'] = sanitize_text_field ( $_REQUEST['COMMENT'] );

// Check if board exists before making a post 
if ( $thisboard ) {
	// board exists, set the board for post creation
	$the_board  = $thisboard;
} else {
	$the_board  = $_REQUEST['board'];
	$checkboard = $wpdb->get_results ( "SELECT board_shortname FROM $regular_board_boards WHERE board_shortname = '$the_board' " );
	if ( count ( $checkboard ) == 0 ) {
		// board doesn't exist, don't post anything
		$timegateactive = true;
	}
}

if ( $_REQUEST['password'] ) {
	// Most likely editing a post - we don't neeed flood protection.
	$timegateactive     = false;
}


// Are we making a reply or a new thread?
if ( $_REQUEST['PARENT'] ) {
	// If parent exists in the form, let's check and make sure it exists in the database
	$post_parent        = $_REQUEST['PARENT'];
	$check_parent       = $wpdb->get_results (
							$wpdb->prepare ( 
								"SELECT * FROM $regular_board_posts WHERE 
								post_id = %d AND post_public != %d AND post_public = %d",
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
	}
} elseif ( !$this_thread ) {
	$post_parent   = 0;
}

if ( count ( $getuser ) == 0 ) {
	if ( $archived == 0 ) {
		echo $timegateactive;
		if ( $timegateactive !== true ) {
			if ( $this_area == 'post' ) {
				$IS_IT_SPAM = 0;
				
				// Check for spam
				if ( function_exists ( 'akismet_admin_init' ) ) {
					$APIKey = get_option ( 'wordpress_api_key' );
					if ( $the_board && !$this_thread ) {
						$website_url = $current_page . '?b=' . $the_board;
					}
					if ( $the_board && $this_thread ) {
						$website_url = $current_page . '?b=' . $the_board . '&amp;t=' . $this_thread;
					}
					$akismet = new Akismet ( $website_url, $APIKey );
					if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
						$akismet = new Akismet ( $website_url, $APIKey );
						$akismet->setCommentAuthorEmail ( $_REQUEST['EMAIL'] );
						$akismet->setCommentAuthorURL   ( $_REQUEST['URL'] );
						$akismet->setCommentContent     ( $_REQUEST['COMMENT'] );
						$akismet->setPermalink          ( $website_url );
						if ( $akismet->isCommentSpam() ) {
							$IS_IT_SPAM = 1;
						}
					}
				}
			
				$IS_IT_SPAM = 0;
				$empty      = 0;
				
				if ( !$_REQUEST['COMMENT'] && !$URL ) {
					$empty  = 1;
				} elseif ( $_REQUEST['LINK'] || $_REQUEST['PAGE'] || $_REQUEST['LOGIN'] || $_REQUEST['USERNAME'] ) {
					// Hidden fields have been filled.
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
				} elseif ( $IS_IT_SPAM == 1 ) {
					// Spam detected
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
					if ( !$IS_IT_SPAM ) {
						
						/**
						 * Nothing suspicious detected so far,
						 * proceed with creating the comment.
						 */
						
						// If URLs are enabled, prepare the entered URL
						if ( $_REQUEST['PARENT'] && $enable_rep || !$_REQUEST['PARENT'] && $enable_url ) {
							if ( !$URL ) {
								$clean_url = $_REQUEST['URL'];
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
									$video_id  = $match[1];
									$post_type = 'youtube';
									$post_url  = $video_id;
								} elseif ( $status == '200' && getimagesize ( $clean_url ) !== false ) {
									// image
									if ( 
										$path_info['extension'] == 'jpg'  || 
										$path_info['extension'] == 'gif'  || 
										$path_info['extension'] == 'jpeg' || 
										$path_info['extension'] == 'png'
									) {
										$post_type = 'image';
										$post_url  = $clean_url;
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
							}
						}
						
						// Comment
						if ( $_REQUEST['COMMENT'] ) {
							$post_comment = substr ( $_REQUEST['COMMENT'], 0, $max_body );
						} else {
							$post_comment = '';
						}
						
						// Subject
						if ( $_REQUEST['SUBJECT'] ) {
							$post_subject = substr ( $_REQUEST['SUBJECT'], 0, $max_text );
						} else {
							$post_subject = '';
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
							$get_duplicate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_posts WHERE post_url = %s AND post_board = %s", $post_url, $the_board ) );
						}
						if ( $post_comment && !$post_url ) {
							$get_duplicate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_posts WHERE post_comment = %s AND post_board = %s", $post_comment, $the_board ) );
						}
						if ( $post_comment && $post_url ) {
							$get_duplicate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_posts WHERE (post_comment = %s OR post_url = %s) AND post_board = %s", $post_comment, $post_url, $the_board ) );
						}
						
						if ( count ( $get_duplicate ) == 0 ) {
							// If posting options are enabled
							if ( $posting_options ) {
								if ( $_REQUEST['EMAIL'] == 'roll' ) {
									$roll       = explode ( ',', $roll );
									$post_email = wp_rand ( $roll[0], $roll[1] );
								} elseif ( $_REQUEST['EMAIL'] == strtolower ( 'heaven' ) ) {
									$post_email   = 'heaven';
									$profile_name = 'null';
								} elseif ( $_REQUEST['EMAIL'] == strtolower ( 'sage' ) ) {
									$post_email   = 'sage';
								} else {
									$post_email   = '';
								}
							} else {
								$post_email       = '';
							}
							
							if ( $profileheaven ) {
								$post_email   = 'heaven';
								$profile_name = 'null';
							}
							
							if ( $is_moderator ) {
								$mod_code = 1;
							} elseif ( $is_user_mod ) {
								$mod_code = 2;
							} elseif ( $is_user ) {
								$mod_code = 0;
							} else {
								$mod_code = 0;
							}
							
							// Password was sent with form, we're editing something
							if ( $_REQUEST['password'] ) {
								$check_password = $_REQUEST['password'];
								$check_id       = $_REQUEST['editthisthread'];
								$check_pass     = $wpdb->get_results ( 
													$wpdb->prepare (
														"SELECT * FROM $regular_board_posts WHERE 
														post_password = %s AND post_id = %d", 
														$check_password, 
														$check_id 
													) 
												  );
								if ( count ( $check_pass ) > 0 ) {
									foreach ( $check_pass as $pass ) {
										$last = $pass->post_last;
										$wpdb->update (
											$regular_board_posts,
											array ( 
												'post_title'   => $post_title,
												'post_comment' => $post_comment,
												'post_url'     => $post_url,
												'post_type'    => $post_type
											),
											array ( 
												'post_id'      => $check_id
											),
											array ( 
												'%s', 
												'%s', 
												'%s', 
												'%s', 
												'%d'
											)
										);
										$edited        = 1;
										$update_post   = $wpdb->get_row ( 
															$wpdb->prepare ( 
																"SELECT * FROM $regular_board_posts 
																WHERE ( post_id = %d OR post_parent = %d ) AND post_public != %d AND post_public = %d 
																ORDER BY post_id DESC", 
																$check_id, 
																$check_id, 
																2, 
																1 
															) 
														 );
										echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '?b=' . $the_board . '"></p>';
									}
								} else {
									$edited = 3;
								}
							} elseif ( $timegateactive !== true ) {
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
											post_reportcount
										)
										VALUES ( 
											%d, 
											%d, 
											%s, 
											%s, 
											%s, 
											%s, 
											%s, 
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
											%d
										)",
										'', 
										$post_parent, 
										$profile_name, 
										$current_timestamp, 
										$post_email, 
										$post_subject, 
										$post_comment, 
										$post_type, 
										$post_url, 
										$the_board, 
										$modCode, 
										$current_timestamp, 
										0, 
										0, 
										$post_password, 
										$profileid, 
										1, 
										'', 
										0
									)
								);
								$wpdb->query ( 
									"UPDATE $regular_board_boards SET 
									board_postcount = board_postcount + 1 
									WHERE board_shortname = '$the_board'" 
								);
								echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '?b=' . $the_board . '"></p>';
							}
							
							if ( $entered_parent && !$LOCKED && strtolower ( $enteredEMAIL ) != 'sage' ) {
								$wpdb->query ( "UPDATE $regular_board_posts 
									SET post_last = $current_timestamp 
									WHERE post_id = $entered_parent" 
								);
							}
							// Delete posts that somehow got through with no data
							$wpdb->delete ( 
								$regular_board_posts, 
								array(
									'post_comment' => '', 'post_type' => '', 'post_url' =>''
								),
								array(
									'%s'
								)
							);
						} else {
							if ( count ( $get_duplicate ) > 0 ) {
								$auto_mute = $wpdb->get_row ( 
												$wpdb->prepare ( 
													"SELECT * FROM $regular_board_bans WHERE 
													banned_ip = %d AND banned_message = %s", 
													$user_ip, 
													'unoriginal' 
												) 
											 );
								if ( count ( $auto_mute ) == 0 ) {
									$mute_count = 5;
									$wpdb->query (
										$wpdb->prepare (
											"INSERT INTO $regular_board_bans 
											( 
												banned_id, banned_date, banned_ip, banned_banned, banned_message, banned_length 
											) 
											VALUES ( 
												%d, %s, %s, %d, %s, %s 
											)",
										'', $current_timestamp, $user_ip, 5, 'unoriginal', '10 minutes' 
										)
									);
								}
								if ( count ( $auto_mute ) == 1 ) {
										foreach ( $auto_mute as $mute ) {
											if ( $mute[banned_banned] == 5 ) { $banned_count = 4; }
											if ( $mute[banned_banned] == 4 ) { $banned_count = 3; }
											if ( $mute[banned_banned] == 3 ) { $banned_count = 2; }
											if ( $mute[banned_banned] == 2 ) { $banned_count = 1; }
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
								echo '<p>' . $mute_count . ' more attempts at submitting unoriginal content before you are auto-muted for 10 minutes.</p>';
							}
						}
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