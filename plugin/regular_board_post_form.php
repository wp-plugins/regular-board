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

if ( $posting == 0 || $archived == 1 ) {
	if ( $this_area == 'newtopic' ) {
		echo '<p>This board is currently locked.</p>';
	}
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
				if ( !$user_exists ) { } else {
					if ( $this_area != 'editpost' && $the_board && $archived == 0 ) {
						if($correct == 0 && $this_area == 'newtopic' || $correct == 0 && $this_thread && count($getposts) > 0){
							if ( $tlast != 1 ) {
								echo '<div id="reply" class="reply">';
								if ( $this_area == 'newtopic' ) {
									echo '<h1>Submit new topic to ' . $thisboard . '</h1>';
								}
								echo '<form enctype="multipart/form-data" name="regularboard" method="post" action="' . $current_page . '?a=post">';
								wp_nonce_field('regularboard');
								echo '<input type="hidden" value="' . $the_board . '" NAME="board" />';
								if ( $this_thread ) { 
									echo '<input type="hidden" name="PARENT" value="' . $this_thread . '" />';
								}
								echo '<input type="hidden" value="" name="LINK" />
								<input type="hidden" value="" name="PAGE" />
								<input type="hidden" value="" name="LOGIN" />
								<input type="hidden" value="" name="USERNAME" />';
								if ( !$profilepassword ) { 
									$profilepassword = $rand;
								}
								echo '<div class="input-group margin-bottom-sm">
									<label for="SUBJECT">Topic</label>
									<span class="input-group-addon">
										<i class="fa fa-quote-right fa-fw"></i>
									</span>
									<input type="text" id="SUBJECT" maxlength="' . $max_text . '" name="SUBJECT" placeholder="Topic (optional)" />
								</div>
								<div class="input-group margin-bottom-sm">
									<label for="COMMENT">Comment</label>
									<span class="input-group-addon">
										<i class="fa fa-pencil fa-fw"></i>
									</span>
									<textarea id="COMMENT" name="COMMENT" placeholder="Comment"></textarea>
								</div>';
								if ( $enable_url && !$this_thread || $enable_rep && $this_thread ) { 
									echo '<div class="input-group margin-bottom-sm">
										<label for="URL">URL</label>
										<span class="input-group-addon">
											<i class="fa fa-link fa-fw"></i>
										</span>
										<input type="text" id="URL" maxlength="' . $max_text . '" value="" name="URL" placeholder=".jpg,gif,png/youtube/http" />
									</div>';
								}
								if ( $imgurid ) { 
									echo '<div class="input-group margin-bottom-sm">
										<label for="img">Upload (overwrites URL)</label>
										<input id="img" name="img" size="35" type="file"/>
									</div>';
								}
								if ( $posting_options ) {
									echo '<div class="input-group margin-bottom-sm">
										<label for="EMAIL">Posting options</label><select id="EMAIL" name="EMAIL">
										<option value="">...posting options</option>
										<option value="heaven"';if($profileheaven == 1){echo ' selected="selected"';}echo '>Make this post anonymously</option>
										<option value="roll">Make this post and roll the dice</option>';
										if ( $this_thread ) { 
											echo '<option value="sage">Make this post without bumping the thread</option>'; 
										}
									echo '	</select>
									</div>';
								}
								echo '<input type="submit" data="' . $current_page . '?a=post" name="FORMSUBMIT" id="FORMSUBMIT" value="';
									if ( $this_thread ) { 
										echo 'Reply';
									} else { 
										echo 'Post';
									}
									echo '"/>
									</form>
									</div>';
								} else {
									echo '<div id="reply"><p>You were the last poster.  Edit your previous post or wait for a new post to comment further.</p></div>';
								}
							}
						}
					}
				}
			}
		}
	}
}