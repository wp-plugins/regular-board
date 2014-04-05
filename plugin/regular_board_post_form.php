<?php 

/**
 * Post Form
 *
 * (1) Display the posting form
 *
 * @package regular_board
 */

 /**
  *
  * While there is no built-in option panel to do something like, say, disable links on Fridays, or force image-only posts for
  * for Saturdays, you CAN alter the display of the form to only include certain elements on certain days. 
  *
  * if ( date ( 'D' ) === 'Sun' ) { code to execute only on Sundays }
  * if ( date ( 'D' ) === 'Mon' ) { code to execute only on Mondays }
  * if ( date ( 'D' ) === 'Tue' ) { code to execute only on Tuesdays }
  * if ( date ( 'D' ) === 'Wed' ) { code to execute only on Wednesdays }
  * if ( date ( 'D' ) === 'Thu' ) { code to execute only on Thursdays }
  * if ( date ( 'D' ) === 'Fri' ) { code to execute only on Fridays }
  * if ( date ( 'D' ) === 'Sat' ) { code to execute only on Saturdays }
  *
  * A basic example would be the following:
  *     $active = 1 // set the following to be active by default
  *     if ( date ( 'D' ) === 'Sun' ) { $active = 0; } // set $active to 0
  *     if ( $active == 1 ) {
  *         // some block of code that relies on active to be 1
  *         // if active is not 1, then this block of code will not render.
  *     }
  *
  */ 
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( $userisbanned ) {

} else {
	$archived = 0;
	if( $this_thread && $this_area != 'editpost' ) {
		$enteredPARENT = intval ( $this_thread );
		$checkPARENT = $wpdb->get_results ( $wpdb->prepare ( "SELECT post_date FROM $regular_board_posts WHERE post_id = %d AND post_public = %d LIMIT 1", $enteredPARENT, 1 ) );
		foreach ( $checkPARENT as $checked ) {
			$checkTIME = strtotime ( $checked->post_date );
			$currentTIME = strtotime ( $current_timestamp );
			$finalTIME = $currentTIME - $checkTIME;
			if ( $finalTIME > $archive_gate ) {
				$archived = 1;
			}
		}
	}

	if ( $posting == 0 || $archived == 1 || $this_thread && $view_this ) {
		if ( $archived == 1 ) {
			echo '<p>This thread has been archived.  It can no longer be replied to.</p>';
		}
	} else {
		if ( $thisboard ) {
			$the_board = $thisboard;
		} else {
			$the_board = $the_board;
		}
		
		if ( filter_var ( $check_this_ip, FILTER_VALIDATE_IP ) ) { 
			$IPPASS = true; 
		} elseif ( filter_var ( $check_this_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) { 
			$IPPASS = true; 
		} else { 
			$IPPASS = false; 
		}
		if ( $timegateactive ) {
			echo '<p>' . (10 - $timegate) . ' seconds until you can post again.</p>';
		} else {
			if ( $posting != 1 ) { echo '<p>Read-Only Mode</p>';
			} elseif ( $posting == 1 && !$IPPASS ) { 
				echo '<p>You are not permitted to post.</p>';
			} elseif ( $posting == 1 && $IPPASS ) {	
				if($this_thread && $currentCountNomber >= $max_replies){
				}else{
					$LOCKED    = 0;
					if ( $this_thread ) { 
						$checkLOCK = $wpdb->get_results ( $wpdb->prepare ( "SELECT post_id FROM $regular_board_posts WHERE post_locked = %d AND post_id = %d AND post_public = %d LIMIT 1", 1, $this_thread, 1 ) );
						if ( count ( $checkLOCK ) == 1 ) { 
							$LOCKED = 1;
						}
					}
					if ( $LOCKED == 1 ) { 
						echo '<p>Thread locked.</p>';
					}
					if ( $LOCKED == 0){
					$correct = 0;
							if ( $this_area != 'editpost' ) {
							echo '<div id="reply" class="reply">';
							$data               = '';
							$current_page_class = '';
							if     ( $the_board  && !$this_thread ) { $data = $current_page . '?b=' . $the_board; }
							elseif ( $this_thread ) { $data = $current_page . '?t=' . $this_thread; }
							else   {                  $data = $current_page; }
							if ( $this_thread ) {
								$current_page_class = 'omitted' . $this_thread;
							}
							if ( $the_board && !$this_thread ) {
								$current_page_class = 'omitted' . htmlentities ( $the_board );
							}
							if ( $this_area && !$the_board && !$this_thread ) {
								$current_page_class = 'omitted' . $this_area;
							}
							if ( $nothing_is_here ) {
								$current_page_class = 'omitted';
							}
							echo '<form enctype="multipart/form-data" xdata="' . $current_page_class . '" data="' . $data . '" name="regularboard" class="regularboard_form" method="post" action="' . $current_page . '?a=post">';
							wp_nonce_field('regularboard');
							if ( $protocol == 'boards' && $the_board ) {
								echo '<input type="hidden" value="' . $the_board . '" NAME="board" />';
							}
							
							if ( $this_thread ) { 
								echo '<input type="hidden" name="PARENT" value="' . $this_thread . '" />';
							}
							echo '<input type="hidden" value="" name="LINK" />
							<input type="hidden" value="" name="PAGE" />
							<input type="hidden" value="" name="LOGIN" />
							<input type="hidden" value="" name="USERNAME" />';
							
							if ( !$profilepassword ) { 
								if ( $this_area != 'messages' ) {
									$profilepassword = $rand;
								}
							}
							if ( $this_area == 'messages' ) {
								if ( $_GET['message'] ) {
									if ( $message_to && $message_from ) {
										echo '<input type="hidden" value="' . $message_to . '" name="message_to" />
										<input type="hidden" value="' . $message_from . '" name="message_from" />';
									}
								}
							}
							if ( $this_area == 'messages' && !$_GET['message'] ) {
								echo '<label for="user_id">send to</label><input type="text" id="user_id" name="user_id" />';
							}
							if ( !$user_exists ) {
								echo '<label>Posting as <em>guest</em> (all posts require approval)</label>';
							} else {
								if ( strtolower ( $profile_name ) == 'null' ) {
									$profile_name = 'anonymous';
								}
								echo '<label>Posting as ' . $profile_name . '</label>';
							}
							
							if ( $imgurid ) { 
								if ( $this_area != 'messages' ) {
									echo '<label for="img">or upload (replaces URL)</label>
									<input id="img" name="img" size="35" type="file"/>';
								}
							}
							$board_post_to = '';
							$tag_post_to   = '';
							if ( $the_board && !$this_thread && !$the_tag ) {
								$board_post_to = '[[' . $the_board . ']] ';
							}
							if ( $the_tag && !$this_thread ) {
								$tag_post_to = '#' . $the_tag . ' ';
							}
							echo '<textarea id="COMMENT" name="COMMENT">' . $board_post_to . $tag_post_to . '</textarea>';
							if ( $this_area != 'messages' ) {
								echo '<small>[[board]] [[title: my new post!]] ++http://url.tld++ *Example format.* || **new line!** |||| paragraph. #tag</small>';
							}
							echo '<input type="submit" data="' . $current_page . '?a=post" data="' . $current_page . '?a=post" name="FORMSUBMIT" id="FORMSUBMIT" value="';
							if ( $this_thread ) {
								echo 'Reply';
							} else { 
								echo 'Post';
							}
							echo '"/>';									
							echo '</form>
							</div>';
						}
					}
				}
			}
		}
	}
}