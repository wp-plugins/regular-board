<?php 

/**
 * Post Functions
 *
 * (1) Handle post and reply creation
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}
if ( isset ( $_POST['FORMSUBMIT'] ) && !$_REQUEST['_wp_http_referer'] ) {
	die();
}

$archived = 0;
if ( $thisboard ) {
	$the_board = $thisboard;
} else {
	$the_board = esc_sql ( $_REQUEST['board'] );
	$checkboard = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_boards WHERE board_shortname = %s", $the_board ) );
	if ( count( $checkboard ) == 0 ) {
		$timegateactive = true;
	}
}
if ( $_REQUEST['password'] ) {
	$timegateactive = false;
}
if ( $_REQUEST['PARENT'] ) {
	$enteredPARENT = intval ( $_REQUEST['PARENT'] );
	$checkPARENT = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_posts WHERE post_id = %d AND post_public != %d AND post_public = %d", $enteredPARENT, 2, 1 ) );
	foreach ( $checkPARENT as $checked ) {
		$checkTIME = strtotime ( $checked->post_date );
		$currentTIME = strtotime ( $current_timestamp );
		$finalTIME = $currentTIME - $checkTIME;
		if ( $finalTIME > $archive_gate ) {
			$archived = 1;
		}
		if( $checked->post_locked == 1 ) {
			$archived = 1;
		}
	}
} elseif ( !$this_thread ) {
	$enteredPARENT = 0;
}
if ( count( $getuser ) == 0 ) {
	if( $archived == 0 ) {
		if( $timegateactive !== true ) {
			if( $posting == 1 && !$this_thread || $currentCountNomber < $max_replies && $posting == 1 && $this_thread || $this_area == 'post' ) {
				$IS_IT_SPAM = 0;
				if ( function_exists ( 'akismet_admin_init' ) ) {
					$APIKey = get_option ( 'wordpress_api_key' );
					if( $the_board && !$this_thread ){
						$website_url = $current_page . '?b=' . $the_board;
					}
					if( $the_board && $this_thread ) {
						$website_url = $current_page . '?b=' . $the_board . '&amp;t=' . $this_thread;
					}
					$akismet = new Akismet( $website_url, $APIKey );
					if( $akismet->isKeyValid() ) { }else{ echo 'Your API key is NOT valid!'; die(); }
					if ( $_SERVER["REQUEST_METHOD"] == 'POST' ) {
						$akismet = new Akismet( $website_url, $APIKey );
						$akismet->setCommentAuthorEmail( esc_sql( $_REQUEST["EMAIL"] ) );
						$akismet->setCommentAuthorURL( esc_sql( $_REQUEST["URL"] ) );
						$akismet->setCommentContent( esc_sql( $_REQUEST["COMMENT"] ) );
						$akismet->setPermalink( esc_url( $_SERVER["HTTP_REFERER"] ) );
						if ( $akismet->isCommentSpam() ) {
							$IS_IS_SPAM = 1;
						}
					}
				}
				$IS_IT_SPAM = $empty = 0;				
				if ( !$_REQUEST['COMMENT'] && !$URL ) {
					$empty = 1;
				}elseif( $_REQUEST['LINK'] || $_REQUEST['PAGE'] || $_REQUEST['LOGIN'] || $_REQUEST['USERNAME'] ) {
					$wpdb->query(
						$wpdb->prepare(
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
				}elseif ( $IS_IT_SPAM == 1 ) {
					$wpdb->query(
						$wpdb->prepare(
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
							'AKISMET detected you as a spammer',
							0
						)
					);
				} else {
					if ( $IS_IT_SPAM == 0 ) {
						if ( !$this_thread && $enable_url == 1 || $this_thread  && $enable_rep == 1 ) {
							
							if ( !$URL ){
								$cleanURL = sanitize_text_field($_REQUEST['URL']);
							} elseif ( $URL ) {
								$cleanURL = $URL;
							}
							
							$ch = curl_init();
							$opts = array ( 
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_URL => $cleanURL,
								CURLOPT_NOBODY => true,
								CURLOPT_TIMEOUT => 10
							);
							curl_setopt_array ( $ch, $opts );
							curl_exec ( $ch );
							$status = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
							curl_close ( $ch );

							if ( $cleanURL ) {
								$path_info = pathinfo ( $cleanURL );
								if ( preg_match ('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $cleanURL, $match ) ) {
									$VIDEOID = $match[1];																
									$TYPE = 'youtube';
									$URL = sanitize_text_field ( $VIDEOID );
								} elseif ( $status == '200' && getimagesize ( $cleanURL ) !== false ) {
									if ( 
										$path_info['extension'] == 'jpg' ||
										$path_info['extension'] == 'gif' ||
										$path_info['extension'] == 'jpeg' ||
										$path_info['extension'] == 'png'
									){
										$TYPE = 'image';
										$URL = $cleanURL;
									}
								} else {
									$TYPE = 'URL';
									if ( false === strpos ( $cleanURL, '://' ) ) {
										$URL = '//' . $cleanURL;
									} else {
										$URL = esc_url ( $cleanURL );
									}
								}
							} else {
								$TYPE = $URL = '';
							}
						}
						if ( $_REQUEST ['COMMENT'] ) {
							$enteredCOMMENT = $_REQUEST['COMMENT'];
							$checkCOMMENT = $enteredCOMMENT = substr ( $enteredCOMMENT, 0, $max_body );
							$checkCOMMENT = $enteredCOMMENT = sanitize_text_field ( $enteredCOMMENT ) ;
						} else {
							$enteredCOMMENT = '';
						}
						$checkURL = $URL;
						$enteredSUBJECT = sanitize_text_field ( $_REQUEST['SUBJECT'] );
						$enteredSUBJECT = substr( $enteredSUBJECT, 0, $max_text );

						if ( $profilepassword ) {
							$enteredPASSWORD = $profilepassword;
						}
						if ( !$profilepassword ) {
							$enteredPASSWORD = wp_hash ( $random_password ) ;
						}
						
						if ( !$_REQUEST['password'] ) {
							if ( !$enteredCOMMENT && $URL ) {
								$getDuplicate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_posts WHERE post_url = %s AND post_board = %s", $checkURL, $the_board ) );
							}
							if ( $enteredCOMMENT && !$URL ) {
								$getDuplicate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_posts WHERE post_comment = %s AND post_board = %s", $checkCOMMENT, $the_board ) );
							}
							if ( $enteredCOMMENT && $URL ) {
								$getDuplicate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_posts WHERE (post_comment = %s OR post_url = %s) AND post_board = %s", $checkCOMMENT, $checkURL, $the_board ) );
							}
						}
						
						if ( count ( $getDuplicate ) == 0 || $_REQUEST['editthisthread'] ) {
						
							if ( $posting_options ) {
								if ( $_REQUEST['EMAIL'] == 'roll' ) {
									$roll = explode(',',$roll);
									$enteredEMAIL = wp_rand($roll[0],$roll[1]); 
								} elseif ( $_REQUEST['EMAIL'] == strtolower ( 'heaven' ) ) {
									$enteredEMAIL = 'heaven';
									$profileid = '';
									$profile_name = '';
								} elseif ( $_REQUEST['EMAIL'] == strtolower ( 'sage' ) ) {
									$enteredEMAIL = 'sage';
								} else {
									$enteredEMAIL = '';
								}
							} else {						
								$enteredEMAIL = '';
							}
							
							if ( $profileheaven ) {
									$modCode = 0;
							} else {
								if ( $is_moderator ) {
									$modCode = 1;
								} elseif ( $is_user_mod ) {
									$modCode = 2;
								} elseif ( $is_user ) {
									$modCode = 0;
								}
							}
							
							$edited = 0;
							
							if ( $_REQUEST['password'] ) {
								$checkPassword = esc_sql( $_REQUEST['password'] );
								$checkID = intval ( $_REQUEST['editthisthread'] );							
								if ( !$is_moderator ) {
									$checkPass = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_posts WHERE post_password = %s AND post_id = %d", $checkPassword, $checkID ) );
									if ( count ( $checkPass ) > 0 ) {
										foreach ( $checkPass as $Pass ) {
											$last = $Pass->post_last;
											$wpdb->update (
												$regular_board_posts,
												array( 
													'post_title' => $enteredSUBJECT, 'post_comment' => $enteredCOMMENT, 'post_url' => $URL, 'post_type' => $TYPE
												),
												array( 
													'post_id' => $checkID
												),
												array( 
													'%s', '%s', '%s', '%s', '%d'
												)
											);
											$edited = 1;
											$LAST = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_posts WHERE (post_id = %d OR post_parent = %d) AND post_public != %d AND post_public = %d ORDER BY post_id DESC", $checkID, $checkID, 2, 1 ) );
											echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '?b=' . $the_board . '"></p>';
										}
									} else {
										$edited = 3;
										echo 'Wrong password.';
									}
								}
								if ( $is_moderator ) {
									$checkPass = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM $regular_board_posts WHERE post_password = %s AND post_id = %d", $checkPassword, $checkID ) );
									if (count ( $checkPass ) > 0 ) {
										foreach ( $checkPass as $Pass ) {
											$last = $Pass->post_last;
											$wpdb->update ( 
												$regular_board_posts,
												array( 
													'post_title' => $enteredSUBJECT, 'post_comment' => $enteredCOMMENT, 'post_url' => $URL, 'post_type' => $TYPE 
												),
												array( 
													'post_id' => $checkID
												),
												array( 
													'%s', '%s', '%s', '%s', '%s', '%d'
												)
											);
											$edited = 1;
											$LAST = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_posts WHERE (post_id = %d OR post_parent = %d) AND post_public != %d AND post_public = %d ORDER BY post_id DESC", $checkID, $checkID, 2, 1 ) );
											echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '?b=' . $the_board . '"></p>';
										}
									} else {
										$edited = 3;
										echo 'Wrong password.';
									}
								}												
							} elseif ( $timegateactive !== true ) {
								$wpdb->query(
									$wpdb->prepare(
										"INSERT INTO $regular_board_posts 
										(
											post_id, post_parent, post_name, post_date, post_email, post_title, post_comment, post_type, post_url, post_board, post_moderator, post_last, post_sticky, post_locked, post_password, post_userid, post_public, post_report, post_reportcount
										)
										VALUES ( 
											%d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %d, %d, %s, %d, %d, %s, %d
										)",
										'', $enteredPARENT, $profile_name, $current_timestamp, $enteredEMAIL, $enteredSUBJECT, $enteredCOMMENT, $TYPE, $URL, $the_board, $modCode, $current_timestamp, 0, 0, $enteredPASSWORD, $profileid, 1, '', 0
									)
								);
								$wpdb->query ( "UPDATE $regular_board_boards SET board_postcount = board_postcount + 1 WHERE board_shortname = '$the_board'" );
								$checkUserExists = $wpdb->get_row ( $wpdb->prepare ( "SELECT user_id FROM $regular_board_users WHERE user_id = %d", $profileid ) );
								echo '<p class="hidden"><meta http-equiv="refresh" content="0;URL=' . $current_page . '?b=' . $the_board . '"></p>';
							}
							if ( $enteredPARENT && $LOCKED != 1 && strtolower ( $enteredEMAIL ) != 'sage' ) {
								$wpdb->query ( "UPDATE $regular_board_posts SET post_last = '$current_timestamp' WHERE post_id = $enteredPARENT" );
							}
							$wpdb->delete ( 
								$regular_board_posts, 
								array(
									'post_comment' => '', 'post_type' => '', 'post_url' =>''
								),
								array(
									'%s'
								)
							);
						}else{
							if ( count ( $getDuplicate ) > 0 ) {
								$automute = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $regular_board_bans WHERE banned_ip = %d AND banned_message = %s", $user_ip, 'unoriginal' ) );
								if ( count ( $automute ) == 0 ) {
									$mutecount = 5;
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
								if ( count ( $automute ) == 1 ) {
										foreach ( $automute as $mute ) {
											if ( $mute[banned_banned] == 5 ) { $banned_count = 4; }
											if ( $mute[banned_banned] == 4 ) { $banned_count = 3; }
											if ( $mute[banned_banned] == 3 ) { $banned_count = 2; }
											if ( $mute[banned_banned] == 2 ) { $banned_count = 1; }
											$mutecount = $banned_count - 1;
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
								
								echo '<p>' . $mutecount . ' more attempts at submitting unoriginal content before you are auto-muted for 10 minutes.</p>';
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
} elseif ( isset ( $_POST['FORMSUBMIT'] ) && $timegateactive ) { 
	echo 'You can\'t do that yet.'; 
}